# Budgeting Schema Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add three separate Laravel migrations for the budgeting schema described by the approved design.

**Architecture:** Keep Laravel's default `users` migration intact and add focused migrations for `categories`, `budgets`, and `transactions`. Each migration owns one table, its indexes, and its foreign keys so rollbacks stay predictable.

**Tech Stack:** Laravel, PHP, database migrations, PHPUnit-compatible app bootstrap verification

---

### Task 1: Add `categories` migration

**Files:**
- Create: `database/migrations/2026_05_08_000003_create_categories_table.php`

- [ ] **Step 1: Create the migration file**

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name', 100);
    $table->string('type', 10);
    $table->timestamps();
});
```

- [ ] **Step 2: Verify the file is syntactically valid**

Run: `php artisan about`
Expected: Laravel boots successfully without PHP parse errors

### Task 2: Add `budgets` migration

**Files:**
- Create: `database/migrations/2026_05_08_000004_create_budgets_table.php`

- [ ] **Step 1: Create the migration file**

```php
Schema::create('budgets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->integer('month');
    $table->integer('year');
    $table->decimal('limit_amount', 15, 2);
    $table->timestamps();

    $table->unique(['user_id', 'category_id', 'month', 'year']);
});
```

- [ ] **Step 2: Verify the file is syntactically valid**

Run: `php artisan about`
Expected: Laravel boots successfully without PHP parse errors

### Task 3: Add `transactions` migration

**Files:**
- Create: `database/migrations/2026_05_08_000005_create_transactions_table.php`

- [ ] **Step 1: Create the migration file**

```php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->decimal('amount', 15, 2);
    $table->string('type', 10);
    $table->date('date');
    $table->text('description')->nullable();
    $table->timestamps();
});
```

- [ ] **Step 2: Verify the file is syntactically valid**

Run: `php artisan about`
Expected: Laravel boots successfully without PHP parse errors

### Task 4: Verify the combined schema

**Files:**
- Verify: `database/migrations/2026_05_08_000003_create_categories_table.php`
- Verify: `database/migrations/2026_05_08_000004_create_budgets_table.php`
- Verify: `database/migrations/2026_05_08_000005_create_transactions_table.php`

- [ ] **Step 1: Inspect migration status**

Run: `php artisan migrate:status`
Expected: Laravel lists migrations without boot-time errors

- [ ] **Step 2: Optional local execution check**

Run: `php artisan migrate`
Expected: Tables are created if a database connection is configured for the environment
