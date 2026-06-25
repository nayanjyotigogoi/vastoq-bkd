<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerUnlock extends Model
{
    protected $fillable = [
        'worker_id', 'user_id', 'coupon_id', 'amount_paid', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function worker() { return $this->belongsTo(Worker::class); }
    public function user()   { return $this->belongsTo(User::class); }
    public function coupon() { return $this->belongsTo(Coupon::class); }
}
