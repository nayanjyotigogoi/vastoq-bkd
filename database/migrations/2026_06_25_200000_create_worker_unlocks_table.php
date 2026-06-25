<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount_paid')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['worker_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_unlocks');
    }
};
