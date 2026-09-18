<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\ServiceJob;
use App\Models\Team;
use App\Models\TeamPayment;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request);
        $canManagePayments = $request->user()->hasPermission('payments.manage', $tenant);

        $customerPayments = $canManagePayments
            ? CustomerPayment::where('tenant_id', $tenant->id)->with(['job.customer', 'customer'])->latest('paid_at')->paginate(10, ['*'], 'customer_page')
            : collect();

        $teamPaymentsQuery = TeamPayment::where('tenant_id', $tenant->id)
            ->with(['job.customer', 'team', 'user'])
            ->when(! $canManagePayments, fn ($query) => $query->where('user_id', $request->user()->id));

        return view('backend.payments.index', [
            'tenant' => $tenant,
            'customerPayments' => $customerPayments,
            'teamPayments' => $teamPaymentsQuery->latest('paid_at')->paginate(10, ['*'], 'team_page'),
            'jobs' => ServiceJob::where('tenant_id', $tenant->id)->with('customer')->latest()->get(),
            'customers' => Customer::where('tenant_id', $tenant->id)->orderBy('name')->get(),
            'teams' => Team::where('tenant_id', $tenant->id)->orderBy('name')->get(),
            'users' => $tenant->users()->orderBy('name')->get(),
            'customerStatuses' => CustomerPayment::statuses(),
            'teamStatuses' => TeamPayment::statuses(),
            'customerMethods' => CustomerPayment::methods(),
            'teamMethods' => TeamPayment::methods(),
            'revenueTotal' => $canManagePayments ? CustomerPayment::where('tenant_id', $tenant->id)->where('status', CustomerPayment::STATUS_PAID)->sum('amount') : 0,
            'teamPaidTotal' => TeamPayment::where('tenant_id', $tenant->id)
                ->when(! $canManagePayments, fn ($query) => $query->where('user_id', $request->user()->id))
                ->where('status', TeamPayment::STATUS_PAID)
                ->sum('amount'),
            'teamPendingTotal' => TeamPayment::where('tenant_id', $tenant->id)
                ->when(! $canManagePayments, fn ($query) => $query->where('user_id', $request->user()->id))
                ->where('status', TeamPayment::STATUS_PENDING)
                ->sum('amount'),
            'canManagePayments' => $canManagePayments,
        ]);
    }

    public function storeCustomerPayment(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'payments.manage');
        $data = $this->validatedCustomerPayment($request, $tenant);

        CustomerPayment::create($data + ['tenant_id' => $tenant->id]);

        return back()->with('status', 'Customer payment saved.');
    }

    public function updateCustomerPayment(Request $request, CustomerPayment $customerPayment): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'payments.manage');
        abort_unless((int) $customerPayment->tenant_id === (int) $tenant->id, 404);

        $customerPayment->update($this->validatedCustomerPayment($request, $tenant));

        return back()->with('status', 'Customer payment updated.');
    }

    public function storeTeamPayment(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'payments.manage');
        $data = $this->validatedTeamPayment($request, $tenant);

        TeamPayment::create($data + ['tenant_id' => $tenant->id]);

        return back()->with('status', 'Team payment saved.');
    }

    public function updateTeamPayment(Request $request, TeamPayment $teamPayment): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'payments.manage');
        abort_unless((int) $teamPayment->tenant_id === (int) $tenant->id, 404);

        $teamPayment->update($this->validatedTeamPayment($request, $tenant));

        return back()->with('status', 'Team payment updated.');
    }

    private function currentTenant(Request $request, ?string $permission = null): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');

        if ($permission) {
            abort_unless($request->user()->hasPermission($permission, $tenant), 403);
        } else {
            abort_unless($request->user()->hasPermission('payments.manage', $tenant) || $request->user()->hasPermission('team-payments.view-own', $tenant), 403);
        }

        return $tenant;
    }

    private function validatedCustomerPayment(Request $request, Tenant $tenant): array
    {
        return $request->validate([
            'service_job_id' => ['nullable', Rule::exists('service_jobs', 'id')->where('tenant_id', $tenant->id)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('tenant_id', $tenant->id)],
            'status' => ['required', Rule::in(CustomerPayment::statuses())],
            'method' => ['required', Rule::in(CustomerPayment::methods())],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function validatedTeamPayment(Request $request, Tenant $tenant): array
    {
        return $request->validate([
            'service_job_id' => ['nullable', Rule::exists('service_jobs', 'id')->where('tenant_id', $tenant->id)],
            'team_id' => ['nullable', Rule::exists('teams', 'id')->where('tenant_id', $tenant->id)],
            'user_id' => ['nullable', Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenant->id)],
            'status' => ['required', Rule::in(TeamPayment::statuses())],
            'method' => ['required', Rule::in(TeamPayment::methods())],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'paid_at' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
