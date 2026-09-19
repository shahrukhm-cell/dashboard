<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JobPhoto;
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
        $quoteStatus = (string) $request->query('quote_status', '');
        $due = (string) $request->query('due', '');
        $customerId = (string) $request->query('customer_id', '');
        $teamId = (string) $request->query('team_id', '');
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');
        $search = trim((string) $request->query('search'));

        $jobs = ServiceJob::query()
            ->where('tenant_id', $tenant->id)
            ->when($request->user()->isFieldStaff($tenant), fn ($query) => $this->forAssignedFieldStaff($query, $request->user()->id))
            ->with(['customer', 'team', 'assignee', 'statusEvents' => fn ($query) => $query->latest('changed_at')])
            ->withCount(['items', 'beforePhotos', 'afterPhotos'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($quoteStatus !== '', fn ($query) => $query->where('quote_status', $quoteStatus))
            ->when($customerId !== '', fn ($query) => $query->where('customer_id', $customerId))
            ->when($teamId !== '', fn ($query) => $query->where('team_id', $teamId))
            ->when($from !== '', fn ($query) => $query->whereDate('scheduled_at', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('scheduled_at', '<=', $to))
            ->when($due === 'due_not_completed', fn ($query) => $query->whereNotIn('status', [ServiceJob::STATUS_COMPLETED, ServiceJob::STATUS_CANCELLED])->whereNotNull('scheduled_at')->where('scheduled_at', '<=', now()))
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
            'quoteStatuses' => ServiceJob::quoteStatuses(),
            'status' => $status,
            'quoteStatus' => $quoteStatus,
            'due' => $due,
            'customerId' => $customerId,
            'teamId' => $teamId,
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'customers' => Customer::where('tenant_id', $tenant->id)->orderBy('name')->get(),
            'teams' => Team::where('tenant_id', $tenant->id)->orderBy('name')->get(),
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
            'quote_status' => ServiceJob::QUOTE_DRAFT,
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
                'quote_status' => $data['quote_status'] ?? ServiceJob::QUOTE_DRAFT,
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

        $job->load(['customer', 'team.users', 'assignee', 'items.service', 'photos.uploader', 'timeEntries.user', 'workEvents.user', 'statusEvents.user', 'expenses.category', 'expenses.submitter', 'expenses.approver', 'customerPayments.customer', 'teamPayments.team', 'teamPayments.user']);
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
            'canUploadJobPhotos' => $this->canUploadJobPhotos($request, $tenant, $job),
            'statusOptions' => $this->availableStatusOptions($request, $tenant, $job),
            'quoteStatuses' => ServiceJob::quoteStatuses(),
            'photoTypes' => JobPhoto::types(),
            'workState' => $this->workState($workEvents),
            'workSummary' => $this->workSummary($workEvents),
            'expenseCategories' => ExpenseCategory::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get(),
            'tenantUsers' => $this->tenantVisibleUsers($tenant)->orderBy('name')->get(),
            'expenseStatuses' => Expense::statuses(),
            'jobs' => ServiceJob::where('tenant_id', $tenant->id)->with('customer')->latest()->get(),
            'customers' => Customer::where('tenant_id', $tenant->id)->orderBy('name')->get(),
            'teams' => Team::where('tenant_id', $tenant->id)->orderBy('name')->get(),
            'users' => $this->tenantVisibleUsers($tenant)->orderBy('name')->get(),
            'customerStatuses' => CustomerPayment::statuses(),
            'teamStatuses' => TeamPayment::statuses(),
            'customerMethods' => CustomerPayment::methods(),
            'teamMethods' => TeamPayment::methods(),
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
            if ($oldStatus !== $data['status'] && $data['status'] === ServiceJob::STATUS_COMPLETED) {
                $this->ensureCompletionReady($job);
            }

            $job->update([
                'customer_id' => $data['customer_id'],
                'team_id' => $data['team_id'] ?? null,
                'assigned_user_id' => $data['assigned_user_id'] ?? null,
                'status' => $data['status'],
                'quote_status' => $data['quote_status'] ?? $job->quote_status,
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

    public function updateQuote(Request $request, ServiceJob $job): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.manage');
        $this->ensureTenantJob($tenant, $job);

        $data = $request->validate([
            'quote_status' => ['required', Rule::in(ServiceJob::quoteStatuses())],
        ]);

        $job->update([
            'quote_status' => $data['quote_status'],
            'quote_sent_at' => $data['quote_status'] === ServiceJob::QUOTE_SENT ? now() : $job->quote_sent_at,
            'quote_approved_at' => $data['quote_status'] === ServiceJob::QUOTE_APPROVED ? now() : $job->quote_approved_at,
        ]);

        return back()->with('status', 'Quote status updated.');
    }

    public function storePhoto(Request $request, ServiceJob $job): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.status.update');
        $this->ensureTenantJob($tenant, $job);
        $this->ensureVisibleJob($request, $tenant, $job);
        abort_unless($this->canUploadJobPhotos($request, $tenant, $job), 403);

        $data = $request->validate([
            'type' => ['required', Rule::in(JobPhoto::types())],
            'photo' => ['required', 'image', 'max:6144'],
            'caption' => ['nullable', 'string', 'max:180'],
        ]);

        $path = $request->file('photo')->store('job-photos/'.$tenant->id.'/'.$job->id, 'public');

        $job->photos()->create([
            'tenant_id' => $tenant->id,
            'uploaded_by' => $request->user()->id,
            'type' => $data['type'],
            'path' => $path,
            'caption' => $data['caption'] ?? null,
        ]);

        return back()->with('status', 'Job photo uploaded.');
    }

    public function updateStatus(Request $request, ServiceJob $job): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.status.update');
        $this->ensureTenantJob($tenant, $job);
        $this->ensureVisibleJob($request, $tenant, $job);

        abort_unless($this->canUpdateStatus($request, $tenant, $job), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in($this->availableStatusOptions($request, $tenant, $job))],
            'status_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $job->status;

        if ($oldStatus !== $data['status']) {
            if ($data['status'] === ServiceJob::STATUS_COMPLETED) {
                $this->ensureCompletionReady($job);
            }

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
            'assignableUsers' => $this->tenantVisibleUsers($tenant)->with(['roles' => fn ($query) => $query->wherePivot('tenant_id', $tenant->id)])->orderBy('name')->get(),
            'statuses' => ServiceJob::statuses(),
            'quoteStatuses' => ServiceJob::quoteStatuses(),
        ];
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }

    private function tenantVisibleUsers(Tenant $tenant)
    {
        return $tenant->users()->where('users.id', '!=', 1)->where('users.is_super_admin', false);
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

        abort_unless($this->isAssignedToJob($request, $job), 404);
        abort_unless(in_array($job->status, $this->fieldVisibleStatuses(), true), 404);
    }

    private function isAssignedToJob(Request $request, ServiceJob $job): bool
    {
        $job->loadMissing('team.users');

        return (int) $job->assigned_user_id === (int) $request->user()->id
            || $job->team?->users->contains('id', $request->user()->id);
    }

    private function fieldVisibleStatuses(): array
    {
        return [ServiceJob::STATUS_APPROVED, ServiceJob::STATUS_IN_PROGRESS, ServiceJob::STATUS_COMPLETED];
    }

    private function forAssignedFieldStaff($query, int $userId)
    {
        return $query
            ->whereIn('status', $this->fieldVisibleStatuses())
            ->where(function ($query) use ($userId): void {
                $query->where('assigned_user_id', $userId)
                    ->orWhereHas('team.users', fn ($query) => $query->whereKey($userId));
            });
    }

    private function canWorkJob(Request $request, Tenant $tenant, ServiceJob $job): bool
    {
        if (! ($request->user()->hasPermission('jobs.work', $tenant) || $request->user()->hasPermission('jobs.manage', $tenant))) {
            return false;
        }

        if ($request->user()->hasPermission('jobs.manage', $tenant)) {
            return true;
        }

        return $request->user()->hasTenantRole($tenant, 'team-lead')
            && $this->isAssignedToJob($request, $job)
            && in_array($job->status, [ServiceJob::STATUS_APPROVED, ServiceJob::STATUS_IN_PROGRESS], true);
    }

    private function canUpdateStatus(Request $request, Tenant $tenant, ServiceJob $job): bool
    {
        if ($request->user()->hasPermission('jobs.manage', $tenant)) {
            return true;
        }

        if (! $request->user()->hasPermission('jobs.status.update', $tenant) || ! $request->user()->hasTenantRole($tenant, 'team-lead')) {
            return false;
        }

        return $this->isAssignedToJob($request, $job)
            && $this->availableStatusOptions($request, $tenant, $job) !== [];
    }

    private function availableStatusOptions(Request $request, Tenant $tenant, ServiceJob $job): array
    {
        if ($request->user()->hasPermission('jobs.manage', $tenant)) {
            return ServiceJob::statuses();
        }

        if (! $request->user()->hasTenantRole($tenant, 'team-lead') || ! $this->isAssignedToJob($request, $job)) {
            return [];
        }

        return match ($job->status) {
            ServiceJob::STATUS_APPROVED => [ServiceJob::STATUS_IN_PROGRESS],
            ServiceJob::STATUS_IN_PROGRESS => [ServiceJob::STATUS_COMPLETED],
            default => [],
        };
    }

    private function canUploadJobPhotos(Request $request, Tenant $tenant, ServiceJob $job): bool
    {
        return $request->user()->hasPermission('jobs.manage', $tenant)
            || ($request->user()->hasTenantRole($tenant, 'team-lead')
                && $this->isAssignedToJob($request, $job)
                && in_array($job->status, $this->fieldVisibleStatuses(), true));
    }

    private function ensureCompletionReady(ServiceJob $job): void
    {
        $job->loadCount(['beforePhotos', 'afterPhotos']);

        abort_if($job->before_photos_count < 1 || $job->after_photos_count < 1, 422, 'Upload at least one before photo and one after photo before completing the job.');
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
            'quote_status' => ['nullable', Rule::in(ServiceJob::quoteStatuses())],
            'scheduled_at' => ['nullable', 'date'],
            'service_address' => ['nullable', 'string', 'max:180'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status_notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', Rule::exists('services', 'id')->where('tenant_id', $tenant->id)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);
    }

    private function syncItems(ServiceJob $job, Tenant $tenant, array $items): void
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $service = Service::where('tenant_id', $tenant->id)->findOrFail($item['service_id']);
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
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


