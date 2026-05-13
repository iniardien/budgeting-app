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
