# Reports Monthly Data Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengubah halaman `Reports` menjadi laporan bulanan berbasis data nyata dengan filter `month` dan `year` untuk user login.

**Architecture:** Tambahkan `ReportController@index` untuk menangani normalisasi filter, query transaksi bulanan, dan agregasi summary serta breakdown kategori. Route `/reports` diarahkan ke controller tersebut, lalu Blade report yang ada diubah dari data dummy menjadi tampilan read-only berbasis hasil query controller.

**Tech Stack:** Laravel, Blade, Eloquent ORM, PHPUnit feature tests

---

## File Structure

### Files to Create

- `app/Http/Controllers/ReportController.php`
- `tests/Feature/ReportPageTest.php`

### Files to Modify

- `routes/web.php`
- `resources/views/pages/reports.blade.php`

### Existing Reference Files

- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/TransactionController.php`
- `tests/Feature/DashboardPageTest.php`
- `resources/views/pages/dashboard.blade.php`

### Responsibility Map

- `ReportController.php`: validasi ringan filter bulan/tahun, agregasi transaksi bulanan, dan data yang dikirim ke view.
- `reports.blade.php`: form filter, summary cards, breakdown income, breakdown expense, dan empty state.
- `ReportPageTest.php`: coverage report default period, filtering, authorization scoping, dan empty state.

## Task 1: Add Failing Report Feature Tests

**Files:**
- Create: `tests/Feature/ReportPageTest.php`
- Test: `tests/Feature/ReportPageTest.php`

- [ ] **Step 1: Write the failing test file**

Create `tests/Feature/ReportPageTest.php` with:

```php
<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_report_uses_current_month_and_year_by_default(): void
    {
        $user = User::factory()->create();

        $incomeCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);

        $expenseCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Groceries',
            'type' => 'expense',
        ]);

        $user->transactions()->createMany([
            [
                'category_id' => $incomeCategory->id,
                'type' => 'income',
                'amount' => 5000000,
                'date' => now()->toDateString(),
                'description' => 'Current income',
            ],
            [
                'category_id' => $expenseCategory->id,
                'type' => 'expense',
                'amount' => 150000,
                'date' => now()->toDateString(),
                'description' => 'Current expense',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertOk();
        $response->assertSee('Rp 5.000.000,00');
        $response->assertSee('Rp 150.000,00');
        $response->assertSee('Rp 4.850.000,00');
    }

    public function test_report_can_filter_by_month_and_year(): void
    {
        $user = User::factory()->create();

        $incomeCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Freelance',
            'type' => 'income',
        ]);

        $expenseCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Dining',
            'type' => 'expense',
        ]);

        $selectedDate = now()->subMonth();

        $user->transactions()->createMany([
            [
                'category_id' => $incomeCategory->id,
                'type' => 'income',
                'amount' => 2000000,
                'date' => $selectedDate->copy()->day(10)->toDateString(),
                'description' => 'Filtered income',
            ],
            [
                'category_id' => $expenseCategory->id,
                'type' => 'expense',
                'amount' => 300000,
                'date' => $selectedDate->copy()->day(12)->toDateString(),
                'description' => 'Filtered expense',
            ],
            [
                'category_id' => $expenseCategory->id,
                'type' => 'expense',
                'amount' => 900000,
                'date' => now()->toDateString(),
                'description' => 'Different month expense',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('reports', [
            'month' => $selectedDate->month,
            'year' => $selectedDate->year,
        ]));

        $response->assertOk();
        $response->assertSee('Rp 2.000.000,00');
        $response->assertSee('Rp 300.000,00');
        $response->assertSee('Rp 1.700.000,00');
        $response->assertDontSee('Rp 900.000,00');
    }

    public function test_report_only_shows_the_authenticated_users_transactions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);

        $otherCategory = Category::create([
            'user_id' => $otherUser->id,
            'name' => 'Consulting',
            'type' => 'income',
        ]);

        $user->transactions()->create([
            'category_id' => $userCategory->id,
            'type' => 'income',
            'amount' => 4000000,
            'date' => now()->toDateString(),
            'description' => 'Own transaction',
        ]);

        $otherUser->transactions()->create([
            'category_id' => $otherCategory->id,
            'type' => 'income',
            'amount' => 9000000,
            'date' => now()->toDateString(),
            'description' => 'Other transaction',
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertOk();
        $response->assertSee('Rp 4.000.000,00');
        $response->assertDontSee('Rp 9.000.000,00');
        $response->assertDontSee('Other transaction');
    }

    public function test_report_shows_empty_state_when_selected_period_has_no_transactions(): void
    {
        $user = User::factory()->create();
        $emptyDate = now()->addMonths(2);

        $response = $this->actingAs($user)->get(route('reports', [
            'month' => $emptyDate->month,
            'year' => $emptyDate->year,
        ]));

        $response->assertOk();
        $response->assertSee('Rp 0,00');
        $response->assertSee('Belum ada pemasukan pada periode ini');
        $response->assertSee('Belum ada pengeluaran pada periode ini');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/ReportPageTest.php`

Expected: FAIL because `/reports` still renders dummy data from a static Blade view.

- [ ] **Step 3: Commit the red test**

```bash
git add tests/Feature/ReportPageTest.php
git commit -m "Add failing report page feature tests"
```

## Task 2: Implement Report Controller And Route

**Files:**
- Create: `app/Http/Controllers/ReportController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/ReportPageTest.php`

- [ ] **Step 1: Write minimal controller implementation**

Create `app/Http/Controllers/ReportController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $now = now();
        $selectedMonth = $request->integer('month');
        $selectedYear = $request->integer('year');

        $validMonth = $selectedMonth >= 1 && $selectedMonth <= 12 ? $selectedMonth : (int) $now->month;
        $validYears = range((int) $now->year - 1, (int) $now->year + 1);
        $validYear = in_array($selectedYear, $validYears, true) ? $selectedYear : (int) $now->year;

        $user = $request->user();
        $periodTransactions = $user->transactions()
            ->with('category')
            ->whereMonth('date', $validMonth)
            ->whereYear('date', $validYear);

        $totalIncome = (float) (clone $periodTransactions)->where('type', 'income')->sum('amount');
        $totalExpenses = (float) (clone $periodTransactions)->where('type', 'expense')->sum('amount');
        $netSavings = $totalIncome - $totalExpenses;

        $incomeByCategory = $user->transactions()
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_amount')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', 'income')
            ->whereMonth('transactions.date', $validMonth)
            ->whereYear('transactions.date', $validYear)
            ->groupBy('categories.name')
            ->orderByDesc('total_amount')
            ->get();

        $expenseByCategory = $user->transactions()
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_amount')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.date', $validMonth)
            ->whereYear('transactions.date', $validYear)
            ->groupBy('categories.name')
            ->orderByDesc('total_amount')
            ->get();

        return view('pages.reports', [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netSavings' => $netSavings,
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expenseByCategory,
            'selectedMonth' => $validMonth,
            'selectedYear' => $validYear,
            'months' => [
                1 => 'January',
                2 => 'February',
                3 => 'March',
                4 => 'April',
                5 => 'May',
                6 => 'June',
                7 => 'July',
                8 => 'August',
                9 => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
            ],
            'years' => $validYears,
            'currentPeriodLabel' => now()->setDate($validYear, $validMonth, 1)->format('F Y'),
        ]);
    }
}
```

- [ ] **Step 2: Wire the route**

Update `routes/web.php` imports and route group:

```php
use App\Http\Controllers\ReportController;
```

```php
Route::middleware('auth')->group(function (): void {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
});
```

- [ ] **Step 3: Run tests to verify they still fail at the view layer**

Run: `php artisan test tests/Feature/ReportPageTest.php`

Expected: FAIL because the Blade view still renders dummy content instead of controller-provided values.

- [ ] **Step 4: Commit the controller and route skeleton**

```bash
git add app/Http/Controllers/ReportController.php routes/web.php
git commit -m "Add report controller and route"
```

## Task 3: Replace Dummy Report View With Real Monthly Data

**Files:**
- Modify: `resources/views/pages/reports.blade.php`
- Test: `tests/Feature/ReportPageTest.php`

- [ ] **Step 1: Replace static arrays with real view data**

Update `resources/views/pages/reports.blade.php` to:

```blade
@extends('layouts.app')

