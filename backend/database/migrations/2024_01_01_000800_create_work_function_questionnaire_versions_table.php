<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_function_questionnaire_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('work_function_id')->constrained('work_functions')->cascadeOnDelete();
            $table->foreignId('questionnaire_version_id')->constrained('questionnaire_versions')->cascadeOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'work_function_id', 'questionnaire_version_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_function_questionnaire_versions');
    }
};
