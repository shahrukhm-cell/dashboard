<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ServiceJob;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request);
        $status = (string) $request->query('status', '');
        $canManageExpenses = $request->user()->hasPermission('expenses.manage', $tenant);
        $canApproveExpenses = $request->user()->hasPermission('expenses.approve', $tenant);

        $expenses = Expense::query()
            ->where('tenant_id', $tenant->id)
            ->with(['job.customer', 'category', 'submitter', 'approver'])
            ->when(! $canManageExpenses && ! $canApproveExpenses, fn ($query) => $query->where('submitted_by', $request->user()->id))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();

        $jobs = ServiceJob::query()
            ->where('tenant_id', $tenant->id)
            ->with('customer')
            ->when(! $canManageExpenses && ! $canApproveExpenses, fn ($query) => $query->where(function ($query) use ($request): void {
                $query->where('assigned_user_id', $request->user()->id)
                    ->orWhereHas('team.users', fn ($query) => $query->whereKey($request->user()->id));
            }))
            ->latest()
            ->get();

        return view('backend.expenses.index', [
            'tenant' => $tenant,
            'expenses' => $expenses,
            'categories' => ExpenseCategory::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get(),
            'jobs' => $jobs,
            'users' => $tenant->users()->orderBy('name')->get(),
            'statuses' => Expense::statuses(),
            'status' => $status,
            'approvedTotal' => Expense::where('tenant_id', $tenant->id)->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_REIMBURSED])->sum('amount'),
            'canManageExpenses' => $canManageExpenses,
            'canApproveExpenses' => $canApproveExpenses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request);
        $canManageExpenses = $request->user()->hasPermission('expenses.manage', $tenant);
        $data = $this->validated($request, $tenant, $canManageExpenses);

        $expense = new Expense($data + [
            'tenant_id' => $tenant->id,
            'submitted_by' => $canManageExpenses ? ($data['submitted_by'] ?? $request->user()->id) : $request->user()->id,
            'status' => $canManageExpenses ? $data['status'] : Expense::STATUS_PENDING,
        ]);

        if ($request->hasFile('receipt')) {
            $expense->receipt_path = $request->file('receipt')->store('expense-receipts', 'public');
        }

        $expense->save();

        return back()->with('status', 'Expense submitted.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $tenant = $this->currentTenant($request);
        $this->ensureTenantExpense($tenant, $expense);

        $canManageExpenses = $request->user()->hasPermission('expenses.manage', $tenant);
        $canApproveExpenses = $request->user()->hasPermission('expenses.approve', $tenant);
        abort_unless($canManageExpenses || $canApproveExpenses, 403);

        $data = $canManageExpenses
            ? $this->validated($request, $tenant, true)
            : $request->validate([
                'status' => ['required', Rule::in([Expense::STATUS_PENDING, Expense::STATUS_APPROVED, Expense::STATUS_REJECTED])],
                'approval_notes' => ['nullable', 'string', 'max:2000'],
            ]);

        $oldStatus = $expense->status;

        if ($canManageExpenses && $request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }

            $data['receipt_path'] = $request->file('receipt')->store('expense-receipts', 'public');
        }

        if (in_array($data['status'], [Expense::STATUS_APPROVED, Expense::STATUS_REJECTED], true) && $data['status'] !== $oldStatus) {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['approval_notes'] = $data['approval_notes'] ?? null;
        }

        if ($data['status'] === Expense::STATUS_PENDING) {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        $expense->update($data);

        return back()->with('status', 'Expense updated.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'expenses.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('expense_categories')->where('tenant_id', $tenant->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ExpenseCategory::create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Expense category created.');
    }

    private function currentTenant(Request $request, ?string $permission = null): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');

        if ($permission) {
            abort_unless($request->user()->hasPermission($permission, $tenant), 403);
        } else {
            abort_unless(
                $request->user()->hasPermission('expenses.manage', $tenant)
                    || $request->user()->hasPermission('expenses.submit', $tenant)
                    || $request->user()->hasPermission('expenses.approve', $tenant),
                403
            );
        }

        return $tenant;
    }

    private function ensureTenantExpense(Tenant $tenant, Expense $expense): void
    {
        abort_unless((int) $expense->tenant_id === (int) $tenant->id, 404);
    }

    private function validated(Request $request, Tenant $tenant, bool $canManageExpenses): array
    {
        $rules = [
            'service_job_id' => ['nullable', Rule::exists('service_jobs', 'id')->where('tenant_id', $tenant->id)],
            'expense_category_id' => ['nullable', Rule::exists('expense_categories', 'id')->where('tenant_id', $tenant->id)],
            'category_name' => ['nullable', 'string', 'max:120'],
            'vendor' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ];

        if ($canManageExpenses) {
            $rules['submitted_by'] = ['nullable', Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenant->id)];
            $rules['status'] = ['required', Rule::in(Expense::statuses())];
            $rules['approval_notes'] = ['nullable', 'string', 'max:2000'];
        }

        return $request->validate($rules);
    }
}

