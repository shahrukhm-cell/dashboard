<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\ServiceJob;
use App\Models\TeamPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if (! $tenant) {
            return view('backend.dashboard', [
                'tenant' => null,
                'stats' => [],
                'activities' => [],
                'recentJobs' => collect(),
                'reportsLinkVisible' => false,
            ]);
        }

        abort_unless($request->user()->hasPermission('dashboard.view', $tenant), 403);

        if ($request->user()->isFieldStaff($tenant)) {
            return $this->fieldStaffDashboard($request, $tenant);
        }

        $metrics = ReportController::metrics($tenant);

        return view('backend.dashboard', [
            'tenant' => $tenant,
            'stats' => [
                ['label' => 'Revenue received', 'value' => '$'.number_format($metrics['revenue'], 2), 'change' => '$'.number_format($metrics['balance_due'], 2).' due', 'tone' => 'positive', 'icon' => 'dollar'],
                ['label' => 'Active jobs', 'value' => number_format($metrics['active_jobs']), 'change' => number_format($metrics['completed_jobs']).' completed', 'tone' => 'positive', 'icon' => 'jobs'],
                ['label' => 'Profit', 'value' => '$'.number_format($metrics['profit'], 2), 'change' => '$'.number_format($metrics['approved_expenses'] + $metrics['team_paid'], 2).' costs', 'tone' => $metrics['profit'] >= 0 ? 'positive' : 'negative', 'icon' => 'chart'],
            ],
            'activities' => $this->activities($tenant),
            'recentJobs' => ServiceJob::where('tenant_id', $tenant->id)->with(['customer', 'team'])->latest()->limit(5)->get(),
            'reportsLinkVisible' => $request->user()->hasPermission('reports.view', $tenant),
        ]);
    }

    private function fieldStaffDashboard(Request $request, $tenant): View
    {
        $user = $request->user();
        $assignedJobsQuery = ServiceJob::query()
            ->where('tenant_id', $tenant->id)
            ->where(function ($query) use ($user): void {
                $query->where('assigned_user_id', $user->id)
                    ->orWhereHas('team.users', fn ($query) => $query->whereKey($user->id));
            });

        $assignedJobIds = (clone $assignedJobsQuery)->pluck('id');
        $recentJobs = (clone $assignedJobsQuery)->with(['customer', 'team'])->latest()->limit(5)->get();
        $paidToUser = TeamPayment::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('status', TeamPayment::STATUS_PAID)
            ->sum('amount');
        $pendingPay = TeamPayment::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('status', TeamPayment::STATUS_PENDING)
            ->sum('amount');

        return view('backend.dashboard', [
            'tenant' => $tenant,
            'stats' => [
                ['label' => 'Assigned jobs', 'value' => number_format($assignedJobIds->count()), 'change' => number_format((clone $assignedJobsQuery)->where('status', ServiceJob::STATUS_COMPLETED)->count()).' completed', 'tone' => 'positive', 'icon' => 'jobs'],
                ['label' => 'Open jobs', 'value' => number_format((clone $assignedJobsQuery)->whereIn('status', [ServiceJob::STATUS_SCHEDULED, ServiceJob::STATUS_ASSIGNED, ServiceJob::STATUS_IN_PROGRESS])->count()), 'change' => 'Ready for field work', 'tone' => 'positive', 'icon' => 'jobs'],
                ['label' => 'Your pay', 'value' => '$'.number_format((float) $paidToUser, 2), 'change' => '$'.number_format((float) $pendingPay, 2).' pending', 'tone' => 'positive', 'icon' => 'dollar'],
            ],
            'activities' => $this->fieldActivities($tenant, $user->id, $assignedJobIds),
            'recentJobs' => $recentJobs,
            'reportsLinkVisible' => false,
        ]);
    }

    private function activities($tenant): array
    {
        $latestPayment = CustomerPayment::where('tenant_id', $tenant->id)->latest('paid_at')->first();
        $latestExpense = Expense::where('tenant_id', $tenant->id)->latest('expense_date')->first();
        $latestJob = ServiceJob::where('tenant_id', $tenant->id)->latest()->first();

        return collect([
            $latestJob ? ['title' => 'Latest job', 'description' => $latestJob->job_number.' is '.str($latestJob->status)->headline(), 'time' => $latestJob->created_at->diffForHumans(), 'icon' => 'job'] : null,
            $latestPayment ? ['title' => 'Payment received', 'description' => '$'.number_format((float) $latestPayment->amount, 2).' via '.str($latestPayment->method)->headline(), 'time' => $latestPayment->paid_at->diffForHumans(), 'icon' => 'check'] : null,
            $latestExpense ? ['title' => 'Expense logged', 'description' => '$'.number_format((float) $latestExpense->amount, 2).' '.$latestExpense->status, 'time' => $latestExpense->expense_date->diffForHumans(), 'icon' => 'expense'] : null,
        ])->filter()->values()->all();
    }

    private function fieldActivities($tenant, int $userId, $assignedJobIds): array
    {
        $latestExpense = Expense::where('tenant_id', $tenant->id)->where('submitted_by', $userId)->latest('expense_date')->first();
        $latestJob = ServiceJob::where('tenant_id', $tenant->id)->whereIn('id', $assignedJobIds)->latest()->first();
        $latestPay = TeamPayment::where('tenant_id', $tenant->id)->where('user_id', $userId)->latest('paid_at')->first();

        return collect([
            $latestJob ? ['title' => 'Latest assigned job', 'description' => $latestJob->job_number.' is '.str($latestJob->status)->headline(), 'time' => $latestJob->created_at->diffForHumans(), 'icon' => 'job'] : null,
            $latestExpense ? ['title' => 'Your expense', 'description' => '$'.number_format((float) $latestExpense->amount, 2).' '.$latestExpense->status, 'time' => $latestExpense->expense_date->diffForHumans(), 'icon' => 'expense'] : null,
            $latestPay ? ['title' => 'Your payment', 'description' => '$'.number_format((float) $latestPay->amount, 2).' '.str($latestPay->status)->headline(), 'time' => $latestPay->paid_at?->diffForHumans() ?? $latestPay->created_at->diffForHumans(), 'icon' => 'check'] : null,
        ])->filter()->values()->all();
    }
}

