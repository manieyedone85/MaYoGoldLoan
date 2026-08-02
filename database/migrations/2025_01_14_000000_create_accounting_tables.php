<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gl_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('type', 20); // ASSET, LIABILITY, INCOME, EXPENSE
            $table->timestamps();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 30)->unique();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('type', 20); // RECEIPT, PAYMENT, JOURNAL, CONTRA
            $table->date('voucher_date');
            $table->string('source', 40)->nullable(); // e.g. LOAN_DISBURSEMENT#123 - auto-posted, not manual
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('voucher_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('gl_account_id')->constrained('gl_accounts');
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('cash_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('book_date');
            $table->decimal('opening_balance', 12, 2);
            $table->decimal('closing_balance', 12, 2);
            $table->timestamps();
        });

        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('loan_id')->nullable()->constrained('loans');
            $table->string('particulars', 255);
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('bank_reconciliation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('statement_date');
            $table->decimal('bank_balance', 12, 2);
            $table->decimal('book_balance', 12, 2);
            $table->boolean('is_reconciled')->default(false);
            $table->foreignId('reconciled_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_logs');
        Schema::dropIfExists('customer_ledgers');
        Schema::dropIfExists('cash_books');
        Schema::dropIfExists('voucher_details');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('gl_accounts');
    }
};
