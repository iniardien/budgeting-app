@extends('layouts.app')

@section('title', 'Create Category')
@section('active_nav', 'categories')

@section('content')
    <section class="mx-auto max-w-3xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Create Category</h1>
                <p class="mt-1 text-sm text-slate-500">Tambahkan kategori baru untuk transaksi atau budget Anda.</p>
            </div>

            <a href="{{ route('categories.index') }}" class="budget-button budget-button-secondary">Back to Categories</a>
        </div>

        <section class="budget-panel">
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
                @csrf
                @include('categories.partials.form', ['category' => null])

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button type="submit" class="budget-button budget-button-primary">Save Category</button>
                    <a href="{{ route('categories.index') }}" class="budget-button budget-button-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </section>
@endsection
