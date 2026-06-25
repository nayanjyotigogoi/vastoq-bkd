<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category'); // plumber, electrician, carpenter, etc.
            $table->json('skills')->nullable();
            $table->text('bio')->nullable();
            $table->string('city')->default('Guwahati');
            $table->string('locality')->nullable();
            $table->unsignedInteger('rate_per_day')->nullable(); // in rupees
            $table->string('photo_url')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('contact_unlocks')->default(0);
            $table->unsignedInteger('jobs_completed')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->enum('aadhaar_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified');
            $table->boolean('is_active')->default(true);
            $table->boolean('available_today')->default(true);
            $table->json('service_areas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
