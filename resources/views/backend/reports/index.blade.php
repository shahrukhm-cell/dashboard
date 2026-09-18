<x-backend-layout title="Reports">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Reports</h2>
        </div>
    </section>

    <section class="dashboard-grid management-grid">
        <article class="glass-card stat-card"><span class="stat-label">Revenue received</span><strong class="stat-value">${{ number_format($metrics['revenue'], 2) }}</strong><span class="stat-change positive">${{ number_format($metrics['balance_due'], 2) }} balance due</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Approved expenses</span><strong class="stat-value">${{ number_format($metrics['approved_expenses'], 2) }}</strong><span class="stat-change negative">Job costs</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Team payouts</span><strong class="stat-value">${{ number_format($metrics['team_paid'], 2) }}</strong><span class="stat-change negative">Paid to team</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Profit</span><strong class="stat-value">${{ number_format($metrics['profit'], 2) }}</strong><span class="stat-change {{ $metrics['profit'] >= 0 ? 'positive' : 'negative' }}>Revenue minus costs</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Jobs</span><strong class="stat-value">{{ number_format($metrics['jobs']) }}</strong><span class="stat-change positive">{{ number_format($metrics['active_jobs']) }} active</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Customers</span><strong class="stat-value">{{ number_format($metrics['customers']) }}</strong><span class="stat-change positive">{{ number_format($metrics['hours']) }} work hours</span></article>
    </section>

    <section class="dashboard-grid management-grid">
        <article class="glass-card activity-card">
            <div class="section-heading"><div><span class="eyebrow">Pipeline</span><h2>Jobs by status</h2></div></div>
            <div class="report-list">
                @foreach (App\Models\ServiceJob::statuses() as $status)
                    <div class="report-row"><span>{{ Str::headline($status) }}</span><strong>{{ number_format($jobsByStatus[$status] ?? 0) }}</strong></div>
                @endforeach
            </div>
        </article>

        <article class="glass-card activity-card">
            <div class="section-heading"><div><span class="eyebrow">Recent</span><h2>Recent jobs</h2></div><a class="text-link" href="{{ route('jobs.index') }}">View jobs</a></div>
            <div class="report-list">
                @forelse ($recentJobs as $job)
                    <a class="report-row" href="{{ route('jobs.show', $job) }}"><span>{{ $job->job_number }} · {{ $job->customer->name }}</span><strong>${{ number_format((float) $job->total, 2) }}</strong></a>
                @empty
                    <p class="customer-notes">No jobs yet.</p>
                @endforelse
            </div>
        </article>
    </section>
</x-backend-layout>