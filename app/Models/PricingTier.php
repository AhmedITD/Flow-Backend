<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PricingTier extends Model
{
    use HasUuids;

    protected $fillable = [
        'service_type',
        'min_tokens',
        'discount_percent',
        'price_per_1k_tokens',
    ];

    protected $casts = [
        'min_tokens' => 'integer',
        'discount_percent' => 'decimal:2',
        'price_per_1k_tokens' => 'decimal:4',
    ];

    /**
     * Get the applicable tier for a given cumulative token usage.
     */
    public static function getTierForUsage(string $serviceType, int $cumulativeTokens): ?self
    {
        return static::where('service_type', $serviceType)
            ->where('min_tokens', '<=', $cumulativeTokens)
            ->orderBy('min_tokens', 'desc')
            ->first();
    }

    /**
     * Get all tiers for a service type ordered by min_tokens.
     */
    public static function getTiersForService(string $serviceType): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('service_type', $serviceType)
            ->orderBy('min_tokens', 'asc')
            ->get();
    }

    /**
     * Calculate the effective price per 1k tokens for this tier.
     * If price_per_1k_tokens is set, use it. Otherwise, apply discount to base price.
     */
    public function getEffectivePrice(float $basePrice): float
    {
        if ($this->price_per_1k_tokens !== null) {
            return (float) $this->price_per_1k_tokens;
        }

        // Apply discount
        return $basePrice * (1 - ((float) $this->discount_percent / 100));
    }
}
