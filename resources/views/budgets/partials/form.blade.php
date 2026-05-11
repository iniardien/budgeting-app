<label class="budget-field">
    <span>Category</span>
    <select name="category_id" required>
        <option value="" disabled {{ old('category_id', $budget?->category_id) ? '' : 'selected' }}>Pilih kategori expense</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $budget?->category_id) === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
    @enderror
</label>

<div class="grid gap-5 sm:grid-cols-2">
    <label class="budget-field">
        <span>Month</span>
        <select name="month" required>
            @foreach ($months as $monthNumber => $monthName)
                <option value="{{ $monthNumber }}" @selected((string) old('month', $budget?->month ?? now()->month) === (string) $monthNumber)>
                    {{ $monthName }}
                </option>
            @endforeach
        </select>
        @error('month')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="budget-field">
        <span>Year</span>
        <select name="year" required>
            @foreach ($years as $year)
                <option value="{{ $year }}" @selected((string) old('year', $budget?->year ?? now()->year) === (string) $year)>
                    {{ $year }}
                </option>
            @endforeach
        </select>
        @error('year')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<label class="budget-field">
    <span>Budget Limit</span>
    <input
        type="number"
        name="limit_amount"
        step="0.01"
        min="0.01"
        value="{{ old('limit_amount', $budget?->limit_amount) }}"
        placeholder="Contoh: 1500000"
        required
    >
    @error('limit_amount')
        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
    @enderror
</label>
