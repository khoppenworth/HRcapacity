<?php

namespace App\Models;

use App\Support\Collection;
use App\Support\Database;

class QuestionnaireVersion extends Model
{
    protected static function table(): string
    {
        return 'questionnaire_versions';
    }

    public function sections(): Collection
    {
        $rows = Database::where('questionnaire_sections', fn ($row) => $row['questionnaire_version_id'] === $this->id);

        return new Collection(array_map(fn ($row) => new QuestionnaireSection($row), $rows));
    }

    public function items(): Collection
    {
        $rows = Database::where('questionnaire_items', fn ($row) => $row['questionnaire_version_id'] === $this->id);

        return new Collection(array_map(fn ($row) => new QuestionnaireItem($row), $rows));
    }
}
