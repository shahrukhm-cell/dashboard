<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceJob extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'team_id',
        'assigned_user_id',
        'job_number',
        'status',
        'scheduled_at',
        'service_address',
        'subtotal',
        'discount',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public static function statuses(): array
    {
        return [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_ASSIGNED, self::STATUS_APPROVED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED, self::STATUS_CANCELLED];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceJobItem::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function workEvents(): HasMany
    {
        return $this->hasMany(JobWorkEvent::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(ServiceJobStatusEvent::class);
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
}


