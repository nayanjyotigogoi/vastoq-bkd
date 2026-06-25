<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'skills',
        'bio',
        'city',
        'locality',
        'rate_per_day',
        'photo_url',
        'rating',
        'review_count',
        'view_count',
        'contact_unlocks',
        'jobs_completed',
        'is_verified',
        'aadhaar_status',
        'is_active',
        'available_today',
        'service_areas',
    ];

    protected $attributes = [
        'skills'        => '[]',
        'service_areas' => '[]',
    ];

    protected $casts = [
        'skills'        => 'array',
        'service_areas' => 'array',
        'is_verified'   => 'boolean',
        'is_active'     => 'boolean',
        'available_today' => 'boolean',
        'rating'        => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
