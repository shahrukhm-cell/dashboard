<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\ServiceJob;
use App\Models\TeamPayment;
use App\Models\Tenant;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'reports.view');
        $metrics = $this->metrics($tenant);

        return view('backend.reports.index', [
            'tenant' => $tenant,
            'metrics' => $metrics,
            'jobsByStatus' => ServiceJob::where('tenant_id', $tenant->id)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'recentJobs' => ServiceJob::where('tenant_id', $tenant->id)->with(['customer', 'team'])->latest()->limit(8)->get(),
        ]);
    }

    public static function metrics(Tenant $tenant): array
    {
        $revenue = (float) CustomerPayment::where('tenant_id', $tenant->id)->where('status', CustomerPayment::STATUS_PAID)->sum('amount');
        $approvedExpenses = (float) Expense::where('tenant_id', $tenant->id)->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_REIMBURSED])->sum('amount');
        $teamPaid = (float) TeamPayment::where('tenant_id', $tenant->id)->where('status', TeamPayment::STATUS_PAID)->sum('amount');
        $jobTotal = (float) ServiceJob::where('tenant_id', $tenant->id)->sum('total');
        $balanceDue = max(0, $jobTotal - $revenue);

        return [
            'revenue' => $revenue,
            'approved_expenses' => $approvedExpenses,
            'team_paid' => $teamPaid,
            'profit' => $revenue - $approvedExpenses - $teamPaid,
            'job_total' => $jobTotal,
            'balance_due' => $balanceDue,
            'jobs' => ServiceJob::where('tenant_id', $tenant->id)->count(),
            'active_jobs' => ServiceJob::where('tenant_id', $tenant->id)->whereNotIn('status', [ServiceJob::STATUS_COMPLETED, ServiceJob::STATUS_CANCELLED])->count(),
            'completed_jobs' => ServiceJob::where('tenant_id', $tenant->id)->where('status', ServiceJob::STATUS_COMPLETED)->count(),
            'customers' => Customer::where('tenant_id', $tenant->id)->count(),
            'hours' => round(TimeEntry::where('tenant_id', $tenant->id)->sum('minutes') / 60, 2),
        ];
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }
}