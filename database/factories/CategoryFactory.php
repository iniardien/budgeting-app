<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->randomElement([
                'Groceries',
                'Transport',
                'Dining',
                'Salary',
                'Utilities',
                'Freelance',
            ]).' '.fake()->unique()->numberBetween(1, 999),
            'type' => fake()->randomElement(['income', 'expense']),
        ];
    }
}
