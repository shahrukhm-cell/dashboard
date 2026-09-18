<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = ['created_by', 'name', 'slug', 'status', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public static function statuses(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_SUSPENDED, self::STATUS_ARCHIVED];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function teamPayments(): HasMany
    {
        return $this->hasMany(TeamPayment::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function themeColor(): string
    {
        return $this->settings['theme_color'] ?? '#8b5cf6';
    }

    public function secondaryColor(): string
    {
        return $this->settings['secondary_color'] ?? '#22c55e';
    }

    public function brandName(): string
    {
        return $this->settings['brand_name'] ?? $this->name;
    }

    public function logoPath(): ?string
    {
        return $this->settings['logo_path'] ?? null;
    }

    public function logoUrl(): ?string
    {
        return $this->logoPath() ? asset('storage/'.$this->logoPath()) : null;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
