<select class="auth-input" name="service_job_id">
    <option value="">No job</option>
    @foreach ($jobs as $job)
        <option value="{{ $job->id }}" @selected((string) old('service_job_id', $payment->service_job_id) === (string) $job->id)>{{ $job->job_number }} - {{ $job->customer->name ?? '' }}</option>
    @endforeach
</select>
<select class="auth-input" name="team_id">
    <option value="">No team</option>
    @foreach ($teams as $team)
        <option value="{{ $team->id }}" @selected((string) old('team_id', $payment->team_id) === (string) $team->id)>{{ $team->name }}</option>
    @endforeach
</select>
<select class="auth-input" name="user_id">
    <option value="">No user</option>
    @foreach ($users as $user)
        <option value="{{ $user->id }}" @selected((string) old('user_id', $payment->user_id) === (string) $user->id)>{{ $user->name }}</option>
    @endforeach
</select>
<select class="auth-input" name="status" required>
    @foreach ($teamStatuses as $paymentStatus)
        <option value="{{ $paymentStatus }}" @selected(old('status', $payment->status) === $paymentStatus)>{{ Str::headline($paymentStatus) }}</option>
    @endforeach
</select>
<select class="auth-input" name="method" required>
    @foreach ($teamMethods as $method)
        <option value="{{ $method }}" @selected(old('method', $payment->method) === $method)>{{ Str::headline($method) }}</option>
    @endforeach
</select>
<input class="auth-input" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $payment->amount) }}" placeholder="Amount" required>
<input class="auth-input" name="paid_at" type="date" value="{{ old('paid_at', optional($payment->paid_at)->format('Y-m-d')) }}">
<input class="auth-input" name="reference" value="{{ old('reference', $payment->reference) }}" placeholder="Reference">
<input class="auth-input" name="notes" value="{{ old('notes', $payment->notes) }}" placeholder="Notes">