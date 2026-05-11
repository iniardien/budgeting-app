<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $budgets = $request->user()
            ->budgets()
            ->with('category')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderBy('category_id')
            ->get();

        return view('budgets.index', [
            'budgets' => $budgets,
        ]);
    }

    public function create(Request $request): View
    {
        return view('budgets.create', [
            'categories' => $this->expenseCategories($request),
            'months' => $this->months(),
            'years' => $this->years(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBudget($request);

        $request->user()->budgets()->create($validated);

        return redirect()
            ->route('budgets.index')
            ->with('status', 'Budget berhasil ditambahkan.');
    }

    public function edit(Request $request, Budget $budget): View
    {
        $this->authorizeBudget($budget);

        return view('budgets.edit', [
            'budget' => $budget->load('category'),
            'categories' => $this->expenseCategories($request),
            'months' => $this->months(),
            'years' => $this->years(),
        ]);
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorizeBudget($budget);

        $validated = $this->validateBudget($request, $budget->id);

        $budget->update($validated);

        return redirect()
            ->route('budgets.index')
            ->with('status', 'Budget berhasil diperbarui.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorizeBudget($budget);

        $budget->delete();

        return redirect()
            ->route('budgets.index')
            ->with('status', 'Budget berhasil dihapus.');
    }

    /**
     * @return array<int, string>
     */
    private function months(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    /**
     * @return array<int, int>
     */
    private function years(): array
    {
        $currentYear = (int) now()->year;

        return range($currentYear - 1, $currentYear + 3);
    }

    private function authorizeBudget(Budget $budget): void
    {
        abort_unless($budget->user_id === auth()->id(), 403);
    }

    private function expenseCategories(Request $request)
    {
        return $request->user()
            ->categories()
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBudget(Request $request, ?int $ignoreBudgetId = null): array
    {
        return $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('type', 'expense')),
                Rule::unique('budgets')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('category_id', $request->input('category_id'))
                    ->where('month', $request->input('month'))
                    ->where('year', $request->input('year')))
                    ->ignore($ignoreBudgetId),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'limit_amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'category_id.exists' => 'Kategori budget harus milik Anda dan bertipe expense.',
            'category_id.unique' => 'Budget untuk kategori dan periode tersebut sudah ada.',
            'limit_amount.min' => 'Limit budget minimal 0.01.',
        ]);
    }
}
