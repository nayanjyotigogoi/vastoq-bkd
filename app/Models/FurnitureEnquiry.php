<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FurnitureEnquiry extends Model
{
    use HasFactory;

    protected $table = 'furniture_enquiries';

    protected $fillable = [
        'furniture_id',
        'user_id',
        'name',
        'phone',
        'locality',
        'message',
        'status',
        'admin_notes',
    ];

    /**
     * Relationships
     */

    public function furniture()
    {
        return $this->belongsTo(Furniture::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}