<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'services.view');
        $search = trim((string) $request->query('search'));

        $services = Service::query()
            ->where('tenant_id', $tenant->id)
            ->with('category')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('backend.services.index', [
            'tenant' => $tenant,
            'services' => $services,
            'categories' => ServiceCategory::where('tenant_id', $tenant->id)->orderBy('name')->get(),
            'search' => $search,
            'unitTypes' => Service::unitTypes(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'services.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('service_categories')->where('tenant_id', $tenant->id)],
            'description' => ['nullable', 'string', 'max:180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ServiceCategory::create($data + ['tenant_id' => $tenant->id, 'is_active' => $request->boolean('is_active', true)]);

        return back()->with('status', 'Service category created.');
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'services.manage');
        $data = $this->validated($request, $tenant);

        Service::create($data + ['tenant_id' => $tenant->id]);

        return back()->with('status', 'Service created.');
    }

    public function edit(Request $request, Service $service): View
    {
        $tenant = $this->currentTenant($request, 'services.manage');
        $this->ensureTenantService($tenant, $service);

        return view('backend.services.edit', [
            'tenant' => $tenant,
            'service' => $service,
            'categories' => ServiceCategory::where('tenant_id', $tenant->id)->orderBy('name')->get(),
            'unitTypes' => Service::unitTypes(),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'services.manage');
        $this->ensureTenantService($tenant, $service);

        $service->update($this->validated($request, $tenant, $service));

        return redirect()->route('services.index')->with('status', 'Service updated.');
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }

    private function ensureTenantService(Tenant $tenant, Service $service): void
    {
        abort_unless((int) $service->tenant_id === (int) $tenant->id, 404);
    }

    private function validated(Request $request, Tenant $tenant, ?Service $service = null): array
    {
        return $request->validate([
            'service_category_id' => ['nullable', Rule::exists('service_categories', 'id')->where('tenant_id', $tenant->id)],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('services')->where('tenant_id', $tenant->id)->ignore($service?->id),
            ],
            'unit_type' => ['required', Rule::in(Service::unitTypes())],
            'base_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}