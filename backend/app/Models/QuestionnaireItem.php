<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_version_id',
        'section_id',
        'type',
        'code',
        'text',
        'help_text',
        'is_required',
        'order_index',
        'weight_percent',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'weight_percent' => 'float',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireVersion::class, 'questionnaire_version_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireSection::class, 'section_id');
    }
}
