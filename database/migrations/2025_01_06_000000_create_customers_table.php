<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 30)->unique(); // e.g. CUST00012345
            $table->string('name', 150);
            $table->string('mobile', 15);
            $table->string('email', 150)->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('aadhaar_last4', 4)->nullable();
            $table->string('aadhaar_hash', 128)->nullable()->index(); // SHA-256, never full number
            $table->string('pan_number', 15)->nullable();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('registered_by')->nullable()->constrained('users');
            $table->string('kyc_status', 20)->default('PENDING'); // PENDING, VERIFIED, REJECTED
            $table->boolean('is_blacklisted')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type', 20)->default('CURRENT'); // CURRENT, PERMANENT
            $table->string('line1', 255);
            $table->string('line2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('pincode', 10);
            $table->timestamps();
        });

        Schema::create('customer_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relation', 50);
            $table->string('mobile', 15)->nullable();
            $table->timestamps();
        });

        Schema::create('customer_nominees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relation', 50);
            $table->string('mobile', 15)->nullable();
            $table->string('id_proof_type', 30)->nullable();
            $table->string('id_proof_number', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('customer_duplicate_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('matched_customer_id')->constrained('customers')->cascadeOnDelete();
            $table->decimal('match_score', 5, 2);
            $table->string('status', 20)->default('PENDING_REVIEW'); // PENDING_REVIEW, CONFIRMED, DISMISSED
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('customer_merge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_customer_id')->constrained('customers');
            $table->foreignId('merged_customer_id')->constrained('customers');
            $table->foreignId('approved_by')->constrained('users'); // Maker-Checker: Regional Manager
            $table->timestamps();
        });

        Schema::create('customer_biometrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type', 20); // FACE, FINGERPRINT, SIGNATURE
            $table->string('file_ref'); // S3 path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_biometrics');
        Schema::dropIfExists('customer_merge_logs');
        Schema::dropIfExists('customer_duplicate_logs');
        Schema::dropIfExists('customer_nominees');
        Schema::dropIfExists('customer_family_members');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
