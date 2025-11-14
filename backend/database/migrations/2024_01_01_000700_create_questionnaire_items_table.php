<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('questionnaire_version_id')->constrained('questionnaire_versions')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('questionnaire_sections')->cascadeOnDelete();
            $table->enum('type', ['likert', 'boolean', 'text']);
            $table->string('code');
            $table->text('text');
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('order_index')->default(0);
            $table->decimal('weight_percent', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['questionnaire_version_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_items');
    }
};
