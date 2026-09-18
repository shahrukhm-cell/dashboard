<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_tenant_user')->withPivot('tenant_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function submittedExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'submitted_by');
    }

    public function assignedJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'assigned_user_id');
    }

    public function teamPayments(): HasMany
    {
        return $this->hasMany(TeamPayment::class);
    }

    public function canAccessTenant(Tenant $tenant): bool
    {
        return $this->tenants()->whereKey($tenant->id)->exists();
    }

    public function currentTenant(): ?Tenant
    {
        return app()->bound('currentTenant') ? app('currentTenant') : null;
    }

    public function availableTenants(): Collection
    {
        return $this->tenants()->orderBy('name')->get();
    }

    public function hasPermission(string $permission, Tenant $tenant): bool
    {
        return $this->is_super_admin || ($this->canAccessTenant($tenant) && $this->rolesFor($tenant)->get()->flatMap->permissions->contains('slug', $permission));
    }

    public function rolesFor(Tenant $tenant): BelongsToMany
    {
        return $this->roles()->wherePivot('tenant_id', $tenant->id)->with('permissions');
    }

    public function hasTenantRole(Tenant $tenant, string|array $roles): bool
    {
        $roles = (array) $roles;

        return $this->rolesFor($tenant)->whereIn('slug', $roles)->exists();
    }

    public function isFieldStaff(Tenant $tenant): bool
    {
        return $this->hasTenantRole($tenant, ['team-lead', 'team-member']);
    }
}
