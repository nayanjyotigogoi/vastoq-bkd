<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('listings', function (Blueprint $table) {

            if (!Schema::hasColumn('listings', 'latitude')) {
                $table->decimal('latitude', 10, 7)
                    ->nullable()
                    ->after('address');
            }

            if (!Schema::hasColumn('listings', 'longitude')) {
                $table->decimal('longitude', 10, 7)
                    ->nullable()
                    ->after('latitude');
            }

            if (!Schema::hasColumn('listings', 'view_count')) {
                $table->unsignedBigInteger('view_count')
                    ->default(0);
            }

            if (!Schema::hasColumn('listings', 'unlock_count')) {
                $table->unsignedBigInteger('unlock_count')
                    ->default(0);
            }

            if (!Schema::hasColumn('listings', 'admin_reviewed_at')) {
                $table->timestamp('admin_reviewed_at')
                    ->nullable();
            }

            if (!Schema::hasColumn('listings', 'admin_reviewed_by')) {
                $table->foreignId('admin_reviewed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('listings', function (Blueprint $table) {

            if (Schema::hasColumn('listings', 'admin_reviewed_by')) {
                $table->dropConstrainedForeignId('admin_reviewed_by');
            }

            $table->dropColumn([
                'latitude',
                'longitude',
                'view_count',
                'unlock_count',
                'admin_reviewed_at',
            ]);
        });
    }
};