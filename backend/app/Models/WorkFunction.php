<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkFunction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_work_function');
    }

    public function questionnaireVersions(): BelongsToMany
    {
        return $this->belongsToMany(QuestionnaireVersion::class, 'work_function_questionnaire_versions')
            ->withPivot(['tenant_id', 'is_mandatory'])
            ->withTimestamps();
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
