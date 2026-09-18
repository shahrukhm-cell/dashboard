<x-backend-layout title="Time tracking">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Time tracking</h2>
        </div>
        <span class="tenant-status tenant-status-active">{{ round($totalMinutes / 60, 2) }} total hours</span>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    @if (auth()->user()->hasPermission('jobs.manage', $tenant))
        <section class="glass-card management-card">
            <h2>Log time</h2>
            <form class="time-entry-form" method="POST" action="{{ route('time-entries.store') }}">
                @csrf
                <select class="auth-input" name="service_job_id" required>
                    <option value="">Select job</option>
                    @foreach ($jobs as $job)
                        <option value="{{ $job->id }}">{{ $job->job_number }} - {{ $job->customer->name }}</option>
                    @endforeach
                </select>
                <select class="auth-input" name="user_id" required>
                    <option value="">Select user</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <input class="auth-input" name="started_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                <input class="auth-input" name="hours" type="number" min="0.01" step="0.01" placeholder="Hours" required>
                <input class="auth-input" name="notes" placeholder="Notes">
                <button class="button button-primary" type="submit">Log time</button>
            </form>
        </section>
    @endif

    <section class="glass-card management-card">
        <div class="time-entry-list">
            @forelse ($entries as $entry)
                <article class="time-entry-row">
                    <div>
                        <strong>{{ $entry->job->job_number }}</strong>
                        <small>{{ $entry->job->customer->name }}</small>
                    </div>
                    <span>{{ $entry->user->name }}</span>
                    <span>{{ $entry->started_at->format('M j, Y g:i A') }}</span>
                    <strong>{{ $entry->hours() }}h</strong>
                    <span>{{ $entry->notes ?: 'No notes' }}</span>
                </article>
            @empty
                <div class="empty-state">
                    <span class="eyebrow">No time logged</span>
                    <h2>Log the first work entry</h2>
                    <p>Time entries attach work hours to jobs and team members.</p>
                </div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $entries->links() }}</div>
    </section>
</x-backend-layout>