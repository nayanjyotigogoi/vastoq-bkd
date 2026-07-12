<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_password_strength_on_registration()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short1', // less than 8 characters
            'role' => 'tenant',
            'email_otp' => '123456',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'onlyletters', // no numbers
            'role' => 'tenant',
            'email_otp' => '123456',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '12345678', // no letters
            'role' => 'tenant',
            'email_otp' => '123456',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
}
