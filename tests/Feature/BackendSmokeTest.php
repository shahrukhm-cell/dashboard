<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_backend_pages_render_for_tenant_owner(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $tenant = Tenant::create([
            'created_by' => $user->id,
            'name' => 'Smoke Tenant',
            'slug' => 'smoke-tenant',
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => ['theme_color' => '#8b5cf6'],
        ]);

        $tenant->users()->attach($user->id);
        $this->seed(PermissionSeeder::class);

        $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Tenant Owner', 'slug' => 'tenant-owner']);
        $role->permissions()->sync(Permission::pluck('id'));
        $user->roles()->attach($role->id, ['tenant_id' => $tenant->id]);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id]);

        foreach ([
            '/dashboard',
            '/reports',
            '/customers',
            '/services',
            '/jobs',
            '/teams',
            '/time-entries',
            '/expenses',
            '/payments',
            '/admin/plans',
            '/admin/tenants',
        ] as $path) {
            $response = $this->get($path);
            $this->assertSame(200, $response->getStatusCode(), $path);
        }
    }
}