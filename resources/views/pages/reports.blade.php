@extends('layouts.app')

@section('title', 'Reports')
@section('active_nav', 'reports')

@section('content')
    @php
        $summary = [
            ['label' => 'Total Income', 'amount' => '$21,600.00', 'tone' => 'success'],
            ['label' => 'Total Expenses', 'amount' => '$11,940.50', 'tone' => 'danger'],
            ['label' => 'Net Savings', 'amount' => '$9,659.50', 'tone' => 'primary'],
        ];

        $months = [
            ['month' => 'Dec', 'income' => '60%', 'expense' => '40%'],
            ['month' => 'Jan', 'income' => '68%', 'expense' => '44%'],
            ['month' => 'Feb', 'income' => '64%', 'expense' => '38%'],
            ['month' => 'Mar', 'income' => '72%', 'expense' => '47%'],
            ['month' => 'Apr', 'income' => '75%', 'expense' => '51%'],
            ['month' => 'May', 'income' => '70%', 'expense' => '49%'],
        ];
    @endphp

    <section class="mx-auto max-w-6xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Reports</h1>
            <p class="mt-1 text-sm text-slate-500">Analyze your financial trends and patterns.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($summary as $item)
                <article @class([
                    'budget-card',
                    'budget-card-success' => $item['tone'] === 'success',
                    'budget-card-danger' => $item['tone'] === 'danger',
                    'budget-card-primary' => $item['tone'] === 'primary',
                ])>
                    <p class="budget-label">{{ $item['label'] }}</p>
                    <p class="budget-amount">{{ $item['amount'] }}</p>
                    <p class="budget-meta">Static 6-month snapshot</p>
                </article>
            @endforeach
        </div>

        <section class="budget-panel">
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Income vs Expenses</h2>
                    <p class="text-sm text-slate-500">Last six months visualized as static comparison bars.</p>
                </div>
                <span class="budget-pill">6 months</span>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($months as $month)
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-slate-900">{{ $month['month'] }}</h3>
                            <span class="text-xs text-slate-500">Projected</span>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                                    <span>Income</span>
                                    <span>{{ $month['income'] }}</span>
                                </div>
                                <div class="budget-progress-track">
                                    <div class="budget-progress-fill bg-emerald-500" style="width: {{ $month['income'] }}"></div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                                    <span>Expense</span>
                                    <span>{{ $month['expense'] }}</span>
                                </div>
                                <div class="budget-progress-track">
                                    <div class="budget-progress-fill bg-rose-500" style="width: {{ $month['expense'] }}"></div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </section>
@endsection
