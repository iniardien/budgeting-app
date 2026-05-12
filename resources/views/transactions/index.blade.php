@extends('layouts.app')

@section('title', 'Transactions')
@section('active_nav', 'transactions')

@section('content')
    <section class="mx-auto max-w-7xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Transactions</h1>
                <p class="mt-1 text-sm text-slate-500">Manage your income and expenses.</p>
            </div>

            <a href="{{ route('transactions.create') }}" class="budget-button budget-button-primary">Add Transaction</a>
        </div>

        <section class="budget-panel">
            <form method="GET" action="{{ route('transactions.index') }}" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                <label class="budget-field">
                    <span>Type</span>
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="income" @selected($selectedType === 'income')>Income</option>
                        <option value="expense" @selected($selectedType === 'expense')>Expense</option>
                    </select>
                </label>

                <label class="budget-field">
                    <span>Category</span>
                    <select name="category_id">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($selectedCategoryId === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="flex gap-3">
                    <button type="submit" class="budget-button budget-button-primary">Apply Filters</button>
                    <a href="{{ route('transactions.index') }}" class="budget-button budget-button-secondary">Reset Filters</a>
                </div>
            </form>
        </section>

        @if ($transactions->isEmpty())
            <section class="budget-panel text-center">
                <h2 class="text-xl font-semibold text-slate-900">Belum ada transaksi</h2>
                <p class="mt-2 text-sm text-slate-500">Tambahkan transaksi pertama Anda untuk mulai membaca ringkasan keuangan.</p>
                <a href="{{ route('transactions.create') }}" class="budget-button budget-button-primary mt-6">Create First Transaction</a>
            </section>
        @else
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
                                    <td class="px-6 py-4 font-medium text-slate-800">{{ $transaction->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $transaction->category->name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="budget-badge {{ $transaction->type === 'income' ? 'budget-badge-success' : 'budget-badge-danger' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">{{ $transaction->description ?: '-' }}</td>
                                    <td class="px-6 py-4 text-right font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}Rp {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="budget-button budget-button-secondary px-4 py-2">Edit</a>
                                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Hapus transaksi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="budget-button budget-button-danger px-4 py-2">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </section>
@endsection
