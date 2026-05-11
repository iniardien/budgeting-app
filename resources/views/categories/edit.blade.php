@extends('layouts.app')

@section('title', 'Edit Category')
@section('active_nav', 'categories')

@section('content')
    <section class="mx-auto max-w-3xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Edit Category</h1>
                <p class="mt-1 text-sm text-slate-500">Perbarui kategori sesuai kebutuhan pencatatan Anda.</p>
            </div>

            <a href="{{ route('categories.index') }}" class="budget-button budget-button-secondary">Back to Categories</a>
        </div>

        <section class="budget-panel">
            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('categories.partials.form', ['category' => $category])

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button type="submit" class="budget-button budget-button-primary">Update Category</button>
                    <a href="{{ route('categories.index') }}" class="budget-button budget-button-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </section>
@endsection
