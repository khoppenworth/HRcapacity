<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'questionnaire_item_id',
        'raw_value',
        'numeric_value',
    ];

    protected $casts = [
        'numeric_value' => 'float',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function questionnaireItem(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireItem::class);
    }
}
