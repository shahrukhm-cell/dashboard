<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'trial_ends_at',
        'ends_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'trial_ends_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_TRIALING,
            self::STATUS_ACTIVE,
            self::STATUS_PAST_DUE,
            self::STATUS_CANCELLED,
            self::STATUS_EXPIRED,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_TRIALING, self::STATUS_ACTIVE], true)
            && (! $this->ends_at || $this->ends_at->isFuture());
    }
}
