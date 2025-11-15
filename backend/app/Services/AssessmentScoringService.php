<?php

namespace App\Services;

use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireVersion;
use App\Support\Collection;

class AssessmentScoringService
{
    public function calculateScorePercent(QuestionnaireVersion $version, Collection $responses): float
    {
        $items = $version->items();
        $scorable = $items->filter(fn (QuestionnaireItem $item) => $item->type === 'likert');

        if ($scorable->isEmpty()) {
            return 0.0;
        }

        $totalWeight = $scorable->sum(fn (QuestionnaireItem $item) => $item->weight_percent ?? 0.0);
        if ($totalWeight <= 0) {
            $totalWeight = $scorable->count();
            $scorable = $scorable->map(function (QuestionnaireItem $item) {
                $item->computed_weight = 1.0;
                return $item;
            });
        } else {
            $scorable = $scorable->map(function (QuestionnaireItem $item) {
                $item->computed_weight = $item->weight_percent ?? 0.0;
                return $item;
            });
        }

        $scoreSum = 0.0;
        foreach ($scorable as $item) {
            $response = $responses->firstWhere('questionnaire_item_id', $item->id);
            $value = $response ? (float) ($response['numeric_value'] ?? 0) : 0;
            $normalized = max(min($value, 5), 0) / 5;
            $scoreSum += ($item->computed_weight ?? 1.0) * $normalized;
        }

        return round(($scoreSum / $totalWeight) * 100, 2);
    }
}
