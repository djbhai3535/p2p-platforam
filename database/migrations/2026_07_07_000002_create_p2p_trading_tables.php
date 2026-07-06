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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('logo_path')->nullable();
            $table->json('fields')->nullable(); // Configurable fields e.g. account_number, account_title, bank_code
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->string('account_title');
            $table->json('account_details'); // Store actual values for payment method fields
            $table->string('qr_code_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('advertisements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('type'); // 'buy', 'sell'
            $table->string('price_type')->default('fixed'); // 'fixed', 'margin'
            $table->decimal('rate', 12, 2); // PKR rate per USDT
            $table->decimal('amount', 18, 8); // Total USDT advertised
            $table->decimal('min_limit', 12, 2); // Min fiat limit per order
            $table->decimal('max_limit', 12, 2); // Max fiat limit per order
            $table->json('payment_method_ids'); // List of user_payment_method_ids allowed
            $table->text('terms')->nullable();
            $table->string('status')->default('active'); // 'active', 'inactive', 'paused'
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount_usdt', 18, 8);
            $table->decimal('amount_fiat', 12, 2);
            $table->decimal('rate', 12, 2);
            $table->string('status')->default('pending'); // 'pending', 'paid', 'completed', 'cancelled', 'disputed'
            $table->string('payment_screenshot')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expiry_at');
            $table->timestamps();
        });

        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('seller_wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->decimal('amount_usdt', 18, 8);
            $table->decimal('fee_usdt', 18, 8);
            $table->string('status')->default('locked'); // 'locked', 'released', 'refunded'
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('attachment_path')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('disputed_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolution')->nullable(); // 'release_to_buyer', 'refund_to_seller'
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // 'general', 'email', 'nowpayments', 'fees', 'theme', 'language'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('order_messages');
        Schema::dropIfExists('escrows');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('advertisements');
        Schema::dropIfExists('user_payment_methods');
        Schema::dropIfExists('payment_methods');
    }
};
