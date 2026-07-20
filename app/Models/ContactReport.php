<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactReport extends Model
{
    protected $fillable = [
        'user_id',
        'reporter_phone',
        'reportable_type',
        'reportable_id',
        'reason',
        'elaborated_reason',
        'status',
        'admin_note',
        'points_refunded',
    ];

    protected $casts = [
        'points_refunded' => 'integer',
    ];

    /* ------------------------------------------------------------------ */
    /* Relations                                                            */
    /* ------------------------------------------------------------------ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Returns the related Listing when reportable_type === 'listing' */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'reportable_id');
    }

    /** Returns the related Worker when reportable_type === 'worker' */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'reportable_id');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                              */
    /* ------------------------------------------------------------------ */

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'already_rented'  => 'Property already rented / Worker unavailable',
            'invalid_details' => 'Number switched off / Invalid details',
            'extra_brokerage' => 'Demanded extra brokerage / unreasonable charges',
            'other'           => 'Other',
            default           => ucfirst($this->reason),
        };
    }

    public function isListing(): bool
    {
        return $this->reportable_type === 'listing';
    }

    public function isWorker(): bool
    {
        return $this->reportable_type === 'worker';
    }
}
