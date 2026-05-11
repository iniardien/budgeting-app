<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public static function pageProvider(): array
    {
        return [
            'dashboard' => ['/', 'Dashboard', "Welcome back! Here's your financial overview."],
            'transactions' => ['/transactions', 'Transactions', 'Manage your income and expenses'],
            'budgets' => ['/budgets', 'Budgets', 'Set and manage your spending limits'],
            'reports' => ['/reports', 'Reports', 'Analyze your financial trends and patterns'],
            'settings' => ['/settings', 'Settings', 'Manage your account preferences'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_budget_pages_render(string $uri, string $title, string $copy): void
    {
        $response = $this->actingAs(User::factory()->create())->get($uri);

        $response->assertOk();
        $response->assertSee('Budget');
        $response->assertSee($title);
        $response->assertSee($copy, false);
    }
}
