<?php

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ApiKeyService extends Pivot
{
    protected $table = 'api_key_services';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'api_key_id',
        'service_type',
    ];

    protected $casts = [
        'service_type' => ServiceType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the API key that owns this service.
     */
    public function apiKey(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
