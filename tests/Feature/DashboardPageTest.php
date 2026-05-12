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
