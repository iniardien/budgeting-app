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
                $percentage = $limit > 0 ? min(100, (int) round(($spent / $limit) * 100)) : 0;

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
            'currentMonthLabel' => $now->format('F Y'),
        ]);
    }
}
