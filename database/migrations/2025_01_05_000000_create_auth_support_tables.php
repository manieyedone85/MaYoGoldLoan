<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mobile', 15);
            $table->string('otp_hash');
            $table->string('purpose', 30)->default('LOGIN'); // LOGIN, FORGOT_PASSWORD, MPIN_RESET
            $table->boolean('is_verified')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['mobile', 'purpose']);
        });

        Schema::create('user_device_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_id', 191);
            $table->string('device_model', 100)->nullable();
            $table->string('push_token', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('bound_at');
            $table->timestamps();
            $table->unique(['user_id', 'device_id']);
        });

        Schema::create('user_biometric_refs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // FACE, FINGERPRINT
            $table->string('template_ref'); // pointer to secure storage, never raw biometric
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_biometric_refs');
        Schema::dropIfExists('user_device_bindings');
        Schema::dropIfExists('user_otps');
    }
};
