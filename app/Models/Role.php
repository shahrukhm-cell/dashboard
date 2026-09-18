<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = ['tenant_id', 'name', 'slug'];

    public static function defaultRoles(): array
    {
        return [
            'tenant-owner' => [
                'name' => 'Tenant Owner',
                'permissions' => ['*'],
            ],
            'business-admin' => [
                'name' => 'Business Admin',
                'permissions' => [
                    'dashboard.view', 'tenant.settings.update', 'roles.manage', 'users.manage',
                    'customers.view', 'customers.manage', 'services.view', 'services.manage',
                    'jobs.view', 'jobs.manage', 'jobs.assign', 'jobs.work', 'jobs.status.update',
                    'expenses.manage', 'expenses.submit', 'expenses.approve',
                    'payments.manage', 'team-payments.view-own', 'reports.view',
                ],
            ],
            'manager' => [
                'name' => 'Manager',
                'permissions' => [
                    'dashboard.view', 'tenant.settings.update', 'users.manage',
                    'customers.view', 'customers.manage', 'services.view', 'services.manage',
                    'jobs.view', 'jobs.manage', 'jobs.assign', 'jobs.work', 'jobs.status.update',
                    'expenses.manage', 'expenses.submit', 'expenses.approve',
                    'payments.manage', 'team-payments.view-own', 'reports.view',
                ],
            ],
            'team-lead' => [
                'name' => 'Team Lead',
                'permissions' => [
                    'dashboard.view', 'customers.view', 'jobs.view', 'jobs.work', 'jobs.status.update',
                    'expenses.submit', 'team-payments.view-own',
                ],
            ],
            'team-member' => [
                'name' => 'Team Member',
                'permissions' => [
                    'dashboard.view', 'jobs.view', 'team-payments.view-own',
                ],
            ],
        ];
    }
    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_tenant_user')->withPivot('tenant_id');
    }
}

