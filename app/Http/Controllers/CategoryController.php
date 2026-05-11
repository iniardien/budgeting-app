<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = $request->user()
            ->categories()
            ->latest()
            ->get();

        return view('categories.index', [
            'categories' => $categories,
            'categoryTypes' => $this->categoryTypes(),
        ]);
    }

    public function create(): View
    {
        return view('categories.create', [
            'categoryTypes' => $this->categoryTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);

        $request->user()->categories()->create($validated);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        $this->authorizeCategory($category);

        return view('categories.edit', [
            'category' => $category,
            'categoryTypes' => $this->categoryTypes(),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $validated = $this->validateCategory($request, $category->id);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil dihapus.');
    }

    /**
     * @return array<int, string>
     */
    private function categoryTypes(): array
    {
        return ['income', 'expense'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, ?int $ignoreCategoryId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->where(fn ($query) => $query->where('user_id', $request->user()->id))
                    ->ignore($ignoreCategoryId),
            ],
            'type' => ['required', Rule::in($this->categoryTypes())],
        ], [
            'name.unique' => 'Nama kategori sudah digunakan.',
            'type.in' => 'Tipe kategori tidak valid.',
        ]);
    }

    private function authorizeCategory(Category $category): void
    {
        abort_unless($category->user_id === auth()->id(), 403);
    }
}
