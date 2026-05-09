# v0.dev Budget UI Blade Conversion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the exported budgeting UI into a static Laravel multi-page Blade + Tailwind interface.

**Architecture:** Laravel routes render dedicated Blade page templates under a shared application layout. Shared navigation and frame markup live in partials, while each page provides section-specific content and uses static placeholder data styled through Tailwind in `resources/css/app.css`.

**Tech Stack:** Laravel 13, Blade, PHPUnit feature tests, Vite, Tailwind CSS 4

---

### Task 1: Lock Route and Page Expectations with Feature Tests

**Files:**
- Create: `tests/Feature/BudgetPagesTest.php`
- Modify: `tests/Feature/ExampleTest.php`

- [ ] **Step 1: Write the failing feature tests**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class BudgetPagesTest extends TestCase
{
    public static function pageProvider(): array
    {
        return [
            'dashboard' => ['/', 'Dashboard', 'Welcome back!'],
            'transactions' => ['/transactions', 'Transactions', 'Manage your income and expenses'],
            'budgets' => ['/budgets', 'Budgets', 'Set and manage your spending limits'],
            'reports' => ['/reports', 'Reports', 'Analyze your financial trends and patterns'],
            'settings' => ['/settings', 'Settings', 'Manage your account preferences'],
        ];
    }

    /**
     * @dataProvider pageProvider
     */
    public function test_budget_pages_render(string $uri, string $title, string $copy): void
    {
        $response = $this->get($uri);

        $response->assertOk();
        $response->assertSee($title);
        $response->assertSee($copy);
        $response->assertSee('Budget');
    }
}
```

- [ ] **Step 2: Remove the starter example test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertOk();
    }
}
```

- [ ] **Step 3: Run the new test file to verify RED**

Run: `rtk php artisan test tests/Feature/BudgetPagesTest.php`

Expected: FAIL because the new routes and page copy do not exist yet.

### Task 2: Add the Shared Blade Frame and Static Page Templates

**Files:**
- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/partials/sidebar.blade.php`
- Create: `resources/views/partials/mobile-header.blade.php`
- Create: `resources/views/pages/dashboard.blade.php`
- Create: `resources/views/pages/transactions.blade.php`
- Create: `resources/views/pages/budgets.blade.php`
- Create: `resources/views/pages/reports.blade.php`
- Create: `resources/views/pages/settings.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Register the static page routes**

```php
<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.dashboard')->name('dashboard');
Route::view('/transactions', 'pages.transactions')->name('transactions');
Route::view('/budgets', 'pages.budgets')->name('budgets');
Route::view('/reports', 'pages.reports')->name('reports');
Route::view('/settings', 'pages.settings')->name('settings');
```

- [ ] **Step 2: Create the shared app layout**

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($title ?? 'Budget') . ' | Budget' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
        @include('partials.sidebar', ['active' => $active ?? 'dashboard'])

        <div class="flex min-h-screen flex-col">
            @include('partials.mobile-header')

            <main class="flex-1">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 3: Create shared navigation partials**

```blade
<aside class="hidden border-r border-slate-200 bg-white lg:flex lg:flex-col">
    <div class="flex h-20 items-center gap-3 border-b border-slate-200 px-6">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-lg font-bold text-white shadow-sm">B</div>
        <div>
            <p class="text-lg font-semibold text-slate-900">Budget</p>
            <p class="text-sm text-slate-500">Manage your finances</p>
        </div>
    </div>

    <nav class="flex-1 space-y-2 px-4 py-6">
        @php
            $items = [
                'dashboard' => ['label' => 'Dashboard', 'route' => 'dashboard'],
                'transactions' => ['label' => 'Transactions', 'route' => 'transactions'],
                'budgets' => ['label' => 'Budgets', 'route' => 'budgets'],
                'reports' => ['label' => 'Reports', 'route' => 'reports'],
                'settings' => ['label' => 'Settings', 'route' => 'settings'],
            ];
        @endphp

        @foreach ($items as $key => $item)
            <a href="{{ route($item['route']) }}" @class([
                'flex items-center rounded-2xl px-4 py-3 text-sm font-medium transition',
                'bg-blue-50 text-blue-700 shadow-sm' => $active === $key,
                'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => $active !== $key,
            ])>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
```

```blade
<header class="sticky top-0 z-10 border-b border-slate-200 bg-white/90 backdrop-blur lg:hidden">
    <div class="flex h-16 items-center justify-between px-4">
        <div>
            <p class="text-base font-semibold text-slate-900">Budget</p>
            <p class="text-xs text-slate-500">Static preview</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Multi-page</span>
    </div>
</header>
```

- [ ] **Step 4: Create Blade page views that extend the layout**

```blade
@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-7xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Welcome back! Here's your financial overview.</p>
        </div>
    </section>
@endsection
```

Repeat the same structure for the other page files with their approved page titles and descriptions.

- [ ] **Step 5: Point the starter welcome view at the dashboard page**

```blade
@include('pages.dashboard')
```

- [ ] **Step 6: Run the page feature test to verify GREEN**

Run: `rtk php artisan test tests/Feature/BudgetPagesTest.php`

Expected: PASS with 5 route checks succeeding.

### Task 3: Build the Static Section Content and Shared Styling

**Files:**
- Modify: `resources/views/pages/dashboard.blade.php`
- Modify: `resources/views/pages/transactions.blade.php`
- Modify: `resources/views/pages/budgets.blade.php`
- Modify: `resources/views/pages/reports.blade.php`
- Modify: `resources/views/pages/settings.blade.php`
- Modify: `resources/css/app.css`

- [ ] **Step 1: Fill the dashboard with summary cards and chart placeholders**

```blade
<div class="grid gap-4 md:grid-cols-3">
    <article class="budget-card budget-card-primary">
        <p class="budget-label">Total Balance</p>
        <p class="budget-amount">$4,348.50</p>
    </article>
</div>
```

- [ ] **Step 2: Fill transactions with a static filter bar and table**

```blade
<section class="budget-panel">
    <div class="grid gap-4 md:grid-cols-3">
        <label class="budget-field">
            <span>Type</span>
            <select>
                <option>All Types</option>
            </select>
        </label>
    </div>
</section>
```

- [ ] **Step 3: Fill budgets, reports, and settings with static cards matching the source UI**

```blade
<article class="budget-panel">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-slate-900">Groceries</h3>
        <span class="budget-badge budget-badge-success">61%</span>
    </div>
</article>
```

- [ ] **Step 4: Add reusable application component classes in the Tailwind stylesheet**

```css
@layer components {
    .budget-panel {
        @apply rounded-3xl border border-slate-200 bg-white p-6 shadow-sm;
    }

    .budget-card {
        @apply rounded-3xl p-6 shadow-sm;
    }

    .budget-field select,
    .budget-field input {
        @apply mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700;
    }
}
```

- [ ] **Step 5: Run a production build to verify the Blade/Tailwind markup compiles**

Run: `rtk npm run build`

Expected: Vite build completes successfully.

### Task 4: Final Verification

**Files:**
- Verify only

- [ ] **Step 1: Run the focused route tests**

Run: `rtk php artisan test tests/Feature/BudgetPagesTest.php`

Expected: PASS

- [ ] **Step 2: Run the full test suite**

Run: `rtk php artisan test`

Expected: PASS

- [ ] **Step 3: Run the frontend build again after all edits**

Run: `rtk npm run build`

Expected: PASS
