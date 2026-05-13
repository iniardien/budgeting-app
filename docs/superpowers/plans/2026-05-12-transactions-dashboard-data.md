# Transactions And Dashboard Data Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengubah `Transactions` dan `Dashboard` dari halaman berbasis data dummy menjadi fitur berbasis data nyata untuk user login.

**Architecture:** Tambahkan model dan relasi transaksi, ganti halaman statis menjadi controller-driven pages, lalu sambungkan Blade yang sudah ada ke query database dan agregasi sederhana. Dashboard tetap read-only dan memakai perhitungan di controller agar konsisten dengan pola `BudgetController` dan `CategoryController`.

**Tech Stack:** Laravel, Blade, Eloquent ORM, PHPUnit feature tests, SQLite test database via Laravel test harness

---

## File Structure

### Files to Create

- `app/Models/Transaction.php`
- `app/Http/Controllers/TransactionController.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/transactions/index.blade.php`
- `resources/views/transactions/create.blade.php`
- `resources/views/transactions/edit.blade.php`
- `resources/views/transactions/partials/form.blade.php`
- `tests/Feature/TransactionPagesTest.php`
- `tests/Feature/DashboardPageTest.php`

### Files to Modify

- `app/Models/User.php`
- `app/Models/Category.php`
- `routes/web.php`
- `resources/views/pages/dashboard.blade.php`
- `database/seeders/DatabaseSeeder.php`

### Existing Reference Files

- `app/Http/Controllers/BudgetController.php`
- `app/Http/Controllers/CategoryController.php`
- `resources/views/budgets/index.blade.php`
- `resources/views/budgets/partials/form.blade.php`
- `tests/Feature/BudgetPagesTest.php`
- `tests/Feature/Auth/LoginTest.php`

### Responsibility Map

- `Transaction.php`: fillable fields, casts, `user()` and `category()` relations.
- `TransactionController.php`: CRUD, filtering, ownership checks, validation.
- `DashboardController.php`: all dashboard aggregates and empty-state input data.
- `transactions/*.blade.php`: real transaction pages replacing the current static page.
- `TransactionPagesTest.php`: transaction CRUD/filter/authorization coverage.
- `DashboardPageTest.php`: dashboard aggregate coverage and empty-state coverage.

### Proposed Route Shape

```php
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('transactions', TransactionController::class)->except('show');
```

## Task 1: Add Transaction Model And User Relations

**Files:**
- Create: `app/Models/Transaction.php`
- Modify: `app/Models/User.php`
- Modify: `app/Models/Category.php`
- Test: `tests/Feature/TransactionPagesTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_authenticated_user_can_view_their_transactions_index_page(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);

        $user->transactions()->create([
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 5000000,
            'date' => '2026-05-10',
            'description' => 'Monthly salary',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertOk();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/TransactionPagesTest.php --filter=test_authenticated_user_can_view_their_transactions_index_page`

Expected: FAIL because `transactions()` relation or transaction route is not defined yet.

- [ ] **Step 3: Write minimal implementation**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'category_id', 'amount', 'type', 'date', 'description'])]
class Transaction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
```

```php
// app/Models/User.php
public function transactions(): HasMany
{
    return $this->hasMany(Transaction::class);
}
```

`app/Models/Category.php` already has `transactions()`; keep it and ensure `use App\Models\Transaction;` is not needed because same namespace.

- [ ] **Step 4: Run test to verify it still fails for the next missing piece**

Run: `php artisan test tests/Feature/TransactionPagesTest.php --filter=test_authenticated_user_can_view_their_transactions_index_page`

Expected: FAIL on missing `transactions.index` route or controller.

- [ ] **Step 5: Commit**

```bash
git add app/Models/Transaction.php app/Models/User.php tests/Feature/TransactionPagesTest.php
git commit -m "Add transaction model and user relation"
```

## Task 2: Implement Transaction Index Route, Filtering, And Empty State

**Files:**
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/TransactionController.php`
- Create: `resources/views/transactions/index.blade.php`
- Test: `tests/Feature/TransactionPagesTest.php`

