<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('roles.manage', $tenant), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'slug' => Role::slugFromName($data['name']).'-'.str()->random(4),
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('status', 'Role created.');
    }

    public function update(Request $request, Tenant $tenant, Role $role): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('roles.manage', $tenant), 403);
        abort_unless((int) $role->tenant_id === (int) $tenant->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('status', 'Role updated.');
    }

    public function destroy(Request $request, Tenant $tenant, Role $role): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('roles.manage', $tenant), 403);
        abort_unless((int) $role->tenant_id === (int) $tenant->id, 404);

        $isAssigned = $role->users()->wherePivot('tenant_id', $tenant->id)->exists();
        abort_if($isAssigned, 422, 'This role is assigned to users and cannot be deleted.');

        $role->permissions()->detach();
        $role->delete();

        return back()->with('status', 'Role deleted.');
    }
}