<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Allow tenants to register without a phone number.
        // Using raw SQL to avoid doctrine/dbal version conflicts.
        DB::statement('ALTER TABLE users MODIFY phone VARCHAR(20) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY phone VARCHAR(20) NOT NULL');
    }
};