- [ ] **Step 1: Write the failing tests**

Append these tests to `tests/Feature/TransactionPagesTest.php`:

```php
public function test_transactions_index_only_shows_transactions_owned_by_the_authenticated_user(): void
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $userCategory = Category::create([
        'user_id' => $user->id,
        'name' => 'Groceries',
        'type' => 'expense',
    ]);

    $otherCategory = Category::create([
        'user_id' => $otherUser->id,
        'name' => 'Consulting',
        'type' => 'income',
    ]);

    $user->transactions()->create([
        'category_id' => $userCategory->id,
        'type' => 'expense',
        'amount' => 150000,
        'date' => '2026-05-09',
        'description' => 'Weekly groceries',
    ]);

    $otherUser->transactions()->create([
        'category_id' => $otherCategory->id,
        'type' => 'income',
        'amount' => 900000,
        'date' => '2026-05-08',
        'description' => 'Consulting payment',
    ]);

    $response = $this->actingAs($user)->get(route('transactions.index'));

    $response->assertOk();
    $response->assertSee('Weekly groceries');
    $response->assertDontSee('Consulting payment');
}

public function test_transactions_index_can_filter_by_type_and_category(): void
{
    $user = User::factory()->create();

    $salary = Category::create([
        'user_id' => $user->id,
        'name' => 'Salary',
        'type' => 'income',
    ]);

    $groceries = Category::create([
        'user_id' => $user->id,
        'name' => 'Groceries',
        'type' => 'expense',
    ]);

    $user->transactions()->create([
        'category_id' => $salary->id,
        'type' => 'income',
        'amount' => 5000000,
        'date' => '2026-05-10',
        'description' => 'Monthly salary',
    ]);

    $user->transactions()->create([
        'category_id' => $groceries->id,
        'type' => 'expense',
        'amount' => 200000,
        'date' => '2026-05-11',
        'description' => 'Groceries shopping',
    ]);

    $response = $this->actingAs($user)->get(route('transactions.index', [
        'type' => 'expense',
        'category_id' => $groceries->id,
    ]));

    $response->assertOk();
    $response->assertSee('Groceries shopping');
    $response->assertDontSee('Monthly salary');
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/TransactionPagesTest.php`

Expected: FAIL because route/controller/view do not exist yet.

- [ ] **Step 3: Write minimal implementation**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $categories = $request->user()
            ->categories()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $transactions = $request->user()
            ->transactions()
            ->with('category')
            ->when(
                in_array($request->string('type')->toString(), ['income', 'expense'], true),
                fn ($query) => $query->where('type', $request->string('type')->toString())
            )
            ->when(
                $categories->pluck('id')->contains((int) $request->integer('category_id')),
                fn ($query) => $query->where('category_id', $request->integer('category_id'))
            )
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('transactions.index', [
            'transactions' => $transactions,
            'categories' => $categories,
            'selectedType' => $request->string('type')->toString(),
            'selectedCategoryId' => (string) $request->input('category_id', ''),
        ]);
    }
}
```

```php
// routes/web.php
use App\Http\Controllers\TransactionController;

