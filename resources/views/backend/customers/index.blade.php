<x-backend-layout title="Customers">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Customers</h2>
        </div>
        @if (auth()->user()->hasPermission('customers.manage', $tenant))
            <a class="button button-primary" href="{{ route('customers.create') }}">Add customer</a>
        @endif
    </section>

    @if (session('status'))
    
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="glass-card management-card">
        <form class="customer-toolbar" method="GET" action="{{ route('customers.index') }}">
            <input class="auth-input" name="search" value="{{ $search }}" placeholder="Search name, email, phone, or company">
            <button class="button" type="submit">Search</button>
            @if ($search !== '')
                <a class="button" href="{{ route('customers.index') }}">Clear</a>
            @endif
        </form>
    </section>

    <section class="glass-card management-card">
        <div class="customer-list">
            @forelse ($customers as $customer)
                <a class="customer-row" href="{{ route('customers.show', $customer) }}">
                    <span class="customer-avatar">{{ Str::of($customer->name)->substr(0, 2)->upper() }}</span>
                    <span>
                        <strong>{{ $customer->name }}</strong>
                        <small>{{ $customer->company ?: 'Individual customer' }}</small>
                    </span>
                    <span>{{ $customer->phone ?: 'No phone' }}</span>
                    <span>{{ $customer->email ?: 'No email' }}</span>
                    <span class="tenant-status tenant-status-{{ $customer->status }}">{{ Str::title($customer->status) }}</span>
                </a>
            @empty
                <div class="empty-state">
                    <span class="eyebrow">No customers</span>
                    <h2>Add your first customer</h2>
                    <p>Customers will be scoped to the selected workspace.</p>
                </div>
            @endforelse
        </div>

        <div class="pagination-wrap">{{ $customers->links() }}</div>
    </section>
</x-backend-layout>