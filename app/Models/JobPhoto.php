<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JobPhoto extends Model
{
    public const TYPE_BEFORE = 'before';
    public const TYPE_AFTER = 'after';

    protected $fillable = [
        'tenant_id',
        'service_job_id',
        'uploaded_by',
        'type',
        'path',
        'caption',
    ];

    public static function types(): array
    {
        return [self::TYPE_BEFORE, self::TYPE_AFTER];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
