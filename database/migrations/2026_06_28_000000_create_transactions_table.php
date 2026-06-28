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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('listing_id')->nullable()->constrained('listings')->onDelete('set null');
            $table->foreignId('worker_id')->nullable()->constrained('workers')->onDelete('set null');
            $table->integer('amount_cents');
            $table->string('currency', 3)->default('INR');
            $table->string('razorpay_payment_id')->nullable()->unique();
            $table->string('razorpay_order_id')->unique();
            $table->string('razorpay_signature')->nullable();
            $table->enum('status', ['created', 'paid', 'failed'])->default('created');
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['razorpay_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
