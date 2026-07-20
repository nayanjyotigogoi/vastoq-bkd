<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_reports', function (Blueprint $table) {
            $table->string('reporter_phone', 20)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('contact_reports', function (Blueprint $table) {
            $table->dropColumn('reporter_phone');
        });
    }
};
