<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class Listing extends Model
{
    use HasFactory;
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'bhk_type',
        'furnishing',
        'property_type',
        'listing_class',
        'locality',
        'city',
        'address',
        'rent_per_month',
        'deposit',
        'amenities',
        'photos',
        // 'owner_phone',
        // 'owner_email',
        'status',
        'is_broker',
        'is_featured',
        'area_sqft',
        'floor_number',
        'gender_preference',
        'latitude',
        'longitude',
        'pincode',

        ];

    protected $casts = [
        'amenities' => 'array',
        'photos' => 'array',
        'is_broker' => 'boolean',
        'is_featured' => 'boolean',
        'admin_reviewed_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function unlocks()
    {
        return $this->hasMany(
            ListingUnlock::class
        );
    }

    public function savedByUsers()
    {
        return $this->hasMany(
            SavedListing::class
        );
    }
}