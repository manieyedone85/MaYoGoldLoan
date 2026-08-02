<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'mobile' => '9999999999',
            'password' => bcrypt('1234'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'mobile' => '9999999999',
            'password' => '1234',
            'device_id' => 'device-1',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }
}
