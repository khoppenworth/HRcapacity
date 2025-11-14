<?php

namespace Tests\Unit;

use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireVersion;
use App\Services\AssessmentScoringService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ScoringTest extends TestCase
{
    public function testCalculatesWeightedScore(): void
    {
        $service = new AssessmentScoringService();

        $itemA = new QuestionnaireItem(['id' => 1, 'type' => 'likert', 'weight_percent' => 60]);
        $itemB = new QuestionnaireItem(['id' => 2, 'type' => 'likert', 'weight_percent' => 40]);

        $version = new QuestionnaireVersion();
        $version->setRelation('items', collect([$itemA, $itemB]));

        $responses = new Collection([
            ['questionnaire_item_id' => 1, 'numeric_value' => 5],
            ['questionnaire_item_id' => 2, 'numeric_value' => 3],
        ]);

        $score = $service->calculateScorePercent($version, $responses);

        $this->assertEquals(84.0, $score);
    }

    public function testDefaultsToEvenWeights(): void
    {
        $service = new AssessmentScoringService();

        $itemA = new QuestionnaireItem(['id' => 10, 'type' => 'likert']);
        $itemB = new QuestionnaireItem(['id' => 11, 'type' => 'likert']);

        $version = new QuestionnaireVersion();
        $version->setRelation('items', collect([$itemA, $itemB]));

        $responses = new Collection([
            ['questionnaire_item_id' => 10, 'numeric_value' => 5],
            ['questionnaire_item_id' => 11, 'numeric_value' => 1],
        ]);

        $score = $service->calculateScorePercent($version, $responses);

        $this->assertEquals(60.0, $score);
    }
}
