<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'user_id',
        'subscription_id',
        'name',
        'key_hash',
        'key_prefix',
        'scopes',
        'status',
        'last_used_at',
        'expires_at',
        'revoked_at',
        'revoke_reason',
        'metadata',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'key_hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the API key.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription for this API key.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the usage records for this API key.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(ApiKeyUsage::class);
    }


    /**
     * Check if API key is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if API key is revoked.
     */
    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    /**
     * Check if API key is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Revoke the API key.
     */
    public function revoke(?string $reason = null): void
    {
        $this->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoke_reason' => $reason,
        ]);
    }

    /**
     * Record usage of this API key.
     */
    public function recordUsage(array $data): void
    {
        $this->usageRecords()->create([
            'endpoint' => $data['endpoint'] ?? '',
            'method' => $data['method'] ?? 'GET',
            'status_code' => $data['status_code'] ?? 200,
            'response_time_ms' => $data['response_time_ms'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'metadata' => $data['metadata'] ?? [],
            'used_at' => now(),
        ]);

        $this->update(['last_used_at' => now()]);
    }

    /**
     * Verify if the provided key matches this API key.
     */
    public function verify(string $plainKey): bool
    {
        return hash_equals($this->key_hash, hash('sha256', $plainKey));
    }

    /**
     * Get masked key for display (shows only prefix).
     */
    public function getMaskedKey(): string
    {
        return $this->key_prefix . '...' . substr($this->key_hash, -4);
    }
}
