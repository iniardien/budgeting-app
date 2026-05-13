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


    </section>
@endsection
