<x-backend-layout title="Job details">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $job->customer?->name ?? 'Deleted customer' }}</span>
            <h2>{{ $job->job_number }}</h2>
        </div>
        <div class="management-actions">
            <a class="button button-primary" href="{{ route('jobs.quote.download', $job) }}">Download quote</a>
            @if ($canViewFinance)
                <a class="button button-primary" href="{{ route('jobs.current-invoice', $job) }}">Download current invoice</a>
            @endif
            @if ($job->status === 'completed' && $canViewFinance)
                <a class="button button-primary" href="{{ route('jobs.invoice', $job) }}">Download invoice</a>
            @endif
            @if (auth()->user()->hasPermission('jobs.manage', $tenant))
                <a class="button button-primary" href="{{ route('jobs.edit', $job) }}">Edit job</a>
            @endif
            @if ($job->customer)
                <a class="button" href="{{ route('customers.show', $job->customer) }}">Back to customer</a>
            @endif
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <section class="auth-errors customer-errors">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </section>
    @endif

    <section class="dashboard-grid management-grid">
        <article class="glass-card customer-detail-card">
            <span class="tenant-status tenant-status-{{ $job->status === 'completed' ? 'active' : ($job->status === 'cancelled' ? 'archived' : 'suspended') }}">{{ Str::headline($job->status) }}</span>
            <h2>Job summary</h2>
            <dl class="detail-list">
                <dt>Customer</dt><dd>@if ($job->customer)<a class="text-link" href="{{ route('customers.show', $job->customer) }}">{{ $job->customer->name }}</a>@else Deleted customer @endif</dd>
                <dt>Phone</dt><dd>{{ $job->customer?->phone ?: 'Not added' }}</dd>
                <dt>Scheduled</dt><dd>{{ $job->scheduled_at?->format('M j, Y g:i A') ?? 'Not scheduled' }}</dd>
                <dt>Team</dt><dd>{{ $job->team?->name ?? 'Unassigned' }}</dd>
                <dt>Lead</dt><dd>{{ $job->team?->lead()?->name ?? 'No lead selected' }}</dd>
                <dt>Assignee</dt><dd>{{ $job->assignee?->name ?? 'Unassigned' }}</dd>
                <dt>Address</dt><dd>{{ $job->service_address ?: ($job->customer?->addressSummary() ?: 'Not added') }}</dd>
                <dt>Time logged</dt><dd>{{ round($job->timeEntries->sum('minutes') / 60, 2) }} hours</dd>
                <dt>Quote</dt><dd>{{ Str::headline($job->quote_status ?? 'draft') }}</dd>
                @if ($canViewFinance)
                    <dt>Total</dt><dd>${{ number_format((float) $job->total, 2) }}</dd>
                    <dt>Customer paid</dt><dd>${{ number_format((float) $job->customerPayments->where('status', 'paid')->sum('amount'), 2) }}</dd>
                    <dt>Balance due</dt><dd>${{ number_format(max(0, (float) $job->total - (float) $job->customerPayments->where('status', 'paid')->sum('amount')), 2) }}</dd>
                    <dt>Approved expenses</dt><dd>${{ number_format((float) $job->expenses->whereIn('status', ['approved', 'reimbursed'])->sum('amount'), 2) }}</dd>
                    <dt>Team paid</dt><dd>${{ number_format((float) $job->teamPayments->where('status', 'paid')->sum('amount'), 2) }}</dd>
                    <dt>Current invoice</dt><dd><a class="text-link" href="{{ route('jobs.current-invoice', $job) }}">Download current invoice</a></dd>
                    @if ($job->status === 'completed')
                        <dt>Final invoice</dt><dd><a class="text-link" href="{{ route('jobs.invoice', $job) }}">Download final invoice</a></dd>
                    @endif
                @endif
            </dl>
        </article>

        <article class="glass-card customer-detail-card activity-card">
            <h2>Services</h2>
            <div class="job-items-summary">
                @foreach ($job->items as $item)
                    <div class="job-summary-row">
                        <span>{{ $item->name }} <small>{{ $item->quantity }} {{ $item->unit_type }}</small></span>
                        @if ($canViewFinance)
                            <strong>${{ number_format((float) $item->line_total, 2) }}</strong>
                        @endif
                    </div>
                @endforeach
                @if ($canViewFinance)
                    <div class="job-summary-row"><span>Subtotal</span><strong>${{ number_format((float) $job->subtotal, 2) }}</strong></div>
                    <div class="job-summary-row"><span>Discount</span><strong>${{ number_format((float) $job->discount, 2) }}</strong></div>
                    <div class="job-summary-row total"><span>Total</span><strong>${{ number_format((float) $job->total, 2) }}</strong></div>
                @endif
            </div>
        </article>
    </section>


    <section class="dashboard-grid management-grid">
        <article class="glass-card customer-detail-card">
            <div class="section-heading">
                <div><span class="eyebrow">Quote</span><h2>Job quote</h2></div>
                <span class="tenant-status tenant-status-{{ ($job->quote_status ?? 'draft') === 'approved' ? 'active' : (($job->quote_status ?? 'draft') === 'declined' ? 'archived' : 'suspended') }}">{{ Str::headline($job->quote_status ?? 'draft') }}</span>
            </div>
            @if (auth()->user()->hasPermission('jobs.manage', $tenant))
                <form class="customer-toolbar" method="POST" action="{{ route('jobs.quote.update', $job) }}">
                    @csrf
                    <select class="auth-input" name="quote_status" required>
                        @foreach ($quoteStatuses as $jobQuoteStatus)
                            <option value="{{ $jobQuoteStatus }}" @selected(($job->quote_status ?? 'draft') === $jobQuoteStatus)>{{ Str::headline($jobQuoteStatus) }}</option>
                        @endforeach
                    </select>
                    <button class="button button-primary" type="submit">Update quote</button>
                </form>
            @endif
            <dl class="detail-list">
                <dt>Sent</dt><dd>{{ $job->quote_sent_at?->format('M j, Y g:i A') ?? 'Not sent' }}</dd>
                <dt>Approved</dt><dd>{{ $job->quote_approved_at?->format('M j, Y g:i A') ?? 'Not approved' }}</dd>
            </dl>
        </article>

        <article class="glass-card customer-detail-card activity-card">
            <div class="section-heading"><div><span class="eyebrow">Completion</span><h2>Before / after images</h2></div></div>
            @if ($canUploadJobPhotos)
                <form class="expense-form" method="POST" action="{{ route('jobs.photos.store', $job) }}" enctype="multipart/form-data">
                    @csrf
                    <select class="auth-input" name="type" required>
                        @foreach ($photoTypes as $photoType)
                            <option value="{{ $photoType }}">{{ Str::headline($photoType) }}</option>
                        @endforeach
                    </select>
                    <input class="auth-input" name="photo" type="file" accept="image/png,image/jpeg,image/webp" required>
                    <input class="auth-input" name="caption" placeholder="Caption">
                    <button class="button button-primary" type="submit">Upload image</button>
                </form>
            @endif
      
            <div class="job-items-summary">
                @foreach (['before' => 'Before', 'after' => 'After'] as $type => $label)
                    <div class="job-summary-row"><span>{{ $label }}</span><strong>{{ $job->photos->where('type', $type)->count() }}</strong></div>
                    @foreach ($job->photos->where('type', $type) as $photo)
                        <div class="job-summary-row">
                    {{-- @php
                        dd($photo->url() );
                    @endphp --}}
                            <span><a class="text-link" href="{{ $photo->url() }}" target="_blank" rel="noopener">{{ $photo->caption ?: $label.' image' }}</a> <small>{{ $photo->uploader?->name ?? 'Unknown' }}</small></span>
                            <strong>{{ $photo->created_at->format('M j') }}</strong>
                        </div>
                    @endforeach
                @endforeach
            </div>
            <p class="customer-notes">A job needs at least one before image and one after image before it can be completed.</p>
        </article>
    </section>    <section class="glass-card management-card">
        <div class="section-heading">
            <div><span class="eyebrow">Status</span><h2>Job status</h2></div>
            <span class="tenant-status tenant-status-{{ $job->status === 'completed' ? 'active' : ($job->status === 'cancelled' ? 'archived' : 'suspended') }}">{{ Str::headline($job->status) }}</span>
        </div>
        @if ($canUpdateStatus)
            <form class="customer-toolbar" method="POST" action="{{ route('jobs.status.update', $job) }}">
                @csrf
                <select class="auth-input" name="status" required>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}" @selected($job->status === $statusOption)>{{ Str::headline($statusOption) }}</option>
                    @endforeach
                </select>
                <input class="auth-input" name="status_notes" placeholder="Status notes">
                <button class="button button-primary" type="submit">Update status</button>
            </form>
        @endif
        <div class="time-entry-list compact">
            @forelse ($job->statusEvents->sortByDesc('changed_at') as $event)
                <article class="time-entry-row job-event-row">
                    <div><strong>{{ Str::headline($event->new_status) }}</strong><small>{{ $event->changed_at->format('M j, Y g:i A') }}</small></div>
                    <span>{{ $event->user?->name ?? 'System' }}</span>
                    <span>{{ $event->old_status ? Str::headline($event->old_status).' to '.Str::headline($event->new_status) : 'Initial status' }}</span>
                    <span>{{ $event->notes ?: 'No notes' }}</span>
                </article>
            @empty
                <p class="customer-notes">No status history yet.</p>
            @endforelse
        </div>
    </section>
    <section class="glass-card management-card">
        <div class="section-heading">
            <div><span class="eyebrow">Time</span><h2>Job timer</h2></div>
            <span class="tenant-status tenant-status-{{ $workState === 'ended' ? 'active' : ($workState === 'not_started' ? 'suspended' : 'active') }}">{{ Str::headline($workState) }}</span>
        </div>
        <div class="job-timer-grid">
            <div class="timer-stat"><span>Total time</span><strong>{{ intdiv($workSummary['total_minutes'], 60) }}h {{ $workSummary['total_minutes'] % 60 }}m</strong></div>
            <div class="timer-stat"><span>Working time</span><strong>{{ intdiv($workSummary['working_minutes'], 60) }}h {{ $workSummary['working_minutes'] % 60 }}m</strong></div>
            <div class="timer-stat"><span>Break time</span><strong>{{ intdiv($workSummary['break_minutes'], 60) }}h {{ $workSummary['break_minutes'] % 60 }}m</strong></div>
        </div>
        @if ($canWorkJob)
            <div class="management-actions job-work-actions">
                @if ($workState === 'not_started')
                    <form method="POST" action="{{ route('jobs.work.start', $job) }}">@csrf<button class="button button-primary" type="submit">Start job</button></form>
                @elseif ($workState === 'working')
                    <form method="POST" action="{{ route('jobs.work.break.start', $job) }}">@csrf<button class="button" type="submit">Start break</button></form>
                    <form method="POST" action="{{ route('jobs.work.end', $job) }}">@csrf<button class="button button-primary" type="submit">End job</button></form>
                @elseif ($workState === 'on_break')
                    <form method="POST" action="{{ route('jobs.work.break.end', $job) }}">@csrf<button class="button button-primary" type="submit">End break</button></form>
                    <form method="POST" action="{{ route('jobs.work.end', $job) }}">@csrf<button class="button" type="submit">End job</button></form>
                @else
                    <p class="customer-notes">This job timer is complete.</p>
                @endif
            </div>
        @endif
        <div class="time-entry-list compact">
            @forelse ($job->workEvents->sortByDesc('occurred_at') as $event)
                <article class="time-entry-row job-event-row">
                    <div><strong>{{ Str::headline($event->event_type) }}</strong><small>{{ $event->occurred_at->format('M j, Y g:i A') }}</small></div>
                    <span>{{ $event->user->name }}</span>
                    <span>{{ $event->notes ?: 'No notes' }}</span>
                </article>
            @empty
                <p class="customer-notes">No timer activity yet.</p>
            @endforelse
        </div>
    </section>

    <section class="glass-card management-card">
        <div class="section-heading"><div><span class="eyebrow">Costs</span><h2>Job expenses</h2></div></div>
        @if ($canManageExpenses || $canSubmitExpenses)
            <form class="expense-form" method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="service_job_id" value="{{ $job->id }}">
                <input class="auth-input" name="category_name" placeholder="Category e.g. fuel, supplies, parking">
                @if ($canManageExpenses)
                    <select class="auth-input" name="submitted_by">
                        <option value="{{ auth()->id() }}">{{ auth()->user()->name }}</option>
                        @foreach ($tenantUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <select class="auth-input" name="status">
                        @foreach ($expenseStatuses as $expenseStatus)
                            <option value="{{ $expenseStatus }}" @selected($expenseStatus === 'pending')>{{ Str::headline($expenseStatus) }}</option>
                        @endforeach
                    </select>
                @endif
                <input class="auth-input" name="vendor" placeholder="Vendor">
                <input class="auth-input" name="amount" type="number" min="0.01" step="0.01" placeholder="Amount" required>
                <input class="auth-input" name="expense_date" type="date" value="{{ now()->format('Y-m-d') }}" required>
                <input class="auth-input" name="notes" placeholder="Notes">
                <input class="auth-input" name="receipt" type="file" accept="image/png,image/jpeg,image/webp,application/pdf">
                <button class="button button-primary" type="submit">Add expense</button>
            </form>
        @endif
        <div class="expense-list compact">
            @forelse ($job->expenses as $expense)
                <article class="expense-summary-row">
                    <div>
                        <strong>${{ number_format((float) $expense->amount, 2) }}</strong>
                        <small>{{ $expense->categoryLabel() }} / {{ $expense->vendor ?: 'No vendor' }} / {{ $expense->submitter?->name ?? 'No submitter' }}</small>
                        <small>{{ $expense->expense_date->format('M j, Y') }}{{ $expense->approval_notes ? ' / '.$expense->approval_notes : '' }}</small>
                        <small>Reviewed by: {{ $expense->approver?->name ?? 'Not reviewed' }}{{ $expense->approved_at ? ' on '.$expense->approved_at->format('M j, Y g:i A') : '' }}</small>
                    </div>
                    <span class="tenant-status tenant-status-{{ in_array($expense->status, ['approved', 'reimbursed']) ? 'active' : ($expense->status === 'rejected' ? 'archived' : 'suspended') }}">{{ Str::headline($expense->status) }}</span>
                    @if ($expense->receiptUrl())
                    
                        <a class="text-link" href="{{ $expense->receiptUrl() }}" target="_blank" rel="noopener">Receipt</a>
                    @endif
                    @if ($canApproveExpenses || $canManageExpenses)
                        <form class="expense-form" method="POST" action="{{ route('expenses.update', $expense) }}">
                            @csrf
                            @method('PATCH')
                            @if (! $canManageExpenses)
                                <select class="auth-input" name="status" required>
                                    <option value="pending" @selected($expense->status === 'pending')>Pending</option>
                                    <option value="approved" @selected($expense->status === 'approved')>Approved</option>
                                    <option value="rejected" @selected($expense->status === 'rejected')>Rejected</option>
                                </select>
                                <input class="auth-input" name="approval_notes" value="{{ $expense->approval_notes }}" placeholder="Approval notes">
                            @else
                                <input type="hidden" name="service_job_id" value="{{ $job->id }}">
                                <input type="hidden" name="category_name" value="{{ $expense->category_name }}">
                                <input type="hidden" name="submitted_by" value="{{ $expense->submitted_by }}">
                                <input type="hidden" name="vendor" value="{{ $expense->vendor }}">
                                <input type="hidden" name="amount" value="{{ $expense->amount }}">
                                <input type="hidden" name="expense_date" value="{{ $expense->expense_date->format('Y-m-d') }}">
                                <input type="hidden" name="notes" value="{{ $expense->notes }}">
                                <select class="auth-input" name="status" required>
                                    @foreach ($expenseStatuses as $expenseStatus)
                                        <option value="{{ $expenseStatus }}" @selected($expense->status === $expenseStatus)>{{ Str::headline($expenseStatus) }}</option>
                                    @endforeach
                                </select>
                                <input class="auth-input" name="approval_notes" value="{{ $expense->approval_notes }}" placeholder="Approval notes">
                            @endif
                            <button class="button button-primary" type="submit">Update expense</button>
                        </form>
                    @endif
                </article>
            @empty
                <p class="customer-notes">No expenses logged for this job yet.</p>
            @endforelse
        </div>
    </section>

    @if ($canManageFinance)
        <section class="glass-card management-card">
            <div class="section-heading"><div><span class="eyebrow">Finance</span><h2>Job payments</h2></div></div>
            <div class="dashboard-grid management-grid">
                <article class="glass-card management-card service-form-card">
                    <h2>Customer payment</h2>
                    <form class="payment-form" method="POST" action="{{ route('customer-payments.store') }}">
                        @csrf
                        <input type="hidden" name="service_job_id" value="{{ $job->id }}">
                        <input type="hidden" name="customer_id" value="{{ $job->customer_id }}">
                        <select class="auth-input" name="status" required>@foreach ($customerPaymentStatuses as $paymentStatus)<option value="{{ $paymentStatus }}" @selected($paymentStatus === 'paid')>{{ Str::headline($paymentStatus) }}</option>@endforeach</select>
                        <select class="auth-input" name="method" required>@foreach ($customerPaymentMethods as $method)<option value="{{ $method }}">{{ Str::headline($method) }}</option>@endforeach</select>
                        <input class="auth-input" name="amount" type="number" min="0.01" step="0.01" placeholder="Amount" required>
                        <input class="auth-input" name="paid_at" type="date" value="{{ now()->format('Y-m-d') }}" required>
                        <input class="auth-input" name="reference" placeholder="Reference">
                        <input class="auth-input" name="notes" placeholder="Notes">
                        <button class="button button-primary" type="submit">Add customer payment</button>
                    </form>
                </article>
                <article class="glass-card management-card service-form-card activity-card">
                    <h2>Team payment</h2>
                    <form class="payment-form" method="POST" action="{{ route('team-payments.store') }}">
                        @csrf
                        <input type="hidden" name="service_job_id" value="{{ $job->id }}">
                        <input type="hidden" name="team_id" value="{{ $job->team_id }}">
                        <select class="auth-input" name="user_id">
                            <option value="">No user</option>
                            @foreach ($tenantUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <select class="auth-input" name="status" required>@foreach ($teamPaymentStatuses as $paymentStatus)<option value="{{ $paymentStatus }}">{{ Str::headline($paymentStatus) }}</option>@endforeach</select>
                        <select class="auth-input" name="method" required>@foreach ($teamPaymentMethods as $method)<option value="{{ $method }}">{{ Str::headline($method) }}</option>@endforeach</select>
                        <input class="auth-input" name="amount" type="number" min="0.01" step="0.01" placeholder="Amount" required>
                        <input class="auth-input" name="paid_at" type="date">
                        <input class="auth-input" name="reference" placeholder="Reference">
                        <input class="auth-input" name="notes" placeholder="Notes">
                        <button class="button button-primary" type="submit">Add team payment</button>
                    </form>
                </article>
            </div>
            <div class="expense-list compact">
                @forelse ($job->customerPayments as $payment)
                    <form class="payment-row" method="POST" action="{{ route('customer-payments.update', $payment) }}">
                        @csrf
                        @method('PATCH')
                        @include('backend.payments._customer_form', ['payment' => $payment])
                        <button class="button" type="submit">Save</button>
                    </form>
                @empty
                    <p class="customer-notes">No customer payments logged for this job yet.</p>
                @endforelse
                @foreach ($job->teamPayments as $payment)
                    <form class="payment-row team-payment-row" method="POST" action="{{ route('team-payments.update', $payment) }}">
                        @csrf
                        @method('PATCH')
                        @include('backend.payments._team_form', ['payment' => $payment])
                        <button class="button" type="submit">Save</button>
                    </form>
                @endforeach
            </div>
        </section>
    @elseif (auth()->user()->hasPermission('team-payments.view-own', $tenant))
        <section class="glass-card management-card">
            <div class="section-heading"><div><span class="eyebrow">Pay</span><h2>My pay for this job</h2></div></div>
            <div class="expense-list compact">
                @forelse ($job->teamPayments->where('user_id', auth()->id()) as $payment)
                    <article class="expense-summary-row"><div><strong>${{ number_format((float) $payment->amount, 2) }}</strong><small>{{ Str::headline($payment->method) }} / {{ $payment->reference ?: 'No reference' }}</small></div><span class="tenant-status tenant-status-{{ $payment->status === 'paid' ? 'active' : ($payment->status === 'cancelled' ? 'archived' : 'suspended') }}">{{ Str::headline($payment->status) }}</span><span>{{ $payment->paid_at?->format('M j, Y') ?? 'Unpaid' }}</span></article>
                @empty
                    <p class="customer-notes">No pay has been logged for you on this job yet.</p>
                @endforelse
            </div>
        </section>
    @endif

    <section class="glass-card management-card">
        <h2>Notes</h2>
        <p class="customer-notes">{{ $job->notes ?: 'No notes yet.' }}</p>
    </section>
</x-backend-layout>







