<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        ALTER TABLE listings
        MODIFY property_type ENUM(
            'room',
            'shared_room',
            'flat',
            'house',
            'pg',
            'office',
            'shop',
            'warehouse'
        ) NOT NULL
    ");

    DB::statement("
        ALTER TABLE listings
        MODIFY bhk_type ENUM(
            'na',
            '1rk',
            '2rk',
            '1bhk',
            '2bhk',
            '3bhk',
            '4bhk',
            '5bhk'
        ) NOT NULL DEFAULT 'na'
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
