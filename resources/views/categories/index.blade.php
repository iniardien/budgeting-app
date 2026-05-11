@extends('layouts.app')

@section('title', 'Categories')
@section('active_nav', 'categories')

@section('content')
    <section class="mx-auto max-w-6xl space-y-8 px-4 py-6 md:px-8 md:py-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Categories</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola kategori pemasukan dan pengeluaran Anda.</p>
            </div>

            <a href="{{ route('categories.create') }}" class="budget-button budget-button-primary">Add Category</a>
        </div>

        <section class="budget-panel overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Created</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($categories as $category)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-4 font-medium text-slate-800">{{ $category->name }}</td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'budget-badge',
                                        'budget-badge-success' => $category->type === 'income',
                                        'budget-badge-danger' => $category->type === 'expense',
                                    ])>
                                        {{ ucfirst($category->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $category->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('categories.edit', $category) }}" class="budget-button budget-button-secondary px-4 py-2">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="budget-button budget-button-danger px-4 py-2">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                    Belum ada kategori. Tambahkan kategori pertama Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
