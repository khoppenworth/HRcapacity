<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('questionnaire_item_id')->constrained('questionnaire_items');
            $table->text('raw_value')->nullable();
            $table->decimal('numeric_value', 8, 2)->nullable();
            $table->timestamps();
            $table->unique(['assessment_id', 'questionnaire_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_responses');
    }
};
