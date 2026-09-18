<x-backend-layout title="Edit service">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Edit service</h2>
        </div>
        <a class="text-link" href="{{ route('services.index') }}">Back to services</a>
    </section>

    <section class="glass-card management-card">
        <form method="POST" action="{{ route('services.update', $service) }}">
            @csrf
            @method('PATCH')
            @include('backend.services._form')
            <div class="management-actions">
                <button class="button button-primary" type="submit">Save service</button>
                <a class="button" href="{{ route('services.index') }}">Cancel</a>
            </div>
        </form>
    </section>
</x-backend-layout>