@extends('layouts.app')

@section('title', 'Settings')
@section('active_nav', 'settings')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6 px-4 py-6 md:px-8 md:py-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Settings</h1>
            <p class="mt-1 text-sm text-slate-500">Manage your account preferences.</p>
        </div>

        <section class="budget-panel space-y-5">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Account Settings</h2>
                <p class="mt-1 text-sm text-slate-500">Presentation-only controls for the first Blade pass.</p>
            </div>

            <div class="space-y-4">
                <label class="budget-field">
                    <span>Account Name</span>
                    <input type="text" value="Ibrahim Personal Budget" readonly>
                </label>

                <label class="budget-field">
                    <span>Default Currency</span>
                    <input type="text" value="USD" readonly>
                </label>
            </div>
        </section>

        <section class="budget-panel space-y-5">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Data Management</h2>
                <p class="mt-1 text-sm text-slate-500">These controls are shown as static actions only.</p>
            </div>

            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Your data workflow will be wired later. This Blade version only previews the settings surface and action placement.
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" class="budget-button budget-button-secondary">Export Data</button>
                <button type="button" class="budget-button budget-button-danger">Clear Data</button>
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
