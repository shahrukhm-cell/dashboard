<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTenantEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_email_must_be_unique_inside_current_tenant(): void
    {
        [$user, $tenant] = $this->tenantUser();

        Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Existing Customer',
            'email' => 'hacker@gmail.com',
            'phone' => '123456',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->from('/customers/create')
            ->post('/customers', $this->customerPayload(['email' => 'hacker@gmail.com']));

        $response->assertRedirect('/customers/create');
        $response->assertSessionHasErrors('email');
        $this->assertSame(1, Customer::where('tenant_id', $tenant->id)->where('email', 'hacker@gmail.com')->count());
    }

    public function test_same_customer_email_can_exist_in_different_tenants(): void
    {
        [$user, $tenant] = $this->tenantUser();
        $otherTenant = Tenant::create([
            'created_by' => $user->id,
            'name' => 'Other Tenant',
            'slug' => 'other-tenant',
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => ['theme_color' => '#8b5cf6'],
        ]);

        Customer::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Tenant Customer',
            'email' => 'hacker@gmail.com',
            'phone' => '123456',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->post('/customers', $this->customerPayload(['email' => 'hacker@gmail.com']));

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'tenant_id' => $tenant->id,
            'email' => 'hacker@gmail.com',
        ]);
    }

    public function test_customer_can_be_updated_without_changing_its_email(): void
    {
        [$user, $tenant] = $this->tenantUser();
        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Existing Customer',
            'email' => 'hacker@gmail.com',
            'phone' => '123456',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->patch("/customers/{$customer->id}", $this->customerPayload([
                'name' => 'Updated Customer',
                'email' => 'hacker@gmail.com',
            ]));

        $response->assertRedirect("/customers/{$customer->id}");
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'email' => 'hacker@gmail.com',
        ]);
    }

    private function tenantUser(): array
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $tenant = Tenant::create([
            'created_by' => $user->id,
            'name' => 'Tenant',
            'slug' => 'tenant',
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => ['theme_color' => '#8b5cf6'],
        ]);

        $tenant->users()->attach($user->id);

        return [$user, $tenant];
    }

    private function customerPayload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'New Customer',
            'email' => 'customer@example.com',
            'phone' => '123456',
            'company' => 'Example Co',
            'status' => Customer::STATUS_ACTIVE,
            'address_line' => '123 Main St',
            'city' => 'Karachi',
            'state' => 'Sindh',
            'postal_code' => '74000',
            'notes' => 'Important customer',
        ];
    }
}
