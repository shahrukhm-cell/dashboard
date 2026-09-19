<x-backend-layout title="Roles & permissions">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Roles & permissions</h2>
        </div>
        <a class="text-link" href="{{ route('tenant.users.index', $tenant) }}">Back to settings</a>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="glass-card management-card">
        <h2>Create role</h2>
        <form class="role-create-form" method="POST" action="{{ route('tenant.roles.store', $tenant) }}">
            @csrf
            <input class="auth-input" name="name" placeholder="New role name" required>
            <div class="permission-grid compact">
                @foreach ($permissions as $permission)
                    <label class="permission-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}">
                        <span>{{ $permission->name }}</span>
                    </label>
                @endforeach
            </div>
            <button class="button button-primary" type="submit">Create role</button>
        </form>
    </section>

    <section class="role-list">
        @foreach ($roles as $role)
            <form class="glass-card role-card" method="POST" action="{{ route('tenant.roles.update', [$tenant, $role]) }}">
                @csrf
                @method('PATCH')
                <div class="role-card-header">
                    <input class="auth-input" name="name" value="{{ $role->name }}" required>
                    <button class="button button-primary" type="submit">Save role</button>
                </div>
                <div class="permission-grid">
                    @foreach ($permissions as $permission)
                        <label class="permission-check">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains($permission))>
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </form>
        @endforeach
    </section>
</x-backend-layout>