Route::middleware('auth')->group(function (): void {
    Route::resource('transactions', TransactionController::class)->except('show');
});
```

```blade
{{-- resources/views/transactions/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Transactions')
@section('active_nav', 'transactions')

@section('content')
    <section class="mx-auto max-w-7xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Transactions</h1>
                <p class="mt-1 text-sm text-slate-500">Manage your income and expenses.</p>
            </div>

            <a href="{{ route('transactions.create') }}" class="budget-button budget-button-primary">Add Transaction</a>
        </div>

        <section class="budget-panel">
            <form method="GET" action="{{ route('transactions.index') }}" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                <label class="budget-field">
                    <span>Type</span>
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="income" @selected($selectedType === 'income')>Income</option>
                        <option value="expense" @selected($selectedType === 'expense')>Expense</option>
                    </select>
                </label>

                <label class="budget-field">
                    <span>Category</span>
                    <select name="category_id">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($selectedCategoryId === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="flex gap-3">
                    <button type="submit" class="budget-button budget-button-primary">Apply Filters</button>
                    <a href="{{ route('transactions.index') }}" class="budget-button budget-button-secondary">Reset Filters</a>
                </div>
            </form>
        </section>

        @if ($transactions->isEmpty())
            <section class="budget-panel text-center">
                <h2 class="text-xl font-semibold text-slate-900">Belum ada transaksi</h2>
                <p class="mt-2 text-sm text-slate-500">Tambahkan transaksi pertama Anda untuk mulai membaca ringkasan keuangan.</p>
                <a href="{{ route('transactions.create') }}" class="budget-button budget-button-primary mt-6">Create First Transaction</a>
            </section>
        @else
            {{-- render table with transaction rows --}}
        @endif
    </section>
@endsection
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/TransactionPagesTest.php --filter=transactions_index`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add routes/web.php app/Http/Controllers/TransactionController.php resources/views/transactions/index.blade.php tests/Feature/TransactionPagesTest.php
git commit -m "Add transaction index page with filters"
```

## Task 3: Implement Transaction Create And Store Validation

**Files:**
- Modify: `app/Http/Controllers/TransactionController.php`
- Create: `resources/views/transactions/create.blade.php`
- Create: `resources/views/transactions/partials/form.blade.php`
- Test: `tests/Feature/TransactionPagesTest.php`

- [ ] **Step 1: Write the failing tests**

Append these tests:

```php
public function test_authenticated_user_can_create_a_transaction(): void
{
    $user = User::factory()->create();
    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Freelance',
        'type' => 'income',
    ]);

    $response = $this->actingAs($user)->post(route('transactions.store'), [
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => '1250000.50',
        'date' => '2026-05-12',
        'description' => 'Landing page deposit',
    ]);

    $response->assertRedirect(route('transactions.index'));
    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'income',
        'description' => 'Landing page deposit',
    ]);
}

public function test_user_cannot_create_a_transaction_with_a_category_owned_by_another_user(): void
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherCategory = Category::create([
        'user_id' => $otherUser->id,
        'name' => 'Other Salary',
        'type' => 'income',
    ]);

    $response = $this->actingAs($user)->from(route('transactions.create'))->post(route('transactions.store'), [
        'category_id' => $otherCategory->id,
        'type' => 'income',
        'amount' => '1000000',
        'date' => '2026-05-12',
        'description' => 'Invalid category',
    ]);

    $response->assertRedirect(route('transactions.create'));
    $response->assertSessionHasErrors('category_id');
}

public function test_user_cannot_create_a_transaction_with_mismatched_type_and_category_type(): void
{
    $user = User::factory()->create();
    $expenseCategory = Category::create([
        'user_id' => $user->id,
        'name' => 'Groceries',
        'type' => 'expense',
    ]);

    $response = $this->actingAs($user)->from(route('transactions.create'))->post(route('transactions.store'), [
        'category_id' => $expenseCategory->id,
        'type' => 'income',
        'amount' => '100000',
        'date' => '2026-05-12',
        'description' => 'Wrong type',
    ]);

    $response->assertRedirect(route('transactions.create'));
    $response->assertSessionHasErrors('type');
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/TransactionPagesTest.php --filter=create_a_transaction`

Expected: FAIL because `create`/`store` are not implemented.

- [ ] **Step 3: Write minimal implementation**

```php
// app/Http/Controllers/TransactionController.php
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

public function create(Request $request): View
{
    return view('transactions.create', [
        'transaction' => null,
        'categories' => $request->user()->categories()->orderBy('type')->orderBy('name')->get(),
        'transactionTypes' => ['income', 'expense'],
    ]);
}

public function store(Request $request): RedirectResponse
{
    $validated = $this->validateTransaction($request);

    $request->user()->transactions()->create($validated);

    return redirect()
        ->route('transactions.index')
        ->with('status', 'Transaksi berhasil ditambahkan.');
}

private function validateTransaction(Request $request): array
{
    $category = $request->user()
        ->categories()
        ->whereKey($request->input('category_id'))
        ->first();

    return $request->validate([
        'category_id' => [
            'required',
            Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
        ],
        'type' => [
            'required',
            Rule::in(['income', 'expense']),
            function (string $attribute, mixed $value, \Closure $fail) use ($category): void {
                if ($category && $category->type !== $value) {
                    $fail('Tipe transaksi harus sama dengan tipe kategori.');
                }
            },
        ],
        'amount' => ['required', 'numeric', 'gt:0'],
        'date' => ['required', 'date'],
        'description' => ['nullable', 'string'],
    ], [
        'category_id.exists' => 'Kategori transaksi harus milik Anda.',
    ]);
}
```

```blade
{{-- resources/views/transactions/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Transaction')
@section('active_nav', 'transactions')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-6 md:px-8 md:py-8">
        <div class="budget-panel">
            <h1 class="text-3xl font-bold text-slate-900">Add Transaction</h1>
            <p class="mt-2 text-sm text-slate-500">Tambahkan pemasukan atau pengeluaran baru.</p>

            <form method="POST" action="{{ route('transactions.store') }}" class="mt-8 space-y-5">
                @csrf
                @include('transactions.partials.form', ['submitLabel' => 'Save Transaction'])
            </form>
        </div>
    </section>
@endsection
```

```blade
{{-- resources/views/transactions/partials/form.blade.php --}}
<div class="grid gap-5 sm:grid-cols-2">
    <label class="budget-field">
        <span>Type</span>
        <select name="type" required>
            @foreach ($transactionTypes as $transactionType)
                <option value="{{ $transactionType }}" @selected(old('type', $transaction?->type) === $transactionType)>
                    {{ ucfirst($transactionType) }}
                </option>
            @endforeach
        </select>
        @error('type')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="budget-field">
        <span>Category</span>
        <select name="category_id" required>
            <option value="" disabled {{ old('category_id', $transaction?->category_id) ? '' : 'selected' }}>Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $transaction?->category_id) === (string) $category->id)>
                    {{ $category->name }} ({{ ucfirst($category->type) }})
                </option>
            @endforeach
        </select>
        @error('category_id')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <label class="budget-field">
        <span>Amount</span>
        <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $transaction?->amount) }}" required>
        @error('amount')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="budget-field">
        <span>Date</span>
        <input type="date" name="date" value="{{ old('date', optional($transaction?->date)->format('Y-m-d')) }}" required>
        @error('date')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<label class="budget-field">
    <span>Description</span>
    <textarea name="description" rows="4" placeholder="Catatan transaksi">{{ old('description', $transaction?->description) }}</textarea>
    @error('description')
        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
    @enderror
</label>

<div class="flex gap-3">
    <button type="submit" class="budget-button budget-button-primary">{{ $submitLabel }}</button>
    <a href="{{ route('transactions.index') }}" class="budget-button budget-button-secondary">Cancel</a>
</div>
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/TransactionPagesTest.php --filter="create_a_transaction|cannot_create"`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/TransactionController.php resources/views/transactions/create.blade.php resources/views/transactions/partials/form.blade.php tests/Feature/TransactionPagesTest.php
git commit -m "Add transaction create flow"
```

## Task 4: Implement Transaction Edit, Update, And Delete Authorization

**Files:**
- Modify: `app/Http/Controllers/TransactionController.php`
- Create: `resources/views/transactions/edit.blade.php`
- Modify: `resources/views/transactions/index.blade.php`
- Test: `tests/Feature/TransactionPagesTest.php`

- [ ] **Step 1: Write the failing tests**

Append these tests:

```php
public function test_authenticated_user_can_update_their_transaction(): void
{
    $user = User::factory()->create();
    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Transport',
        'type' => 'expense',
    ]);

    $transaction = $user->transactions()->create([
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 50000,
        'date' => '2026-05-10',
        'description' => 'Bus fare',
    ]);

    $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => '75000',
        'date' => '2026-05-11',
        'description' => 'Taxi fare',
    ]);

    $response->assertRedirect(route('transactions.index'));
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'amount' => 75000,
        'description' => 'Taxi fare',
    ]);
}

