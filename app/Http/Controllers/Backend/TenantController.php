<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->is_super_admin, 403);

        return view('backend.tenants.index', [
            'tenants' => Tenant::withCount('users')->with(['creator', 'subscription.plan'])->latest()->get(),
            'statuses' => Tenant::statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->is_super_admin, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'brand_name' => ['nullable', 'string', 'max:120'],
            'theme_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => ['nullable', Rule::in(Tenant::statuses())],
        ]);

        $tenant = Tenant::create([
            'created_by' => auth()->id(),
            'name' => $data['name'],
            'slug' => str($data['name'])->slug().'-'.str()->random(5),
            'status' => $data['status'] ?? Tenant::STATUS_ACTIVE,
            'settings' => [
                'brand_name' => ($data['brand_name'] ?? null) ?: $data['name'],
                'theme_color' => $data['theme_color'],
                'secondary_color' => $data['secondary_color'] ?? '#22c55e',
            ],
        ]);

        $tenant->users()->attach(auth()->id());
        $tenantOwner = $this->seedDefaultRoles($tenant)['tenant-owner'];
        $tenant->creator->rolesFor($tenant)->attach($tenantOwner->id, ['tenant_id' => $tenant->id]);

        $defaultPlan = Plan::where('is_active', true)->orderBy('monthly_price')->first();
        if ($defaultPlan) {
            $tenant->subscription()->create([
                'plan_id' => $defaultPlan->id,
                'status' => Subscription::STATUS_ACTIVE,
                'starts_at' => now()->toDateString(),
            ]);
        }

        $request->session()->put('current_tenant_id', $tenant->id);

        return back()->with('status', 'Tenant created successfully.');
    }

    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate(['tenant_id' => ['required', 'integer', 'exists:tenants,id']]);
        $tenant = Tenant::findOrFail($data['tenant_id']);

        abort_unless($request->user()->canAccessTenant($tenant), 403);

        $request->session()->put('current_tenant_id', $tenant->id);

        return back()->with('status', 'Workspace switched to '.$tenant->name.'.');
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless(auth()->user()->is_super_admin || auth()->user()->hasPermission('tenant.settings.update', $tenant), 403);

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'brand_name' => ['nullable', 'string', 'max:120'],
            'theme_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ];

        if (auth()->user()->is_super_admin) {
            $rules['status'] = ['required', Rule::in(Tenant::statuses())];
        }

        $data = $request->validate($rules);
        $settings = array_merge($tenant->settings ?? [], [
            'brand_name' => ($data['brand_name'] ?? null) ?: $data['name'],
            'theme_color' => $data['theme_color'],
            'secondary_color' => $data['secondary_color'],
        ]);

        if ($request->hasFile('logo')) {
            if ($tenant->logoPath()) {
                Storage::disk('public')->delete($tenant->logoPath());
            }

            $settings['logo_path'] = $request->file('logo')->store('tenant-logos', 'public');
        }

        $tenant->update([
            'name' => $data['name'],
            'status' => $data['status'] ?? $tenant->status,
            'settings' => $settings,
        ]);

        return back()->with('status', 'Tenant settings updated.');
    }

    public function updateTheme(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('tenant.settings.update', $tenant), 403);

        $data = $request->validate([
            'theme_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $tenant->update([
            'settings' => array_merge($tenant->settings ?? [], [
                'theme_color' => $data['theme_color'],
                'secondary_color' => $data['secondary_color'] ?? $tenant->secondaryColor(),
            ]),
        ]);

        return back()->with('status', 'Tenant theme updated.');
    }

    /**
     * @return array<string, Role>
     */
    private function seedDefaultRoles(Tenant $tenant): array
    {
        $permissionsBySlug = Permission::pluck('id', 'slug');
        $roles = [];

        foreach (Role::defaultRoles() as $slug => $roleTemplate) {
            $role = Role::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $slug],
                ['name' => $roleTemplate['name']]
            );

            $permissionIds = $roleTemplate['permissions'] === ['*']
                ? $permissionsBySlug->values()
                : collect($roleTemplate['permissions'])->map(fn ($permission) => $permissionsBySlug[$permission] ?? null)->filter()->values();

            $role->permissions()->sync($permissionIds);
            $roles[$slug] = $role;
        }

        return $roles;
    }
}


