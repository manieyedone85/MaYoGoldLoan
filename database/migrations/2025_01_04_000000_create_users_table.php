<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 30)->unique()->nullable(); // null for CUSTOMER-role users
            $table->string('name', 150);
            $table->string('mobile', 15)->unique();
            $table->string('email', 150)->nullable()->unique();
            $table->string('password')->nullable(); // nullable: customers may be OTP-only
            $table->string('mpin')->nullable();
            $table->foreignId('role_id')->constrained('roles');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
