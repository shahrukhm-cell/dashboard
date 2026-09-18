<header class="topbar">
    <div>
        <span class="eyebrow">{{ now()->format('l, F j, Y') }}</span>
        <h1>Welcome, {{ Str::of(auth()->user()->name)->before(' ') }}</h1>
    </div>
    <div class="topbar-actions">
        @if (($availableTenants ?? collect())->isNotEmpty())
            <form class="tenant-switcher" method="POST" action="{{ route('tenant.switch') }}">
                @csrf
                <span class="tenant-dot" style="background: {{ $currentTenant?->themeColor() ?? '#8b5cf6' }}"></span>
                <select name="tenant_id" aria-label="Switch workspace" onchange="this.form.submit()">
                    @foreach ($availableTenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($currentTenant?->is($tenant))>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </form>
        @else
            <span class="tenant-switcher tenant-switcher-empty">No workspace</span>
        @endif
        <label class="search-box">
            <span>âŒ•</span>
            <input type="search" placeholder="Search workspace" aria-label="Search workspace">
        </label>
        <button class="icon-button" type="button" title="Toggle light and dark mode" data-theme-toggle>â—</button>
        <a class="user-avatar" href="{{ route('profile.edit') }}" title="Open profile">{{ Str::of(auth()->user()->name)->substr(0, 2)->upper() }}</a>
    </div>
</header>
