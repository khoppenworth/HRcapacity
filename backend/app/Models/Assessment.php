<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'work_function_id',
        'questionnaire_version_id',
        'performance_period',
        'status',
        'score_percent',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'score_percent' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workFunction(): BelongsTo
    {
        return $this->belongsTo(WorkFunction::class);
    }

    public function questionnaireVersion(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireVersion::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AssessmentResponse::class);
    }
}
