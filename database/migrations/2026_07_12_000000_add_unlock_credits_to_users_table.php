<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('free_unlocks_remaining')
                ->default(2)
                ->after('credit_balance')
                ->comment('Free contact unlocks granted on sign-up');

            $table->unsignedInteger('paid_unlocks_remaining')
                ->default(0)
                ->after('free_unlocks_remaining')
                ->comment('Paid unlock credits from premium packages');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['free_unlocks_remaining', 'paid_unlocks_remaining']);
        });
    }
};
