<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JobWorkEvent;
use App\Models\Service;
use App\Models\ServiceJob;
use App\Models\ServiceJobStatusEvent;
use App\Models\Team;
use App\Models\TeamPayment;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceJobController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'jobs.view');
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('search'));

        $jobs = ServiceJob::query()
            ->where('tenant_id', $tenant->id)
            ->when($request->user()->isFieldStaff($tenant), fn ($query) => $this->forAssignedFieldStaff($query, $request->user()->id))
            ->with(['customer', 'team', 'assignee', 'statusEvents' => fn ($query) => $query->latest('changed_at')])
            ->withCount('items')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('job_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('backend.jobs.index', [
            'tenant' => $tenant,
            'jobs' => $jobs,
            'statuses' => ServiceJob::statuses(),
            'status' => $status,
            'search' => $search,
            'canViewFinance' => ! $request->user()->isFieldStaff($tenant),
        ]);
    }

    public function create(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'jobs.manage');
        $customer = null;

        if ($request->filled('customer_id')) {
            $customer = Customer::where('tenant_id', $tenant->id)->findOrFail($request->integer('customer_id'));
        }

        $job = new ServiceJob([
            'status' => ServiceJob::STATUS_DRAFT,
            'customer_id' => $customer?->id,
            'service_address' => $customer?->addressSummary(),
        ]);

        return view('backend.jobs.create', $this->formData($tenant, $job) + ['prefilledCustomer' => $customer]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.manage');
        $data = $this->validated($request, $tenant);

        $job = DB::transaction(function () use ($tenant, $data, $request): ServiceJob {
            $job = ServiceJob::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $data['customer_id'],
                'team_id' => $data['team_id'] ?? null,
                'assigned_user_id' => $data['assigned_user_id'] ?? null,
                'job_number' => $this->nextJobNumber($tenant),
                'status' => $data['status'],
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'service_address' => $data['service_address'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($job, $tenant, $data['items']);
            $this->recordStatusChange($tenant, $job, null, $job->status, $request->user()->id, 'Job created.');

            return $job;
        });

        return redirect()->route('jobs.show', $job)->with('status', 'Job created.');
    }

    public function show(Request $request, ServiceJob $job): View
    {
        $tenant = $this->currentTenant($request, 'jobs.view');
        $this->ensureTenantJob($tenant, $job);
        $this->ensureVisibleJob($request, $tenant, $job);

        $job->load(['customer', 'team.users', 'assignee', 'items.service', 'timeEntries.user', 'workEvents.user', 'statusEvents.user', 'expenses.category', 'expenses.submitter', 'expenses.approver', 'customerPayments', 'teamPayments']);
        $workEvents = $job->workEvents->sortBy('occurred_at')->values();

        $canViewFinance = ! $request->user()->isFieldStaff($tenant);
        $canManageFinance = $request->user()->hasPermission('payments.manage', $tenant);
        $canManageExpenses = $request->user()->hasPermission('expenses.manage', $tenant);
        $canSubmitExpenses = $request->user()->hasPermission('expenses.submit', $tenant);
        $canApproveExpenses = $request->user()->hasPermission('expenses.approve', $tenant);

        return view('backend.jobs.show', [
            'tenant' => $tenant,
            'job' => $job,
            'canViewFinance' => $canViewFinance,
            'canManageFinance' => $canManageFinance,
            'canManageExpenses' => $canManageExpenses,
            'canSubmitExpenses' => $canSubmitExpenses,
            'canApproveExpenses' => $canApproveExpenses,
            'canWorkJob' => $this->canWorkJob($request, $tenant, $job),
            'canUpdateStatus' => $this->canUpdateStatus($request, $tenant, $job),
            'statusOptions' => ServiceJob::statuses(),
            'workState' => $this->workState($workEvents),
            'workSummary' => $this->workSummary($workEvents),
            'expenseCategories' => ExpenseCategory::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get(),
            'tenantUsers' => $tenant->users()->orderBy('name')->get(),
            'expenseStatuses' => Expense::statuses(),
            'customerPaymentStatuses' => CustomerPayment::statuses(),
            'customerPaymentMethods' => CustomerPayment::methods(),
            'teamPaymentStatuses' => TeamPayment::statuses(),
            'teamPaymentMethods' => TeamPayment::methods(),
        ]);
    }

    public function edit(Request $request, ServiceJob $job): View
    {
        $tenant = $this->currentTenant($request, 'jobs.manage');
        $this->ensureTenantJob($tenant, $job);

        return view('backend.jobs.edit', $this->formData($tenant, $job->load('items')));
    }

    public function update(Request $request, ServiceJob $job): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.manage');
        $this->ensureTenantJob($tenant, $job);
        $data = $this->validated($request, $tenant);
        $oldStatus = $job->status;

        DB::transaction(function () use ($job, $tenant, $data, $oldStatus, $request): void {
            $job->update([
                'customer_id' => $data['customer_id'],
                'team_id' => $data['team_id'] ?? null,
                'assigned_user_id' => $data['assigned_user_id'] ?? null,
                'status' => $data['status'],
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'service_address' => $data['service_address'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $job->items()->delete();
            $this->syncItems($job, $tenant, $data['items']);

            if ($oldStatus !== $job->status) {
                $this->recordStatusChange($tenant, $job, $oldStatus, $job->status, $request->user()->id, $request->input('status_notes'));
            }
        });

        return redirect()->route('jobs.show', $job)->with('status', 'Job updated.');
    }

    public function updateStatus(Request $request, ServiceJob $job): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.status.update');
        $this->ensureTenantJob($tenant, $job);
        $this->ensureVisibleJob($request, $tenant, $job);

        abort_unless($this->canUpdateStatus($request, $tenant, $job), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(ServiceJob::statuses())],
            'status_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $job->status;

        if ($oldStatus !== $data['status']) {
            $job->update(['status' => $data['status']]);
            $this->recordStatusChange($tenant, $job, $oldStatus, $data['status'], $request->user()->id, $data['status_notes'] ?? null);
        }

        return back()->with('status', 'Job status updated.');
    }

    private function formData(Tenant $tenant, ServiceJob $job): array
    {
        return [
            'tenant' => $tenant,
            'job' => $job,
            'customers' => Customer::where('tenant_id', $tenant->id)->where('status', Customer::STATUS_ACTIVE)->orderBy('name')->get(),
            'services' => Service::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get(),
            'teams' => Team::where('tenant_id', $tenant->id)->where('is_active', true)->with('users')->orderBy('name')->get(),
            'assignableUsers' => $tenant->users()->with(['roles' => fn ($query) => $query->wherePivot('tenant_id', $tenant->id)])->orderBy('name')->get(),
            'statuses' => ServiceJob::statuses(),
        ];
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }

    private function ensureTenantJob(Tenant $tenant, ServiceJob $job): void
    {
        abort_unless((int) $job->tenant_id === (int) $tenant->id, 404);
    }

    private function ensureVisibleJob(Request $request, Tenant $tenant, ServiceJob $job): void
    {
        if (! $request->user()->isFieldStaff($tenant)) {
            return;
        }

        $job->loadMissing('team.users');

        $isAssigned = (int) $job->assigned_user_id === (int) $request->user()->id
            || $job->team?->users->contains('id', $request->user()->id);

        abort_unless($isAssigned, 404);
    }

    private function forAssignedFieldStaff($query, int $userId)
    {
        return $query->where(function ($query) use ($userId): void {
            $query->where('assigned_user_id', $userId)
                ->orWhereHas('team.users', fn ($query) => $query->whereKey($userId));
        });
    }

    private function canWorkJob(Request $request, Tenant $tenant, ServiceJob $job): bool
    {
        if (! ($request->user()->hasPermission('jobs.work', $tenant) || $request->user()->hasPermission('jobs.manage', $tenant))) {
            return false;
        }

        return $request->user()->hasPermission('jobs.manage', $tenant)
            || (int) $job->assigned_user_id === (int) $request->user()->id
            || $job->team?->users->contains('id', $request->user()->id);
    }

    private function canUpdateStatus(Request $request, Tenant $tenant, ServiceJob $job): bool
    {
        if ($request->user()->hasPermission('jobs.manage', $tenant)) {
            return true;
        }

        if (! $request->user()->hasPermission('jobs.status.update', $tenant)) {
            return false;
        }

        $job->loadMissing('team.users');

        return (int) $job->assigned_user_id === (int) $request->user()->id
            || $job->team?->users->contains('id', $request->user()->id);
    }

    private function recordStatusChange(Tenant $tenant, ServiceJob $job, ?string $oldStatus, string $newStatus, ?int $userId, ?string $notes = null): void
    {
        ServiceJobStatusEvent::create([
            'tenant_id' => $tenant->id,
            'service_job_id' => $job->id,
            'user_id' => $userId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_at' => now(),
            'notes' => $notes,
        ]);
    }

    private function workState($events): string
    {
        $last = $events->last();

        return match ($last?->event_type) {
            JobWorkEvent::TYPE_START, JobWorkEvent::TYPE_BREAK_END => 'working',
            JobWorkEvent::TYPE_BREAK_START => 'on_break',
            JobWorkEvent::TYPE_END => 'ended',
            default => 'not_started',
        };
    }

    private function workSummary($events): array
    {
        $startedAt = $events->firstWhere('event_type', JobWorkEvent::TYPE_START)?->occurred_at;
        $endedAt = $events->reverse()->firstWhere('event_type', JobWorkEvent::TYPE_END)?->occurred_at;
        $clockEnd = $endedAt ?? now();
        $breakMinutes = 0;
        $openBreak = null;

        foreach ($events as $event) {
            if ($event->event_type === JobWorkEvent::TYPE_BREAK_START) {
                $openBreak = $event->occurred_at;
            }

            if ($event->event_type === JobWorkEvent::TYPE_BREAK_END && $openBreak) {
                $breakMinutes += $openBreak->diffInMinutes($event->occurred_at);
                $openBreak = null;
            }
        }

        if ($openBreak) {
            $breakMinutes += $openBreak->diffInMinutes($clockEnd);
        }

        $totalMinutes = $startedAt ? $startedAt->diffInMinutes($clockEnd) : 0;

        return [
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'total_minutes' => $totalMinutes,
            'break_minutes' => $breakMinutes,
            'working_minutes' => max(0, $totalMinutes - $breakMinutes),
        ];
    }

    private function validated(Request $request, Tenant $tenant): array
    {
        return $request->validate([
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('tenant_id', $tenant->id)],
            'team_id' => ['nullable', Rule::exists('teams', 'id')->where('tenant_id', $tenant->id)],
            'assigned_user_id' => ['nullable', Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenant->id)],
            'status' => ['required', Rule::in(ServiceJob::statuses())],
            'scheduled_at' => ['nullable', 'date'],
            'service_address' => ['nullable', 'string', 'max:180'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status_notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', Rule::exists('services', 'id')->where('tenant_id', $tenant->id)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);
    }

    private function syncItems(ServiceJob $job, Tenant $tenant, array $items): void
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $service = Service::where('tenant_id', $tenant->id)->findOrFail($item['service_id']);
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $service->base_price;
            $lineTotal = round($quantity * $unitPrice, 2);
            $subtotal += $lineTotal;

            $job->items()->create([
                'service_id' => $service->id,
                'name' => $service->name,
                'unit_type' => $service->unit_type,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }

        $discount = (float) $job->discount;
        $job->update([
            'subtotal' => $subtotal,
            'total' => max(0, round($subtotal - $discount, 2)),
        ]);
    }

    private function nextJobNumber(Tenant $tenant): string
    {
        $next = ServiceJob::where('tenant_id', $tenant->id)->count() + 1;

        return 'JOB-'.now()->format('Ymd').'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}




