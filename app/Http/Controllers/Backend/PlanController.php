<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->is_super_admin, 403);

        return view('backend.plans.index', [
            'plans' => Plan::withCount('subscriptions')->latest()->get(),
            'activePlans' => Plan::where('is_active', true)->orderBy('monthly_price')->get(),
            'tenants' => Tenant::with(['subscription.plan'])->withCount('users')->orderBy('name')->get(),
            'statuses' => Subscription::statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->is_super_admin, 403);

        $data = $this->validatedPlan($request);

        Plan::create($this->normalizePlanData($data));

        return back()->with('status', 'Plan created successfully.');
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        abort_unless(auth()->user()->is_super_admin, 403);

        $data = $this->validatedPlan($request, $plan);

        $plan->update($this->normalizePlanData($data));

        return back()->with('status', 'Plan updated successfully.');
    }

    public function subscribe(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless(auth()->user()->is_super_admin, 403);

        $data = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => ['required', Rule::in(Subscription::statuses())],
            'starts_at' => ['required', 'date'],
            'trial_ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant->subscription()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $data
        );

        return back()->with('status', $tenant->name.' subscription updated.');
    }

    private function validatedPlan(Request $request, ?Plan $plan = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:140',
                Rule::unique('plans', 'slug')->ignore($plan),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'max_users' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'max_jobs' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'features' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function normalizePlanData(array $data): array
    {
        $features = collect(preg_split('/\r\n|\r|\n/', $data['features'] ?? ''))
            ->map(fn (string $feature): string => trim($feature))
            ->filter()
            ->values()
            ->all();

        return [
            'name' => $data['name'],
            'slug' => filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']).'-'.Str::random(5),
            'description' => $data['description'] ?? null,
            'monthly_price' => $data['monthly_price'],
            'max_users' => $data['max_users'] ?? null,
            'max_jobs' => $data['max_jobs'] ?? null,
            'features' => $features,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}