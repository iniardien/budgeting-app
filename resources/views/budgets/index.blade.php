@extends('layouts.app')

@section('title', 'Budgets')
@section('active_nav', 'budgets')

@section('content')
    <section class="mx-auto max-w-5xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Budgets</h1>
                <p class="mt-1 text-sm text-slate-500">Set and manage your spending limits.</p>
            </div>

            <a href="{{ route('budgets.create') }}" class="budget-button budget-button-primary">Add Budget</a>
        </div>

        @if ($budgets->isEmpty())
            <section class="budget-panel text-center">
                <h2 class="text-xl font-semibold text-slate-900">Belum ada budget</h2>
                <p class="mt-2 text-sm text-slate-500">Buat budget bulanan pertama Anda untuk mulai mengatur pengeluaran.</p>
                <a href="{{ route('budgets.create') }}" class="budget-button budget-button-primary mt-6">Create First Budget</a>
            </section>
        @else
            <div class="space-y-4">
                @foreach ($budgets as $budget)
                    <article class="budget-panel">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <h2 class="text-lg font-semibold text-slate-900">{{ $budget->category->name }}</h2>
                                    <span class="budget-badge budget-badge-danger">{{ ucfirst($budget->category->type) }}</span>
                                </div>
                                <p class="text-sm text-slate-500">
                                    Period: {{ \Carbon\Carbon::create()->month($budget->month)->format('F') }} {{ $budget->year }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('budgets.edit', $budget) }}" class="budget-button budget-button-secondary px-4 py-2">
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('Hapus budget ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="budget-button budget-button-danger px-4 py-2">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Budget Limit</p>
                                <p class="mt-2 text-2xl font-bold text-slate-900">Rp {{ number_format((float) $budget->limit_amount, 2, ',', '.') }}</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Category</p>
                                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $budget->category->name }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
