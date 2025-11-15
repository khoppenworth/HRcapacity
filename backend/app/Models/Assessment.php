<?php

namespace App\Models;

use App\Support\Collection;
use App\Support\Database;

class Assessment extends Model
{
    protected static function table(): string
    {
        return 'assessments';
    }

    public function responses(): Collection
    {
        $rows = Database::where('assessment_responses', fn ($row) => $row['assessment_id'] === $this->id);

        return new Collection(array_map(fn ($row) => new AssessmentResponse($row), $rows));
    }
}
