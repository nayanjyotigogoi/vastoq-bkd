<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make phone nullable using raw SQL (avoids doctrine/dbal version conflicts with Laravel 9)
        DB::statement('ALTER TABLE users MODIFY phone VARCHAR(20) NULL');

        // Drop the unique index on phone so NULL values don't conflict
        // (MySQL treats multiple NULLs as distinct in unique indexes, so this is fine to leave,
        //  but we update the column to remain consistent with the original schema)

        Schema::table('users', function (Blueprint $table) {
            // Store Google's unique user ID for fast lookup on repeat sign-ins
            $table->string('google_id')->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });

        DB::statement('ALTER TABLE users MODIFY phone VARCHAR(20) NOT NULL');
    }
};
