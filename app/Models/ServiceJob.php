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
    public const QUOTE_DRAFT = 'draft';
    public const QUOTE_SENT = 'sent';
    public const QUOTE_APPROVED = 'approved';
    public const QUOTE_DECLINED = 'declined';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'team_id',
        'assigned_user_id',
        'job_number',
        'status',
        'quote_status',
        'quote_sent_at',
        'quote_approved_at',
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
            'quote_sent_at' => 'datetime',
            'quote_approved_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public static function statuses(): array
    {
        return [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_ASSIGNED, self::STATUS_APPROVED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED, self::STATUS_CANCELLED];
    }

    public static function quoteStatuses(): array
    {
        return [self::QUOTE_DRAFT, self::QUOTE_SENT, self::QUOTE_APPROVED, self::QUOTE_DECLINED];
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

    public function photos(): HasMany
    {
        return $this->hasMany(JobPhoto::class);
    }

    public function beforePhotos(): HasMany
    {
        return $this->photos()->where('type', JobPhoto::TYPE_BEFORE);
    }

    public function afterPhotos(): HasMany
    {
        return $this->photos()->where('type', JobPhoto::TYPE_AFTER);
    }
}




