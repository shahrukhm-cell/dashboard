@php($sidebarTenant = $currentTenant ?? null)

<aside class="sidebar" data-sidebar>
    <a class="brand" href="{{ route('dashboard') }}">
        @if ($sidebarTenant?->logoUrl())
            <img class="brand-logo" src="{{ $sidebarTenant->logoUrl() }}" alt="{{ $sidebarTenant->brandName() }} logo">
        @else
            <span class="brand-mark">{{ Str::of($sidebarTenant?->brandName() ?? 'Servico Fusion')->substr(0, 1)->upper() }}</span>
        @endif
        <span class="brand-name">{{ $sidebarTenant?->brandName() ?? 'Servico Fusion' }}</span>
    </a>

    <p class="nav-label">Workspace</p>
    <nav class="nav-menu" aria-label="Main navigation">
        <a class="nav-link" href="{{ route('dashboard') }}"><span class="nav-symbol">O</span> Overview</a>

        @if ($sidebarTenant && auth()->user()->hasPermission('reports.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('reports.index') }}"><span class="nav-symbol">R</span> Reports</a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('customers.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('customers.index') }}"><span class="nav-symbol">C</span> Customers</a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('services.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('services.index') }}"><span class="nav-symbol">S</span> Services</a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('jobs.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('jobs.index') }}"><span class="nav-symbol">J</span> Jobs</a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('jobs.assign', $sidebarTenant))
            <a class="nav-link" href="{{ route('teams.index') }}"><span class="nav-symbol">M</span> Teams</a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('tenant.settings.update', $sidebarTenant))
            <a class="nav-link" href="{{ route('tenant.users.index', $sidebarTenant) }}"><span class="nav-symbol">G</span> Settings</a>
            @if (auth()->user()->hasPermission('roles.manage', $sidebarTenant))
                <a class="nav-link" href="{{ route('tenant.roles.index', $sidebarTenant) }}"><span class="nav-symbol">P</span> Roles</a>
            @endif
        @endif

        @if (auth()->user()->is_super_admin)
            <a class="nav-link" href="{{ route('admin.plans.index') }}"><span class="nav-symbol">L</span> Plans</a>
            <a class="nav-link" href="{{ route('admin.tenants.index') }}"><span class="nav-symbol">T</span> Tenants</a>
        @endif
    </nav>

    {{-- <div class="sidebar-footer">
        <div class="plan-card">
            <span class="eyebrow">Current plan</span>
            <strong>Scale workspace</strong>
            <div class="plan-meter"><span></span></div>
            <small>72% of monthly usage</small>
        </div>
    </div> --}}
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="nav-link logout-button" type="submit"><span class="nav-symbol">X</span> Sign out</button>
        </form>
    </div>
</aside>



