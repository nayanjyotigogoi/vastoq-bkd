<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Listing;
use App\Models\ListingUnlock;
use App\Models\WorkerUnlock;
use App\Models\SavedListing;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'credit_balance',
        'free_unlocks_remaining',
        'vastoq_points',
        'is_blocked',
        'is_verified',
        'profile_photo_url',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'credit_balance'         => 'integer',
        'free_unlocks_remaining' => 'integer',
        'vastoq_points'          => 'integer',
        'is_blocked'             => 'boolean',
        'is_verified'            => 'boolean',
    ];

    /**
     * Whether the user can unlock a listing (costs 20 pts) using their Vastoq Points wallet.
     */
    public function canUnlockListing(): bool
    {
        return ($this->vastoq_points ?? 0) >= 20;
    }

    /**
     * Whether the user can unlock a worker profile (costs 10 pts) using Vastoq Points.
     */
    public function canUnlockWorker(): bool
    {
        return ($this->vastoq_points ?? 0) >= 10;
    }

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