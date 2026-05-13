# Settings Account Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengubah halaman `Settings` menjadi halaman pengaturan akun nyata untuk update profil dan password user login.

**Architecture:** Tambahkan `SettingsController` dengan satu halaman `index` dan dua action terpisah untuk update profil dan password. Route `/settings` tidak lagi memakai `Route::view`, dan Blade settings dihubungkan ke dua form mandiri agar validasi dan flash message tidak bercampur.

**Tech Stack:** Laravel, Blade, Eloquent ORM, built-in validation, PHPUnit feature tests

---

## File Structure

### Files to Create

- `app/Http/Controllers/SettingsController.php`
- `tests/Feature/SettingsPageTest.php`

### Files to Modify

- `routes/web.php`
- `resources/views/pages/settings.blade.php`

### Existing Reference Files

- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `resources/views/auth/login.blade.php`
- `tests/Feature/Auth/LoginTest.php`
- `tests/Feature/Auth/RegisterTest.php`

### Responsibility Map

- `SettingsController.php`: render halaman settings, update profil, dan update password.
- `settings.blade.php`: dua panel form terpisah, flash message, field validation errors, dan placeholder data management.
- `SettingsPageTest.php`: coverage halaman settings, update profil, email uniqueness, update password, dan password mismatch/current password failure.

## Task 1: Add Failing Settings Feature Tests

**Files:**
- Create: `tests/Feature/SettingsPageTest.php`
- Test: `tests/Feature/SettingsPageTest.php`

- [ ] **Step 1: Write the failing test file**

Create `tests/Feature/SettingsPageTest.php`:

```php
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
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/SettingsPageTest.php`

Expected: FAIL because settings still uses a static Blade view and update routes/controllers do not exist yet.

- [ ] **Step 3: Commit the red test**

```bash
git add tests/Feature/SettingsPageTest.php
git commit -m "Add failing settings page feature tests"
```

## Task 2: Implement Settings Controller And Routes

**Files:**
- Create: `app/Http/Controllers/SettingsController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/SettingsPageTest.php`

- [ ] **Step 1: Write minimal controller implementation**

Create `app/Http/Controllers/SettingsController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.settings', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
        ]);

        $request->user()->update($validated);

        return redirect()
            ->route('settings')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('settings')
            ->with('status', 'Password berhasil diperbarui.');
    }
}
```

- [ ] **Step 2: Wire the routes**

Update `routes/web.php` imports and auth group:

```php
use App\Http\Controllers\SettingsController;
```

```php
Route::middleware('auth')->group(function (): void {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
});
```

- [ ] **Step 3: Run tests to verify they still fail at the view layer**

Run: `php artisan test tests/Feature/SettingsPageTest.php`

Expected: FAIL because the page still renders readonly placeholder fields and not the new form structure.

- [ ] **Step 4: Commit controller and route skeleton**

```bash
git add app/Http/Controllers/SettingsController.php routes/web.php
git commit -m "Add settings controller and update routes"
```

## Task 3: Replace Static Settings View With Real Forms

**Files:**
- Modify: `resources/views/pages/settings.blade.php`
- Test: `tests/Feature/SettingsPageTest.php`

- [ ] **Step 1: Replace placeholder account panel with real settings forms**

Update `resources/views/pages/settings.blade.php` to:

