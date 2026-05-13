# Auth Login and Register Refresh Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refresh the auth UI into a single polished login/register page and add a lightweight registration backend with auto-login.

**Architecture:** Keep guest authentication on one Blade page, but use separate Laravel POST endpoints for login and registration so validation and redirects stay simple. Use flashed `auth_mode` state to reopen the correct form after validation errors, keep session handling in the existing login controller, and add one dedicated registration controller for account creation plus immediate authentication.

**Tech Stack:** Laravel 13, Blade, PHPUnit feature tests, Tailwind CSS 4, Vite

---

### Task 1: Lock the New Auth Flow with Feature Tests

**Files:**
- Modify: `tests/Feature/Auth/LoginTest.php`
- Create: `tests/Feature/Auth/RegisterTest.php`

- [ ] **Step 1: Expand the login feature test to cover the shared auth page**

```php
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
```

- [ ] **Step 2: Add registration feature coverage before writing the backend**

```php
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
```

- [ ] **Step 3: Run the auth feature tests to verify RED**

Run: `rtk php artisan test tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php`

Expected: FAIL because the page does not yet include register content, `/register` is not defined, and login does not yet flash `auth_mode`.

### Task 2: Add Registration Routing and Controller Behavior

**Files:**
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/Auth/RegisteredUserController.php`
- Modify: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

- [ ] **Step 1: Register the guest registration endpoint**

```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::view('/', 'pages.dashboard')->name('dashboard');
    Route::resource('budgets', BudgetController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::view('/transactions', 'pages.transactions')->name('transactions');
    Route::view('/reports', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
```

- [ ] **Step 2: Create the registration controller with validation, persistence, and auto-login**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($validated);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
```

- [ ] **Step 3: Flash auth mode on login validation failures and pass active mode to the view**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        $authMode = old('auth_mode', 'login');

        return view('auth.login', [
            'authMode' => in_array($authMode, ['login', 'register'], true) ? $authMode : 'login',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak valid.',
            ])->withInput([
                'email' => $request->string('email')->toString(),
                'remember' => $remember,
                'auth_mode' => 'login',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
```

- [ ] **Step 4: Run the auth feature tests to verify partial GREEN**

Run: `rtk php artisan test tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php`

Expected: login and register routes now resolve, but page-content assertions should still FAIL until the shared Blade page is updated.

### Task 3: Convert the Login Blade into a Shared Login/Register Page

**Files:**
- Modify: `resources/views/auth/login.blade.php`

- [ ] **Step 1: Replace the current single-form markup with a dual-mode auth page**

```blade
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Budget</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $authMode = $authMode ?? 'login';
@endphp

<body class="min-h-screen bg-[radial-gradient(circle_at_top,_#dbeafe,_#f8fafc_55%)] text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-10">
        <div class="grid w-full overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/70 lg:grid-cols-[1.1fr_minmax(24rem,30rem)]">
            <section class="hidden bg-blue-600 px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-white/15 text-lg font-bold">B</div>
                    <h1 class="mt-8 text-4xl font-bold tracking-tight">Budget Dashboard</h1>
                    <p class="mt-4 max-w-md text-sm leading-6 text-blue-100">
                        Kelola anggaran kuliah, pengeluaran harian, dan target finansial dalam satu dashboard yang rapi.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-6">
                        <p class="text-sm font-semibold">Akun demo seeder</p>
                        <p class="mt-3 text-sm text-blue-100">Email: demo@budgeting-app.test</p>
                        <p class="mt-1 text-sm text-blue-100">Password: password123</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-6">
                        <p class="text-sm font-semibold">Cocok untuk tugas kuliah</p>
                        <p class="mt-3 text-sm leading-6 text-blue-100">
                            Masuk dengan akun yang sudah ada atau buat akun baru untuk langsung mencoba dashboard budgeting ini.
                        </p>
                    </div>
                </div>
            </section>

            <section class="px-6 py-8 sm:px-10 sm:py-12">
                <div class="mx-auto w-full max-w-md">
                    <div class="mb-8">
                        <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-blue-600 text-base font-bold text-white lg:hidden">B</div>
                        <h1 class="mt-6 text-3xl font-bold tracking-tight text-slate-900">Akses Budget App</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Masuk ke akun Anda atau daftar akun baru untuk mulai mengelola keuangan.
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mt-6 grid grid-cols-2 rounded-2xl bg-slate-100 p-1">
                        <button
                            type="button"
                            data-auth-target="login"
                            @class([
                                'rounded-[1rem] px-4 py-3 text-sm font-semibold transition',
                                'bg-white text-slate-900 shadow-sm' => $authMode === 'login',
                                'text-slate-500' => $authMode !== 'login',
                            ])
                        >
                            Masuk
                        </button>

                        <button
                            type="button"
                            data-auth-target="register"
                            @class([
                                'rounded-[1rem] px-4 py-3 text-sm font-semibold transition',
                                'bg-white text-slate-900 shadow-sm' => $authMode === 'register',
                                'text-slate-500' => $authMode !== 'register',
                            ])
                        >
                            Daftar
                        </button>
                    </div>

                    <div class="mt-8">
                        <form
                            method="POST"
                            action="{{ route('login.store') }}"
                            class="space-y-5"
                            data-auth-panel="login"
                            @if ($authMode !== 'login') hidden @endif
                        >
                            @csrf
                            <input type="hidden" name="auth_mode" value="login">

                            <div>
                                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Masuk</h2>
                                <p class="mt-2 text-sm text-slate-500">Gunakan email dan password untuk melanjutkan ke dashboard.</p>
                            </div>

                            <label class="budget-field">
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                                @error('email')
                                    <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="budget-field">
                                <span>Password</span>
                                <input type="password" name="password" placeholder="Masukkan password" required>
                                @error('password')
                                    <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="flex items-center gap-3 text-sm text-slate-600">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>Ingat saya</span>
                            </label>

                            <button type="submit" class="budget-button budget-button-primary w-full">
                                Login ke Dashboard
                            </button>

                            <p class="text-center text-sm text-slate-500">
                                Belum punya akun?
                                <button type="button" class="font-semibold text-blue-600" data-auth-target="register">
                                    Buat Akun
                                </button>
                            </p>
                        </form>

                        <form
                            method="POST"
                            action="{{ route('register.store') }}"
                            class="space-y-5"
                            data-auth-panel="register"
                            @if ($authMode !== 'register') hidden @endif
                        >
                            @csrf
                            <input type="hidden" name="auth_mode" value="register">

                            <div>
                                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Daftar</h2>
                                <p class="mt-2 text-sm text-slate-500">Buat akun baru dan langsung masuk ke dashboard budgeting Anda.</p>
                            </div>

                            <label class="budget-field">
                                <span>Nama Lengkap</span>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required>
                                @error('name')
                                    <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="budget-field">
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                                @error('email')
                                    <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="budget-field">
                                <span>Password</span>
                                <input type="password" name="password" placeholder="Minimal 8 karakter" required>
                                @error('password')
                                    <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="budget-field">
                                <span>Konfirmasi Password</span>
                                <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
                            </label>

                            <button type="submit" class="budget-button budget-button-primary w-full">
                                Buat Akun
                            </button>

                            <p class="text-center text-sm text-slate-500">
                                Sudah punya akun?
                                <button type="button" class="font-semibold text-blue-600" data-auth-target="login">
                                    Masuk
                                </button>
                            </p>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        const authButtons = document.querySelectorAll('[data-auth-target]');
        const authPanels = document.querySelectorAll('[data-auth-panel]');

        const setAuthMode = (mode) => {
            authPanels.forEach((panel) => {
                panel.hidden = panel.dataset.authPanel !== mode;
            });

            authButtons.forEach((button) => {
                const active = button.dataset.authTarget === mode;
                button.classList.toggle('bg-white', active);
                button.classList.toggle('text-slate-900', active);
                button.classList.toggle('shadow-sm', active);
                button.classList.toggle('text-slate-500', ! active);
            });
        };

        authButtons.forEach((button) => {
            button.addEventListener('click', () => setAuthMode(button.dataset.authTarget));
        });
    </script>
</body>

</html>
```

- [ ] **Step 2: Keep the document title consistent with the combined page**

```blade
<title>Akses Akun | Budget</title>
```

- [ ] **Step 3: Run the auth feature tests to verify GREEN**

Run: `rtk php artisan test tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php`

Expected: PASS with both login and registration flows covered.

### Task 4: Move the Auth Toggle Script into the Existing App Bundle and Polish Shared Styles

**Files:**
- Modify: `resources/js/app.js`
- Modify: `resources/css/app.css`
- Modify: `resources/views/auth/login.blade.php`

- [ ] **Step 1: Move the auth switch behavior into `resources/js/app.js`**

```js
const authButtons = document.querySelectorAll('[data-auth-target]');
const authPanels = document.querySelectorAll('[data-auth-panel]');

if (authButtons.length > 0 && authPanels.length > 0) {
    const setAuthMode = (mode) => {
        authPanels.forEach((panel) => {
            panel.hidden = panel.dataset.authPanel !== mode;
        });

        authButtons.forEach((button) => {
            const active = button.dataset.authTarget === mode;

            button.classList.toggle('bg-white', active);
            button.classList.toggle('text-slate-900', active);
            button.classList.toggle('shadow-sm', active);
            button.classList.toggle('text-slate-500', !active);
        });
    };

    authButtons.forEach((button) => {
        button.addEventListener('click', () => setAuthMode(button.dataset.authTarget));
    });
}
```

- [ ] **Step 2: Add focused auth utility classes to the shared stylesheet**

```css
@layer components {
    .budget-auth-switch {
        @apply mt-6 grid grid-cols-2 rounded-2xl bg-slate-100 p-1;
    }

    .budget-auth-switch-button {
        @apply rounded-[1rem] px-4 py-3 text-sm font-semibold text-slate-500 transition;
    }

    .budget-auth-switch-button-active {
        @apply bg-white text-slate-900 shadow-sm;
    }

    .budget-auth-copy {
        @apply mt-2 text-sm leading-6 text-slate-500;
    }
}
```

- [ ] **Step 3: Replace inline class duplication in the Blade view with the new shared classes**

```blade
<div class="budget-auth-switch">
    <button
        type="button"
        data-auth-target="login"
        @class([
            'budget-auth-switch-button',
            'budget-auth-switch-button-active' => $authMode === 'login',
        ])
    >
        Masuk
    </button>

    <button
        type="button"
        data-auth-target="register"
        @class([
            'budget-auth-switch-button',
            'budget-auth-switch-button-active' => $authMode === 'register',
        ])
    >
        Daftar
    </button>
</div>
```

- [ ] **Step 4: Remove the inline `<script>` block from the Blade template**

```blade
</main>
</body>
```

- [ ] **Step 5: Run the auth feature tests again**

Run: `rtk php artisan test tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php`

Expected: PASS with no behavior change after moving the toggle logic into the shared JS entry.

### Task 5: Run the Full Relevant Test Slice and Prepare a Clean Checkpoint

**Files:**
- Modify: `tests/Feature/Auth/LoginTest.php`
- Create: `tests/Feature/Auth/RegisterTest.php`
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/Auth/RegisteredUserController.php`
- Modify: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- Modify: `resources/views/auth/login.blade.php`
- Modify: `resources/js/app.js`
- Modify: `resources/css/app.css`

- [ ] **Step 1: Run the focused auth suite**

Run: `rtk php artisan test tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php`

Expected: PASS with all auth scenarios green.

- [ ] **Step 2: Run the broader feature regression slice that already exists in the repo**

Run: `rtk php artisan test tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php tests/Feature/BudgetPagesTest.php`

Expected: PASS so the auth changes do not break existing protected-page behavior.

- [ ] **Step 3: Review the changed files before commit**

Run: `rtk git diff -- app/Http/Controllers/Auth/AuthenticatedSessionController.php app/Http/Controllers/Auth/RegisteredUserController.php routes/web.php resources/views/auth/login.blade.php resources/js/app.js resources/css/app.css tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php`

Expected: diff only shows the auth page refresh, registration endpoint, and test additions described in this plan.

- [ ] **Step 4: Create the implementation commit**

Run:

```bash
rtk git add app/Http/Controllers/Auth/AuthenticatedSessionController.php app/Http/Controllers/Auth/RegisteredUserController.php routes/web.php resources/views/auth/login.blade.php resources/js/app.js resources/css/app.css tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php
rtk git commit -m "Add combined login and registration flow"
```

Expected: one commit containing the refreshed auth UI, registration backend, and tests.
