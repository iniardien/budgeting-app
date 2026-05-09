@extends('layouts.app')

@section('title', 'Budgets')
@section('active_nav', 'budgets')

@section('content')
    @php
        $budgets = [
            ['category' => 'Groceries', 'spent' => '$245.75', 'limit' => '$400.00', 'remaining' => '$154.25', 'width' => '61%', 'status' => 'good'],
            ['category' => 'Dining', 'spent' => '$250.50', 'limit' => '$300.00', 'remaining' => '$49.50', 'width' => '84%', 'status' => 'warn'],
            ['category' => 'Entertainment', 'spent' => '$180.00', 'limit' => '$200.00', 'remaining' => '$20.00', 'width' => '90%', 'status' => 'danger'],
            ['category' => 'Transport', 'spent' => '$95.00', 'limit' => '$250.00', 'remaining' => '$155.00', 'width' => '38%', 'status' => 'good'],
        ];
    @endphp

    <section class="mx-auto max-w-5xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Budgets</h1>
                <p class="mt-1 text-sm text-slate-500">Set and manage your spending limits.</p>
            </div>

            <button type="button" class="budget-button budget-button-primary">Add Budget</button>
        </div>

        <div class="space-y-4">
            @foreach ($budgets as $budget)
                <article class="budget-panel">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-semibold text-slate-900">{{ $budget['category'] }}</h2>
                                <span @class([
                                    'budget-badge',
                                    'budget-badge-success' => $budget['status'] === 'good',
                                    'budget-badge-warn' => $budget['status'] === 'warn',
                                    'budget-badge-danger' => $budget['status'] === 'danger',
                                ])>{{ $budget['width'] }}</span>
                            </div>
                            <p class="text-sm text-slate-500">{{ $budget['spent'] }} of {{ $budget['limit'] }} spent</p>
                        </div>

                        <div class="flex items-center gap-3 text-xs font-medium text-slate-500">
                            <span class="rounded-full bg-slate-100 px-3 py-1">Edit</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1">Delete</span>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2">
                        <div class="budget-progress-track">
                            <div @class([
                                'budget-progress-fill',
                                'bg-emerald-500' => $budget['status'] === 'good',
                                'bg-amber-500' => $budget['status'] === 'warn',
                                'bg-rose-500' => $budget['status'] === 'danger',
                            ]) style="width: {{ $budget['width'] }}"></div>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Remaining: {{ $budget['remaining'] }}</span>
                            <span>Static preview only</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