@section('title', 'Reports')
@section('active_nav', 'reports')

@section('content')
    @php
        $summary = [
            ['label' => 'Total Income', 'amount' => $totalIncome, 'tone' => 'success'],
            ['label' => 'Total Expenses', 'amount' => $totalExpenses, 'tone' => 'danger'],
            ['label' => 'Net Savings', 'amount' => $netSavings, 'tone' => 'primary'],
        ];

        $formatMoney = fn (float $amount): string => 'Rp '.number_format($amount, 2, ',', '.');
        $maxIncome = max((float) ($incomeByCategory->max('total_amount') ?? 0), 1);
        $maxExpense = max((float) ($expenseByCategory->max('total_amount') ?? 0), 1);
    @endphp

    <section class="mx-auto max-w-6xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Reports</h1>
                <p class="mt-1 text-sm text-slate-500">Analyze your financial trends and patterns.</p>
            </div>

            <form method="GET" action="{{ route('reports') }}" class="grid gap-3 sm:grid-cols-2 lg:flex lg:items-end">
                <label class="budget-field min-w-[10rem]">
                    <span>Month</span>
                    <select name="month">
                        @foreach ($months as $monthNumber => $monthName)
                            <option value="{{ $monthNumber }}" @selected($selectedMonth === $monthNumber)>
                                {{ $monthName }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="budget-field min-w-[8rem]">
                    <span>Year</span>
                    <select name="year">
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected($selectedYear === $year)>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="flex gap-3">
                    <button type="submit" class="budget-button budget-button-primary">Apply</button>
                    <a href="{{ route('reports') }}" class="budget-button budget-button-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($summary as $item)
                <article @class([
                    'budget-card',
                    'budget-card-success' => $item['tone'] === 'success',
                    'budget-card-danger' => $item['tone'] === 'danger',
                    'budget-card-primary' => $item['tone'] === 'primary',
                ])>
                    <p class="budget-label">{{ $item['label'] }}</p>
                    <p class="budget-amount">{{ $formatMoney((float) $item['amount']) }}</p>
                    <p class="budget-meta">{{ $currentPeriodLabel }}</p>
                </article>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="budget-panel">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Income by Category</h2>
                        <p class="text-sm text-slate-500">Breakdown pemasukan pada periode terpilih.</p>
                    </div>
                    <span class="budget-pill">{{ $currentPeriodLabel }}</span>
                </div>

                <div class="mt-8 space-y-5">
                    @forelse ($incomeByCategory as $item)
                        @php
                            $width = min(100, (int) round((((float) $item->total_amount) / $maxIncome) * 100));
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $item->category_name }}</span>
                                <span class="text-slate-500">{{ $formatMoney((float) $item->total_amount) }}</span>
                            </div>
                            <div class="budget-progress-track">
                                <div class="budget-progress-fill bg-emerald-500" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
                            Belum ada pemasukan pada periode ini.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="budget-panel">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Expense by Category</h2>
                        <p class="text-sm text-slate-500">Breakdown pengeluaran pada periode terpilih.</p>
                    </div>
                    <span class="budget-pill">{{ $currentPeriodLabel }}</span>
                </div>

                <div class="mt-8 space-y-5">
                    @forelse ($expenseByCategory as $item)
                        @php
                            $width = min(100, (int) round((((float) $item->total_amount) / $maxExpense) * 100));
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $item->category_name }}</span>
                                <span class="text-slate-500">{{ $formatMoney((float) $item->total_amount) }}</span>
                            </div>
                            <div class="budget-progress-track">
                                <div class="budget-progress-fill bg-rose-500" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
                            Belum ada pengeluaran pada periode ini.
                        </p>
                    @endforelse
                </div>
            </section>
        </div>
    </section>
