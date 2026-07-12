<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('vastoq_points')
                ->default(0)
                ->after('free_unlocks_remaining')
                ->comment('Vastoq Points wallet — 100 pts per ₹99 pack; listing unlock=20pts, worker unlock=10pts');
        });

        // Migrate existing paid_unlocks_remaining → vastoq_points
        // 1 old "unlock credit" = 20 new Vastoq Points (preserves value: 5 old = 100 new)
        DB::statement('UPDATE users SET vastoq_points = paid_unlocks_remaining * 20 WHERE paid_unlocks_remaining > 0');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('paid_unlocks_remaining');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('paid_unlocks_remaining')
                ->default(0)
                ->after('free_unlocks_remaining');
        });

        // Convert back: 20 points = 1 old unlock (integer division)
        DB::statement('UPDATE users SET paid_unlocks_remaining = FLOOR(vastoq_points / 20) WHERE vastoq_points > 0');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vastoq_points');
        });
    }
};
