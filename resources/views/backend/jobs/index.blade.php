<x-backend-layout title="Jobs">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Jobs</h2>
        </div>
        @if (auth()->user()->hasPermission('jobs.manage', $tenant))
            <a class="button button-primary" href="{{ route('jobs.create') }}">Create job</a>
        @endif
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
            <button class="button" type="submit">Filter</button>
        </form>
    </section>

    <section class="glass-card management-card">
        <div class="service-list">
            @forelse ($jobs as $job)
                <article class="service-row job-row">
                    <div>
                        <strong><a href="{{ route('jobs.show', $job) }}">{{ $job->job_number }}</a></strong>
                        <small>{{ $job->customer->name }} · {{ $job->team?->name ?? 'Unassigned' }}</small>
                    </div>
                    <span>{{ $job->scheduled_at?->format('M j, Y g:i A') ?? 'Not scheduled' }}</span>
                    @php($lastStatusEvent = $job->statusEvents->first())
                    <span>{{ $lastStatusEvent?->changed_at?->format('M j, Y g:i A') ?? 'No status time' }}</span>
                    @if ($canViewFinance ?? true)<span>${{ number_format((float) $job->total, 2) }}</span>@else<span>{{ $job->items_count ?? $job->items()->count() }} services</span>@endif
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


