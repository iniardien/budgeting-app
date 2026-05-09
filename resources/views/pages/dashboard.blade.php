@extends('layouts.app')

@section('title', 'Dashboard')
@section('active_nav', 'dashboard')

@section('content')
    @php
        $cards = [
            ['label' => 'Total Balance', 'amount' => '$4,348.50', 'tone' => 'primary', 'meta' => '+12.4% vs last month'],
            ['label' => 'Total Income', 'amount' => '$7,420.00', 'tone' => 'success', 'meta' => '3 income streams tracked'],
            ['label' => 'Total Expenses', 'amount' => '$3,071.50', 'tone' => 'danger', 'meta' => 'Across 8 categories'],
        ];

        $expenseBars = [
            ['label' => 'Groceries', 'value' => '$860', 'width' => '82%'],
            ['label' => 'Utilities', 'value' => '$640', 'width' => '64%'],
            ['label' => 'Dining', 'value' => '$420', 'width' => '42%'],
            ['label' => 'Transport', 'value' => '$290', 'width' => '28%'],
            ['label' => 'Shopping', 'value' => '$190', 'width' => '18%'],
        ];

        $budgetUsage = [
            ['category' => 'Groceries', 'spent' => '$245.75', 'limit' => '$400.00', 'width' => '61%', 'status' => 'good'],
            ['category' => 'Dining', 'spent' => '$250.50', 'limit' => '$300.00', 'width' => '84%', 'status' => 'warn'],
            ['category' => 'Entertainment', 'spent' => '$180.00', 'limit' => '$200.00', 'width' => '90%', 'status' => 'danger'],
            ['category' => 'Transport', 'spent' => '$95.00', 'limit' => '$250.00', 'width' => '38%', 'status' => 'good'],
        ];
    @endphp

    <section class="mx-auto max-w-7xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-500">Welcome back! Here's your financial overview.</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500 shadow-sm">
                Updated for static Laravel preview
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($cards as $card)
                <article @class([
                    'budget-card',
                    'budget-card-primary' => $card['tone'] === 'primary',
                    'budget-card-success' => $card['tone'] === 'success',
                    'budget-card-danger' => $card['tone'] === 'danger',
                ])>
                    <p class="budget-label">{{ $card['label'] }}</p>
                    <p class="budget-amount">{{ $card['amount'] }}</p>
                    <p class="budget-meta">{{ $card['meta'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(18rem,1fr)]">
            <section class="budget-panel">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Expenses by Category</h2>
                        <p class="mt-1 text-sm text-slate-500">A static snapshot of this month's spending mix.</p>
                    </div>
                    <span class="budget-pill">May 2026</span>
                </div>

                <div class="mt-8 space-y-5">
                    @foreach ($expenseBars as $bar)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $bar['label'] }}</span>
                                <span class="text-slate-500">{{ $bar['value'] }}</span>
                            </div>
                            <div class="budget-progress-track">
                                <div class="budget-progress-fill bg-blue-500" style="width: {{ $bar['width'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="budget-panel">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Budget Usage</h2>
                    <p class="mt-1 text-sm text-slate-500">How close each budget is to its current limit.</p>
                </div>

                <div class="mt-6 space-y-5">
                    @foreach ($budgetUsage as $item)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $item['category'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $item['spent'] }} of {{ $item['limit'] }}</p>
                                </div>
                                <span @class([
                                    'budget-badge',
                                    'budget-badge-success' => $item['status'] === 'good',
                                    'budget-badge-warn' => $item['status'] === 'warn',
                                    'budget-badge-danger' => $item['status'] === 'danger',
                                ])>{{ $item['width'] }}</span>
                            </div>

                            <div class="budget-progress-track">
                                <div @class([
                                    'budget-progress-fill',
                                    'bg-emerald-500' => $item['status'] === 'good',
                                    'bg-amber-500' => $item['status'] === 'warn',
                                    'bg-rose-500' => $item['status'] === 'danger',
                                ]) style="width: {{ $item['width'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </section>
@endsection
