<x-backend-layout title="Expenses">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>{{ $canManageExpenses ? 'Expenses' : 'My expenses' }}</h2>
        </div>
        @if ($canManageExpenses)
            <span class="tenant-status tenant-status-active">${{ number_format((float) $approvedTotal, 2) }} approved</span>
        @endif
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    @if ($canManageExpenses)
        <section class="dashboard-grid management-grid finance-summary-grid">
            <article class="glass-card stat-card"><span class="stat-label">Job expenses</span><strong class="stat-value">${{ number_format((float) $jobExpenseTotal, 2) }}</strong><span class="stat-change negative">Assigned to jobs</span></article>
            <article class="glass-card stat-card"><span class="stat-label">Company expenses</span><strong class="stat-value">${{ number_format((float) $companyExpenseTotal, 2) }}</strong><span class="stat-change negative">Rent, shop, overhead</span></article>
        </section>
    @endif

    <section class="dashboard-grid management-grid">
        @if ($canManageExpenses)
            <article class="glass-card management-card service-form-card">
                <h2>Create category</h2>
                <form class="service-stack-form" method="POST" action="{{ route('expense-categories.store') }}">
                    @csrf
                    <input class="auth-input" name="name" placeholder="Category name" required>
                    <label class="permission-check"><input type="checkbox" name="is_active" value="1" checked><span>Active</span></label>
                    <button class="button button-primary" type="submit">Create category</button>
                </form>
            </article>
        @endif

        <article class="glass-card management-card service-form-card {{ $canManageExpenses ? 'activity-card' : 'activity-card' }}">
            <h2>{{ $canManageExpenses ? 'Log expense' : 'Submit expense' }}</h2>
            <form class="expense-form" method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                @csrf
                @include('backend.expenses._form', ['expense' => new App\Models\Expense(['status' => 'pending', 'expense_date' => now()])])
                <button class="button button-primary" type="submit">{{ $canManageExpenses ? 'Log expense' : 'Submit expense' }}</button>
            </form>
        </article>
    </section>

    @if ($errors->any())
        <section class="auth-errors customer-errors">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </section>
    @endif

    <section class="glass-card management-card">
        <form class="customer-toolbar" method="GET" action="{{ route('expenses.index') }}">
            <select class="auth-input" name="type">
                <option value="">All expense types</option>
                <option value="job" @selected($type === 'job')>Job expenses</option>
                <option value="company" @selected($type === 'company')>Company expenses</option>
            </select>
            <select class="auth-input" name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $expenseStatus)
                    <option value="{{ $expenseStatus }}" @selected($status === $expenseStatus)>{{ Str::headline($expenseStatus) }}</option>
                @endforeach
            </select>
            <button class="button" type="submit">Filter</button>
            @if ($status !== '' || $type !== '')
                <a class="button" href="{{ route('expenses.index') }}">Clear</a>
            @endif
        </form>
    </section>

    <section class="glass-card management-card">
        <div class="expense-list">
            @forelse ($expenses as $expense)
                @if ($canManageExpenses)
                    <form class="expense-row" method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        @include('backend.expenses._form', ['expense' => $expense])
                        <button class="button" type="submit">Save</button>
                    </form>
                    <div class="expense-meta-row">
                        <span>{{ $expense->job?->job_number ?? 'Company expense' }} / {{ $expense->submitter?->name ?? 'No submitter' }}</span>
                        <span>Approval: {{ $expense->approver?->name ?? 'Not reviewed' }}{{ $expense->approved_at ? ' on '.$expense->approved_at->format('M j, Y') : '' }}</span>
                    </div>
                @elseif ($canApproveExpenses)
                    <form class="expense-row" method="POST" action="{{ route('expenses.update', $expense) }}">
                        @csrf
                        @method('PATCH')
                        <div>
                            <strong>${{ number_format((float) $expense->amount, 2) }}</strong>
                            <small>{{ $expense->job?->job_number ?? 'Company expense' }} / {{ $expense->submitter?->name ?? 'No submitter' }} / {{ $expense->vendor ?: 'No vendor' }}</small>
                        </div>
                        <select class="auth-input" name="status" required>
                            <option value="pending" @selected($expense->status === 'pending')>Pending</option>
                            <option value="approved" @selected($expense->status === 'approved')>Approved</option>
                            <option value="rejected" @selected($expense->status === 'rejected')>Rejected</option>
                        </select>
                        <input class="auth-input" name="approval_notes" value="{{ $expense->approval_notes }}" placeholder="Approval notes">
                        @if ($expense->receiptUrl())
                            <a class="button" href="{{ $expense->receiptUrl() }}" target="_blank" rel="noopener">Receipt</a>
                        @endif
                        <button class="button" type="submit">Review</button>
                    </form>
                    <div class="expense-meta-row">
                        <span>{{ $expense->categoryLabel() }} / {{ $expense->expense_date->format('M j, Y') }}</span>
                        <span>Approval: {{ $expense->approver?->name ?? 'Not reviewed' }}{{ $expense->approved_at ? ' on '.$expense->approved_at->format('M j, Y') : '' }}</span>
                    </div>
                @else
                    <article class="expense-summary-row">
                        <div>
                            <strong>${{ number_format((float) $expense->amount, 2) }}</strong>
                            <small>{{ $expense->job?->job_number ?? 'Company expense' }} / {{ $expense->category?->name ?? 'No category' }} / {{ $expense->vendor ?: 'No vendor' }}</small>
                        </div>
                        <span class="tenant-status tenant-status-{{ in_array($expense->status, ['approved', 'reimbursed']) ? 'active' : ($expense->status === 'rejected' ? 'archived' : 'suspended') }}">{{ Str::headline($expense->status) }}</span>
                        @if ($expense->receiptUrl())
                            <a class="button" href="{{ $expense->receiptUrl() }}" target="_blank" rel="noopener">Receipt</a>
                        @else
                            <span>No receipt</span>
                        @endif
                    </article>
                @endif
            @empty
                <div class="empty-state">
                    <span class="eyebrow">No expenses</span>
                    <h2>{{ $canManageExpenses ? 'Log your first cost' : 'Submit your first expense' }}</h2>
                    <p>Job expenses attach to jobs. Company expenses like shop rent stay unassigned and report as overhead.</p>
                </div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $expenses->links() }}</div>
    </section>
</x-backend-layout>


