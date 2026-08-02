<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('mode', 20); // CASH, ONLINE
            $table->string('receipt_number', 30)->unique();
            $table->foreignId('collected_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('loan_part_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('principal_amount', 10, 2)->default(0);
            $table->decimal('interest_amount', 10, 2)->default(0);
            $table->foreignId('collected_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('loan_reloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('excess_amount_eligible', 12, 2);
            $table->decimal('reload_amount', 12, 2);
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('loan_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('total_amount_collected', 12, 2);
            $table->date('closure_date');
            $table->foreignId('closed_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('gold_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignId('jewellery_item_id')->constrained('jewellery_items');
            $table->boolean('id_proof_verified')->default(false);
            $table->boolean('signature_captured')->default(false);
            $table->boolean('photo_captured')->default(false);
            $table->foreignId('released_by')->constrained('users');
            $table->string('released_to', 150); // name on ID proof at release time
            $table->string('status', 20)->default('PENDING'); // PENDING, RELEASED
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gold_releases');
        Schema::dropIfExists('loan_closures');
        Schema::dropIfExists('loan_reloads');
        Schema::dropIfExists('loan_part_payments');
        Schema::dropIfExists('interest_collections');
    }
};
