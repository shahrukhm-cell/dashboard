<x-backend-layout title="Teams">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Teams</h2>
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="glass-card management-card">
        <h2>Create team</h2>
        <form class="team-form" method="POST" action="{{ route('teams.store') }}">
            @csrf
            <input class="auth-input" name="name" placeholder="Team name" required>
            <input class="auth-input" name="description" placeholder="Description">
            <label class="permission-check"><input type="checkbox" name="is_active" value="1" checked><span>Active</span></label>
            <label class="auth-field">
                <span>Team lead</span>
                <select class="auth-input" name="team_lead_user_id">
                    <option value="">Select team lead</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}{{ $user->roles->isNotEmpty() ? ' - '.$user->roles->pluck('name')->join(', ') : '' }}</option>
                    @endforeach
                </select>
            </label>
            <div class="permission-grid compact">
                @foreach ($users as $user)
                    <label class="permission-check">
                        <input type="checkbox" name="users[]" value="{{ $user->id }}">
                        <span>{{ $user->name }}{{ $user->roles->isNotEmpty() ? ' - '.$user->roles->pluck('name')->join(', ') : '' }}</span>
                    </label>
                @endforeach
            </div>
            <button class="button button-primary" type="submit">Create team</button>
        </form>
    </section>

    <section class="dashboard-grid management-grid">
        @forelse ($teams as $team)
            @php($teamLead = $team->lead())
            <article class="glass-card tenant-card">
                <div class="tenant-card-header">
                    <span class="tenant-status tenant-status-{{ $team->is_active ? 'active' : 'archived' }}">{{ $team->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="eyebrow">{{ $team->users->count() }} members</span>
                </div>
                <h2>{{ $team->name }}</h2>
                <p>{{ $team->description ?: 'No description added.' }}</p>
                <p><strong>Lead:</strong> {{ $teamLead?->name ?? 'No lead selected' }}</p>
                <p><strong>Members:</strong> {{ $team->memberNames() ?: 'No members yet' }}</p>

                <form class="team-form" method="POST" action="{{ route('teams.update', $team) }}">
                    @csrf
                    @method('PATCH')
                    <input class="auth-input" name="name" value="{{ $team->name }}" required>
                    <input class="auth-input" name="description" value="{{ $team->description }}" placeholder="Description">
                    <label class="permission-check"><input type="checkbox" name="is_active" value="1" @checked($team->is_active)><span>Active</span></label>
                    <label class="auth-field">
                        <span>Team lead</span>
                        <select class="auth-input" name="team_lead_user_id">
                            <option value="">No lead selected</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) $teamLead?->id === (string) $user->id)>{{ $user->name }}{{ $user->roles->isNotEmpty() ? ' - '.$user->roles->pluck('name')->join(', ') : '' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="permission-grid compact">
                        @foreach ($users as $user)
                            <label class="permission-check">
                                <input type="checkbox" name="users[]" value="{{ $user->id }}" @checked($team->users->contains($user))>
                                <span>{{ $user->name }}{{ $user->roles->isNotEmpty() ? ' - '.$user->roles->pluck('name')->join(', ') : '' }}</span>
                            </label>
                        @endforeach
                    </div>
                    <button class="button button-primary" type="submit">Save team</button>
                </form>
            </article>
        @empty
            <article class="glass-card tenant-card">
                <span class="eyebrow">No teams</span>
                <h2>Create your first team</h2>
                <p>Teams can be assigned to jobs after they are created.</p>
            </article>
        @endforelse
    </section>
</x-backend-layout>

