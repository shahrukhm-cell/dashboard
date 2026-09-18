<x-backend-layout title="Create job">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $prefilledCustomer?->name ?? $tenant->name }}</span>
            <h2>Create job</h2>
        </div>
        <a class="text-link" href="{{ $prefilledCustomer ? route('customers.show', $prefilledCustomer) : route('jobs.index') }}">{{ $prefilledCustomer ? 'Back to customer' : 'Back to jobs' }}</a>
    </section>

    <section class="glass-card management-card">
        <form method="POST" action="{{ route('jobs.store') }}">
            @csrf
            @include('backend.jobs._form')
            <div class="management-actions">
                <button class="button button-primary" type="submit">Create job</button>
                <a class="button" href="{{ $prefilledCustomer ? route('customers.show', $prefilledCustomer) : route('jobs.index') }}">Cancel</a>
            </div>
        </form>
    </section>
</x-backend-layout>
