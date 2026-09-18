<x-backend-layout title="Profile">
    <section class="glass-card profile-card">
        <span class="eyebrow">Account</span>
        <h2>{{ auth()->user()->name }}</h2>
        <p>{{ auth()->user()->email }}</p>
        <form method="POST" action="{{ route('logout') }}"><button class="button button-primary" type="submit">Sign out</button>@csrf</form>
    </section>
</x-backend-layout>