<x-backend-layout title="Reports">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Reports</h2>
        </div>
    </section>

    <section class="dashboard-grid management-grid">
        <article class="glass-card stat-card"><span class="stat-label">Revenue received</span><strong class="stat-value">${{ number_format($metrics['revenue'], 2) }}</strong><span class="stat-change positive">${{ number_format($metrics['balance_due'], 2) }} balance due</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Job expenses</span><strong class="stat-value">${{ number_format($metrics['job_expenses'], 2) }}</strong><span class="stat-change negative">Assigned to jobs</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Company expenses</span><strong class="stat-value">${{ number_format($metrics['company_expenses'], 2) }}</strong><span class="stat-change negative">Rent, shop, overhead</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Team payouts</span><strong class="stat-value">${{ number_format($metrics['team_paid'], 2) }}</strong><span class="stat-change negative">Paid to team</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Profit / loss</span><strong class="stat-value">${{ number_format($metrics['profit'], 2) }}</strong><span class="stat-change {{ $metrics['profit'] >= 0 ? 'positive' : 'negative' }}>Revenue minus job, company, and team costs</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Jobs</span><strong class="stat-value">{{ number_format($metrics['jobs']) }}</strong><span class="stat-change positive">{{ number_format($metrics['completed_jobs']) }} completed</span></article>
        <article class="glass-card stat-card"><span class="stat-label">Clients</span><strong class="stat-value">{{ number_format($metrics['customers']) }}</strong><span class="stat-change positive">{{ number_format($metrics['hours']) }} work hours</span></article>
    </section>

    <section class="glass-card management-card">
        <div class="section-heading"><div><span class="eyebrow">Finance</span><h2>Profit / loss by month</h2></div></div>
        @php($maxMonthly = max(1, collect($profitLoss)->flatMap(fn ($row) => [$row['revenue'], $row['costs'], abs($row['profit'])])->max()))
        <div class="report-list">
            @foreach ($profitLoss as $month)
                <div class="report-row">
                    <span>{{ $month['label'] }}</span>
                    <div style="min-width: 45%;">
                        <div title="Revenue" style="height: 8px; width: {{ max(2, ($month['revenue'] / $maxMonthly) * 100) }}%; background: #22c55e; border-radius: 999px; margin-bottom: 4px;"></div>
                        <div title="Costs" style="height: 8px; width: {{ max(2, ($month['costs'] / $maxMonthly) * 100) }}%; background: #ef4444; border-radius: 999px; margin-bottom: 4px;"></div>
                        <div title="Profit" style="height: 8px; width: {{ max(2, (abs($month['profit']) / $maxMonthly) * 100) }}%; background: {{ $month['profit'] >= 0 ? '#2563eb' : '#f59e0b' }}; border-radius: 999px;"></div>
                    </div>
                    <strong>${{ number_format($month['profit'], 2) }}</strong>
                </div>
            @endforeach
        </div>
        <p class="customer-notes">Green is revenue, red is total costs. Job expenses, company overhead, and team payouts are kept separate in the totals.</p>
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
            <div class="section-heading"><div><span class="eyebrow">Clients</span><h2>Top clients</h2></div><a class="text-link" href="{{ route('customers.index') }}">View clients</a></div>
            <div class="report-list">
                @forelse ($topClients as $client)
                    <a class="report-row" href="{{ route('customers.show', $client) }}">
                        <span>{{ $client->name }} <small>{{ number_format($client->jobs_count) }} jobs / {{ number_format($client->completed_jobs_count) }} completed</small></span>
                        <strong>${{ number_format((float) $client->job_total_sum, 2) }}</strong>
                    </a>
                @empty
                    <p class="customer-notes">No clients yet.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="glass-card management-card">
        <div class="section-heading"><div><span class="eyebrow">Team management</span><h2>Team history and payouts</h2></div><a class="text-link" href="{{ route('teams.index') }}">Manage teams</a></div>
        <div class="report-list">
            @forelse ($teamPerformance as $team)
                <div class="report-row">
                    <span>
                        {{ $team->name }}
                        <small>{{ $team->memberNames() ?: 'No members' }}</small>
                    </span>
                    <span>{{ number_format($team->completed_jobs_count) }} / {{ number_format($team->jobs_count) }} jobs done</span>
                    <span>${{ number_format((float) $team->team_paid_sum, 2) }} paid</span>
                    <span>${{ number_format((float) $team->team_pending_sum, 2) }} pending</span>
                    <strong class="{{ $team->profit_after_team_costs >= 0 ? 'stat-change positive' : 'stat-change negative' }}">${{ number_format((float) $team->profit_after_team_costs, 2) }}</strong>
                </div>
                @foreach ($team->member_details as $member)
                    <div class="report-row">
                        <span>
                            {{ $member['name'] }}
                            <small>Phone: {{ $member['phone'] ?: 'Not added' }} / Reference: {{ $member['reference'] ?: 'Not added' }}</small>
                            <small>Address: {{ $member['address'] ?: 'Not added' }} / Emergency: {{ $member['emergency_contact'] ?: 'Not added' }}</small>
                        </span>
                        <span>{{ number_format($member['attendance_days']) }} attendance days</span>
                        <span>{{ number_format($member['leave_days']) }} leave days</span>
                        <span>{{ number_format($member['hours'], 2) }} hours</span>
                        <strong>${{ number_format($member['paid'], 2) }} paid / ${{ number_format($member['pending'], 2) }} pending</strong>
                    </div>
                @endforeach
            @empty
                <p class="customer-notes">No team history yet.</p>
            @endforelse
        </div>
    </section>

    <section class="glass-card management-card">
        <div class="section-heading"><div><span class="eyebrow">Recent</span><h2>Recent jobs</h2></div><a class="text-link" href="{{ route('jobs.index') }}">View jobs</a></div>
        <div class="report-list">
            @forelse ($recentJobs as $job)
                <a class="report-row" href="{{ route('jobs.show', $job) }}"><span>{{ $job->job_number }} / {{ $job->customer->name }}</span><strong>${{ number_format((float) $job->total, 2) }}</strong></a>
            @empty
                <p class="customer-notes">No jobs yet.</p>
            @endforelse
        </div>
    </section>
</x-backend-layout>

