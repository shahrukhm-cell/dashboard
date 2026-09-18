<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    public function index(Tenant $tenant): View
    {
        abort_unless(auth()->user()->hasPermission('users.manage', $tenant), 403);

        return view('backend.tenants.users', [
            'tenant' => $tenant,
            'users' => $tenant->users()->with(['roles' => fn ($query) => $query->wherePivot('tenant_id', $tenant->id)])->get(),
            'roles' => Role::where('tenant_id', $tenant->id)->with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('users.manage', $tenant), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $role = Role::where('tenant_id', $tenant->id)->findOrFail($data['role_id']);
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);

        $tenant->users()->attach($user);
        $user->rolesFor($tenant)->attach($role->id, ['tenant_id' => $tenant->id]);

        return back()->with('status', 'User assigned to tenant.');
    }
}