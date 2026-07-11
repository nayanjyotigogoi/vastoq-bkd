<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedSearch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'filters',
        'is_active',
        'last_alerted_at',
    ];

    protected $casts = [
        'filters'         => 'array',
        'is_active'       => 'boolean',
        'last_alerted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a listing matches this saved search's filters.
     */
    public function matches(Listing $listing): bool
    {
        $f = $this->filters;

        if (!empty($f['city']) && strtolower($listing->city) !== strtolower($f['city'])) {
            return false;
        }

        if (!empty($f['listing_class']) && $listing->listing_class !== $f['listing_class']) {
            return false;
        }

        if (!empty($f['property_type']) && $listing->property_type !== $f['property_type']) {
            return false;
        }

        if (!empty($f['bhk_type']) && $listing->bhk_type !== $f['bhk_type']) {
            return false;
        }

        if (!empty($f['furnishing']) && $listing->furnishing !== $f['furnishing']) {
            return false;
        }

        if (!empty($f['max_rent']) && $listing->rent_per_month > (int) $f['max_rent']) {
            return false;
        }

        if (!empty($f['min_rent']) && $listing->rent_per_month < (int) $f['min_rent']) {
            return false;
        }

        return true;
    }
}
