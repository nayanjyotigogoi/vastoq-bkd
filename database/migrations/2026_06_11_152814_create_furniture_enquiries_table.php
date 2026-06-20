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
        Schema::create('furniture_enquiries', function (Blueprint $table) {

            $table->id();

            $table->foreignId('furniture_id')
                ->constrained('furniture')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');

            $table->string('phone', 20);

            $table->string('locality');

            $table->text('message')->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'declined',
                'completed'
            ])->default('pending');

            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('furniture_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('furniture_enquiries');
    }
};