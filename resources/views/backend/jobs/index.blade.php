<x-backend-layout title="Jobs">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>All jobs</h2>
        </div>
        <div class="management-actions">
            @if (auth()->user()->hasPermission('tenant.settings.update', $tenant))
                <a class="button" href="{{ route('invoice-settings.edit') }}">Invoice settings</a>
            @endif
            @if (auth()->user()->hasPermission('jobs.manage', $tenant))
                <a class="button button-primary" href="{{ route('jobs.create') }}">Create job</a>
            @endif
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="glass-card management-card">
        <form class="customer-toolbar" method="GET" action="{{ route('jobs.index') }}">
            <input class="auth-input" name="search" value="{{ $search }}" placeholder="Search job number or customer">
            <select class="auth-input" name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $jobStatus)
                    <option value="{{ $jobStatus }}" @selected($status === $jobStatus)>{{ Str::headline($jobStatus) }}</option>
                @endforeach
            </select>
            <select class="auth-input" name="quote_status">
                <option value="">All quotes</option>
                @foreach ($quoteStatuses as $jobQuoteStatus)
                    <option value="{{ $jobQuoteStatus }}" @selected($quoteStatus === $jobQuoteStatus)>{{ Str::headline($jobQuoteStatus) }}</option>
                @endforeach
            </select>
            <select class="auth-input" name="due">
                <option value="">All due states</option>
                <option value="due_not_completed" @selected($due === 'due_not_completed')>Due and not completed</option>
            </select>
            <select class="auth-input" name="period">
                <option value="">Custom / all dates</option>
                <option value="daily" @selected($period === 'daily')>Today</option>
                <option value="monthly" @selected($period === 'monthly')>This month</option>
            </select>
            <select class="auth-input" name="customer_id">
                <option value="">All customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((string) $customerId === (string) $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
            <select class="auth-input" name="team_id">
                <option value="">All teams</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) $teamId === (string) $team->id)>{{ $team->name }}</option>
                @endforeach
            </select>
            <input class="auth-input" name="from" type="date" value="{{ $from }}">
            <input class="auth-input" name="to" type="date" value="{{ $to }}">
            <button class="button" type="submit">Filter</button>
        </form>
    </section>

    @if ($canViewFinance ?? true)
        <section class="dashboard-grid management-grid finance-summary-grid">
            <article class="glass-card stat-card"><span class="stat-label">Customer paid</span><strong class="stat-value">${{ number_format($financeSummary['customer_paid'], 2) }}</strong><span class="stat-change positive">Filtered payments</span></article>
            <article class="glass-card stat-card"><span class="stat-label">Job expenses</span><strong class="stat-value">${{ number_format($financeSummary['job_expenses'], 2) }}</strong><span class="stat-change negative">Only assigned to jobs</span></article>
            <article class="glass-card stat-card"><span class="stat-label">Team paid</span><strong class="stat-value">${{ number_format($financeSummary['team_paid'], 2) }}</strong><span class="stat-change negative">Filtered payouts</span></article>
            <article class="glass-card stat-card"><span class="stat-label">Company expenses</span><strong class="stat-value">${{ number_format($financeSummary['company_expenses'], 2) }}</strong><span class="stat-change negative">Rent, shop, overhead</span></article>
            <article class="glass-card stat-card activity-card"><span class="stat-label">Profit / loss</span><strong class="stat-value">${{ number_format($financeSummary['profit'], 2) }}</strong><span class="stat-change {{ $financeSummary['profit'] >= 0 ? 'positive' : 'negative' }}">Paid minus job, team, and company costs</span></article>
        </section>
    @endif

    <section class="glass-card management-card">
        <div class="service-list">
            @forelse ($jobs as $job)
                <article class="service-row job-row">
                    <div>
                        <strong><a href="{{ route('jobs.show', $job) }}">{{ $job->job_number }}</a></strong>
                        <small>{{ $job->customer->name }} - {{ $job->team?->name ?? 'Unassigned' }}</small>
                    </div>
                    <span>{{ $job->scheduled_at?->format('M j, Y g:i A') ?? 'Not scheduled' }}</span>
                    <span>{{ Str::headline($job->quote_status ?? 'draft') }} quote</span>
                    <span>{{ $job->before_photos_count ?? 0 }} before / {{ $job->after_photos_count ?? 0 }} after</span>
                    @if ($canViewFinance ?? true)
                        @php($jobPaid = (float) ($job->paid_customer_sum ?? 0))
                        @php($jobExpense = (float) ($job->approved_expense_sum ?? 0))
                        @php($teamPaid = (float) ($job->paid_team_sum ?? 0))
                        @php($jobProfit = $jobPaid - $jobExpense - $teamPaid)
                        <div class="job-finance-stack">
                            <span>Total ${{ number_format((float) $job->total, 2) }}</span>
                            <small>Paid ${{ number_format($jobPaid, 2) }}</small>
                            <small>Expense ${{ number_format($jobExpense, 2) }}</small>
                            <small>Team ${{ number_format($teamPaid, 2) }}</small>
                            <strong class="{{ $jobProfit >= 0 ? 'stat-change positive' : 'stat-change negative' }}">Profit ${{ number_format($jobProfit, 2) }}</strong>
                        </div>
                    @else<span>{{ $job->items_count ?? $job->items()->count() }} services</span>@endif
                    <span class="tenant-status tenant-status-{{ in_array($job->status, ['completed']) ? 'active' : ($job->status === 'cancelled' ? 'archived' : 'suspended') }}">{{ Str::headline($job->status) }}</span>
                    @if (auth()->user()->hasPermission('jobs.manage', $tenant))
                        <a class="button" href="{{ route('jobs.edit', $job) }}">Edit</a>
                    @endif
                </article>
            @empty
                <div class="empty-state">
                    <span class="eyebrow">No jobs</span>
                    <h2>Create your first job</h2>
                    <p>Jobs connect customers with services and become the center of operations.</p>
                </div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $jobs->links() }}</div>
    </section>
</x-backend-layout>
