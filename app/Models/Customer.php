<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'company',
        'status',
        'address_line',
        'city',
        'state',
        'postal_code',
        'notes',
    ];

    public static function statuses(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_INACTIVE];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class);
    }

    public function addressSummary(): string
    {
        return collect([$this->address_line, $this->city, $this->state, $this->postal_code])
            ->filter()
            ->join(', ');
    }
}


