<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pricing';

    protected $fillable = [
        'service_type',
        'price_per_1k_tokens',
        'min_tokens',
        'currency',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'price_per_1k_tokens' => 'decimal:4',
        'min_tokens' => 'integer',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
    ];

    /**
     * Get the current active pricing for a service type.
     */
    public static function getCurrentPricing(string $serviceType): ?self
    {
        return static::where('service_type', $serviceType)
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>', now());
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    /**
     * Calculate cost for a given number of tokens.
     */
    public function calculateCost(int $tokens): float
    {
        $billableTokens = max($tokens, $this->min_tokens);
        return ($billableTokens / 1000) * (float) $this->price_per_1k_tokens;
    }

    /**
     * Check if this pricing is currently active.
     */
    public function isActive(): bool
    {
        $now = now();
        return $this->effective_from <= $now 
            && ($this->effective_until === null || $this->effective_until > $now);
    }

    /**
     * Get all active pricing records.
     */
    public static function getAllActivePricing(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>', now());
            })
            ->get();
    }
}