public function test_authenticated_user_can_delete_their_transaction(): void
{
    $user = User::factory()->create();
    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Dining',
        'type' => 'expense',
    ]);

    $transaction = $user->transactions()->create([
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 85000,
        'date' => '2026-05-10',
        'description' => 'Lunch',
    ]);

    $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

    $response->assertRedirect(route('transactions.index'));
    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
}

public function test_user_cannot_edit_or_delete_another_users_transaction(): void
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $category = Category::create([
        'user_id' => $otherUser->id,
        'name' => 'Bonus',
        'type' => 'income',
    ]);

    $transaction = $otherUser->transactions()->create([
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => 300000,
        'date' => '2026-05-10',
        'description' => 'Other user bonus',
    ]);

    $this->actingAs($user)
        ->get(route('transactions.edit', $transaction))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('transactions.update', $transaction), [
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => '500000',
            'date' => '2026-05-12',
            'description' => 'Attempted update',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('transactions.destroy', $transaction))
        ->assertForbidden();
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/TransactionPagesTest.php --filter="update_their_transaction|delete_their_transaction|cannot_edit_or_delete"`

Expected: FAIL because `edit`, `update`, and `destroy` do not exist yet.

- [ ] **Step 3: Write minimal implementation**

```php
// app/Http/Controllers/TransactionController.php
use App\Models\Transaction;

