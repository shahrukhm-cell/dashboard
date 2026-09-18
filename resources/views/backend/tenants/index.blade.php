<x-backend-layout title="Tenants">
    <section class="section-heading">
        <div>
            <span class="eyebrow">Platform administration</span>
            <h2>Tenant workspaces</h2>
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="glass-card management-card">
        <h2>Create tenant</h2>
        <form class="tenant-create-form" method="POST" action="{{ route('admin.tenants.store') }}">
            @csrf
            <input class="auth-input" name="name" placeholder="Company or workspace name" value="{{ old('name') }}" required>
            <input class="auth-input" name="brand_name" placeholder="Brand name" value="{{ old('brand_name') }}">
            <select class="auth-input" name="status" aria-label="Tenant status">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ Str::title($status) }}</option>
                @endforeach
            </select>
            <input class="auth-input" name="theme_color" type="color" value="{{ old('theme_color', '#8b5cf6') }}" title="Tenant primary color" required>
            <input class="auth-input" name="secondary_color" type="color" value="{{ old('secondary_color', '#22c55e') }}" title="Tenant secondary color">
            <button class="button button-primary" type="submit">Create tenant</button>
        </form>
        @foreach (['name', 'brand_name', 'status', 'theme_color', 'secondary_color'] as $field)
            @error($field) <p class="form-error">{{ $message }}</p> @enderror
        @endforeach
    </section>

    <section class="dashboard-grid management-grid">
        @forelse ($tenants as $tenant)
            <article class="glass-card tenant-card">
                <div class="tenant-card-header">
                    <div class="tenant-brand-lockup">
                        @if ($tenant->logoUrl())
                            <img src="{{ $tenant->logoUrl() }}" alt="{{ $tenant->brandName() }} logo">
                        @else
                            <span class="tenant-swatch" style="background: linear-gradient(135deg, {{ $tenant->themeColor() }}, {{ $tenant->secondaryColor() }})"></span>
                        @endif
                    </div>
                    <span class="tenant-status tenant-status-{{ $tenant->status }}">{{ Str::title($tenant->status) }}</span>
                </div>

                <span class="eyebrow">{{ $tenant->slug }}</span>
                <h2>{{ $tenant->brandName() }}</h2>
                <p>Workspace: {{ $tenant->name }}</p>
                <p>Owner: {{ $tenant->creator->name }} - {{ $tenant->users_count }} users</p>
                <p>Current plan: {{ $tenant->subscription?->plan?->name ?? 'No plan assigned' }} - {{ Str::title(str_replace('_', ' ', $tenant->subscription?->status ?? 'unassigned')) }}</p>

                <form class="tenant-edit-form" method="POST" action="{{ route('admin.tenants.update', $tenant) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <input class="auth-input" name="name" value="{{ old('name', $tenant->name) }}" required>
                    <input class="auth-input" name="brand_name" value="{{ old('brand_name', $tenant->brandName()) }}" placeholder="Brand name">
                    <select class="auth-input" name="status" aria-label="Tenant status">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $tenant->status) === $status)>{{ Str::title($status) }}</option>
                        @endforeach
                    </select>
                    <input class="auth-input" name="theme_color" type="color" value="{{ old('theme_color', $tenant->themeColor()) }}" title="Tenant primary color" required>
                    <input class="auth-input" name="secondary_color" type="color" value="{{ old('secondary_color', $tenant->secondaryColor()) }}" title="Tenant secondary color" required>
                    <button class="button button-primary" type="submit">Save</button>
                </form>

                <div class="management-actions">
                    <a class="button" href="{{ route('tenant.users.index', $tenant) }}">Manage users</a>
                    <form method="POST" action="{{ route('tenant.switch') }}">
                        @csrf
                        <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                        <button class="button" type="submit">Open workspace</button>
                    </form>
                </div>
            </article>
        @empty
            <article class="glass-card tenant-card">
                <span class="eyebrow">No tenants</span>
                <h2>Create your first workspace</h2>
                <p>Tenants you create here will appear in the workspace switcher.</p>
            </article>
        @endforelse
    </section>
</x-backend-layout>
