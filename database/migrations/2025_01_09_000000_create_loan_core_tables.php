<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->decimal('interest_rate_pct', 5, 2);
            $table->string('interest_type', 20)->default('FLAT'); // FLAT, REDUCING
            $table->integer('tenure_months');
            $table->decimal('processing_fee_pct', 5, 2)->default(0);
            $table->decimal('gst_pct', 5, 2)->default(18.00);
            $table->decimal('insurance_pct', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_account_number', 30)->unique(); // e.g. LGH001000123
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('loan_product_id')->constrained('loan_products');
            $table->decimal('eligible_amount', 12, 2);
            $table->decimal('sanctioned_amount', 12, 2);
            $table->decimal('interest_rate_pct', 5, 2);
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->decimal('insurance_amount', 10, 2)->default(0);
            $table->decimal('net_disbursed_amount', 12, 2)->nullable();
            $table->date('loan_date');
            $table->date('due_date');
            $table->string('status', 20)->default('DRAFT');
            // DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, DISBURSED, ACTIVE, RENEWED,
            // PART_PAID, SETTLED, NPA, AUCTION_ELIGIBLE, AUCTIONED, CLOSED
            $table->foreignId('created_by')->constrained('users'); // Branch Executive
            $table->timestamps();
        });

        Schema::create('loan_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->string('charge_type', 30); // PROCESSING_FEE, GST, INSURANCE, LATE_FEE
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_charges');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_products');
    }
};