public function edit(Request $request, Transaction $transaction): View
{
    $this->authorizeTransaction($transaction);

    return view('transactions.edit', [
        'transaction' => $transaction,
        'categories' => $request->user()->categories()->orderBy('type')->orderBy('name')->get(),
        'transactionTypes' => ['income', 'expense'],
    ]);
}

public function update(Request $request, Transaction $transaction): RedirectResponse
{
    $this->authorizeTransaction($transaction);

    $validated = $this->validateTransaction($request);

    $transaction->update($validated);

    return redirect()
        ->route('transactions.index')
        ->with('status', 'Transaksi berhasil diperbarui.');
}

public function destroy(Transaction $transaction): RedirectResponse
{
    $this->authorizeTransaction($transaction);

    $transaction->delete();

    return redirect()
        ->route('transactions.index')
        ->with('status', 'Transaksi berhasil dihapus.');
}

private function authorizeTransaction(Transaction $transaction): void
{
    abort_unless($transaction->user_id === auth()->id(), 403);
}
```

```blade
{{-- resources/views/transactions/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Transaction')
@section('active_nav', 'transactions')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-6 md:px-8 md:py-8">
        <div class="budget-panel">
            <h1 class="text-3xl font-bold text-slate-900">Edit Transaction</h1>
            <p class="mt-2 text-sm text-slate-500">Perbarui detail transaksi Anda.</p>

            <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="mt-8 space-y-5">
                @csrf
                @method('PUT')
                @include('transactions.partials.form', ['submitLabel' => 'Update Transaction'])
            </form>
        </div>
    </section>
@endsection
```

Replace the placeholder table block in `resources/views/transactions/index.blade.php` with:

```blade
<section class="budget-panel overflow-hidden p-0">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                <tr>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Description</th>
                    <th class="px-6 py-4 text-right">Amount</th>
                    <th class="px-6 py-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @foreach ($transactions as $transaction)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $transaction->date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $transaction->category->name }}</td>
                        <td class="px-6 py-4">
                            <span class="budget-badge {{ $transaction->type === 'income' ? 'budget-badge-success' : 'budget-badge-danger' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $transaction->description ?: '-' }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $transaction->type === 'income' ? '+' : '-' }}Rp {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('transactions.edit', $transaction) }}" class="budget-button budget-button-secondary px-4 py-2">Edit</a>
                                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Hapus transaksi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="budget-button budget-button-danger px-4 py-2">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/TransactionPagesTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/TransactionController.php resources/views/transactions/edit.blade.php resources/views/transactions/index.blade.php tests/Feature/TransactionPagesTest.php
git commit -m "Add transaction edit and delete flow"
```

## Task 5: Add Dashboard Feature Tests For Real Aggregates

**Files:**
- Create: `tests/Feature/DashboardPageTest.php`
- Test: `tests/Feature/DashboardPageTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/DashboardPageTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_dashboard_shows_real_income_expense_and_balance_totals(): void
    {
        $user = User::factory()->create(['name' => 'Dashboard User']);

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
                'amount' => 7000000,
                'date' => now()->toDateString(),
                'description' => 'Salary',
            ],
            [
                'category_id' => $expenseCategory->id,
                'type' => 'expense',
                'amount' => 250000,
                'date' => now()->toDateString(),
                'description' => 'Groceries',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Rp 6.750.000,00');
        $response->assertSee('Rp 7.000.000,00');
        $response->assertSee('Rp 250.000,00');
    }

    public function test_dashboard_shows_budget_usage_for_current_month(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Dining',
            'type' => 'expense',
        ]);

        Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => (int) now()->month,
            'year' => (int) now()->year,
            'limit_amount' => 500000,
        ]);

        $user->transactions()->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 125000,
            'date' => now()->toDateString(),
            'description' => 'Lunch and coffee',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dining');
        $response->assertSee('Rp 125.000,00 of Rp 500.000,00');
    }

    public function test_dashboard_handles_empty_state_without_transactions_or_budgets(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Rp 0,00');
        $response->assertSee('Belum ada pengeluaran bulan ini');
        $response->assertSee('Belum ada budget aktif');
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/DashboardPageTest.php`

Expected: FAIL because dashboard still uses dummy view data.

- [ ] **Step 3: Commit the red test**

```bash
git add tests/Feature/DashboardPageTest.php
git commit -m "Add failing dashboard feature tests"
```

## Task 6: Implement Dashboard Controller And Real Data View

**Files:**
- Create: `app/Http/Controllers/DashboardController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/pages/dashboard.blade.php`
- Test: `tests/Feature/DashboardPageTest.php`

- [ ] **Step 1: Write minimal implementation**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $now = now();

        $totalIncome = (float) $user->transactions()->where('type', 'income')->sum('amount');
        $totalExpenses = (float) $user->transactions()->where('type', 'expense')->sum('amount');
        $totalBalance = $totalIncome - $totalExpenses;

        $expenseByCategory = $user->transactions()
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_amount')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.date', $now->month)
            ->whereYear('transactions.date', $now->year)
            ->groupBy('categories.name')
            ->orderByDesc('total_amount')
            ->get();

        $spentByCategory = $user->transactions()
            ->selectRaw('category_id, SUM(amount) as spent_total')
            ->where('type', 'expense')
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->groupBy('category_id')
            ->pluck('spent_total', 'category_id');

        $budgetUsage = $user->budgets()
            ->with('category')
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->orderBy('category_id')
            ->get()
            ->map(function ($budget) use ($spentByCategory) {
                $spent = (float) ($spentByCategory[$budget->category_id] ?? 0);
                $limit = (float) $budget->limit_amount;
                $percentage = $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0;

                return [
                    'category' => $budget->category->name,
                    'spent' => $spent,
                    'limit' => $limit,
                    'percentage' => $percentage,
                    'status' => $percentage >= 90 ? 'danger' : ($percentage >= 75 ? 'warn' : 'good'),
                ];
            });

        return view('pages.dashboard', [
            'totalBalance' => $totalBalance,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'expenseByCategory' => $expenseByCategory,
            'budgetUsage' => $budgetUsage,
            'currentMonthLabel' => $now->translatedFormat('F Y'),
        ]);
    }
}
```

```php
// routes/web.php
use App\Http\Controllers\DashboardController;

Route::middleware('auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});
```

Replace the dummy arrays in `resources/views/pages/dashboard.blade.php` with:

```blade
@php
    $cards = [
        ['label' => 'Total Balance', 'amount' => $totalBalance, 'tone' => 'primary', 'meta' => 'Seluruh transaksi Anda'],
        ['label' => 'Total Income', 'amount' => $totalIncome, 'tone' => 'success', 'meta' => 'Akumulasi pemasukan'],
        ['label' => 'Total Expenses', 'amount' => $totalExpenses, 'tone' => 'danger', 'meta' => 'Akumulasi pengeluaran'],
    ];

    $formatMoney = fn (float $amount): string => 'Rp '.number_format($amount, 2, ',', '.');
@endphp
```

Replace the expense panel body with:

```blade
<span class="budget-pill">{{ $currentMonthLabel }}</span>

<div class="mt-8 space-y-5">
    @forelse ($expenseByCategory as $item)
        @php
            $maxExpense = max((float) $expenseByCategory->max('total_amount'), 1);
            $width = min(100, round((((float) $item->total_amount) / $maxExpense) * 100));
        @endphp
        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-slate-700">{{ $item->category_name }}</span>
                <span class="text-slate-500">{{ $formatMoney((float) $item->total_amount) }}</span>
            </div>
            <div class="budget-progress-track">
                <div class="budget-progress-fill bg-blue-500" style="width: {{ $width }}%"></div>
            </div>
        </div>
    @empty
        <p class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
            Belum ada pengeluaran bulan ini.
        </p>
    @endforelse
</div>
```

Replace the budget usage body with:

```blade
<div class="mt-6 space-y-5">
    @forelse ($budgetUsage as $item)
        <div class="space-y-2">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ $item['category'] }}</p>
                    <p class="text-xs text-slate-500">{{ $formatMoney($item['spent']) }} of {{ $formatMoney($item['limit']) }}</p>
                </div>
                <span class="budget-badge {{ $item['status'] === 'good' ? 'budget-badge-success' : ($item['status'] === 'warn' ? 'budget-badge-warn' : 'budget-badge-danger') }}">
                    {{ $item['percentage'] }}%
                </span>
            </div>
            <div class="budget-progress-track">
                <div class="budget-progress-fill {{ $item['status'] === 'good' ? 'bg-emerald-500' : ($item['status'] === 'warn' ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $item['percentage'] }}%"></div>
            </div>
        </div>
    @empty
        <p class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
            Belum ada budget aktif.
        </p>
    @endforelse
</div>
```

Replace card rendering amount line with:

```blade
<p class="budget-amount">{{ $formatMoney((float) $card['amount']) }}</p>
```

- [ ] **Step 2: Run tests to verify they pass**

Run: `php artisan test tests/Feature/DashboardPageTest.php`

Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/DashboardController.php routes/web.php resources/views/pages/dashboard.blade.php tests/Feature/DashboardPageTest.php
git commit -m "Wire dashboard to real transaction data"
```

## Task 7: Seed Demo Transactions

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/DashboardPageTest.php`
- Test: `tests/Feature/TransactionPagesTest.php`

- [ ] **Step 1: Write the minimal seed data**

Add this block near the end of `DatabaseSeeder::run()` after budgets are created:

```php
foreach ([
    ['category' => 'Salary', 'type' => 'income', 'amount' => 7000000, 'date' => now()->startOfMonth()->addDays(1)->toDateString(), 'description' => 'Gaji bulanan'],
    ['category' => 'Freelance', 'type' => 'income', 'amount' => 1500000, 'date' => now()->startOfMonth()->addDays(6)->toDateString(), 'description' => 'Project landing page'],
    ['category' => 'Groceries', 'type' => 'expense', 'amount' => 250000, 'date' => now()->startOfMonth()->addDays(3)->toDateString(), 'description' => 'Belanja mingguan'],
    ['category' => 'Transport', 'type' => 'expense', 'amount' => 90000, 'date' => now()->startOfMonth()->addDays(4)->toDateString(), 'description' => 'Transport kantor'],
    ['category' => 'Utilities', 'type' => 'expense', 'amount' => 300000, 'date' => now()->startOfMonth()->addDays(8)->toDateString(), 'description' => 'Tagihan bulanan'],
    ['category' => 'Dining', 'type' => 'expense', 'amount' => 175000, 'date' => now()->startOfMonth()->addDays(9)->toDateString(), 'description' => 'Makan bersama teman'],
] as $transaction) {
    $category = Category::query()
        ->where('user_id', $demoUser->id)
        ->where('name', $transaction['category'])
        ->where('type', $transaction['type'])
        ->first();

    if ($category) {
        $demoUser->transactions()->updateOrCreate(
            [
                'category_id' => $category->id,
                'date' => $transaction['date'],
                'description' => $transaction['description'],
            ],
            [
                'type' => $transaction['type'],
                'amount' => $transaction['amount'],
            ],
        );
    }
}
```

- [ ] **Step 2: Run targeted tests to verify nothing regressed**

Run: `php artisan test tests/Feature/TransactionPagesTest.php tests/Feature/DashboardPageTest.php`

Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add database/seeders/DatabaseSeeder.php
git commit -m "Seed demo transactions for dashboard data"
```

## Task 8: Final Verification

**Files:**
- Test: `tests/Feature/TransactionPagesTest.php`
- Test: `tests/Feature/DashboardPageTest.php`
- Test: `tests/Feature/BudgetPagesTest.php`
- Test: `tests/Feature/Auth/LoginTest.php`
- Test: `tests/Feature/Auth/RegisterTest.php`

- [ ] **Step 1: Run focused feature suite**

Run: `php artisan test tests/Feature/TransactionPagesTest.php tests/Feature/DashboardPageTest.php tests/Feature/BudgetPagesTest.php tests/Feature/Auth/LoginTest.php tests/Feature/Auth/RegisterTest.php`

Expected: PASS.

- [ ] **Step 2: Run full test suite**

Run: `php artisan test`

Expected: PASS.

- [ ] **Step 3: Review manual smoke paths**

Check these browser paths locally:

```text
/login
/
/transactions
/transactions/create
/transactions/{id}/edit
```

Verify:

- login still works
- add transaction works
- filter works
- edit/delete works
- dashboard totals update after transaction changes

- [ ] **Step 4: Commit final polish if needed**

```bash
git add app/Http/Controllers/DashboardController.php app/Http/Controllers/TransactionController.php app/Models/Transaction.php app/Models/User.php routes/web.php resources/views/pages/dashboard.blade.php resources/views/transactions tests/Feature/TransactionPagesTest.php tests/Feature/DashboardPageTest.php database/seeders/DatabaseSeeder.php
git commit -m "Finish transactions and dashboard data integration"
```

## Spec Coverage Check

- Transaction CRUD: covered by Tasks 2, 3, and 4.
- Transaction filtering: covered by Task 2.
- Ownership and validation rules: covered by Tasks 3 and 4.
- Dashboard real aggregates: covered by Tasks 5 and 6.
- Seeder data: covered by Task 7.
- Final verification: covered by Task 8.

## Placeholder Scan

- No `TODO` or `TBD` markers remain.
- Each coding step contains target file paths and concrete code snippets.
- Each test step includes an exact command and expected result.

## Type Consistency Check

- Route names use `transactions.index`, `transactions.store`, `transactions.update`, `transactions.destroy`, and `dashboard` consistently.
- Model relation name is consistently `transactions()`.
- Dashboard aggregate keys use `totalBalance`, `totalIncome`, `totalExpenses`, `expenseByCategory`, `budgetUsage`, and `currentMonthLabel` consistently.
