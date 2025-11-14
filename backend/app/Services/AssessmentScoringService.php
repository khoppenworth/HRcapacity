<?php

namespace App\Services;

use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireVersion;
use Illuminate\Support\Collection;

class AssessmentScoringService
{
    public function calculateScorePercent(QuestionnaireVersion $version, Collection $responses): float
    {
        $items = $version->items;

        $scorableItems = $items->filter(static function (QuestionnaireItem $item): bool {
            return $item->type === 'likert';
        });

        if ($scorableItems->isEmpty()) {
            return 0.0;
        }

        $totalWeight = $scorableItems->sum(function (QuestionnaireItem $item): float {
            return $item->weight_percent ?? 0.0;
        });

        if ($totalWeight <= 0) {
            $totalWeight = $scorableItems->count();
            $scorableItems = $scorableItems->map(function (QuestionnaireItem $item) use ($totalWeight) {
                $item->computed_weight = 1.0;
                return $item;
            });
        } else {
            $scorableItems = $scorableItems->map(function (QuestionnaireItem $item) {
                $item->computed_weight = $item->weight_percent;
                return $item;
            });
        }

        $scoreSum = 0.0;

        foreach ($scorableItems as $item) {
            $response = $responses->firstWhere('questionnaire_item_id', $item->id);
            $value = $response ? (float) ($response['numeric_value'] ?? 0) : 0;
            $normalized = max(min($value, 5), 0) / 5;
            $scoreSum += $item->computed_weight * $normalized;
        }

        return round(($scoreSum / $totalWeight) * 100, 2);
    }
}
