<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('gold_packets', function (Blueprint $table) {
            $table->id();
            $table->string('packet_code', 40)->unique();
            $table->foreignId('jewellery_item_id')->constrained('jewellery_items');
            $table->foreignId('vault_id')->constrained('vaults');
            $table->string('status', 20)->default('IN_VAULT');
            // IN_VAULT, PLEDGED, RELEASED, AUCTION_ELIGIBLE, AUCTIONED
            $table->timestamps();
        });

        Schema::create('packet_transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gold_packet_id')->constrained('gold_packets')->cascadeOnDelete();
            $table->foreignId('from_vault_id')->nullable()->constrained('vaults');
            $table->foreignId('to_vault_id')->constrained('vaults');
            $table->foreignId('transferred_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('auction_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('auction_date');
            $table->string('status', 20)->default('SCHEDULED'); // SCHEDULED, NOTICE_SENT, COMPLETED, CANCELLED
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('auction_notice_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_schedule_id')->constrained('auction_schedules')->cascadeOnDelete();
            $table->foreignId('loan_id')->constrained('loans');
            $table->string('channel', 20); // SMS, EMAIL, POST
            $table->timestamp('sent_at');
            $table->timestamps();
        });

        Schema::create('auction_bidders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_schedule_id')->constrained('auction_schedules')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('mobile', 15);
            $table->string('id_proof_number', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('auction_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_schedule_id')->constrained('auction_schedules')->cascadeOnDelete();
            $table->foreignId('gold_packet_id')->constrained('gold_packets');
            $table->foreignId('bidder_id')->constrained('auction_bidders');
            $table->decimal('bid_amount', 12, 2);
            $table->timestamps();
        });

        Schema::create('auction_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gold_packet_id')->constrained('gold_packets');
            $table->foreignId('bidder_id')->constrained('auction_bidders');
            $table->decimal('winning_amount', 12, 2);
            $table->timestamps();
        });

        Schema::create('auction_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans');
            $table->foreignId('gold_packet_id')->constrained('gold_packets');
            $table->decimal('outstanding_loan_amount', 12, 2);
            $table->decimal('auction_amount', 12, 2);
            $table->decimal('remaining_balance_to_customer', 12, 2)->default(0);
            $table->foreignId('settled_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_settlements');
        Schema::dropIfExists('auction_winners');
        Schema::dropIfExists('auction_bids');
        Schema::dropIfExists('auction_bidders');
        Schema::dropIfExists('auction_notice_logs');
        Schema::dropIfExists('auction_schedules');
        Schema::dropIfExists('packet_transfer_logs');
        Schema::dropIfExists('gold_packets');
        Schema::dropIfExists('vaults');
    }
};
