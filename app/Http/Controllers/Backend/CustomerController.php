<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ServiceJob;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'customers.view');
        $search = trim((string) $request->query('search'));

        $customers = Customer::query()
            ->where('tenant_id', $tenant->id)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('backend.customers.index', [
            'tenant' => $tenant,
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'customers.manage');

        return view('backend.customers.create', [
            'tenant' => $tenant,
            'customer' => new Customer(['status' => Customer::STATUS_ACTIVE]),
            'statuses' => Customer::statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'customers.manage');
        $data = $this->validated($request);

        $customer = Customer::create($data + ['tenant_id' => $tenant->id]);

        return redirect()->route('customers.show', $customer)->with('status', 'Customer created.');
    }

    public function show(Request $request, Customer $customer): View
    {
        $tenant = $this->currentTenant($request, 'customers.view');
        $this->ensureTenantCustomer($tenant, $customer);

        return view('backend.customers.show', [
            'tenant' => $tenant,
            'customer' => $customer,
            'dueNotCompletedJobs' => ServiceJob::where('tenant_id', $tenant->id)
                ->where('customer_id', $customer->id)
                ->whereNotIn('status', [ServiceJob::STATUS_COMPLETED, ServiceJob::STATUS_CANCELLED])
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->count(),
            'jobs' => ServiceJob::where('tenant_id', $tenant->id)
                ->where('customer_id', $customer->id)
                ->with(['team', 'assignee'])->withCount('items')
                ->latest()
                ->get(),
        ]);
    }

    public function edit(Request $request, Customer $customer): View
    {
        $tenant = $this->currentTenant($request, 'customers.manage');
        $this->ensureTenantCustomer($tenant, $customer);

        return view('backend.customers.edit', [
            'tenant' => $tenant,
            'customer' => $customer,
            'statuses' => Customer::statuses(),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'customers.manage');
        $this->ensureTenantCustomer($tenant, $customer);

        $customer->update($this->validated($request));

        return redirect()->route('customers.show', $customer)->with('status', 'Customer updated.');
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }

    private function ensureTenantCustomer(Tenant $tenant, Customer $customer): void
    {
        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 404);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(Customer::statuses())],
            'address_line' => ['nullable', 'string', 'max:180'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}


