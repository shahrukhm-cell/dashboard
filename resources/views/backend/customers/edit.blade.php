<x-backend-layout title="Edit customer">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Edit customer</h2>
        </div>
        <a class="text-link" href="{{ route('customers.show', $customer) }}">Back to customer</a>
    </section>

    <section class="glass-card management-card">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PATCH')
            @include('backend.customers._form')
            <div class="management-actions">
                <button class="button button-primary" type="submit">Save customer</button>
                <a class="button" href="{{ route('customers.show', $customer) }}">Cancel</a>
            </div>
        </form>
    </section>
</x-backend-layout>