<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentResponse;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireVersion;
use App\Support\Collection;
use App\Support\Http\JsonResponse;
use App\Support\Http\Request;
use App\Support\Database;
use App\Services\AssessmentScoringService;
use App\Support\Exceptions\HttpException;

class AssessmentController
{
    public function __construct(private readonly AssessmentScoringService $scoring)
    {
    }

    public function create(Request $request): JsonResponse
    {
        $tenant = $request->tenant();
        $user = $request->user();
        $data = $request->all();
        foreach (['work_function_id', 'questionnaire_version_id', 'performance_period'] as $field) {
            if (empty($data[$field])) {
                abort(422, sprintf('%s is required', $field));
            }
        }

        $assessment = Assessment::updateOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'work_function_id' => (int) $data['work_function_id'],
            'questionnaire_version_id' => (int) $data['questionnaire_version_id'],
            'performance_period' => $data['performance_period'],
        ], [
            'status' => 'draft',
        ]);

        return new JsonResponse($assessment->toArray());
    }

    public function submit(Request $request, string $assessmentId): JsonResponse
    {
        $assessment = Assessment::find((int) $assessmentId);
        if (! $assessment) {
            throw new HttpException(404, 'Assessment not found');
        }

        if ($assessment->tenant_id !== $request->tenant()->id || $assessment->user_id !== $request->user()->id) {
            throw new HttpException(404, 'Assessment not accessible');
        }

        $payload = $request->all();
        if (! isset($payload['responses']) || ! is_array($payload['responses'])) {
            abort(422, 'Responses are required.');
        }

        $responses = $payload['responses'];
        $version = QuestionnaireVersion::find($assessment->questionnaire_version_id);
        if (! $version) {
            throw new HttpException(404, 'Questionnaire version missing');
        }

        $items = $version->items();
        $collection = new Collection($responses);

        foreach ($items as $item) {
            if ($item->is_required) {
                $match = $collection->firstWhere('questionnaire_item_id', $item->id);
                if (! $match) {
                    abort(422, sprintf('Missing response for required item %s', $item->code));
                }
            }
        }

        Database::transaction(function () use ($assessment, $responses) {
            foreach ($responses as $response) {
                AssessmentResponse::updateOrCreate([
                    'assessment_id' => $assessment->id,
                    'questionnaire_item_id' => $response['questionnaire_item_id'],
                ], [
                    'raw_value' => $response['raw_value'] ?? null,
                    'numeric_value' => $response['numeric_value'] ?? null,
                ]);
            }
        });

        $score = $this->scoring->calculateScorePercent($version, new Collection($responses));
        $assessment->status = 'submitted';
        $assessment->score_percent = $score;
        $assessment->submitted_at = now();
        $assessment->save();

        return new JsonResponse($assessment->toArray());
    }
}
