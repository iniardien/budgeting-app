@extends('layouts.app')

@section('title', 'Transactions')
@section('active_nav', 'transactions')

@section('content')
    @php
        $transactions = [
            ['date' => 'Apr 20, 2025', 'category' => 'Groceries', 'type' => 'Expense', 'description' => 'Weekly groceries at Whole Foods', 'amount' => '-$85.50', 'tone' => 'danger'],
            ['date' => 'Apr 19, 2025', 'category' => 'Salary', 'type' => 'Income', 'description' => 'Monthly salary', 'amount' => '+$5,000.00', 'tone' => 'success'],
            ['date' => 'Apr 18, 2025', 'category' => 'Dining', 'type' => 'Expense', 'description' => 'Dinner with friends', 'amount' => '-$45.25', 'tone' => 'danger'],
            ['date' => 'Apr 17, 2025', 'category' => 'Utilities', 'type' => 'Expense', 'description' => 'Electric and water bill', 'amount' => '-$120.00', 'tone' => 'danger'],
            ['date' => 'Apr 16, 2025', 'category' => 'Freelance', 'type' => 'Income', 'description' => 'Landing page project deposit', 'amount' => '+$850.00', 'tone' => 'success'],
        ];
    @endphp

    <section class="mx-auto max-w-7xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Transactions</h1>
                <p class="mt-1 text-sm text-slate-500">Manage your income and expenses.</p>
            </div>

            <button type="button" class="budget-button budget-button-primary">Add Transaction</button>
        </div>

        <section class="budget-panel">
            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                <label class="budget-field">
                    <span>Type</span>
                    <select>
                        <option>All Types</option>
                        <option>Income</option>
                        <option>Expense</option>
                    </select>
                </label>

                <label class="budget-field">
                    <span>Category</span>
                    <select>
                        <option>All Categories</option>
                        <option>Groceries</option>
                        <option>Utilities</option>
                        <option>Dining</option>
                        <option>Salary</option>
                    </select>
                </label>

                <button type="button" class="budget-button budget-button-secondary">Reset Filters</button>
            </div>
        </section>

        <section class="budget-panel overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($transactions as $transaction)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-4 font-medium text-slate-800">{{ $transaction['date'] }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $transaction['category'] }}</td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'budget-badge',
                                        'budget-badge-success' => $transaction['tone'] === 'success',
                                        'budget-badge-danger' => $transaction['tone'] === 'danger',
                                    ])>{{ $transaction['type'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $transaction['description'] }}</td>
                                <td @class([
                                    'px-6 py-4 text-right font-semibold',
                                    'text-emerald-600' => $transaction['tone'] === 'success',
                                    'text-rose-600' => $transaction['tone'] === 'danger',
                                ])>{{ $transaction['amount'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">Static</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
