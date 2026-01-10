<?php

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SubscriptionService extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'subscription_id',
        'service_type',
        'allocated_tokens',
        'tokens_used',
        'reset_at',
    ];

    protected $casts = [
        'service_type' => ServiceType::class,
        'allocated_tokens' => 'integer',
        'tokens_used' => 'integer',
        'reset_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Get the subscription that owns this service allocation.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if token limit is exceeded.
     */
    public function isLimitExceeded(): bool
    {
        // If no limit is set (allocated_tokens = 0), allow unlimited usage
        if ($this->allocated_tokens === 0) {
            return false;
        }
        
        return $this->tokens_used >= $this->allocated_tokens;
    }

    /**
     * Get remaining tokens.
     */
    public function getRemainingTokens(): int
    {
        // If no limit is set, return a large number to indicate unlimited
        if ($this->allocated_tokens === 0) {
            return PHP_INT_MAX;
        }
        
        return max(0, $this->allocated_tokens - $this->tokens_used);
    }

    /**
     * Increment tokens used.
     */
    public function incrementTokensUsed(int $tokens): void
    {
        if ($tokens > 0) {
            $this->increment('tokens_used', $tokens);
        }
    }
}
