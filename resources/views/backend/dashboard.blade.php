<x-backend-layout title="Overview">
    @if (! $tenant)
        <section class="glass-card management-card">
            <span class="eyebrow">No workspace</span>
            <h2>Create or select a tenant workspace</h2>
            <p class="customer-notes">Dashboard metrics appear after a workspace is selected.</p>
        </section>
    @else
        <section class="dashboard-grid">
            @foreach ($stats as $stat)
                <article class="glass-card stat-card">
                    <div class="stat-header">
                        <span class="stat-label">{{ $stat['label'] }}</span>
                        <span class="stat-icon">{{ $stat['icon'] === 'dollar' ? '$' : ($stat['icon'] === 'jobs' ? 'J' : 'P') }}</span>
                    </div>
                    <strong class="stat-value">{{ $stat['value'] }}</strong>
                    <span class="stat-change {{ $stat['tone'] }}">{{ $stat['change'] }}</span>
                </article>
            @endforeach

            <article class="glass-card activity-card">
                <div class="section-heading"><div><span class="eyebrow">Recent jobs</span><h2>Workspace jobs</h2></div><a class="text-link" href="{{ route('customers.index') }}">Customers</a></div>
                <div class="report-list">
                    @forelse ($recentJobs as $job)
                        <a class="report-row" href="{{ route('jobs.show', $job) }}"><span>{{ $job->job_number }} · {{ $job->customer?->name ?? 'Deleted customer' }}</span><strong>{{ Str::headline($job->status) }}</strong></a>
                    @empty
                        <p class="customer-notes">No jobs yet.</p>
                    @endforelse
                </div>
            </article>

            <article class="glass-card activity-card">
                <div class="section-heading"><div><span class="eyebrow">Workspace pulse</span><h2>Recent activity</h2></div>@if ($reportsLinkVisible ?? false)<a class="text-link" href="{{ route('reports.index') }}">Reports</a>@endif</div>
                <ul class="activity-list">
                    @forelse ($activities as $activity)
                        <li class="activity-item"><span class="activity-icon">{{ $activity['icon'] === 'check' ? '$' : '+' }}</span><div><strong>{{ $activity['title'] }}</strong><p>{{ $activity['description'] }}</p></div><time>{{ $activity['time'] }}</time></li>
                    @empty
                        <li class="activity-item"><span class="activity-icon">+</span><div><strong>No activity yet</strong><p>Create jobs, payments, and expenses to populate this feed.</p></div><time>Now</time></li>
                    @endforelse
                </ul>
            </article>
        </section>
    @endif
</x-backend-layout>


