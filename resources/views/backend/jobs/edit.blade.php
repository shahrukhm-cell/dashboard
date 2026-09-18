<x-backend-layout title="Edit job">
    <section class="section-heading">
        <div><span class="eyebrow">{{ $tenant->name }}</span><h2>Edit {{ $job->job_number }}</h2></div>
        <a class="text-link" href="{{ route('jobs.show', $job) }}">Back to job</a>
    </section>

    <section class="glass-card management-card">
        <form method="POST" action="{{ route('jobs.update', $job) }}">
            @csrf
            @method('PATCH')
            @include('backend.jobs._form')
            <div class="management-actions">
                <button class="button button-primary" type="submit">Save job</button>
                <a class="button" href="{{ route('jobs.show', $job) }}">Cancel</a>
            </div>
        </form>
    </section>
</x-backend-layout>