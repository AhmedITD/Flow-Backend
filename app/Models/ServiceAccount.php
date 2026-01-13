<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'status',
        'balance',
        'currency',
        'credit_limit',
        'metadata',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns this service account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all usage records for this service account.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * Get all payments for this service account.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all invoices for this service account.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all API keys for this service account.
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Check if the account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the account has sufficient balance for a given amount.
     */
    public function hasSufficientBalance(float $amount): bool
    {
        $availableCredit = $this->balance + $this->credit_limit;
        return $availableCredit >= $amount;
    }

    /**
     * Get the available credit (balance + credit limit).
     */
    public function getAvailableCreditAttribute(): float
    {
        return (float) $this->balance + (float) $this->credit_limit;
    }

    /**
     * Deduct an amount from the balance.
     */
    public function deductBalance(float $amount): bool
    {
        if (!$this->hasSufficientBalance($amount)) {
            return false;
        }

        $this->balance = (float) $this->balance - $amount;
        $this->save();

        return true;
    }

    /**
     * Add an amount to the balance (top-up).
     */
    public function addBalance(float $amount): void
    {
        $this->balance = (float) $this->balance + $amount;
        $this->save();
    }

    /**
     * Suspend the account.
     */
    public function suspend(): void
    {
        $this->status = 'suspended';
        $this->save();
    }

    /**
     * Activate the account.
     */
    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Close the account.
     */
    public function close(): void
    {
        $this->status = 'closed';
        $this->save();
    }

    /**
     * Get total tokens used in a given period.
     */
    public function getTokensUsed(?string $serviceType = null, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): int
    {
        $query = $this->usageRecords();

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        if ($from) {
            $query->where('recorded_at', '>=', $from);
        }

        if ($to) {
            $query->where('recorded_at', '<=', $to);
        }

        return (int) $query->sum('tokens_used');
    }

    /**
     * Get total cost in a given period.
     */
    public function getTotalCost(?string $serviceType = null, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): float
    {
        $query = $this->usageRecords();

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        if ($from) {
            $query->where('recorded_at', '>=', $from);
        }

        if ($to) {
            $query->where('recorded_at', '<=', $to);
        }

        return (float) $query->sum('cost');
    }
}
