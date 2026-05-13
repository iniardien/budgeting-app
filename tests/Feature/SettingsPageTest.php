<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_settings_page_shows_authenticated_users_profile_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Ibrahimovic',
            'email' => 'ibrahim@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertOk();
        $response->assertSee('Ibrahimovic');
        $response->assertSee('ibrahim@example.com');
    }

    public function test_settings_page_shows_profile_and_password_forms(): void
    {
        $user = User::factory()->create([
            'name' => 'Settings User',
            'email' => 'settings@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertOk();
        $response->assertSee('Profile Settings');
        $response->assertSee('Password Settings');
        $response->assertSee('Save Profile');
        $response->assertSee('Update Password');
        $response->assertSee('Fitur export dan clear data belum tersedia pada versi ini.');
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->put(route('settings.profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('settings'));
        $response->assertSessionHas('status', 'Profil berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_user_cannot_update_profile_to_an_email_used_by_another_user(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $otherUser = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->from(route('settings'))->put(route('settings.profile.update'), [
            'name' => $user->name,
            'email' => $otherUser->email,
        ]);

        $response->assertRedirect(route('settings'));
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $response = $this->actingAs($user)->put(route('settings.password.update'), [
            'current_password' => 'password123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertRedirect(route('settings'));
        $response->assertSessionHas('status', 'Password berhasil diperbarui.');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-456', $user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $originalHash = $user->password;

        $response = $this->actingAs($user)->from(route('settings'))->put(route('settings.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertRedirect(route('settings'));
        $response->assertSessionHasErrors('current_password');

        $user->refresh();
        $this->assertSame($originalHash, $user->password);
    }
}
