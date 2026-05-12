<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_can_view_the_auth_page(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Masuk');
        $response->assertSee('Daftar');
        $response->assertSee('Login ke Dashboard');
        $response->assertSee('Buat Akun');
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_login_with_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'demo@budgeting-app.test',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_login_returns_to_login_mode(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'missing@budgeting-app.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasInput('auth_mode', 'login');
    }

    public function test_user_can_logout_from_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
