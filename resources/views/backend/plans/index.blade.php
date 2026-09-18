<x-backend-layout title="Plans">
    <section class="section-heading">
        <div>
            <span class="eyebrow">Platform billing</span>
            <h2>Plans & subscriptions</h2>
        </div>
    </section>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="glass-card management-card">
        <h2>Create plan</h2>
        <form class="management-form" method="POST" action="{{ route('admin.plans.store') }}">
            @csrf
            <input class="auth-input" name="name" placeholder="Plan name" value="{{ old('name') }}" required>
            <input class="auth-input" name="slug" placeholder="Optional slug" value="{{ old('slug') }}">
            <input class="auth-input" name="monthly_price" type="number" min="0" step="0.01" placeholder="Monthly price" value="{{ old('monthly_price', 0) }}" required>
            <input class="auth-input" name="max_users" type="number" min="1" placeholder="Max users" value="{{ old('max_users') }}">
            <input class="auth-input" name="max_jobs" type="number" min="1" placeholder="Max jobs" value="{{ old('max_jobs') }}">
            <textarea class="auth-input" name="description" rows="2" placeholder="Short description">{{ old('description') }}</textarea>
            <textarea class="auth-input" name="features" rows="3" placeholder="Features, one per line">{{ old('features') }}</textarea>
            <label class="checkbox-line">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                Active plan
            </label>
            <button class="button button-primary" type="submit">Create plan</button>
        </form>
        @foreach (['name', 'slug', 'monthly_price', 'max_users', 'max_jobs', 'description', 'features'] as $field)
            @error($field) <p class="form-error">{{ $message }}</p> @enderror
        @endforeach
    </section>

    <section class="dashboard-grid management-grid">
        @forelse ($plans as $plan)
            <article class="glass-card tenant-card">
                <div class="tenant-card-header">
                    <span class="tenant-status tenant-status-{{ $plan->is_active ? 'active' : 'archived' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="eyebrow">{{ $plan->subscriptions_count }} tenants</span>
                </div>

                <span class="eyebrow">{{ $plan->slug }}</span>
                <h2>{{ $plan->name }}</h2>
                <p>${{ number_format((float) $plan->monthly_price, 2) }} / month</p>
                <p>{{ $plan->max_users ? $plan->max_users.' users' : 'Unlimited users' }} - {{ $plan->max_jobs ? $plan->max_jobs.' jobs' : 'Unlimited jobs' }}</p>

                @if ($plan->features)
                    <ul class="compact-list">
                        @foreach ($plan->features as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif

                <form class="tenant-edit-form" method="POST" action="{{ route('admin.plans.update', $plan) }}">
                    @csrf
                    @method('PATCH')
                    <input class="auth-input" name="name" value="{{ old('name', $plan->name) }}" required> 
                    <input class="auth-input" name="slug" value="{{ old('slug', $plan->slug) }}" required>
                    <input class="auth-input" name="monthly_price" type="number" min="0" step="0.01" value="{{ old('monthly_price', $plan->monthly_price) }}" required>
                    <input class="auth-input" name="max_users" type="number" min="1" placeholder="Max users" value="{{ old('max_users', $plan->max_users) }}">
                    <input class="auth-input" name="max_jobs" type="number" min="1" placeholder="Max jobs" value="{{ old('max_jobs', $plan->max_jobs) }}">
                    <textarea class="auth-input" name="description" rows="2">{{ old('description', $plan->description) }}</textarea>
                    <textarea class="auth-input" name="features" rows="3">{{ old('features', implode(PHP_EOL, $plan->features ?? [])) }}</textarea>
                    <label class="checkbox-line">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active))>
                        Active plan
                    </label>
                    <button class="button button-primary" type="submit">Save plan</button>
                </form>
            </article>
        @empty
            <article class="glass-card tenant-card">
                <span class="eyebrow">No plans</span>
                <h2>Create your first plan</h2>
                <p>Plans define limits, pricing, and tenant subscription assignments.</p>
            </article>
        @endforelse
    </section>

    <section class="glass-card management-card">
        <h2>Tenant subscriptions</h2>
        <div class="stacked-list">
            @forelse ($tenants as $tenant)
                @php($subscription = $tenant->subscription)
                <form class="management-form" method="POST" action="{{ route('admin.tenants.subscription', $tenant) }}">
                    @csrf
                    <div>
                        <span class="eyebrow">{{ $tenant->users_count }} users</span>
                        <strong>{{ $tenant->name }}</strong>
                        <p>{{ $subscription?->plan?->name ?? 'No plan assigned' }} - {{ Str::title(str_replace('_', ' ', $subscription?->status ?? 'unassigned')) }}</p>
                    </div>
                    <select class="auth-input" name="plan_id" required @disabled($activePlans->isEmpty())>
                        <option value="">Choose plan</option>
                        @foreach ($activePlans as $plan)
                            <option value="{{ $plan->id }}" @selected((int) old('plan_id', $subscription?->plan_id) === $plan->id)>{{ $plan->name }} - ${{ number_format((float) $plan->monthly_price, 2) }}/mo</option>
                        @endforeach
                    </select>
                    <select class="auth-input" name="status" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $subscription?->status ?? 'active') === $status)>{{ Str::title(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <input class="auth-input" name="starts_at" type="date" value="{{ old('starts_at', optional($subscription?->starts_at)->format('Y-m-d') ?? now()->toDateString()) }}" required>
                    <input class="auth-input" name="trial_ends_at" type="date" value="{{ old('trial_ends_at', optional($subscription?->trial_ends_at)->format('Y-m-d')) }}" title="Trial ends">
                    <input class="auth-input" name="ends_at" type="date" value="{{ old('ends_at', optional($subscription?->ends_at)->format('Y-m-d')) }}" title="Ends at">
                    <input class="auth-input" name="notes" value="{{ old('notes', $subscription?->notes) }}" placeholder="Internal note">
                    <button class="button button-primary" type="submit" @disabled($activePlans->isEmpty())>Update</button>
                </form>
            @empty
                <p>No tenants yet.</p>
            @endforelse
        </div>
    </section>
</x-backend-layout>