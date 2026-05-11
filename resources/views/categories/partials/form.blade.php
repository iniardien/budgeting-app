<label class="budget-field">
    <span>Category Name</span>
    <input
        type="text"
        name="name"
        value="{{ old('name', $category?->name) }}"
        placeholder="Contoh: Groceries"
        required
    >
    @error('name')
        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
    @enderror
</label>

<label class="budget-field">
    <span>Type</span>
    <select name="type" required>
        <option value="" disabled {{ old('type', $category?->type) ? '' : 'selected' }}>Pilih tipe kategori</option>
        @foreach ($categoryTypes as $type)
            <option value="{{ $type }}" @selected(old('type', $category?->type) === $type)>
                {{ ucfirst($type) }}
            </option>
        @endforeach
    </select>
    @error('type')
        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
    @enderror
</label>
