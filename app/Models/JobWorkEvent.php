<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobWorkEvent extends Model
{
    use HasFactory;

    public const TYPE_START = 'start';
    public const TYPE_BREAK_START = 'break_start';
    public const TYPE_BREAK_END = 'break_end';
    public const TYPE_END = 'end';

    protected $fillable = [
        'tenant_id',
        'service_job_id',
        'user_id',
        'event_type',
        'occurred_at',
        'notes',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public static function types(): array
    {
        return [self::TYPE_START, self::TYPE_BREAK_START, self::TYPE_BREAK_END, self::TYPE_END];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
