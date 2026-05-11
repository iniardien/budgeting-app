@extends('layouts.app')

@section('title', 'Create Budget')
@section('active_nav', 'budgets')

@section('content')
    <section class="mx-auto max-w-3xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Create Budget</h1>
                <p class="mt-1 text-sm text-slate-500">Tambahkan limit pengeluaran per kategori dan periode.</p>
            </div>

            <a href="{{ route('budgets.index') }}" class="budget-button budget-button-secondary">Back to Budgets</a>
        </div>

        @if ($categories->isEmpty())
            <section class="budget-panel">
                <h2 class="text-xl font-semibold text-slate-900">Kategori expense belum tersedia</h2>
                <p class="mt-2 text-sm text-slate-500">Buat kategori bertipe expense terlebih dahulu sebelum membuat budget.</p>
                <a href="{{ route('categories.create') }}" class="budget-button budget-button-primary mt-6">Create Expense Category</a>
            </section>
        @else
            <section class="budget-panel">
                <form method="POST" action="{{ route('budgets.store') }}" class="space-y-5">
                    @csrf
                    @include('budgets.partials.form', ['budget' => null])

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <button type="submit" class="budget-button budget-button-primary">Save Budget</button>
                        <a href="{{ route('budgets.index') }}" class="budget-button budget-button-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        @endif
    </section>
@endsection
