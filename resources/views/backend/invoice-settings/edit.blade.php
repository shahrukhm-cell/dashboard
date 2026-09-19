<x-backend-layout title="Invoice settings">
    <section class="section-heading">
        <div>
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2>Invoice design</h2>
        </div>
        <div class="management-actions">
            <a class="button" href="{{ route('jobs.index') }}">Back to jobs</a>
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <section class="auth-errors customer-errors">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </section>
    @endif

    @php
        $accent = old('accent_color', $settings['accent_color'] ?? $tenant->themeColor());
        $heading = old('heading', $settings['heading'] ?? 'Customer invoice');
        $terms = old('terms', $settings['terms'] ?? 'Payment is due on receipt unless otherwise agreed.');
        $footer = old('footer', $settings['footer'] ?? 'Thank you for your business.');
        $showPayments = (bool) old('show_payments', $settings['show_payments'] ?? true);
    @endphp

    <section class="dashboard-grid management-grid">
        <article class="glass-card management-card service-form-card">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Template</span>
                    <h2>Design settings</h2>
                </div>
            </div>
            <form method="POST" action="{{ route('invoice-settings.update') }}" class="customer-form-grid">
                @csrf
                @method('PATCH')
                <label class="auth-field compact-color-field">
                    <span>Accent color</span>
                    <input class="auth-input" name="accent_color" type="color" value="{{ $accent }}">
                </label>
                <label class="auth-field">
                    <span>Invoice heading</span>
                    <input class="auth-input" name="heading" value="{{ $heading }}" placeholder="Customer invoice">
                </label>
                <label class="auth-field">
                    <span>Payment summary</span>
                    <select class="auth-input" name="show_payments">
                        <option value="1" @selected($showPayments)>Show paid and balance</option>
                        <option value="0" @selected(! $showPayments)>Hide payment summary</option>
                    </select>
                </label>
                <label class="auth-field customer-span-2">
                    <span>Terms</span>
                    <textarea class="auth-input" name="terms" rows="4" placeholder="Payment terms, warranty, or service notes">{{ $terms }}</textarea>
                </label>
                <label class="auth-field customer-span-2">
                    <span>Footer</span>
                    <textarea class="auth-input" name="footer" rows="3" placeholder="Thank you message">{{ $footer }}</textarea>
                </label>
                <div class="customer-span-2 management-actions">
                    <button class="button button-primary" type="submit">Save invoice design</button>
                </div>
            </form>
        </article>

        <article class="glass-card customer-detail-card activity-card">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Preview</span>
                    <h2>Invoice sample</h2>
                </div>
                <span class="tenant-status tenant-status-active">PDF</span>
            </div>
            <div class="job-items-summary" style="border-top: 4px solid {{ $accent }}; padding-top: 1rem;">
                <div class="job-summary-row total"><span>{{ $tenant->brandName() }}</span><strong>{{ $heading }}</strong></div>
                <div class="job-summary-row"><span>Exterior wash</span><strong>$120.00</strong></div>
                <div class="job-summary-row"><span>Interior detailing</span><strong>$80.00</strong></div>
                <div class="job-summary-row"><span>Subtotal</span><strong>$200.00</strong></div>
                <div class="job-summary-row total"><span>Total</span><strong>$200.00</strong></div>
                @if ($showPayments)
                    <div class="job-summary-row"><span>Paid</span><strong>$50.00</strong></div>
                    <div class="job-summary-row"><span>Balance due</span><strong>$150.00</strong></div>
                @endif
                <p class="customer-notes">{{ $terms ?: 'No terms added.' }}</p>
                <p class="customer-notes"><strong>{{ $footer ?: 'No footer added.' }}</strong></p>
            </div>
        </article>
    </section>
</x-backend-layout>
