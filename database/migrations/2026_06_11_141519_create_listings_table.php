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
        Schema::create('listings', function (Blueprint $table) {

            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Basic Details
            |--------------------------------------------------------------------------
            */

            $table->string('title', 191);

            $table->text('description')->nullable();

            $table->enum('property_type', [
                'room',
                'shared_room',
                'flat',
                'house',
                'pg',
                'office',
                'shop',
                'warehouse'
            ]);

            $table->enum('bhk_type', [
                'na',
                '1rk',
                '2rk',
                '1bhk',
                '2bhk',
                '3bhk',
                '4bhk',
                '5bhk'
            ])->default('na');

            $table->enum('furnishing', [
                'unfurnished',
                'semi_furnished',
                'fully_furnished'
            ]);

            $table->enum('listing_class', [
                'residential',
                'commercial'
            ])->default('residential');

            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            $table->string('locality', 100);

            $table->string('city', 100);

            $table->string('pincode', 20)->nullable();

            $table->text('address');

            $table->decimal('latitude', 10, 7)->nullable();

            $table->decimal('longitude', 10, 7)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->integer('rent_per_month');

            $table->integer('deposit')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Property Details
            |--------------------------------------------------------------------------
            */

            $table->integer('area_sqft')->nullable();

            $table->integer('floor_number')->nullable();

            $table->enum('gender_preference', [
                'male',
                'female',
                'family',
                'any'
            ])->default('any');

            /*
            |--------------------------------------------------------------------------
            | Amenities & Media
            |--------------------------------------------------------------------------
            */

            $table->json('amenities')->nullable();

            $table->json('photos')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Listing Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            $table->boolean('is_broker')->default(false);

            $table->boolean('is_featured')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Statistics
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('view_count')->default(0);

            $table->unsignedBigInteger('unlock_count')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Admin Review
            |--------------------------------------------------------------------------
            */

            $table->timestamp('admin_reviewed_at')->nullable();

            $table->foreignId('admin_reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(['city', 'locality']);

            $table->index(['city', 'status']);

            $table->index('status');

            $table->index('is_featured');

            $table->index('rent_per_month');

            $table->index('property_type');

            $table->index('listing_class');

            $table->index('owner_id');

            $table->index('admin_reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listings');
    }
};