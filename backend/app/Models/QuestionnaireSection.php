<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_version_id',
        'title',
        'order_index',
    ];

    public $timestamps = false;

    public function version(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireVersion::class, 'questionnaire_version_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuestionnaireItem::class, 'section_id')->orderBy('order_index');
    }
}
