<select class="auth-input" name="service_job_id">
    <option value="">Company expense - no job</option>
    @foreach ($jobs as $job)
        <option value="{{ $job->id }}" @selected((string) old('service_job_id', $expense->service_job_id) === (string) $job->id)>{{ $job->job_number }} - {{ $job->customer?->name ?? 'Deleted customer' }}</option>
    @endforeach
</select>
<input class="auth-input" name="category_name" value="{{ old('category_name', $expense->category_name) }}" placeholder="Category e.g. shop rent, fuel, supplies">
@if ($canManageExpenses ?? false)
    <select class="auth-input" name="submitted_by">
        <option value="">No submitter</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected((string) old('submitted_by', $expense->submitted_by) === (string) $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
    <select class="auth-input" name="status" required>
        @foreach ($statuses as $expenseStatus)
            <option value="{{ $expenseStatus }}" @selected(old('status', $expense->status) === $expenseStatus)>{{ Str::headline($expenseStatus) }}</option>
        @endforeach
    </select>
@endif
<input class="auth-input" name="vendor" value="{{ old('vendor', $expense->vendor) }}" placeholder="Vendor">
<input class="auth-input" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $expense->amount) }}" placeholder="Amount" required>
<input class="auth-input" name="expense_date" type="date" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
<input class="auth-input" name="notes" value="{{ old('notes', $expense->notes) }}" placeholder="Notes">
<input class="auth-input" name="receipt" type="file" accept="image/png,image/jpeg,image/webp,application/pdf">
@if (($canManageExpenses ?? false))
    <input class="auth-input" name="approval_notes" value="{{ old('approval_notes', $expense->approval_notes) }}" placeholder="Approval notes">
@endif
@if ($expense->receiptUrl())
    <a class="button" href="{{ $expense->receiptUrl() }}" target="_blank" rel="noopener">Receipt</a>
@endif


