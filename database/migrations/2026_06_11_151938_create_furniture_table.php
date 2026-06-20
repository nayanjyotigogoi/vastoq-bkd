<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Furniture extends Model
{
    use HasFactory;

    protected $table = 'furniture';

    protected $fillable = [
        'name',
        'category',
        'description',
        'price_per_month',
        'image_url',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function enquiries()
    {
        return $this->hasMany(
            FurnitureEnquiry::class
        );
    }
}