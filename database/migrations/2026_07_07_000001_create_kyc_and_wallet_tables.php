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
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('full_name');
            $table->date('dob');
            $table->foreignId('country_id')->constrained('countries');
            $table->string('document_type'); // 'id_card' or 'passport'
            $table->string('document_number');
            $table->string('front_image_path');
            $table->string('back_image_path')->nullable();
            $table->string('selfie_image_path');
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('available_balance', 18, 8)->default(0.00000000);
            $table->decimal('locked_balance', 18, 8)->default(0.00000000);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('type'); // 'deposit', 'withdrawal'
            $table->decimal('amount', 18, 8);
            $table->decimal('fee', 18, 8)->default(0.00000000);
            $table->string('status'); // 'pending', 'processing', 'completed', 'rejected', 'cancelled'
            $table->string('txid')->nullable();
            $table->string('address')->nullable();
            $table->string('payment_provider')->default('manual'); // 'nowpayments', 'manual'
            $table->string('payment_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('device_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address');
            $table->text('user_agent');
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // e.g. 'LOGIN', 'ESCROW_LOCK', 'KYC_APPROVE'
            $table->text('description');
            $table->string('ip_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('device_logins');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('kyc_verifications');
    }
};
