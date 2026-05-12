<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Akun | Budget</title>
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
                        <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-blue-600 text-base font-bold text-white">B</div>
                        <h1 class="mt-6 text-3xl font-bold tracking-tight text-slate-900">Akses Budget App</h1>
                        <p class="budget-auth-copy">
                            Masuk ke akun Anda atau daftar akun baru untuk mulai mengelola keuangan.
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="budget-auth-switch">
                        <button
                            type="button"
                            data-auth-target="login"
                            @class([
                                'budget-auth-switch-button',
                                'budget-auth-switch-button-active' => $authMode === 'login',
                            ])
                            aria-pressed="{{ $authMode === 'login' ? 'true' : 'false' }}"
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
                            aria-pressed="{{ $authMode === 'register' ? 'true' : 'false' }}"
                        >
                            Daftar
                        </button>
                    </div>

                    <div class="mt-8">
                        <form method="POST" action="{{ route('login.store') }}" class="space-y-5" data-auth-panel="login" @if ($authMode !== 'login') hidden @endif>
                            @csrf
                            <input type="hidden" name="auth_mode" value="login">

                            <div>
                                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Masuk</h2>
                                <p class="budget-auth-copy">Gunakan email dan password untuk melanjutkan ke dashboard.</p>
                            </div>

                            <label class="budget-field">
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('auth_mode', 'login') === 'login' ? old('email') : '' }}" placeholder="nama@email.com" required autofocus>
                                @error('email')
                                    @if (old('auth_mode', 'login') === 'login')
                                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                    @endif
                                @enderror
                            </label>

                            <label class="budget-field">
                                <span>Password</span>
                                <input type="password" name="password" placeholder="Masukkan password" required>
                                @error('password')
                                    @if (old('auth_mode', 'login') === 'login')
                                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                    @endif
                                @enderror
                            </label>

                            <label class="flex items-center gap-3 text-sm text-slate-600">
                                <input type="checkbox" name="remember" value="1" @checked(old('auth_mode', 'login') === 'login' && old('remember')) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>Ingat saya</span>
                            </label>

                            <button type="submit" class="budget-button budget-button-primary w-full">
                                Login ke Dashboard
                            </button>

                            <p class="text-center text-sm text-slate-500">
                                Belum punya akun?
                                <button type="button" class="font-semibold text-blue-600" data-auth-target="register">Buat Akun</button>
                            </p>
                        </form>

                        <form method="POST" action="{{ route('register.store') }}" class="space-y-5" data-auth-panel="register" @if ($authMode !== 'register') hidden @endif>
                            @csrf
                            <input type="hidden" name="auth_mode" value="register">

                            <div>
                                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Daftar</h2>
                                <p class="budget-auth-copy">Buat akun baru dan langsung masuk ke dashboard budgeting Anda.</p>
                            </div>

                            <label class="budget-field">
                                <span>Nama Lengkap</span>
                                <input type="text" name="name" value="{{ old('auth_mode') === 'register' ? old('name') : '' }}" placeholder="Nama Anda" required>
                                @error('name')
                                    <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="budget-field">
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('auth_mode') === 'register' ? old('email') : '' }}" placeholder="nama@email.com" required>
                                @error('email')
                                    @if (old('auth_mode') === 'register')
                                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                    @endif
                                @enderror
                            </label>

                            <label class="budget-field">
                                <span>Password</span>
                                <input type="password" name="password" placeholder="Minimal 8 karakter" required>
                                @error('password')
                                    @if (old('auth_mode') === 'register')
                                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                                    @endif
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
                                <button type="button" class="font-semibold text-blue-600" data-auth-target="login">Masuk</button>
                            </p>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>

</html>
