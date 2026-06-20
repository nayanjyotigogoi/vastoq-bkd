<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin

        User::create([
            'name' => 'Vastoq Admin',
            'phone' => '9999999999',
            'email' => 'admin@vastoq.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_verified' => true,
        ]);

        // Owners

        User::factory()
            ->count(10)
            ->create([
                'role' => 'owner',
                'is_verified' => true,
            ]);

        // Tenants

        User::factory()
            ->count(20)
            ->create([
                'role' => 'tenant',
                'is_verified' => true,
            ]);
    }
}