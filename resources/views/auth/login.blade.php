<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Budget</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[radial-gradient(circle_at_top,_#dbeafe,_#f8fafc_55%)] text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-10">
        <div class="grid w-full overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/70 lg:grid-cols-[1.1fr_minmax(24rem,28rem)]">
            <section class="hidden bg-blue-600 px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-white/15 text-lg font-bold">B</div>
                    <h1 class="mt-8 text-4xl font-bold tracking-tight">Budget Dashboard</h1>
                    <p class="mt-4 max-w-md text-sm leading-6 text-blue-100">
                        Login dengan email dan password untuk mengakses dashboard pengelolaan keuangan Anda.
                    </p>
                </div>

                <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-6">
                    <p class="text-sm font-semibold">Akun demo seeder</p>
                    <p class="mt-3 text-sm text-blue-100">Email: demo@budgeting-app.test</p>
                    <p class="mt-1 text-sm text-blue-100">Password: password123</p>
                </div>
            </section>

            <section class="px-6 py-8 sm:px-10 sm:py-12">
                <div class="mx-auto w-full max-w-md">
                    <div class="mb-8 lg:hidden">
                        <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-blue-600 text-base font-bold text-white">B</div>
                        <h1 class="mt-6 text-3xl font-bold tracking-tight text-slate-900">Login Dashboard</h1>
                        <p class="mt-2 text-sm text-slate-500">Masuk untuk membuka halaman budget Anda.</p>
                    </div>

                    <div class="hidden lg:block">
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900">Welcome back</h2>
                        <p class="mt-2 text-sm text-slate-500">Masukkan email dan password untuk melanjutkan.</p>
                    </div>

                    @if (session('status'))
                        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                        @csrf

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
                            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Ingat saya</span>
                        </label>

                        <button type="submit" class="budget-button budget-button-primary w-full">
                            Login ke Dashboard
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>

</html>
