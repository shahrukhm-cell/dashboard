<x-guest-layout>
    <div class="auth-heading"><span class="eyebrow">Start free</span><h2>Create your workspace</h2><p>Set up your Nexus account in under a minute.</p></div>
    @if ($errors->any()) <div class="auth-errors">{{ $errors->first() }}</div> @endif
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="auth-field"><label for="name">Full name</label><input class="auth-input" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"></div>
        <div class="auth-field"><label for="email">Work email</label><input class="auth-input" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></div>
        <div class="auth-field"><label for="password">Password</label><input class="auth-input" id="password" type="password" name="password" required autocomplete="new-password"></div>
        <div class="auth-field"><label for="password_confirmation">Confirm password</label><input class="auth-input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
        <div class="auth-options"><span>Already registered?</span><a class="text-link" href="{{ route('login') }}">Sign in</a></div>
        <button class="button button-primary auth-submit" type="submit">Create account</button>
    </form>
</x-guest-layout>
