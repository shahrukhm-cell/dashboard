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
        <form class="customer-toolbar" method="GET" action="{{ route('customers.index') }}">
            <input class="auth-input" name="search" value="{{ $search }}" placeholder="Search name, email, phone, or company">
            @if ($withDeleted ?? false)
                <input type="hidden" name="with_deleted" value="1">
            @endif
            <button class="button" type="submit">Search</button>
            @if ($search !== '')
                <a class="button" href="{{ route('customers.index') }}">Clear</a>
            @endif
        </form>
    </section>

    <section class="glass-card management-card">
        <div class="customer-list">
            @forelse ($customers as $customer)
                <article class="customer-row">
                    <span class="customer-avatar">{{ Str::of($customer->name)->substr(0, 2)->upper() }}</span>
                    <span>
                        <strong><a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></strong>
                        <small>{{ $customer->company ?: 'Individual customer' }}</small>
                    </span>
                    <span>{{ $customer->phone ?: 'No phone' }}</span>
                    <span>{{ $customer->email ?: 'No email' }}</span>
                    <span class="tenant-status tenant-status-{{ $customer->trashed() ? 'archived' : $customer->status }}">{{ $customer->trashed() ? 'Deleted' : Str::title($customer->status) }}</span>
                    @if (auth()->user()->hasPermission('customers.manage', $tenant))
                        @if ($customer->trashed())
                            <form method="POST" action="{{ route('customers.restore', $customer) }}">@csrf @method('PATCH')<button class="button" type="submit">Restore</button></form>
                        @else
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}">@csrf @method('DELETE')<button class="button" type="submit">Delete</button></form>
                        @endif
                    @endif
                </article>
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
