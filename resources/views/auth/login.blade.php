<x-guest-layout>
    <div class="auth-heading"><span class="eyebrow">Welcome back</span><h2>Sign in to Nexus</h2><p>Continue to your workspace.</p></div>
    @if ($errors->any())
        <div class="auth-errors">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="auth-field">
            <label for="email">Work email</label>
            <input class="auth-input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>
        <div class="auth-field">
            <label for="password">Password</label>
            <input class="auth-input" id="password" type="password" name="password" required autocomplete="current-password">
        </div>
        <div class="auth-options">
            <label><input type="checkbox" name="remember"> Remember me</label>
            <a class="text-link" href="{{ route('register') }}">Create account</a>
        </div>
        <button class="button button-primary auth-submit" type="submit">Log in</button>
    </form>
</x-guest-layout>
