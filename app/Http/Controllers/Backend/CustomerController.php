<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, Customer, ServiceJob, Tenant};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'customers.view');
        $search = trim((string) $request->query('search'));
        $canViewDeleted = $this->canViewDeleted($request, $tenant);
        $withDeleted = $canViewDeleted && $request->boolean('with_deleted');

        $customers = Customer::query()
            ->when($withDeleted, fn ($query) => $query->withTrashed())
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
            'canViewDeleted' => $canViewDeleted,
            'withDeleted' => $withDeleted,
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
        $data = $this->validated($request, $tenant);

        $customer = Customer::create($data + ['tenant_id' => $tenant->id]);
        ActivityLog::record('customer.created', $customer, $request, 'Customer created.');

        return redirect()->route('customers.show', $customer)->with('status', 'Customer created.');
    }

    public function show(Request $request, Customer $customer): View
    {
        $tenant = $this->currentTenant($request, 'customers.view');
        $this->ensureTenantCustomer($request, $tenant, $customer);
        $jobQuery = $this->canViewDeleted($request, $tenant) ? ServiceJob::withTrashed() : ServiceJob::query();

        return view('backend.customers.show', [
            'tenant' => $tenant,
            'customer' => $customer,
            'dueNotCompletedJobs' => (clone $jobQuery)->where('tenant_id', $tenant->id)
                ->where('customer_id', $customer->id)
                ->whereNotIn('status', [ServiceJob::STATUS_COMPLETED, ServiceJob::STATUS_CANCELLED])
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->count(),
            'jobs' => $jobQuery->where('tenant_id', $tenant->id)
                ->where('customer_id', $customer->id)
                ->with(['team', 'assignee'])->withCount('items')
                ->latest()
                ->get(),
        ]);
    }

    public function edit(Request $request, Customer $customer): View
    {
        $tenant = $this->currentTenant($request, 'customers.manage');
        $this->ensureTenantCustomer($request, $tenant, $customer);

        return view('backend.customers.edit', [
            'tenant' => $tenant,
            'customer' => $customer,
            'statuses' => Customer::statuses(),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'customers.manage');
        $this->ensureTenantCustomer($request, $tenant, $customer);

        $customer->update($this->validated($request, $tenant, $customer));
        ActivityLog::record('customer.updated', $customer, $request, 'Customer updated.');

        return redirect()->route('customers.show', $customer)->with('status', 'Customer updated.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'customers.manage');
        $this->ensureTenantCustomer($request, $tenant, $customer);

        $jobs = ServiceJob::where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->get();

        foreach ($jobs as $job) {
            $job->delete();
            ActivityLog::record('job.deleted', $job, $request, 'Job soft deleted with customer.');
        }

        $customer->delete();
        ActivityLog::record('customer.deleted', $customer, $request, 'Customer soft deleted with jobs.', [
            'jobs_deleted' => $jobs->count(),
        ]);

        return redirect()->route('customers.index')->with('status', 'Customer and customer jobs deleted. Owner and super admin can still view them.');
    }

    public function restore(Request $request, Customer $customer): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'customers.manage');
        $this->ensureTenantCustomer($request, $tenant, $customer);
        abort_unless($this->canViewDeleted($request, $tenant), 403);

        $customer->restore();
        $jobs = ServiceJob::withTrashed()
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->onlyTrashed()
            ->get();

        foreach ($jobs as $job) {
            $job->restore();
            ActivityLog::record('job.restored', $job, $request, 'Job restored with customer.');
        }

        ActivityLog::record('customer.restored', $customer, $request, 'Customer restored with jobs.', [
            'jobs_restored' => $jobs->count(),
        ]);

        return redirect()->route('customers.show', $customer)->with('status', 'Customer and customer jobs restored.');
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }

    private function ensureTenantCustomer(Request $request, Tenant $tenant, Customer $customer): void
    {
        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 404);
        abort_if($customer->trashed() && ! $this->canViewDeleted($request, $tenant), 404);
    }

    private function canViewDeleted(Request $request, Tenant $tenant): bool
    {
        return $request->user()->is_super_admin
            || (int) $tenant->created_by === (int) $request->user()->id
            || $request->user()->hasTenantRole($tenant, 'tenant-owner');
    }

    private function validated(Request $request, Tenant $tenant, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('customers', 'email')
                    ->where('tenant_id', $tenant->id)
                    ->ignore($customer?->id),
            ],
            'phone' => ['required', 'string', 'max:40'],
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




