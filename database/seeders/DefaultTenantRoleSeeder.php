<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultTenantRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissionsBySlug = Permission::pluck('id', 'slug');

        Tenant::query()->each(function (Tenant $tenant) use ($permissionsBySlug): void {
            foreach (Role::defaultRoles() as $slug => $roleTemplate) {
                $role = Role::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'slug' => $slug],
                    ['name' => $roleTemplate['name']]
                );

                $permissionIds = $roleTemplate['permissions'] === ['*']
                    ? $permissionsBySlug->values()
                    : collect($roleTemplate['permissions'])->map(fn ($permission) => $permissionsBySlug[$permission] ?? null)->filter()->values();

                $role->permissions()->sync($permissionIds);
            }
        });
    }
}