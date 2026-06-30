<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('aadhaar_number')->nullable()->after('aadhaar_status');
            $table->string('aadhaar_front_url')->nullable()->after('aadhaar_number');
            $table->string('aadhaar_back_url')->nullable()->after('aadhaar_front_url');
            $table->timestamp('aadhaar_submitted_at')->nullable()->after('aadhaar_back_url');
            $table->text('aadhaar_rejection_reason')->nullable()->after('aadhaar_submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn([
                'aadhaar_number',
                'aadhaar_front_url',
                'aadhaar_back_url',
                'aadhaar_submitted_at',
                'aadhaar_rejection_reason',
            ]);
        });
    }
};
