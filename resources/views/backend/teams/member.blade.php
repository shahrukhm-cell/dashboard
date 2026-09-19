<x-backend-layout title="Team member">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $team->name }}</span>
            <h2>{{ $member->name }}</h2>
        </div>
        <a class="text-link" href="{{ route('teams.show', $team) }}">Back to team</a>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="dashboard-grid management-grid">
        <article class="glass-card tenant-card">
            <span class="eyebrow">Profile</span>
            <h2>{{ $member->name }}</h2>
            <p>{{ $member->email }}</p>
            <p>Phone: {{ $member->phone ?: 'Not added' }}</p>
            <p>Address: {{ $member->address ?: 'Not added' }}</p>
            <p>Emergency: {{ $member->emergency_contact ?: 'Not added' }}</p>
        </article>
        <article class="glass-card tenant-card">
            <span class="eyebrow">Work summary</span>
            <h2>{{ round($totalMinutes / 60, 2) }} hours</h2>
            <p>{{ $completedJobs }} completed jobs</p>
            <p>{{ $assignedJobs->count() }} total assigned/team jobs</p>
        </article>
    </section>

    <section class="glass-card management-card">
        <h2>Record attendance</h2>
        <form class="management-form" method="POST" action="{{ route('teams.members.attendance.store', [$team, $member]) }}">
            @csrf
            <input class="auth-input" name="starts_at" type="date" required>
            <input class="auth-input" name="ends_at" type="date" required>
            <select class="auth-input" name="status" required>
                @foreach ($leaveStatuses as $leaveStatus)
                    <option value="{{ $leaveStatus }}">{{ Str::headline($leaveStatus) }}</option>
                @endforeach
            </select>
            <input class="auth-input" name="reason" placeholder="Attendance note or leave reason">
            <button class="button button-primary" type="submit">Save attendance</button>
        </form>
    </section>

    <section class="glass-card management-card">
        <h2>Recent work</h2>
        <div class="service-list">
            @forelse ($assignedJobs->take(10) as $job)
                <article class="service-row job-row">
                    <div>
                        <strong><a href="{{ route('jobs.show', $job) }}">{{ $job->job_number }}</a></strong>
                        <small>{{ $job->customer->name }}</small>
                    </div>
                    <span>{{ Str::headline($job->status) }}</span>
                    <span>{{ $job->scheduled_at?->format('M j, Y g:i A') ?? 'Not scheduled' }}</span>
                </article>
            @empty
                <p>No work assigned yet.</p>
            @endforelse
        </div>
    </section>

    <section class="glass-card management-card">
        <h2>Time and attendance</h2>
        <div class="member-list">
            @foreach ($timeEntries as $entry)
                <div class="member-row">
                    <span class="user-avatar">{{ $entry->hours() }}</span>
                    <div>
                        <strong>{{ $entry->job?->job_number ?? 'Time entry' }}</strong>
                        <p>{{ $entry->started_at?->format('M j, Y g:i A') }} / {{ $entry->ended_at?->format('M j, Y g:i A') ?? 'Open' }}</p>
                        <p>{{ $entry->notes ?: 'No notes' }}</p>
                    </div>
                </div>
            @endforeach
            @foreach ($leaves as $leave)
                <div class="member-row">
                    <span class="tenant-status tenant-status-{{ $leave->status === 'approved' ? 'active' : ($leave->status === 'rejected' ? 'archived' : 'suspended') }}">{{ Str::headline($leave->status) }}</span>
                    <div>
                        <strong>{{ $leave->starts_at->format('M j, Y') }} - {{ $leave->ends_at->format('M j, Y') }}</strong>
                        <p>{{ $leave->reason ?: 'No note' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-backend-layout>

