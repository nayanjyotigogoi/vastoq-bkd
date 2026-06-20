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
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('phone', 20)->unique();

            $table->string('email', 191)
                ->nullable()
                ->unique();

            $table->string('password')->nullable();

            $table->enum('role', [
                'tenant',
                'owner',
                'worker',
                'admin'
            ])->default('tenant');

            $table->integer('credit_balance')
                ->default(0)
                ->comment('Stored in paise');

            $table->boolean('is_blocked')
                ->default(false);

            $table->boolean('is_verified')
                ->default(false);

            $table->string('profile_photo_url')
                ->nullable();

            $table->rememberToken();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};