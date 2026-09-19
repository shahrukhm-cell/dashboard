<div class="customer-form-grid">
    <label class="auth-field">
        <span>Customer</span>
        <select class="auth-input" name="customer_id" required>
            <option value="">Select customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected((string) old('customer_id', $job->customer_id) === (string) $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
    </label>
    <label class="auth-field">
        <span>Status</span>
        <select class="auth-input" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $job->status) === $status)>{{ Str::headline($status) }}</option>
            @endforeach
        </select>
    </label>
    <label class="auth-field">
        <span>Quote status</span>
        <select class="auth-input" name="quote_status">
            @foreach ($quoteStatuses as $quoteStatus)
                <option value="{{ $quoteStatus }}" @selected(old('quote_status', $job->quote_status) === $quoteStatus)>{{ Str::headline($quoteStatus) }}</option>
            @endforeach
        </select>
    </label>
    <label class="auth-field">
        <span>Team</span>
        <select class="auth-input" name="team_id">
            <option value="">No team assigned</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected((string) old('team_id', $job->team_id) === (string) $team->id)>{{ $team->name }}{{ $team->lead() ? ' - Lead: '.$team->lead()->name : '' }}</option>
            @endforeach
        </select>
    </label>
    <label class="auth-field">
        <span>Assignee</span>
        <select class="auth-input" name="assigned_user_id">
            <option value="">No assignee</option>
            @foreach ($assignableUsers as $user)
                <option value="{{ $user->id }}" @selected((string) old('assigned_user_id', $job->assigned_user_id) === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </label>
    <label class="auth-field">
        <span>Pickup / scheduled time</span>
        <input class="auth-input" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at', $job->scheduled_at?->format('Y-m-d\TH:i')) }}">
    </label>
    <label class="auth-field">
        <span>Discount</span>
        <input class="auth-input" name="discount" type="number" step="0.01" min="0" value="{{ old('discount', $job->discount ?? 0) }}">
    </label>
    <label class="auth-field customer-span-2">
        <span>Customer address / job location</span>
        <input class="auth-input" name="service_address" value="{{ old('service_address', $job->service_address) }}">
    </label>
    <label class="auth-field customer-span-2">
        <span>Notes</span>
        <textarea class="auth-input" name="notes" rows="4">{{ old('notes', $job->notes) }}</textarea>
    </label>
</div>

<div class="job-items-panel">
    <div class="section-heading">
        <div><span class="eyebrow">Line items</span><h2>Services</h2></div>
    </div>

    @php($existingItems = old('items', $job->items->map(fn ($item) => ['service_id' => $item->service_id, 'quantity' => $item->quantity, 'unit_price' => $item->unit_price])->toArray() ?: [['service_id' => '', 'quantity' => 1, 'unit_price' => 0]]))

    <div class="job-items-list">
        @foreach ($existingItems as $index => $item)
            <div class="job-item-row">
                <select class="auth-input" name="items[{{ $index }}][service_id]" required>
                    <option value="">Select service</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" data-price="{{ number_format((float) $service->base_price, 2, '.', '') }}" @selected((string) ($item['service_id'] ?? '') === (string) $service->id)>{{ $service->name }} - base ${{ number_format((float) $service->base_price, 2) }}/{{ $service->unit_type }}</option>
                    @endforeach
                </select>
                <input class="auth-input" name="items[{{ $index }}][quantity]" type="number" min="0.01" step="0.01" value="{{ $item['quantity'] ?? 1 }}" required>
                <input class="auth-input" name="items[{{ $index }}][unit_price]" type="number" min="0" step="0.01" value="{{ $item['unit_price'] ?? 0 }}" placeholder="Job price" required>
            </div>
        @endforeach
    </div>
    <button class="button" type="button" data-add-job-item>Add service</button>
    <p class="form-error">Service base price is shown as a guide. Set the final job price on each line before saving.</p>
</div>

@if ($errors->any())
    <div class="auth-errors customer-errors">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<script>
    document.querySelectorAll('.job-item-row select[name$="[service_id]"]').forEach((select) => {
        const priceInput = select.closest('.job-item-row')?.querySelector('input[name$="[unit_price]"]');
        const defaultPrice = () => select.selectedOptions[0]?.dataset.price || '0.00';

        if (priceInput && Number.parseFloat(priceInput.value || '0') === 0) {
            priceInput.value = defaultPrice();
        }

        select.addEventListener('change', () => {
            if (priceInput) {
                priceInput.value = defaultPrice();
            }
        });
    });

    document.querySelector('[data-add-job-item]')?.addEventListener('click', () => {
        const list = document.querySelector('.job-items-list');
        const first = list?.querySelector('.job-item-row');
        if (!list || !first) return;

        const clone = first.cloneNode(true);
        const index = list.querySelectorAll('.job-item-row').length;
        clone.querySelectorAll('select, input').forEach((field) => {
            field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
            if (field.tagName === 'SELECT') field.selectedIndex = 0;
            if (field.name.endsWith('[quantity]')) field.value = '1';
            if (field.name.endsWith('[unit_price]')) field.value = '0.00';
        });
        list.appendChild(clone);
    });
</script>


