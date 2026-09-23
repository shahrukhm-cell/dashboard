<x-backend-layout title="Tenant users">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }} / {{ Str::title($tenant->status) }}</span>
            <h2>Tenant users & roles</h2>
        </div>
        @if (auth()->user()->is_super_admin)
            <a class="text-link" href="{{ route('admin.tenants.index') }}">Back to tenants</a>
        @endif
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="validation-message">
            <strong>Please fix the following errors:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="glass-card management-card">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Workspace identity</span>
                <h2>Tenant branding</h2>
            </div>
            <div class="brand-preview">
                @if ($tenant->logoUrl())
                    <img src="{{ $tenant->logoUrl() }}" alt="{{ $tenant->brandName() }} logo">
                @else
                    <span>{{ Str::of($tenant->brandName())->substr(0, 2)->upper() }}</span>
                @endif
                <strong>{{ $tenant->brandName() }}</strong>
            </div>
        </div>

        <form class="tenant-settings-form" method="POST" action="{{ route('admin.tenants.update', $tenant) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <label class="auth-field">
                <span>Workspace name</span>
                <input class="auth-input" name="name" value="{{ old('name', $tenant->name) }}" required>
            </label>
            <label class="auth-field">
                <span>Brand name</span>
                <input class="auth-input" name="brand_name" value="{{ old('brand_name', $tenant->brandName()) }}" placeholder="Public business name">
            </label>
            @if (auth()->user()->is_super_admin)
                <label class="auth-field">
                    <span>Status</span>
                    <select class="auth-input" name="status" aria-label="Tenant status">
                        @foreach (App\Models\Tenant::statuses() as $status)
                            <option value="{{ $status }}" @selected(old('status', $tenant->status) === $status)>{{ Str::title($status) }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label class="auth-field compact-color-field">
                <span>Primary color</span>
                <input class="auth-input" name="theme_color" type="color" value="{{ old('theme_color', $tenant->themeColor()) }}" title="Tenant accent color" required>
            </label>
            <label class="auth-field compact-color-field">
                <span>Secondary color</span>
                <input class="auth-input" name="secondary_color" type="color" value="{{ old('secondary_color', $tenant->secondaryColor()) }}" title="Tenant secondary color" required>
            </label>
            <label class="auth-field tenant-logo-field">
                <span>Logo</span>
                <input class="auth-input" name="logo" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            </label>
            <button class="button button-primary" type="submit">Save settings</button>
        </form>

        @foreach (['name', 'brand_name', 'status', 'theme_color', 'secondary_color', 'logo'] as $field)
            @error($field) <p class="form-error">{{ $message }}</p> @enderror
        @endforeach
    </section>

    <section class="glass-card management-card">
        <h2>Invite a user</h2>
        <form class="management-form" method="POST" action="{{ route('tenant.users.store', $tenant) }}">
            @csrf
            <input class="auth-input" name="name" placeholder="Full name" required>
            <input class="auth-input" name="email" type="email" placeholder="Email address" required>
            <div class="password-input-wrap">
                <input class="auth-input" name="password" type="password" placeholder="Temporary password" required>
                <button class="password-toggle" type="button" data-password-toggle aria-label="Show password" aria-pressed="false">Show</button>
            </div>
            <input class="auth-input" name="phone" placeholder="Phone">
            <input class="auth-input" name="address" placeholder="Address">
            <input class="auth-input" name="reference" placeholder="Reference">
            <input class="auth-input" name="emergency_contact" placeholder="Emergency contact">
            <select class="auth-input" name="role_id" required>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <button class="button button-primary" type="submit">Add user</button>
        </form>
    </section>

    @if (auth()->user()->hasPermission('roles.manage', $tenant))
        <section class="glass-card management-card">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Access control</span>
                    <h2>Roles & permissions</h2>
                </div>
                <a class="button" href="{{ route('tenant.roles.index', $tenant) }}">Manage roles</a>
            </div>
        </section>
    @endif

    <section class="glass-card management-card">
        <h2>Record leave</h2>
        <form class="management-form" method="POST" action="{{ route('tenant.team-leaves.store', $tenant) }}">
            @csrf
            <select class="auth-input" name="user_id" required>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <input class="auth-input" name="starts_at" type="date" required>
            <input class="auth-input" name="ends_at" type="date" required>
            <select class="auth-input" name="status" required>
                @foreach ($leaveStatuses as $leaveStatus)
                    <option value="{{ $leaveStatus }}">{{ Str::headline($leaveStatus) }}</option>
                @endforeach
            </select>
            <input class="auth-input" name="reason" placeholder="Reason">
            <button class="button button-primary" type="submit">Save leave</button>
        </form>
    </section>

    <section class="glass-card management-card">
        <h2>Members</h2>
        <div class="member-list">
            @foreach($users as $user)
                <div class="member-row">
                    <span class="user-avatar">{{ Str::of($user->name)->substr(0, 2)->upper() }}</span>
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <p>{{ $user->email }} / {{ $user->roles->pluck('name')->join(', ') ?: 'No role' }}</p>
                        <p>Phone: {{ $user->phone ?: 'Not added' }} / Address: {{ $user->address ?: 'Not added' }}</p>
                        <p>Reference: {{ $user->reference ?: 'Not added' }} / Emergency: {{ $user->emergency_contact ?: 'Not added' }}</p>
                        <p>Latest leave: {{ $user->leaves->first()?->starts_at?->format('M j, Y') ?? 'No leave recorded' }}</p>
                        <form class="management-form" method="POST" action="{{ route('tenant.users.update', [$tenant, $user]) }}">
                            @csrf
                            @method('PATCH')
                            <input class="auth-input" name="name" value="{{ $user->name }}" required>
                            <input class="auth-input" name="email" type="email" value="{{ $user->email }}" required>
                            <input class="auth-input" name="phone" value="{{ $user->phone }}" placeholder="Phone">
                            <input class="auth-input" name="address" value="{{ $user->address }}" placeholder="Address">
                            <input class="auth-input" name="reference" value="{{ $user->reference }}" placeholder="Reference">
                            <input class="auth-input" name="emergency_contact" value="{{ $user->emergency_contact }}" placeholder="Emergency contact">
                            <select class="auth-input" name="role_id" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected($user->roles->contains('id', $role->id))>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <button class="button" type="submit">Update profile</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-backend-layout>





