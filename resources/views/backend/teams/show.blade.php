<x-backend-layout title="Team details">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>{{ $team->name }}</h2>
        </div>
        <a class="text-link" href="{{ route('teams.index') }}">Back to teams</a>
    </section>

    <section class="glass-card management-card">
        <p>{{ $team->description ?: 'No description added.' }}</p>
        <p><strong>Status:</strong> {{ $team->is_active ? 'Active' : 'Inactive' }}</p>
        <p><strong>Lead:</strong> {{ $team->lead()?->name ?? 'No lead selected' }}</p>
    </section>

    <section class="glass-card management-card">
        <h2>Team members</h2>
        <div class="member-list">
            @forelse ($team->users as $user)
                <a class="member-row" href="{{ route('teams.members.show', [$team, $user]) }}">
                    <span class="user-avatar">{{ Str::of($user->name)->substr(0, 2)->upper() }}</span>
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <p>{{ $user->email }} / {{ $user->roles->pluck('name')->join(', ') ?: 'No role' }}</p>
                        <p>{{ $user->assignedJobs->count() }} directly assigned recent jobs</p>
                    </div>
                </a>
            @empty
                <p>No members assigned.</p>
            @endforelse
        </div>
    </section>

    <section class="glass-card management-card">
        <h2>Recent team jobs</h2>
        <div class="service-list">
            @forelse ($jobs as $job)
                <article class="service-row job-row">
                    <div>
                        <strong><a href="{{ route('jobs.show', $job) }}">{{ $job->job_number }}</a></strong>
                        <small>{{ $job->customer->name }}</small>
                    </div>
                    <span>{{ Str::headline($job->status) }}</span>
                    <span>{{ $job->scheduled_at?->format('M j, Y g:i A') ?? 'Not scheduled' }}</span>
                </article>
            @empty
                <p>No jobs assigned to this team.</p>
            @endforelse
        </div>
    </section>
</x-backend-layout>

