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
        Schema::create('otps', function (Blueprint $table) {

            $table->id();

            $table->string('phone', 20);

            $table->string('otp', 6);

            $table->timestamp('expires_at');

            $table->boolean('is_used')
                ->default(false);

            $table->timestamps();

            $table->index('phone');

            $table->index([
                'phone',
                'otp'
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
        Schema::dropIfExists('otps');
    }
};