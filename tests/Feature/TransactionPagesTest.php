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
}
