<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->permissions() as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }
    }

    public static function permissions(): array
    {
        return [
            ['name' => 'View dashboard', 'slug' => 'dashboard.view'],
            ['name' => 'Update tenant settings', 'slug' => 'tenant.settings.update'],
            ['name' => 'Manage roles and permissions', 'slug' => 'roles.manage'],
            ['name' => 'Manage tenant users', 'slug' => 'users.manage'],
            ['name' => 'View customers', 'slug' => 'customers.view'],
            ['name' => 'Manage customers', 'slug' => 'customers.manage'],
            ['name' => 'View services', 'slug' => 'services.view'],
            ['name' => 'Manage services', 'slug' => 'services.manage'],
            ['name' => 'View jobs', 'slug' => 'jobs.view'],
            ['name' => 'Manage jobs', 'slug' => 'jobs.manage'],
            ['name' => 'Assign jobs', 'slug' => 'jobs.assign'],
            ['name' => 'Work assigned jobs', 'slug' => 'jobs.work'],
            ['name' => 'Update job status', 'slug' => 'jobs.status.update'],
            ['name' => 'Manage expenses', 'slug' => 'expenses.manage'],
            ['name' => 'Submit expenses', 'slug' => 'expenses.submit'],
            ['name' => 'Approve expenses', 'slug' => 'expenses.approve'],
            ['name' => 'Manage payments', 'slug' => 'payments.manage'],
            ['name' => 'View own team payments', 'slug' => 'team-payments.view-own'],
            ['name' => 'View reports', 'slug' => 'reports.view'],
        ];
    }
}

