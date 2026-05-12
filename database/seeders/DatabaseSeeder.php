<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoUser = User::query()->updateOrCreate(
            ['email' => 'demo@budgeting-app.test'],
            [
                'name' => 'Demo Budget Admin',
                'password' => 'password123',
                'email_verified_at' => now(),
            ],
        );

        User::factory()->count(5)->create();

        foreach ([
            ['name' => 'Salary', 'type' => 'income'],
            ['name' => 'Freelance', 'type' => 'income'],
            ['name' => 'Groceries', 'type' => 'expense'],
            ['name' => 'Transport', 'type' => 'expense'],
            ['name' => 'Utilities', 'type' => 'expense'],
            ['name' => 'Dining', 'type' => 'expense'],
        ] as $category) {
            Category::query()->updateOrCreate(
                [
                    'user_id' => $demoUser->id,
                    'name' => $category['name'],
                ],
                ['type' => $category['type']],
            );
        }

        foreach ([
            ['category' => 'Groceries', 'month' => 5, 'year' => 2026, 'limit_amount' => 1500000],
            ['category' => 'Transport', 'month' => 5, 'year' => 2026, 'limit_amount' => 750000],
            ['category' => 'Utilities', 'month' => 5, 'year' => 2026, 'limit_amount' => 600000],
            ['category' => 'Dining', 'month' => 5, 'year' => 2026, 'limit_amount' => 900000],
        ] as $budget) {
            $category = Category::query()
                ->where('user_id', $demoUser->id)
                ->where('name', $budget['category'])
                ->first();

            if ($category) {
                Budget::query()->updateOrCreate(
                    [
                        'user_id' => $demoUser->id,
                        'category_id' => $category->id,
                        'month' => $budget['month'],
                        'year' => $budget['year'],
                    ],
                    ['limit_amount' => $budget['limit_amount']],
                );
            }
        }

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
    }
}
