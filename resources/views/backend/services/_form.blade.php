<div class="customer-form-grid">
    <label class="auth-field">
        <span>Name</span>
        <input class="auth-input" name="name" value="{{ old('name', $service->name) }}" required>
    </label>
    <label class="auth-field">
        <span>Category</span>
        <select class="auth-input" name="service_category_id">
            <option value="">No category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('service_category_id', $service->service_category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </label>
    <label class="auth-field">
        <span>Unit type</span>
        <select class="auth-input" name="unit_type" required>
            @foreach ($unitTypes as $unitType)
                <option value="{{ $unitType }}" @selected(old('unit_type', $service->unit_type) === $unitType)>{{ Str::upper($unitType) }}</option>
            @endforeach
        </select>
    </label>
    <label class="auth-field">
        <span>Base price</span>
        <input class="auth-input" name="base_price" type="number" step="0.01" min="0" value="{{ old('base_price', $service->base_price ?? 0) }}" required>
    </label>
    <label class="auth-field customer-span-2">
        <span>Description</span>
        <textarea class="auth-input" name="description" rows="4">{{ old('description', $service->description) }}</textarea>
    </label>
    <label class="permission-check customer-span-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true))>
        <span>Service is active</span>
    </label>
</div>

@if ($errors->any())
    <div class="auth-errors customer-errors">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif