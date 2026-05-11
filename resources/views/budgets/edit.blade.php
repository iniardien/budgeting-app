@extends('layouts.app')

@section('title', 'Edit Budget')
@section('active_nav', 'budgets')

@section('content')
    <section class="mx-auto max-w-3xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Edit Budget</h1>
                <p class="mt-1 text-sm text-slate-500">Perbarui limit budget bulanan sesuai kebutuhan.</p>
            </div>

            <a href="{{ route('budgets.index') }}" class="budget-button budget-button-secondary">Back to Budgets</a>
        </div>

        <section class="budget-panel">
            <form method="POST" action="{{ route('budgets.update', $budget) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('budgets.partials.form', ['budget' => $budget])

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button type="submit" class="budget-button budget-button-primary">Update Budget</button>
                    <a href="{{ route('budgets.index') }}" class="budget-button budget-button-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </section>
@endsection
