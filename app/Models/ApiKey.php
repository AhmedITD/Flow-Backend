<?php

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiKey extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'service_account_id',
        'name',
        'key_hash',
        'key_prefix',
        'status',
        'last_used_at',
        'expires_at',
        'revoked_at',
        'metadata',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'key_hash',
    ];

    /**
     * Get the user that owns the API key.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service account for this API key.
     */
    public function serviceAccount(): BelongsTo
    {
        return $this->belongsTo(ServiceAccount::class);
    }

    /**
     * Get the usage records for this API key.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(ApiKeyUsage::class);
    }

    /**
     * Get the services associated with this API key.
     */
    public function apiKeyServices(): HasMany
    {
        return $this->hasMany(ApiKeyService::class);
    }

    /**
     * Get the service types for this API key.
     */
    public function getServiceTypesAttribute(): \Illuminate\Support\Collection
    {
        return $this->apiKeyServices()->get()->map(fn($service) => ServiceType::from($service->service_type));
    }

    /**
     * Check if API key has access to a specific service.
     */
    public function hasService(ServiceType $serviceType): bool
    {
        return $this->apiKeyServices()->where('service_type', $serviceType->value)->exists();
    }

    /**
     * Attach services to this API key.
     */
    public function attachServices(array $serviceTypes): void
    {
        $serviceValues = array_map(fn(ServiceType $type) => $type->value, $serviceTypes);
        
        // Delete existing services
        $this->apiKeyServices()->delete();
        
        // Create new service associations
        foreach ($serviceValues as $serviceType) {
            ApiKeyService::create([
                'api_key_id' => $this->id,
                'service_type' => $serviceType,
            ]);
        }
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
    public function revoke(): void
    {
        $this->update([
            'status' => 'revoked',
            'revoked_at' => now(),
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

    /**
     * Check if the associated service account has sufficient balance.
     */
    public function hasSufficientBalance(float $estimatedCost): bool
    {
        if (!$this->serviceAccount) {
            return false;
        }

        return $this->serviceAccount->hasSufficientBalance($estimatedCost);
    }
}
