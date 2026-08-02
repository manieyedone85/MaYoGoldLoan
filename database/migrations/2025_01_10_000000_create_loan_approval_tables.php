<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_approval_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles');
            $table->decimal('max_amount', 12, 2);
            $table->timestamps();
        });

        Schema::create('loan_approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->string('current_stage', 30)->default('APPRAISER'); // APPRAISER, MANAGER, REGIONAL_MANAGER, HO
            $table->string('status', 20)->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->timestamps();
        });

        Schema::create('loan_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->string('stage', 30);
            $table->string('action', 20); // APPROVE, REJECT, OVERRIDE
            $table->foreignId('actioned_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Maker-Checker enforced at service layer: actioned_by must never equal loans.created_by
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_approval_logs');
        Schema::dropIfExists('loan_approval_workflows');
        Schema::dropIfExists('loan_approval_limits');
    }
};
