@php($sidebarTenant = $currentTenant ?? null)

<aside class="sidebar" data-sidebar>
    <button class="sidebar-collapse-toggle" type="button" data-sidebar-collapse-toggle aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
    </button>
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
        <a class="nav-link" href="{{ route('dashboard') }}" title="Overview"><span class="nav-symbol">O</span> <span class="nav-text">Overview</span></a>

        @if ($sidebarTenant && auth()->user()->hasPermission('reports.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('reports.index') }}" title="Reports"><span class="nav-symbol">R</span> <span class="nav-text">Reports</span></a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('customers.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('customers.index') }}" title="Customers"><span class="nav-symbol">C</span> <span class="nav-text">Customers</span></a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('services.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('services.index') }}" title="Services"><span class="nav-symbol">S</span> <span class="nav-text">Services</span></a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('jobs.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('jobs.index') }}" title="Jobs"><span class="nav-symbol">J</span> <span class="nav-text">Jobs</span></a>
        @endif

        @if ($sidebarTenant && (auth()->user()->hasPermission('expenses.manage', $sidebarTenant) || auth()->user()->hasPermission('expenses.submit', $sidebarTenant) || auth()->user()->hasPermission('expenses.approve', $sidebarTenant)))
            <a class="nav-link" href="{{ route('expenses.index') }}" title="Expenses"><span class="nav-symbol">E</span> <span class="nav-text">Expenses</span></a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('payments.view', $sidebarTenant))
            <a class="nav-link" href="{{ route('payments.index') }}" title="Payments"><span class="nav-symbol">P</span> <span class="nav-text">Payments</span></a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('jobs.assign', $sidebarTenant))
            <a class="nav-link" href="{{ route('teams.index') }}" title="Teams"><span class="nav-symbol">T</span> <span class="nav-text">Teams</span></a>
        @endif

        @if ($sidebarTenant && auth()->user()->hasPermission('tenant.settings.update', $sidebarTenant))
            <a class="nav-link" href="{{ route('tenant.users.index', $sidebarTenant) }}" title="Settings"><span class="nav-symbol">S</span> <span class="nav-text">Settings</span></a>
            @if (auth()->user()->hasPermission('roles.manage', $sidebarTenant))
                <a class="nav-link" href="{{ route('tenant.roles.index', $sidebarTenant) }}" title="Roles"><span class="nav-symbol">R</span> <span class="nav-text">Roles</span></a>
            @endif
        @endif

        @if (auth()->user()->is_super_admin)
            <a class="nav-link" href="{{ route('admin.plans.index') }}" title="Plans"><span class="nav-symbol">P</span> <span class="nav-text">Plans</span></a>
            <a class="nav-link" href="{{ route('admin.tenants.index') }}" title="Tenants"><span class="nav-symbol">T</span> <span class="nav-text">Tenants</span></a>
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
            <button class="nav-link logout-button" type="submit" title="Sign out"><span class="nav-symbol">X</span> <span class="nav-text">Sign out</span></button>
        </form>
    </div>
</aside>



