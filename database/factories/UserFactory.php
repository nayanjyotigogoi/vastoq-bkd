<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [

            'name' => $this->faker->name(),

            'phone' => '9' . $this->faker->unique()->numerify('#########'),

            'email' => $this->faker->unique()->safeEmail(),

            'password' => bcrypt('password'),

            'role' => $this->faker->randomElement([
                'tenant',
                'owner',
            ]),

            'credit_balance' => rand(0, 5000),

            'is_blocked' => false,

            'is_verified' => true,

            'profile_photo_url' => null,

            'remember_token' => Str::random(10),
        ];
    }
}