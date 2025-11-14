<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id',
        'version_number',
        'status',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(QuestionnaireSection::class)->orderBy('order_index');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuestionnaireItem::class)->orderBy('order_index');
    }
}
