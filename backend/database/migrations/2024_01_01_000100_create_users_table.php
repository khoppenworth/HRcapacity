<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->enum('role', ['tenant_admin', 'supervisor', 'staff']);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
