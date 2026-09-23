<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\ServiceJob;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleted_customer_is_hidden_from_manager_but_visible_to_owner_and_logged(): void
    {
        $owner = User::factory()->create(['is_super_admin' => true]);
        $manager = User::factory()->create(['is_super_admin' => false]);
        $tenant = Tenant::create([
            'created_by' => $owner->id,
            'name' => 'Audit Tenant',
            'slug' => 'audit-tenant',
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => ['theme_color' => '#8b5cf6'],
        ]);
        $tenant->users()->attach([$owner->id, $manager->id]);
        $this->seed(PermissionSeeder::class);

        $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Manager', 'slug' => 'manager']);
        $role->permissions()->sync(Permission::whereIn('slug', ['customers.view', 'customers.manage'])->pluck('id'));
        $manager->roles()->attach($role->id, ['tenant_id' => $tenant->id]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Soft Deleted Customer',
            'email' => 'deleted@example.com',
            'phone' => '123456',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->delete("/customers/{$customer->id}")
            ->assertRedirect('/customers');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'customer.deleted', 'subject_id' => $customer->id]);

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->id])
            ->get('/customers?with_deleted=1')
            ->assertOk()
            ->assertDontSee('Soft Deleted Customer');

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->get('/customers?with_deleted=1')
            ->assertOk()
            ->assertSee('Soft Deleted Customer');

        $this->patch("/customers/{$customer->id}/restore")
            ->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'customer.restored', 'subject_id' => $customer->id]);
        $this->assertGreaterThanOrEqual(2, ActivityLog::where('subject_id', $customer->id)->count());
    }
    public function test_deleting_customer_soft_deletes_and_restores_their_jobs(): void
    {
        $owner = User::factory()->create(['is_super_admin' => true]);
        $tenant = Tenant::create([
            'created_by' => $owner->id,
            'name' => 'Customer Jobs Tenant',
            'slug' => 'customer-jobs-tenant',
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => ['theme_color' => '#8b5cf6'],
        ]);
        $tenant->users()->attach($owner->id);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Customer With Jobs',
            'email' => 'jobs@example.com',
            'phone' => '123456',
            'status' => Customer::STATUS_ACTIVE,
        ]);
        $job = ServiceJob::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'job_number' => 'JOB-CASCADE-001',
            'status' => ServiceJob::STATUS_DRAFT,
        ]);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->delete("/customers/{$customer->id}")
            ->assertRedirect('/customers');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertSoftDeleted('service_jobs', ['id' => $job->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'job.deleted', 'subject_id' => $job->id]);

        $this->patch("/customers/{$customer->id}/restore")
            ->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('service_jobs', ['id' => $job->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'job.restored', 'subject_id' => $job->id]);
    }
}


