@extends('layouts.app')

@section('title', 'Reports')
@section('active_nav', 'reports')

@section('content')
    @php
        $summary = [
            ['label' => 'Total Income', 'amount' => $totalIncome, 'tone' => 'success'],
            ['label' => 'Total Expenses', 'amount' => $totalExpenses, 'tone' => 'danger'],
            ['label' => 'Net Savings', 'amount' => $netSavings, 'tone' => 'primary'],
        ];

        $formatMoney = fn (float $amount): string => 'Rp '.number_format($amount, 2, ',', '.');
        $maxIncome = max((float) ($incomeByCategory->max('total_amount') ?? 0), 1);
        $maxExpense = max((float) ($expenseByCategory->max('total_amount') ?? 0), 1);
    @endphp

    <section class="mx-auto max-w-6xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Reports</h1>
                <p class="mt-1 text-sm text-slate-500">Analyze your financial trends and patterns.</p>
            </div>

            <form method="GET" action="{{ route('reports') }}" class="grid gap-3 sm:grid-cols-2 lg:flex lg:items-end">
                <label class="budget-field min-w-[10rem]">
                    <span>Month</span>
                    <select name="month">
                        @foreach ($months as $monthNumber => $monthName)
                            <option value="{{ $monthNumber }}" @selected($selectedMonth === $monthNumber)>
                                {{ $monthName }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="budget-field min-w-[8rem]">
                    <span>Year</span>
                    <select name="year">
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected($selectedYear === $year)>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="flex gap-3">
                    <button type="submit" class="budget-button budget-button-primary">Apply</button>
                    <a href="{{ route('reports') }}" class="budget-button budget-button-secondary">Reset</a>
                </div>
            </form>
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
                    <p class="budget-amount">{{ $formatMoney((float) $item['amount']) }}</p>
                    <p class="budget-meta">{{ $currentPeriodLabel }}</p>
                </article>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="budget-panel">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Income by Category</h2>
                        <p class="text-sm text-slate-500">Breakdown pemasukan pada periode terpilih.</p>
                    </div>
                    <span class="budget-pill">{{ $currentPeriodLabel }}</span>
                </div>

                <div class="mt-8 space-y-5">
                    @forelse ($incomeByCategory as $item)
                        @php
                            $width = min(100, (int) round((((float) $item->total_amount) / $maxIncome) * 100));
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $item->category_name }}</span>
                                <span class="text-slate-500">{{ $formatMoney((float) $item->total_amount) }}</span>
                            </div>
                            <div class="budget-progress-track">
                                <div class="budget-progress-fill bg-emerald-500" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
                            Belum ada pemasukan pada periode ini.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="budget-panel">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Expense by Category</h2>
                        <p class="text-sm text-slate-500">Breakdown pengeluaran pada periode terpilih.</p>
                    </div>
                    <span class="budget-pill">{{ $currentPeriodLabel }}</span>
                </div>

                <div class="mt-8 space-y-5">
                    @forelse ($expenseByCategory as $item)
                        @php
                            $width = min(100, (int) round((((float) $item->total_amount) / $maxExpense) * 100));
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $item->category_name }}</span>
                                <span class="text-slate-500">{{ $formatMoney((float) $item->total_amount) }}</span>
                            </div>
                            <div class="budget-progress-track">
                                <div class="budget-progress-fill bg-rose-500" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
                            Belum ada pengeluaran pada periode ini.
                        </p>
                    @endforelse
                </div>
            </section>
        </div>
    </section>
@endsection