```blade
@extends('layouts.app')

@section('title', 'Settings')
@section('active_nav', 'settings')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6 px-4 py-6 md:px-8 md:py-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Settings</h1>
            <p class="mt-1 text-sm text-slate-500">Manage your account preferences.</p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="budget-panel space-y-5">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Profile Settings</h2>
                <p class="mt-1 text-sm text-slate-500">Perbarui nama dan email akun Anda.</p>
            </div>

            <form method="POST" action="{{ route('settings.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <label class="budget-field">
                    <span>Account Name</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="budget-field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit" class="budget-button budget-button-primary">Save Profile</button>
            </form>
        </section>

        <section class="budget-panel space-y-5">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Password Settings</h2>
                <p class="mt-1 text-sm text-slate-500">Ganti password akun Anda dengan verifikasi password saat ini.</p>
            </div>

            <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <label class="budget-field">
                    <span>Current Password</span>
                    <input type="password" name="current_password" required>
                    @error('current_password')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="budget-field">
                    <span>New Password</span>
                    <input type="password" name="password" required>
                    @error('password')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="budget-field">
                    <span>Confirm New Password</span>
                    <input type="password" name="password_confirmation" required>
                </label>

                <button type="submit" class="budget-button budget-button-primary">Update Password</button>
            </form>
        </section>

        <section class="budget-panel space-y-5">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Data Management</h2>
                <p class="mt-1 text-sm text-slate-500">Fitur export dan clear data belum tersedia pada versi ini.</p>
            </div>

            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Data management akan ditambahkan pada iterasi berikutnya. Untuk saat ini, panel ini hanya memberi gambaran letak fiturnya.
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" class="budget-button budget-button-secondary" disabled>Export Data</button>
                <button type="button" class="budget-button budget-button-danger" disabled>Clear Data</button>
            </div>
        </section>

        <section class="budget-panel space-y-3">
            <h2 class="text-xl font-semibold text-slate-900">About</h2>
            <p class="text-sm text-slate-500">Budget Manager v1.0</p>
            <p class="text-sm text-slate-600">A Laravel Blade conversion of the v0.dev budgeting dashboard with static multi-page navigation.</p>
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Blade + Tailwind preview</p>
        </section>
    </section>
@endsection
```

- [ ] **Step 2: Run tests to verify they pass**

Run: `php artisan test tests/Feature/SettingsPageTest.php`

Expected: PASS.

- [ ] **Step 3: Commit the settings page integration**

```bash
git add resources/views/pages/settings.blade.php tests/Feature/SettingsPageTest.php
git commit -m "Wire settings page to profile and password updates"
```

## Task 4: Verify Settings Changes Against Existing Features

**Files:**
- Test: `tests/Feature/SettingsPageTest.php`
- Test: `tests/Feature/Auth/LoginTest.php`
- Test: `tests/Feature/Auth/RegisterTest.php`
- Test: `tests/Feature/ReportPageTest.php`
- Test: `tests/Feature/DashboardPageTest.php`

- [ ] **Step 1: Run focused related suites**

Run: `php artisan test tests/Feature/SettingsPageTest.php tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php tests/Feature/ReportPageTest.php tests/Feature/DashboardPageTest.php`

Expected: PASS.

- [ ] **Step 2: Run full test suite**

Run: `php artisan test`

Expected: PASS.

- [ ] **Step 3: Manual smoke check**

Open these paths locally:

```text
/settings
/login
```

Verify:

- settings page shows logged-in user name and email
- profile update persists after refresh
- wrong current password shows validation error
- correct password update still allows login with the new password afterward
- data management panel is clearly disabled

- [ ] **Step 4: Commit any final polish if needed**

```bash
git add app/Http/Controllers/SettingsController.php resources/views/pages/settings.blade.php routes/web.php tests/Feature/SettingsPageTest.php
git commit -m "Finish account settings integration"
```

## Spec Coverage Check

- Settings page backed by controller: covered by Tasks 2 and 3.
- Profile update: covered by Tasks 1, 2, and 3.
- Password update with current password validation: covered by Tasks 1, 2, and 3.
- Data management remains placeholder but explicit: covered by Task 3.
- Regression safety for auth and nearby pages: covered by Task 4.

## Placeholder Scan

- No `TODO`, `TBD`, or deferred implementation markers remain.
- Each task has exact files, exact commands, and concrete code.
- Each behavior in the spec maps to at least one test or implementation task.

## Type Consistency Check

- Route names `settings`, `settings.profile.update`, and `settings.password.update` are used consistently.
- Controller methods `index`, `updateProfile`, and `updatePassword` are used consistently.
- Flash messages use `Profil berhasil diperbarui.` and `Password berhasil diperbarui.` consistently across controller and tests.
