<x-backend-layout title="Payments">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>{{ $canManagePayments ? 'Payments' : 'My pay' }}</h2>
        </div>
        <div class="management-actions">
            @if ($canManagePayments)
                <span class="tenant-status tenant-status-active">${{ number_format((float) $revenueTotal, 2) }} received</span>
                <span class="tenant-status tenant-status-suspended">${{ number_format((float) $teamPaidTotal, 2) }} team paid</span>
            @else
                <span class="tenant-status tenant-status-active">${{ number_format((float) $teamPaidTotal, 2) }} paid</span>
                <span class="tenant-status tenant-status-suspended">${{ number_format((float) $teamPendingTotal, 2) }} pending</span>
            @endif
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    @if ($canManagePayments)
        <section class="dashboard-grid management-grid">
            <article class="glass-card management-card service-form-card">
                <h2>Record customer payment</h2>
                <form class="payment-form" method="POST" action="{{ route('customer-payments.store') }}">
                    @csrf
                    @include('backend.payments._customer_form', ['payment' => new App\Models\CustomerPayment(['status' => 'paid', 'method' => 'cash', 'paid_at' => now()])])
                    <button class="button button-primary" type="submit">Save customer payment</button>
                </form>
            </article>

            <article class="glass-card management-card service-form-card activity-card">
                <h2>Record team payment</h2>
                <form class="payment-form" method="POST" action="{{ route('team-payments.store') }}">
                    @csrf
                    @include('backend.payments._team_form', ['payment' => new App\Models\TeamPayment(['status' => 'pending', 'method' => 'cash'])])
                    <button class="button button-primary" type="submit">Save team payment</button>
                </form>
            </article>
        </section>

        <section class="glass-card management-card">
            <div class="section-heading">
                <div><span class="eyebrow">Money in</span><h2>Customer payments</h2></div>
            </div>
            <div class="payment-list">
                @forelse ($customerPayments as $payment)
                    <form class="payment-row" method="POST" action="{{ route('customer-payments.update', $payment) }}">
                        @csrf
                        @method('PATCH')
                        @include('backend.payments._customer_form', ['payment' => $payment])
                        <button class="button" type="submit">Save</button>
                    </form>
                @empty
                    <div class="empty-state"><span class="eyebrow">No customer payments</span><h2>Record your first payment</h2></div>
                @endforelse
            </div>
            <div class="pagination-wrap">{{ $customerPayments->links() }}</div>
        </section>
    @endif

    <section class="glass-card management-card">
        <div class="section-heading">
            <div><span class="eyebrow">{{ $canManagePayments ? 'Money out' : 'Job pay history' }}</span><h2>{{ $canManagePayments ? 'Team payments' : 'My job payments' }}</h2></div>
        </div>
        <div class="payment-list">
            @forelse ($teamPayments as $payment)
                @if ($canManagePayments)
                    <form class="payment-row team-payment-row" method="POST" action="{{ route('team-payments.update', $payment) }}">
                        @csrf
                        @method('PATCH')
                        @include('backend.payments._team_form', ['payment' => $payment])
                        <button class="button" type="submit">Save</button>
                    </form>
                @else
                    <article class="payment-summary-row">
                        <div>
                            <strong>${{ number_format((float) $payment->amount, 2) }}</strong>
                            <small>{{ $payment->job?->job_number ?? 'No job' }} / {{ $payment->job?->customer?->name ?? 'No customer' }}</small>
                        </div>
                        <span class="tenant-status tenant-status-{{ $payment->status === 'paid' ? 'active' : ($payment->status === 'cancelled' ? 'archived' : 'suspended') }}">{{ Str::headline($payment->status) }}</span>
                        <span>{{ $payment->paid_at?->format('M j, Y') ?? 'Unpaid' }}</span>
                        <span>{{ Str::headline($payment->method) }}</span>
                    </article>
                @endif
            @empty
                <div class="empty-state"><span class="eyebrow">No team payments</span><h2>{{ $canManagePayments ? 'Record your first payout' : 'No pay records yet' }}</h2></div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $teamPayments->links() }}</div>
    </section>
</x-backend-layout>
