<div class="grid gap-5 sm:grid-cols-2">
    <label class="budget-field">
        <span>Type</span>
        <select name="type" required>
            @foreach ($transactionTypes as $transactionType)
                <option value="{{ $transactionType }}" @selected(old('type', $transaction?->type) === $transactionType)>
                    {{ ucfirst($transactionType) }}
                </option>
            @endforeach
        </select>
        @error('type')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="budget-field">
        <span>Category</span>
        <select name="category_id" required>
            <option value="" disabled {{ old('category_id', $transaction?->category_id) ? '' : 'selected' }}>Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $transaction?->category_id) === (string) $category->id)>
                    {{ $category->name }} ({{ ucfirst($category->type) }})
                </option>
            @endforeach
        </select>
        @error('category_id')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <label class="budget-field">
        <span>Amount</span>
        <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $transaction?->amount) }}" required>
        @error('amount')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="budget-field">
        <span>Date</span>
        <input type="date" name="date" value="{{ old('date', optional($transaction?->date)->format('Y-m-d')) }}" required>
        @error('date')
            <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<label class="budget-field">
    <span>Description</span>
    <textarea name="description" rows="4" placeholder="Catatan transaksi">{{ old('description', $transaction?->description) }}</textarea>
    @error('description')
        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
    @enderror
</label>

<div class="flex gap-3">
    <button type="submit" class="budget-button budget-button-primary">{{ $submitLabel }}</button>
    <a href="{{ route('transactions.index') }}" class="budget-button budget-button-secondary">Cancel</a>
</div>
