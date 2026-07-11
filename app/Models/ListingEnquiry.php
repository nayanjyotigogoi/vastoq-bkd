<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingEnquiry extends Model
{
    protected $fillable = [
        'listing_id',
        'tenant_name',
        'tenant_phone',
        'consent',
        'ip_address',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
