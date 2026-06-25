<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Listing;
use App\Models\ListingUnlock;
use App\Models\WorkerUnlock;
use App\Models\SavedListing;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'credit_balance',
        'is_blocked',
        'is_verified',
        'profile_photo_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'credit_balance' => 'integer',
        'is_blocked' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * Listings owned by the user
     */
    public function listings()
    {
        return $this->hasMany(Listing::class, 'owner_id');
    }
    public function listingUnlocks()
    {
        return $this->hasMany(ListingUnlock::class);
    }

    public function workerUnlocks()
    {
        return $this->hasMany(WorkerUnlock::class);
    }
    
    public function savedListings()
    {
        return $this->hasMany(
            SavedListing::class
        );
    }

    public function canAccessFilament(): bool
    {
        return $this->role === 'admin' && !$this->is_blocked;
    }

    
}