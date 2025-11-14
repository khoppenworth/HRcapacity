<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('work_function_id')->constrained('work_functions');
            $table->foreignId('questionnaire_version_id')->constrained('questionnaire_versions');
            $table->string('performance_period');
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id', 'work_function_id', 'questionnaire_version_id', 'performance_period'], 'assessment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
