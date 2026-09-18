<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = ['tenant_id', 'service_job_id', 'customer_id', 'status', 'method', 'amount', 'paid_at', 'reference', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'date'];
    }

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_FAILED, self::STATUS_REFUNDED];
    }

    public static function methods(): array
    {
        return ['cash', 'card', 'bank_transfer', 'check', 'other'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function job(): BelongsTo { return $this->belongsTo(ServiceJob::class, 'service_job_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}