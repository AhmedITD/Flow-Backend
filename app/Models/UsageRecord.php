<?php

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageRecord extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_account_id',
        'service_type',
        'tokens_used',
        'cost',
        'action_type',
        'resource_id',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'service_type' => ServiceType::class,
        'tokens_used' => 'integer',
        'cost' => 'decimal:4',
        'metadata' => 'array',
        'recorded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->recorded_at)) {
                $model->recorded_at = now();
            }
        });
    }

    /**
     * Get the service account that owns this usage record.
     */
    public function serviceAccount(): BelongsTo
    {
        return $this->belongsTo(ServiceAccount::class);
    }

    /**
     * Create a usage record and deduct from service account balance.
     */
    public static function recordUsage(
        ServiceAccount $serviceAccount,
        ServiceType $serviceType,
        int $tokensUsed,
        ?string $actionType = null,
        ?string $resourceId = null,
        ?array $metadata = null
    ): self {
        // Get current pricing
        $pricing = Pricing::getCurrentPricing($serviceType->value);
        
        if (!$pricing) {
            throw new \RuntimeException("No active pricing found for service: {$serviceType->value}");
        }

        // Check for volume discount tier
        $cumulativeTokens = $serviceAccount->getTokensUsed($serviceType->value);
        $tier = PricingTier::getTierForUsage($serviceType->value, $cumulativeTokens);
        
        // Calculate cost
        $basePrice = (float) $pricing->price_per_1k_tokens;
        $effectivePrice = $tier ? $tier->getEffectivePrice($basePrice) : $basePrice;
        $cost = (max($tokensUsed, $pricing->min_tokens) / 1000) * $effectivePrice;

        // Create usage record
        $record = static::create([
            'service_account_id' => $serviceAccount->id,
            'service_type' => $serviceType->value,
            'tokens_used' => $tokensUsed,
            'cost' => $cost,
            'action_type' => $actionType,
            'resource_id' => $resourceId,
            'metadata' => $metadata,
            'recorded_at' => now(),
        ]);

        // Deduct from balance
        $serviceAccount->deductBalance($cost);

        return $record;
    }
}
