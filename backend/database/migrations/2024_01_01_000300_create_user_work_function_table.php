<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_work_function', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('work_function_id')->constrained('work_functions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'work_function_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_work_function');
    }
};