@endsection
```

- [ ] **Step 2: Run tests to verify they pass**

Run: `php artisan test tests/Feature/ReportPageTest.php`

Expected: PASS.

- [ ] **Step 3: Commit the view integration**

```bash
git add resources/views/pages/reports.blade.php tests/Feature/ReportPageTest.php
git commit -m "Wire reports page to monthly transaction data"
```

## Task 4: Verify Report Changes Against Existing Features

**Files:**
- Test: `tests/Feature/ReportPageTest.php`
- Test: `tests/Feature/DashboardPageTest.php`
- Test: `tests/Feature/TransactionPagesTest.php`
- Test: `tests/Feature/BudgetPagesTest.php`

- [ ] **Step 1: Run focused related suites**

Run: `php artisan test tests/Feature/ReportPageTest.php tests/Feature/DashboardPageTest.php tests/Feature/TransactionPagesTest.php tests/Feature/BudgetPagesTest.php`

Expected: PASS.

- [ ] **Step 2: Run full test suite**

Run: `php artisan test`

Expected: PASS.

- [ ] **Step 3: Manual smoke check**

Open these paths locally:

```text
/reports
/reports?month=<current-month>&year=<current-year>
/reports?month=<previous-month>&year=<current-year-or-previous-if-wrap>
```

Verify:

- filter form renders
- summary cards change with selected period
- income and expense breakdowns only show categories from selected period
- reset returns to default current period
- empty state appears on a month with no transactions

- [ ] **Step 4: Commit any final polish if needed**

```bash
git add app/Http/Controllers/ReportController.php resources/views/pages/reports.blade.php routes/web.php tests/Feature/ReportPageTest.php
git commit -m "Finish monthly reports data integration"
```

## Spec Coverage Check

- Monthly report page: covered by Tasks 2 and 3.
- Month/year filters: covered by Tasks 1, 2, and 3.
- Summary cards: covered by Tasks 1 and 3.
- Income/expense category breakdowns: covered by Tasks 1 and 3.
- Empty state: covered by Tasks 1 and 3.
- Existing feature regression safety: covered by Task 4.

## Placeholder Scan

- No `TODO`, `TBD`, or deferred implementation markers remain.
- Every task includes exact file paths and commands.
- Each code-changing step includes concrete code snippets.

## Type Consistency Check

- Route name `reports` is used consistently.
- Filter keys use `month` and `year` consistently.
- View data keys use `totalIncome`, `totalExpenses`, `netSavings`, `incomeByCategory`, `expenseByCategory`, `selectedMonth`, `selectedYear`, `months`, `years`, and `currentPeriodLabel` consistently.
