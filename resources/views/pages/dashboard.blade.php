@extends('layouts.app')

@section('title', 'Dashboard')
@section('active_nav', 'dashboard')

@section('content')
    @php
        $cards = [
            ['label' => 'Total Balance', 'amount' => $totalBalance, 'tone' => 'primary', 'meta' => 'Seluruh transaksi Anda'],
            ['label' => 'Total Income', 'amount' => $totalIncome, 'tone' => 'success', 'meta' => 'Akumulasi pemasukan'],
            ['label' => 'Total Expenses', 'amount' => $totalExpenses, 'tone' => 'danger', 'meta' => 'Akumulasi pengeluaran'],
        ];

        $formatMoney = fn (float $amount): string => 'Rp '.number_format($amount, 2, ',', '.');
        $maxExpense = max((float) ($expenseByCategory->max('total_amount') ?? 0), 1);
    @endphp

    <section class="mx-auto max-w-7xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-500">Welcome back, {{ auth()->user()->name }}! Here's your financial overview.</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="budget-button budget-button-danger">Logout</button>
            </form>
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
                    <p class="budget-amount">{{ $formatMoney((float) $card['amount']) }}</p>
                    <p class="budget-meta">{{ $card['meta'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(18rem,1fr)]">
            <section class="budget-panel">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Expenses by Category</h2>
                        <p class="mt-1 text-sm text-slate-500">Pengeluaran bulan berjalan berdasarkan kategori.</p>
                    </div>
                    <span class="budget-pill">{{ $currentMonthLabel }}</span>
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
                                <div class="budget-progress-fill bg-blue-500" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
                            Belum ada pengeluaran bulan ini.
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="budget-panel">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Budget Usage</h2>
                    <p class="mt-1 text-sm text-slate-500">Pemakaian budget aktif pada bulan berjalan.</p>
                </div>

                <div class="mt-6 space-y-5">
                    @forelse ($budgetUsage as $item)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $item['category'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $formatMoney($item['spent']) }} of {{ $formatMoney($item['limit']) }}</p>
                                </div>
                                <span @class([
                                    'budget-badge',
                                    'budget-badge-success' => $item['status'] === 'good',
                                    'budget-badge-warn' => $item['status'] === 'warn',
                                    'budget-badge-danger' => $item['status'] === 'danger',
                                ])>{{ $item['percentage'] }}%</span>
                            </div>

                            <div class="budget-progress-track">
                                <div @class([
                                    'budget-progress-fill',
                                    'bg-emerald-500' => $item['status'] === 'good',
                                    'bg-amber-500' => $item['status'] === 'warn',
                                    'bg-rose-500' => $item['status'] === 'danger',
                                ]) style="width: {{ $item['percentage'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500">
                            Belum ada budget aktif.
                        </p>
                    @endforelse
                </div>
            </section>
        </div>
    </section>
@endsection
