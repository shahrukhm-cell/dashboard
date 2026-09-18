<x-backend-layout title="Services">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Service catalog</h2>
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    @if (auth()->user()->hasPermission('services.manage', $tenant))
        <section class="dashboard-grid management-grid">
            <article class="glass-card management-card service-form-card">
                <h2>Create category</h2>
                <form class="service-stack-form" method="POST" action="{{ route('service-categories.store') }}">
                    @csrf
                    <input class="auth-input" name="name" placeholder="Category name" required>
                    <input class="auth-input" name="description" placeholder="Short description">
                    <label class="permission-check"><input type="checkbox" name="is_active" value="1" checked><span>Active</span></label>
                    <button class="button button-primary" type="submit">Create category</button>
                </form>
            </article>

            <article class="glass-card management-card service-form-card activity-card">
                <h2>Create service</h2>
                <form method="POST" action="{{ route('services.store') }}">
                    @csrf
                    @include('backend.services._form', ['service' => new App\Models\Service(['unit_type' => 'job', 'base_price' => 0, 'is_active' => true])])
                    <div class="management-actions">
                        <button class="button button-primary" type="submit">Create service</button>
                    </div>
                </form>
            </article>
        </section>
    @endif

    <section class="glass-card management-card">
        <form class="customer-toolbar" method="GET" action="{{ route('services.index') }}">
            <input class="auth-input" name="search" value="{{ $search }}" placeholder="Search services">
            <button class="button" type="submit">Search</button>
            @if ($search !== '')
                <a class="button" href="{{ route('services.index') }}">Clear</a>
            @endif
        </form>
    </section>

    <section class="glass-card management-card">
        <div class="service-list">
            @forelse ($services as $service)
                <article class="service-row">
                    <div>
                        <strong>{{ $service->name }}</strong>
                        <small>{{ $service->category?->name ?? 'No category' }}</small>
                    </div>
                    <span>{{ Str::upper($service->unit_type) }}</span>
                    <span>${{ number_format((float) $service->base_price, 2) }}</span>
                    <span class="tenant-status tenant-status-{{ $service->is_active ? 'active' : 'archived' }}">{{ $service->is_active ? 'Active' : 'Inactive' }}</span>
                    @if (auth()->user()->hasPermission('services.manage', $tenant))
                        <a class="button" href="{{ route('services.edit', $service) }}">Edit</a>
                    @endif
                </article>
            @empty
                <div class="empty-state">
                    <span class="eyebrow">No services</span>
                    <h2>Create your first service</h2>
                    <p>Services become selectable line items when jobs are added.</p>
                </div>
            @endforelse
        </div>

        <div class="pagination-wrap">{{ $services->links() }}</div>
    </section>
</x-backend-layout>