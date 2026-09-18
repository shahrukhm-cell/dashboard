<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_plans_page(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'monthly_price' => 29,
            'features' => ['Customers', 'Jobs'],
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/admin/plans')
            ->assertOk()
            ->assertSeeText('Platform billing')
            ->assertSee('Starter');
    }

    public function test_non_super_admin_cannot_manage_plans(): void
    {
        $user = User::factory()->create(['is_super_admin' => false]);

        $this->actingAs($user)->get('/admin/plans')->assertForbidden();
        $this->actingAs($user)->post('/admin/plans', [
            'name' => 'Blocked',
            'monthly_price' => 10,
        ])->assertForbidden();
    }

    public function test_super_admin_can_create_a_plan_with_normalized_slug_and_features(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);

        $response = $this->actingAs($user)->post('/admin/plans', [
            'name' => 'Pro Plan',
            'slug' => 'Pro Plan',
            'description' => 'For serious teams.',
            'monthly_price' => 99.50,
            'max_users' => 25,
            'max_jobs' => 1000,
            'features' => "Customers\n\nPayments\nReports",
            'is_active' => '1',
        ]);

        $response->assertRedirect();

        $plan = Plan::where('slug', 'pro-plan')->firstOrFail();
        $this->assertSame('Pro Plan', $plan->name);
        $this->assertSame('99.50', $plan->monthly_price);
        $this->assertSame(25, $plan->max_users);
        $this->assertSame(1000, $plan->max_jobs);
        $this->assertTrue($plan->is_active);
        $this->assertSame(['Customers', 'Payments', 'Reports'], $plan->features);
    }

    public function test_super_admin_can_update_a_plan(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $plan = Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'monthly_price' => 29,
            'features' => ['Customers'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch("/admin/plans/{$plan->id}", [
            'name' => 'Starter Plus',
            'slug' => 'starter-plus',
            'description' => 'Updated plan.',
            'monthly_price' => 49,
            'max_users' => 10,
            'max_jobs' => 200,
            'features' => "Customers\nJobs",
        ]);

        $response->assertRedirect();

        $plan->refresh();
        $this->assertSame('Starter Plus', $plan->name);
        $this->assertSame('starter-plus', $plan->slug);
        $this->assertSame('49.00', $plan->monthly_price);
        $this->assertFalse($plan->is_active);
        $this->assertSame(['Customers', 'Jobs'], $plan->features);
    }

    public function test_super_admin_can_assign_and_update_tenant_subscription(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $tenant = $this->makeTenant($user, 'Acme Operations');
        $starter = Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'monthly_price' => 29,
            'is_active' => true,
        ]);
        $growth = Plan::create([
            'name' => 'Growth',
            'slug' => 'growth',
            'monthly_price' => 79,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post("/admin/tenants/{$tenant->id}/subscription", [
            'plan_id' => $starter->id,
            'status' => Subscription::STATUS_TRIALING,
            'starts_at' => '2026-09-17',
            'trial_ends_at' => '2026-09-24',
            'ends_at' => null,
            'notes' => 'Trial setup',
        ])->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_id' => $starter->id,
            'status' => Subscription::STATUS_TRIALING,
            'notes' => 'Trial setup',
        ]);

        $this->actingAs($user)->post("/admin/tenants/{$tenant->id}/subscription", [
            'plan_id' => $growth->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => '2026-09-17',
            'trial_ends_at' => null,
            'ends_at' => null,
            'notes' => 'Upgraded',
        ])->assertRedirect();

        $this->assertSame(1, Subscription::where('tenant_id', $tenant->id)->count());
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_id' => $growth->id,
            'status' => Subscription::STATUS_ACTIVE,
            'notes' => 'Upgraded',
        ]);
    }

    public function test_new_tenant_receives_lowest_active_plan_by_default(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $this->seed(PermissionSeeder::class);

        $expensivePlan = Plan::create([
            'name' => 'Scale',
            'slug' => 'scale',
            'monthly_price' => 149,
            'is_active' => true,
        ]);
        $defaultPlan = Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'monthly_price' => 29,
            'is_active' => true,
        ]);
        Plan::create([
            'name' => 'Archived Free',
            'slug' => 'archived-free',
            'monthly_price' => 0,
            'is_active' => false,
        ]);

        $this->actingAs($user)->post('/admin/tenants', [
            'name' => 'New Tenant',
            'status' => Tenant::STATUS_ACTIVE,
            'theme_color' => '#8b5cf6',
        ])->assertRedirect();

        $tenant = Tenant::where('name', 'New Tenant')->firstOrFail();
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_id' => $defaultPlan->id,
            'status' => Subscription::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseMissing('subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_id' => $expensivePlan->id,
        ]);
    }

    public function test_subscription_requires_valid_status_and_plan(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $tenant = $this->makeTenant($user, 'Validation Tenant');

        $response = $this->actingAs($user)->from('/admin/plans')->post("/admin/tenants/{$tenant->id}/subscription", [
            'plan_id' => 999,
            'status' => 'unknown',
            'starts_at' => '2026-09-17',
        ]);

        $response->assertRedirect('/admin/plans');
        $response->assertSessionHasErrors(['plan_id', 'status']);
        $this->assertDatabaseCount('subscriptions', 0);
    }

    private function makeTenant(User $user, string $name): Tenant
    {
        $tenant = Tenant::create([
            'created_by' => $user->id,
            'name' => $name,
            'slug' => str($name)->slug().'-test',
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => ['theme_color' => '#8b5cf6'],
        ]);

        $tenant->users()->attach($user->id);

        return $tenant;
    }
}