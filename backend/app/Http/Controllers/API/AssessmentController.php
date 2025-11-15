<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentResponse;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireVersion;
use App\Models\Tenant;
use App\Services\AssessmentScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AssessmentController extends Controller
{
    public function __construct(private AssessmentScoringService $scoringService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);
        $user = $request->user();

        $assessments = Assessment::with(['questionnaireVersion.questionnaire', 'workFunction'])
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return new JsonResponse($assessments);
    }

    public function createOrFetch(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);
        $user = $request->user();

        $data = Validator::validate($request->all(), [
            'work_function_id' => ['required', 'integer'],
            'questionnaire_version_id' => ['required', 'integer'],
            'performance_period' => ['required', 'string', 'max:50'],
        ]);

        $version = QuestionnaireVersion::with('questionnaire')
            ->findOrFail($data['questionnaire_version_id']);
        abort_if($version->questionnaire->tenant_id !== $tenant->id, 404);

        $assessment = Assessment::firstOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'work_function_id' => $data['work_function_id'],
            'questionnaire_version_id' => $version->id,
            'performance_period' => $data['performance_period'],
        ], [
            'status' => 'draft',
        ]);

        return $this->show($request, $assessment);
    }

    public function show(Request $request, Assessment $assessment): JsonResponse
    {
        $this->authorizeAssessment($assessment, $request->user());

        $assessment->load([
            'questionnaireVersion.sections.items',
            'responses',
            'workFunction',
        ]);

        return new JsonResponse($assessment);
    }

    public function saveDraft(Request $request, Assessment $assessment): JsonResponse
    {
        $this->authorizeAssessment($assessment, $request->user());

        $data = $this->validateResponses($request);

        DB::transaction(function () use ($assessment, $data) {
            $this->syncResponses($assessment, $data['responses'] ?? []);
        });

        return $this->show($request, $assessment);
    }

    public function submit(Request $request, Assessment $assessment): JsonResponse
    {
        $this->authorizeAssessment($assessment, $request->user());

        $data = $this->validateResponses($request);

        $version = $assessment->questionnaireVersion()->with('items')->first();

        DB::transaction(function () use ($assessment, $data, $version) {
            $responses = $this->syncResponses($assessment, $data['responses'] ?? [], true);

            $score = $this->scoringService->calculateScorePercent($version, collect($responses));

            $assessment->status = 'submitted';
            $assessment->score_percent = $score;
            $assessment->submitted_at = now();
            $assessment->save();
        });

        return $this->show($request, $assessment);
    }

    private function validateResponses(Request $request): array
    {
        return Validator::validate($request->all(), [
            'responses' => ['required', 'array'],
            'responses.*.questionnaire_item_id' => ['required', 'integer'],
            'responses.*.raw_value' => ['nullable', 'string'],
            'responses.*.numeric_value' => ['nullable', 'numeric'],
        ]);
    }

    private function syncResponses(Assessment $assessment, array $responses, bool $enforceRequired = false): array
    {
        $version = $assessment->questionnaireVersion()->with('items')->first();
        $items = $version->items;

        $responseCollection = collect($responses)->keyBy('questionnaire_item_id');

        if ($enforceRequired) {
            foreach ($items as $item) {
                if ($item->is_required && ! $responseCollection->has($item->id)) {
                    abort(422, sprintf('Missing response for required item %s', $item->code));
                }
            }
        }

        $persisted = [];

        foreach ($items as $item) {
            $responseData = $responseCollection->get($item->id);

            if (! $responseData) {
                continue;
            }

            $payload = [
                'raw_value' => $responseData['raw_value'] ?? null,
                'numeric_value' => $responseData['numeric_value'] ?? null,
            ];

            if ($item->type === 'likert' && isset($payload['numeric_value'])) {
                $payload['numeric_value'] = (float) max(min($payload['numeric_value'], 5), 1);
                $payload['raw_value'] = (string) $payload['numeric_value'];
            }

            if ($item->type === 'boolean' && isset($payload['raw_value'])) {
                $payload['raw_value'] = in_array(strtolower($payload['raw_value']), ['1', 'true', 'yes'], true) ? '1' : '0';
                $payload['numeric_value'] = $payload['raw_value'] === '1' ? 1.0 : 0.0;
            }

            $response = AssessmentResponse::updateOrCreate([
                'assessment_id' => $assessment->id,
                'questionnaire_item_id' => $item->id,
            ], $payload);

            $persisted[] = $response->toArray();
        }

        return $persisted;
    }

    private function authorizeAssessment(Assessment $assessment, $user): void
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);

        abort_if($assessment->tenant_id !== $tenant->id || $assessment->user_id !== $user->id, 404);
    }
}
