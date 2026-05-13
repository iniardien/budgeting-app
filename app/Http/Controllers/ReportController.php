<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $now = now();
        $selectedMonth = $request->integer('month');
        $selectedYear = $request->integer('year');

        $validMonth = $selectedMonth >= 1 && $selectedMonth <= 12 ? $selectedMonth : (int) $now->month;
        $validYears = range((int) $now->year - 1, (int) $now->year + 1);
        $validYear = in_array($selectedYear, $validYears, true) ? $selectedYear : (int) $now->year;

        $user = $request->user();
        $periodTransactions = $user->transactions()
            ->with('category')
            ->whereMonth('date', $validMonth)
            ->whereYear('date', $validYear);

        $totalIncome = (float) (clone $periodTransactions)->where('type', 'income')->sum('amount');
        $totalExpenses = (float) (clone $periodTransactions)->where('type', 'expense')->sum('amount');
        $netSavings = $totalIncome - $totalExpenses;

        $incomeByCategory = $user->transactions()
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_amount')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', 'income')
            ->whereMonth('transactions.date', $validMonth)
            ->whereYear('transactions.date', $validYear)
            ->groupBy('categories.name')
            ->orderByDesc('total_amount')
            ->get();

        $expenseByCategory = $user->transactions()
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_amount')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.date', $validMonth)
            ->whereYear('transactions.date', $validYear)
            ->groupBy('categories.name')
            ->orderByDesc('total_amount')
            ->get();

        return view('pages.reports', [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netSavings' => $netSavings,
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expenseByCategory,
            'selectedMonth' => $validMonth,
            'selectedYear' => $validYear,
            'months' => [
                1 => 'January',
                2 => 'February',
                3 => 'March',
                4 => 'April',
                5 => 'May',
                6 => 'June',
                7 => 'July',
                8 => 'August',
                9 => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
            ],
            'years' => $validYears,
            'currentPeriodLabel' => now()->setDate($validYear, $validMonth, 1)->format('F Y'),
        ]);
    }
}
