<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Polymorphic: 'listing' or 'worker'
            $table->string('reportable_type');   // 'listing' | 'worker'
            $table->unsignedBigInteger('reportable_id');

            $table->enum('reason', [
                'already_rented',
                'invalid_details',
                'extra_brokerage',
                'other',
            ])->default('other');

            // Free-text: mandatory when reason='other', optional for all others
            $table->text('elaborated_reason')->nullable();

            $table->enum('status', ['pending', 'refunded', 'rejected'])->default('pending');

            // Admin fills this when rejecting (sent in the rejection email)
            $table->text('admin_note')->nullable();

            // Points credited back to user on refund
            $table->unsignedInteger('points_refunded')->default(0);

            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
            $table->index(['user_id', 'reportable_type', 'reportable_id'], 'user_reportable_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_reports');
    }
};
