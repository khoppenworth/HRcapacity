<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkFunctionQuestionnaireVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'work_function_id',
        'questionnaire_version_id',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workFunction(): BelongsTo
    {
        return $this->belongsTo(WorkFunction::class);
    }

    public function questionnaireVersion(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireVersion::class);
    }
}
