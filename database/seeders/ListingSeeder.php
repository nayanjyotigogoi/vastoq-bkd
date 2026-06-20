<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Listing;
use App\Models\User;

class ListingSeeder extends Seeder
{
    public function run()
    {
        $owners = User::where('role', 'owner')->pluck('id');

        foreach ($owners as $ownerId) {

            Listing::factory()
                ->count(10)
                ->create([
                    'owner_id' => $ownerId,
                ]);
        }
    }
}