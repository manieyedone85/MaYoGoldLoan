<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_aadhaar_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('method', 20); // QR, OFFLINE_XML, OCR
            $table->string('uidai_reference_id', 100)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kyc_face_auth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->boolean('is_matched');
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('kyc_pan_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('pan_number', 15);
            $table->boolean('is_verified')->default(false);
            $table->boolean('name_match')->default(false);
            $table->timestamps();
        });

        Schema::create('kyc_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // VOTER_ID, DRIVING_LICENSE, PASSPORT, UTILITY_BILL, BANK_PASSBOOK
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('kyc_document_types');
            $table->string('file_ref'); // S3 path
            $table->string('status', 20)->default('PENDING'); // PENDING, VERIFIED, REJECTED
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_documents');
        Schema::dropIfExists('kyc_document_types');
        Schema::dropIfExists('kyc_pan_verifications');
        Schema::dropIfExists('kyc_face_auth_logs');
        Schema::dropIfExists('kyc_aadhaar_verifications');
    }
};
