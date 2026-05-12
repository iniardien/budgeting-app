@extends('layouts.app')

@section('title', 'Edit Transaction')
@section('active_nav', 'transactions')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-6 md:px-8 md:py-8">
        <div class="budget-panel">
            <h1 class="text-3xl font-bold text-slate-900">Edit Transaction</h1>
            <p class="mt-2 text-sm text-slate-500">Perbarui detail transaksi Anda.</p>

            <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="mt-8 space-y-5">
                @csrf
                @method('PUT')
                @include('transactions.partials.form', ['submitLabel' => 'Update Transaction'])
            </form>
        </div>
    </section>
@endsection
