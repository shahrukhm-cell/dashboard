<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_user_can_render_allowed_pages_without_permission_lookup_error(): void
    {
        $owner = User::factory()->create(['is_super_admin' => true]);
        $manager = User::factory()->create(['is_super_admin' => false]);
        $tenant = Tenant::create([
            'created_by' => $owner->id,
            'name' => 'Manager Tenant',
            'slug' => 'manager-tenant',
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => ['theme_color' => '#8b5cf6'],
        ]);

        $tenant->users()->attach([$owner->id, $manager->id]);
        $this->seed(PermissionSeeder::class);

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Manager',
            'slug' => 'manager',
        ]);
        $role->permissions()->sync(Permission::whereIn('slug', [
            'dashboard.view',
            'reports.view',
            'customers.view',
            'services.view',
            'jobs.view',
            'jobs.assign',
            'expenses.manage',
            'payments.manage',
        ])->pluck('id'));
        $manager->roles()->attach($role->id, ['tenant_id' => $tenant->id]);

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->id]);

        foreach (['/dashboard', '/reports', '/customers', '/services', '/jobs', '/teams', '/expenses', '/payments'] as $path) {
            $response = $this->get($path);
            $this->assertSame(200, $response->getStatusCode(), $path);
        }

        $this->get('/admin/plans')->assertForbidden();
        $this->get('/admin/tenants')->assertForbidden();
    }
}
