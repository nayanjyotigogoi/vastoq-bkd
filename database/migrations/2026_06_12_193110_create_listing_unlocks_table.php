<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listing_unlocks', function (Blueprint $table) {

            $table->id();

            $table->foreignId('listing_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('coupon_id')
                ->nullable();

            $table->decimal('amount_paid',10,2)
                ->default(0);

            $table->timestamp('expires_at')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'listing_id',
                'user_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listing_unlocks');
    }
};
