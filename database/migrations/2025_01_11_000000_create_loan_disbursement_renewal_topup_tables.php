<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->string('mode', 20); // CASH, IMPS, RTGS, NEFT, UPI, BANK_TRANSFER
            $table->decimal('amount', 12, 2);
            $table->string('reference_number', 60)->nullable();
            $table->string('status', 20)->default('PENDING'); // PENDING, COMPLETED, FAILED
            $table->foreignId('disbursed_by')->constrained('users'); // Cashier
            $table->timestamps();
        });

        Schema::create('loan_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->integer('renewed_tenure_months');
            $table->decimal('interest_paid', 10, 2);
            $table->decimal('renewal_charges', 10, 2)->default(0);
            $table->date('new_due_date');
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('loan_topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('eligible_topup_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->string('status', 20)->default('PENDING'); // PENDING, APPROVED, DISBURSED, REJECTED
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_topups');
        Schema::dropIfExists('loan_renewals');
        Schema::dropIfExists('loan_disbursements');
    }
};
