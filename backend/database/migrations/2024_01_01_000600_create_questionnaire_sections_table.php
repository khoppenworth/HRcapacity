<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('questionnaire_version_id')->constrained('questionnaire_versions')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('order_index')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_sections');
    }
};
