<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'listing_id',
        'worker_id',
        'amount_cents',
        'currency',
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_signature',
        'status',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
