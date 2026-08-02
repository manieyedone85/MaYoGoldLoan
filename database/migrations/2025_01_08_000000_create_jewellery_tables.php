<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jewellery_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100); // Chain, Ring, Bangle, Necklace...
            $table->timestamps();
        });

        Schema::create('gold_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate_per_gram', 10, 2);
            $table->string('karat', 5); // 22K, 18K
            $table->date('effective_date');
            $table->string('status', 20)->default('PENDING_APPROVAL'); // PENDING_APPROVAL, APPROVED
            $table->foreignId('proposed_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('jewellery_items', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 40)->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('category_id')->constrained('jewellery_categories');
            $table->boolean('hallmark_flag')->default(false);
            $table->decimal('gross_weight', 8, 3);
            $table->decimal('stone_weight', 8, 3)->default(0);
            $table->decimal('net_weight', 8, 3); // gross - stone, enforced in service layer + DB trigger
            $table->string('purity_karat', 5);
            $table->foreignId('gold_rate_id')->constrained('gold_rates');
            $table->decimal('applied_rate', 10, 2);
            $table->decimal('eligible_percentage', 5, 2)->default(75.00);
            $table->decimal('eligible_amount', 12, 2);
            $table->foreignId('evaluated_by')->constrained('users'); // Gold Appraiser
            $table->string('status', 20)->default('EVALUATED'); // EVALUATED, PLEDGED, RELEASED, AUCTIONED
            $table->foreignId('loan_id')->nullable(); // set once loan created
            $table->timestamps();
        });

        Schema::create('jewellery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jewellery_item_id')->constrained('jewellery_items')->cascadeOnDelete();
            $table->string('file_ref'); // S3 path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jewellery_images');
        Schema::dropIfExists('jewellery_items');
        Schema::dropIfExists('gold_rates');
        Schema::dropIfExists('jewellery_categories');
    }
};
