<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Budget',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Budget',
            'email' => 'budi@example.com',
        ]);

        $user = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_validation_failure_returns_to_register_mode(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $response = $this->from('/login')->post('/register', [
            'name' => '',
            'email' => $existingUser->email,
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
        ]);
        $response->assertSessionHasInput('auth_mode', 'register');
        $this->assertGuest();
    }
}
