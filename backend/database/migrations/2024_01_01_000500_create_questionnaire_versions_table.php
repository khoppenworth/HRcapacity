<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
            $table->unique(['questionnaire_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_versions');
    }
};
