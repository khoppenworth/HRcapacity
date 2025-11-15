<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireSection;
use App\Models\QuestionnaireVersion;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuestionnaireController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);

        $questionnaires = Questionnaire::with('versions')
            ->where('tenant_id', $tenant->id)
            ->get();

        return new JsonResponse($questionnaires);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);

        $data = $this->validateQuestionnairePayload($request);

        $questionnaire = DB::transaction(function () use ($tenant, $data) {
            $questionnaire = Questionnaire::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            $version = $questionnaire->versions()->create([
                'version_number' => 1,
                'status' => 'draft',
            ]);

            $this->syncSectionsAndItems($version, $data['sections'] ?? []);

            return $questionnaire->load('versions.sections.items');
        });

        return new JsonResponse($questionnaire, 201);
    }

    public function show(Questionnaire $questionnaire): JsonResponse
    {
        $this->authorizeTenant($questionnaire);

        $questionnaire->load('versions.sections.items');

        return new JsonResponse($questionnaire);
    }

    public function update(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorizeTenant($questionnaire);

        $data = $this->validateQuestionnairePayload($request, false);

        $questionnaire = DB::transaction(function () use ($questionnaire, $data) {
            $questionnaire->fill([
                'name' => $data['name'] ?? $questionnaire->name,
                'description' => $data['description'] ?? $questionnaire->description,
            ])->save();

            $draftVersion = $questionnaire->versions()->where('status', 'draft')->first();

            if (! $draftVersion) {
                abort(422, 'No draft version available for editing.');
            }

            if (isset($data['sections'])) {
                $draftVersion->sections()->delete();
                $draftVersion->items()->delete();
                $this->syncSectionsAndItems($draftVersion, $data['sections']);
            }

            return $questionnaire->load('versions.sections.items');
        });

        return new JsonResponse($questionnaire);
    }

    public function publish(Questionnaire $questionnaire): JsonResponse
    {
        $this->authorizeTenant($questionnaire);

        $draftVersion = $questionnaire->versions()->where('status', 'draft')->first();

        if (! $draftVersion) {
            abort(422, 'Questionnaire has no draft version to publish.');
        }

        $draftVersion->status = 'published';
        $draftVersion->valid_from = now();
        $draftVersion->save();

        return new JsonResponse($draftVersion->fresh('sections.items'));
    }

    private function authorizeTenant(Questionnaire $questionnaire): void
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);

        abort_if($questionnaire->tenant_id !== $tenant->id, 404);
    }

    private function validateQuestionnairePayload(Request $request, bool $requireName = true): array
    {
        $rules = [
            'name' => [$requireName ? 'required' : 'sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sections' => ['sometimes', 'array'],
            'sections.*.title' => ['required_with:sections', 'string', 'max:255'],
            'sections.*.order_index' => ['nullable', 'integer'],
            'sections.*.items' => ['required_with:sections.*', 'array'],
            'sections.*.items.*.type' => ['required', 'in:likert,boolean,text'],
            'sections.*.items.*.code' => ['required', 'string', 'max:50'],
            'sections.*.items.*.text' => ['required', 'string'],
            'sections.*.items.*.help_text' => ['nullable', 'string'],
            'sections.*.items.*.is_required' => ['boolean'],
            'sections.*.items.*.order_index' => ['nullable', 'integer'],
            'sections.*.items.*.weight_percent' => ['nullable', 'numeric', 'min:0'],
        ];

        return Validator::validate($request->all(), $rules);
    }

    private function syncSectionsAndItems(QuestionnaireVersion $version, array $sections): void
    {
        foreach ($sections as $sectionData) {
            $section = QuestionnaireSection::create([
                'questionnaire_version_id' => $version->id,
                'title' => $sectionData['title'],
                'order_index' => $sectionData['order_index'] ?? 0,
            ]);

            foreach ($sectionData['items'] as $itemData) {
                QuestionnaireItem::create([
                    'questionnaire_version_id' => $version->id,
                    'section_id' => $section->id,
                    'type' => $itemData['type'],
                    'code' => $itemData['code'],
                    'text' => $itemData['text'],
                    'help_text' => $itemData['help_text'] ?? null,
                    'is_required' => $itemData['is_required'] ?? false,
                    'order_index' => $itemData['order_index'] ?? 0,
                    'weight_percent' => $itemData['weight_percent'] ?? null,
                ]);
            }
        }
    }
}
