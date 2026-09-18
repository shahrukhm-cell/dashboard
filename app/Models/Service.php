<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    public const UNIT_JOB = 'job';
    public const UNIT_ITEM = 'item';
    public const UNIT_SQFT = 'sqft';
    public const UNIT_HOUR = 'hour';

    protected $fillable = [
        'tenant_id',
        'service_category_id',
        'name',
        'unit_type',
        'base_price',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function unitTypes(): array
    {
        return [self::UNIT_JOB, self::UNIT_ITEM, self::UNIT_SQFT, self::UNIT_HOUR];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}