@if ($errors->any())
    <div class="auth-errors customer-errors">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
<div class="customer-form-grid">
    <label class="auth-field">
        <span>Name</span>
        <input class="auth-input" name="name" value="{{ old('name', $customer->name) }}" required>
    </label>
    <label class="auth-field">
        <span>Status</span>
        <select class="auth-input" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $customer->status) === $status)>{{ Str::title($status) }}</option>
            @endforeach
        </select>
    </label>
    <label class="auth-field">
        <span>Email</span>
        <input class="auth-input" name="email" required type="email" value="{{ old('email', $customer->email) }}">
    </label>
    <label class="auth-field">
        <span>Phone</span>
        <input class="auth-input" name="phone" required value="{{ old('phone', $customer->phone) }}">
    </label>
    <label class="auth-field customer-span-2">
        <span>Company</span>
        <input class="auth-input" name="company" value="{{ old('company', $customer->company) }}">
    </label>
    <label class="auth-field customer-span-2">
        <span>Address</span>
        <input class="auth-input" name="address_line" value="{{ old('address_line', $customer->address_line) }}">
    </label>
    <label class="auth-field">
        <span>City</span>
        <input class="auth-input" name="city" value="{{ old('city', $customer->city) }}">
    </label>
    <label class="auth-field">
        <span>State</span>
        <input class="auth-input" name="state" value="{{ old('state', $customer->state) }}">
    </label>
    <label class="auth-field">
        <span>Postal code</span>
        <input type="number" class="auth-input" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}">
    </label>
    <label class="auth-field customer-span-2">
        <span>Notes</span>
        <textarea class="auth-input" name="notes" rows="5">{{ old('notes', $customer->notes) }}</textarea>
    </label>
</div>

