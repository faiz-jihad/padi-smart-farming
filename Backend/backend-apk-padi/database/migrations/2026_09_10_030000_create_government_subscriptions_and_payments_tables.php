<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('government_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name');
            $table->string('agency_email');
            $table->string('pic_name');
            $table->string('pic_phone');
            $table->text('purpose')->nullable();
            $table->string('plan_name')->default('Government Data Access');
            $table->unsignedInteger('plan_days')->default(30);
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('status', 30)->default('PENDING_PAYMENT')->index();
            $table->string('token_hash', 64)->nullable()->index();
            $table->string('token_preview', 10)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('government_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('government_subscription_id')
                ->constrained('government_subscriptions')
                ->cascadeOnDelete();
            $table->string('order_id')->unique()->index();
            $table->string('transaction_id')->nullable()->index();
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_status', 30)->default('pending')->index();
            $table->string('snap_token')->nullable();
            $table->string('snap_redirect_url', 500)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('government_payments');
        Schema::dropIfExists('government_subscriptions');
    }
};
