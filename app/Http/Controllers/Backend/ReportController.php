<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Models\{Customer, CustomerPayment, Expense, ServiceJob, Team, TeamLeave, TeamPayment, Tenant, TimeEntry};

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
            'profitLoss' => $this->profitLoss($tenant),
            'topClients' => $this->topClients($tenant),
            'teamPerformance' => $this->teamPerformance($tenant),
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

    private function profitLoss(Tenant $tenant): array
    {
        $months = collect(range(5, 0))->map(fn ($monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));

        $startDate = $months->first()->copy()->startOfMonth();

        $revenue = CustomerPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', CustomerPayment::STATUS_PAID)
            ->where('paid_at', '>=', $startDate)
            ->get(['amount', 'paid_at'])
            ->groupBy(fn ($payment) => $payment->paid_at?->format('Y-m'))
            ->map(fn ($payments) => (float) $payments->sum('amount'));

        $expenses = Expense::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_REIMBURSED])
            ->where('expense_date', '>=', $startDate)
            ->get(['amount', 'expense_date'])
            ->groupBy(fn ($expense) => $expense->expense_date?->format('Y-m'))
            ->map(fn ($expenses) => (float) $expenses->sum('amount'));

        $teamPaid = TeamPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', TeamPayment::STATUS_PAID)
            ->where('paid_at', '>=', $startDate)
            ->get(['amount', 'paid_at'])
            ->groupBy(fn ($payment) => $payment->paid_at?->format('Y-m'))
            ->map(fn ($payments) => (float) $payments->sum('amount'));

        return $months->map(function ($month) use ($revenue, $expenses, $teamPaid): array {
            $key = $month->format('Y-m');
            $monthRevenue = (float) ($revenue[$key] ?? 0);
            $monthExpenses = (float) ($expenses[$key] ?? 0);
            $monthTeamPaid = (float) ($teamPaid[$key] ?? 0);

            return [
                'label' => $month->format('M Y'),
                'revenue' => $monthRevenue,
                'costs' => $monthExpenses + $monthTeamPaid,
                'profit' => $monthRevenue - $monthExpenses - $monthTeamPaid,
            ];
        })->all();
    }

    private function topClients(Tenant $tenant)
    {
        return Customer::query()
            ->where('tenant_id', $tenant->id)
            ->withCount('jobs')
            ->withSum('jobs as job_total_sum', 'total')
            ->withCount(['jobs as completed_jobs_count' => fn ($query) => $query->where('status', ServiceJob::STATUS_COMPLETED)])
            ->orderByDesc('job_total_sum')
            ->limit(8)
            ->get();
    }

    private function teamPerformance(Tenant $tenant)
    {
        return Team::query()
            ->where('tenant_id', $tenant->id)
            ->with(['users' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(function (Team $team) use ($tenant) {
                $jobIds = ServiceJob::where('tenant_id', $tenant->id)->where('team_id', $team->id)->pluck('id');

                $team->jobs_count = $jobIds->count();
                $team->completed_jobs_count = ServiceJob::whereIn('id', $jobIds)->where('status', ServiceJob::STATUS_COMPLETED)->count();
                $team->job_total_sum = (float) ServiceJob::whereIn('id', $jobIds)->sum('total');
                $team->approved_expense_sum = (float) Expense::whereIn('service_job_id', $jobIds)->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_REIMBURSED])->sum('amount');
                $team->team_paid_sum = (float) TeamPayment::whereIn('service_job_id', $jobIds)->where('status', TeamPayment::STATUS_PAID)->sum('amount');
                $team->team_pending_sum = (float) TeamPayment::whereIn('service_job_id', $jobIds)->where('status', TeamPayment::STATUS_PENDING)->sum('amount');
                $team->profit_after_team_costs = $team->job_total_sum - $team->approved_expense_sum - $team->team_paid_sum;
                $team->member_details = $team->users->map(function ($user) use ($tenant, $jobIds) {
                    $timeEntries = TimeEntry::where('tenant_id', $tenant->id)->where('user_id', $user->id)->whereIn('service_job_id', $jobIds)->get(['started_at', 'minutes']);
                    $paid = (float) TeamPayment::where('tenant_id', $tenant->id)->where('user_id', $user->id)->where('status', TeamPayment::STATUS_PAID)->sum('amount');
                    $pending = (float) TeamPayment::where('tenant_id', $tenant->id)->where('user_id', $user->id)->where('status', TeamPayment::STATUS_PENDING)->sum('amount');
                    $leaveDays = TeamLeave::where('tenant_id', $tenant->id)
                        ->where('user_id', $user->id)
                        ->where('status', TeamLeave::STATUS_APPROVED)
                        ->get()
                        ->sum(fn ($leave) => $leave->days());

                    return [
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'reference' => $user->reference,
                        'emergency_contact' => $user->emergency_contact,
                        'attendance_days' => $timeEntries->pluck('started_at')->filter()->map(fn ($date) => $date->format('Y-m-d'))->unique()->count(),
                        'hours' => round($timeEntries->sum('minutes') / 60, 2),
                        'leave_days' => $leaveDays,
                        'paid' => $paid,
                        'pending' => $pending,
                    ];
                });

                return $team;
            });
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }
}



