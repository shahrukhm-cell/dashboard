<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TeamLeave;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    public function index(Tenant $tenant): View
    {
        abort_unless(auth()->user()->hasPermission('users.manage', $tenant), 403);

        return view('backend.tenants.users', [
            'tenant' => $tenant,
            'users' => $tenant->users()->where('users.id', '!=', 1)->where('users.is_super_admin', false)->with([
                'roles' => fn ($query) => $query->wherePivot('tenant_id', $tenant->id),
                'leaves' => fn ($query) => $query->latest('starts_at'),
            ])->get(),
            'roles' => Role::where('tenant_id', $tenant->id)->with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get(),
            'leaveStatuses' => TeamLeave::statuses(),
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('users.manage', $tenant), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:180'],
            'reference' => ['nullable', 'string', 'max:180'],
            'emergency_contact' => ['nullable', 'string', 'max:120'],
        ]);

        $role = Role::where('tenant_id', $tenant->id)->findOrFail($data['role_id']);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'reference' => $data['reference'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        $tenant->users()->attach($user);
        $user->rolesFor($tenant)->attach($role->id, ['tenant_id' => $tenant->id]);

        return back()->with('status', 'User assigned to tenant.');
    }


    public function update(Request $request, Tenant $tenant, User $user): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('users.manage', $tenant), 403);
        abort_unless($tenant->users()->whereKey($user->id)->exists(), 404);
        abort_if((int) $user->id === 1 || $user->is_super_admin, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:180'],
            'reference' => ['nullable', 'string', 'max:180'],
            'emergency_contact' => ['nullable', 'string', 'max:120'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $role = Role::where('tenant_id', $tenant->id)->findOrFail($data['role_id']);

        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'reference' => $data['reference'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
        ]);

        $user->rolesFor($tenant)->sync([$role->id => ['tenant_id' => $tenant->id]]);

        return back()->with('status', 'Team member profile updated.');
    }
    public function storeLeave(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('users.manage', $tenant), 403);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'reason' => ['nullable', 'string', 'max:180'],
        ]);

        abort_unless($tenant->users()->whereKey($data['user_id'])->exists(), 404);

        TeamLeave::create([
            'tenant_id' => $tenant->id,
            'user_id' => $data['user_id'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
        ]);

        return back()->with('status', 'Leave recorded.');
    }
}



