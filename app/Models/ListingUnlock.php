<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Coupon;
use App\Models\Listing;
use App\Models\User;


class ListingUnlock extends Model
{
    protected $fillable = [
        'listing_id',
        'user_id',
        'coupon_id',
        'amount_paid',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function listing()
    {
        return $this->belongsTo(
            Listing::class
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function coupon()
    {
        return $this->belongsTo(
            Coupon::class
        );
    }


}