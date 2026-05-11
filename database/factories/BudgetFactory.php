<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id,
            'type' => 'expense',
        ]);

        return [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => fake()->numberBetween(1, 12),
            'year' => (int) now()->year,
            'limit_amount' => fake()->randomFloat(2, 100, 5000),
        ];
    }
}
