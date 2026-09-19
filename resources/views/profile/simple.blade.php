<x-backend-layout title="Profile">
    <section class="section-heading">
        <div>
            <span class="eyebrow">Account</span>
            <h2>My profile</h2>
        </div>
    </section>

    @if (session('status') === 'profile-updated')
        <p class="status-message">Profile updated.</p>
    @endif

    <section class="glass-card management-card">
        <form class="customer-form-grid" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <label class="auth-field">
                <span>Name</span>
                <input class="auth-input" name="name" value="{{ old('name', $user->name) }}" required>
            </label>
            <label class="auth-field">
                <span>Email</span>
                <input class="auth-input" name="email" type="email" value="{{ old('email', $user->email) }}" required>
            </label>
            <label class="auth-field">
                <span>Phone</span>
                <input class="auth-input" name="phone" value="{{ old('phone', $user->phone) }}">
            </label>
            <label class="auth-field">
                <span>Emergency contact</span>
                <input class="auth-input" name="emergency_contact" value="{{ old('emergency_contact', $user->emergency_contact) }}">
            </label>
            <label class="auth-field customer-span-2">
                <span>Address</span>
                <input class="auth-input" name="address" value="{{ old('address', $user->address) }}">
            </label>
            <label class="auth-field customer-span-2">
                <span>Reference</span>
                <input class="auth-input" name="reference" value="{{ old('reference', $user->reference) }}">
            </label>

            <button class="button button-primary" type="submit">Update profile</button>
        </form>

        @if ($errors->any())
            <div class="auth-errors customer-errors">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </section>
</x-backend-layout>

