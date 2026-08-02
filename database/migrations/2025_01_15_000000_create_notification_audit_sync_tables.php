<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('channel', 20); // SMS, WHATSAPP, EMAIL, PUSH
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('template_id')->constrained('notification_templates');
            $table->string('channel', 20);
            $table->string('status', 20)->default('QUEUED'); // QUEUED, SENT, FAILED
            $table->integer('retry_count')->default(0);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 60); // e.g. Loan, Customer, JewelleryItem
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 30); // CREATE, UPDATE, DELETE, APPROVE, REJECT
            $table->json('before_value')->nullable();
            $table->json('after_value')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('sync_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('entity_type', 60);
            $table->json('payload');
            $table->string('status', 20)->default('PENDING'); // PENDING, SYNCED, CONFLICT
            $table->timestamps();
        });

        Schema::create('sync_conflict_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_queue_id')->constrained('sync_queues')->cascadeOnDelete();
            $table->json('server_value');
            $table->json('client_value');
            $table->string('resolution', 20)->nullable(); // SERVER_WINS, MANUAL_REVIEW
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_conflict_logs');
        Schema::dropIfExists('sync_queues');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
    }
};
