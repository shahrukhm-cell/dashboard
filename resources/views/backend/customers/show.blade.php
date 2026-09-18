<x-backend-layout title="Customer details">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>{{ $customer->name }}</h2>
        </div>
        <div class="management-actions">
            @if (auth()->user()->hasPermission('jobs.manage', $tenant))
                <a class="button button-primary" href="{{ route('jobs.create', ['customer_id' => $customer->id]) }}">Create job</a>
            @endif
            @if (auth()->user()->hasPermission('customers.manage', $tenant))
                <a class="button" href="{{ route('customers.edit', $customer) }}">Edit</a>
            @endif
            <a class="button" href="{{ route('customers.index') }}">Back</a>
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="dashboard-grid management-grid">
        <article class="glass-card customer-detail-card">
            <span class="tenant-status tenant-status-{{ $customer->status }}">{{ Str::title($customer->status) }}</span>
            <h2>Contact</h2>
            <dl class="detail-list">
                <dt>Company</dt><dd>{{ $customer->company ?: 'Individual customer' }}</dd>
                <dt>Email</dt><dd>{{ $customer->email ?: 'Not added' }}</dd>
                <dt>Phone</dt><dd>{{ $customer->phone ?: 'Not added' }}</dd>
                <dt>Address</dt><dd>{{ $customer->addressSummary() ?: 'Not added' }}</dd>
            </dl>
        </article>

        <article class="glass-card customer-detail-card activity-card">
            <h2>Notes</h2>
            <p class="customer-notes">{{ $customer->notes ?: 'No notes yet.' }}</p>
        </article>
    </section>

    <section class="glass-card management-card">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Customer work</span>
                <h2>Jobs</h2>
            </div>
            @if (auth()->user()->hasPermission('jobs.manage', $tenant))
                <a class="text-link" href="{{ route('jobs.create', ['customer_id' => $customer->id]) }}">Create job</a>
            @endif
        </div>

        <div class="service-list customer-job-list">
            @forelse ($jobs as $job)
                <article class="service-row job-row">
                    <div>
                        <strong><a href="{{ route('jobs.show', $job) }}">{{ $job->job_number }}</a></strong>
                        <small>{{ $job->team?->name ?? 'Unassigned team' }} / {{ $job->assignee?->name ?? 'No assignee' }}</small>
                    </div>
                    <span>{{ $job->scheduled_at?->format('M j, Y g:i A') ?? 'Pickup not set' }}</span>
                    <span>{{ $job->items_count ?? $job->items()->count() }} services</span>
                    <span class="tenant-status tenant-status-{{ in_array($job->status, ['completed']) ? 'active' : ($job->status === 'cancelled' ? 'archived' : 'suspended') }}">{{ Str::headline($job->status) }}</span>
                    @if (auth()->user()->hasPermission('jobs.manage', $tenant))
                        <a class="button" href="{{ route('jobs.edit', $job) }}">Edit</a>
                    @endif
                </article>
            @empty
                <div class="empty-state">
                    <span class="eyebrow">No jobs</span>
                    <h2>No work created yet</h2>
                    <p>Create the first job from this customer profile.</p>
                </div>
            @endforelse
        </div>
    </section>
</x-backend-layout>
