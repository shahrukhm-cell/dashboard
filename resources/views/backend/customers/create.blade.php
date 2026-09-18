<x-backend-layout title="Create customer">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Create customer</h2>
        </div>
        <a class="text-link" href="{{ route('customers.index') }}">Back to customers</a>
    </section>

    <section class="glass-card management-card">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            @include('backend.customers._form')
            <div class="management-actions">
                <button class="button button-primary" type="submit">Create customer</button>
                <a class="button" href="{{ route('customers.index') }}">Cancel</a>
            </div>
        </form>
    </section>
</x-backend-layout>