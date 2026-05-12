<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $categories = $request->user()
            ->categories()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $transactions = $request->user()
            ->transactions()
            ->with('category')
            ->when(
                in_array($request->string('type')->toString(), ['income', 'expense'], true),
                fn ($query) => $query->where('type', $request->string('type')->toString())
            )
            ->when(
                $categories->pluck('id')->contains((int) $request->integer('category_id')),
                fn ($query) => $query->where('category_id', $request->integer('category_id'))
            )
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('transactions.index', [
            'transactions' => $transactions,
            'categories' => $categories,
            'selectedType' => $request->string('type')->toString(),
            'selectedCategoryId' => (string) $request->input('category_id', ''),
        ]);
    }

    public function create(Request $request): View
    {
        return view('transactions.create', [
            'transaction' => null,
            'categories' => $request->user()->categories()->orderBy('type')->orderBy('name')->get(),
            'transactionTypes' => ['income', 'expense'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTransaction($request);

        $request->user()->transactions()->create($validated);

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);

        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => $request->user()->categories()->orderBy('type')->orderBy('name')->get(),
            'transactionTypes' => ['income', 'expense'],
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $validated = $this->validateTransaction($request);

        $transaction->update($validated);

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTransaction(Request $request): array
    {
        $category = $request->user()
            ->categories()
            ->whereKey($request->input('category_id'))
            ->first();

        return $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'type' => [
                'required',
                Rule::in(['income', 'expense']),
                function (string $attribute, mixed $value, Closure $fail) use ($category): void {
                    if ($category && $category->type !== $value) {
                        $fail('Tipe transaksi harus sama dengan tipe kategori.');
                    }
                },
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ], [
            'category_id.exists' => 'Kategori transaksi harus milik Anda.',
        ]);
    }

    private function authorizeTransaction(Transaction $transaction): void
    {
        abort_unless($transaction->user_id === auth()->id(), 403);
    }
}
