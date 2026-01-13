<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'service_account_id',
        'transaction_id',
        'qicard_payment_id',
        'amount',
        'currency',
        'type',
        'status',
        'description',
        'payment_method',
        'metadata',
        'qicard_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'qicard_response' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service account for this payment.
     */
    public function serviceAccount(): BelongsTo
    {
        return $this->belongsTo(ServiceAccount::class);
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if this is a top-up payment.
     */
    public function isTopup(): bool
    {
        return $this->type === 'topup';
    }

    /**
     * Check if this is a refund.
     */
    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }

    /**
     * Complete the payment and add balance if it's a top-up.
     */
    public function complete(): void
    {
        $this->status = 'completed';
        $this->paid_at = now();
        $this->save();

        // Add balance for top-ups
        if ($this->isTopup() && $this->serviceAccount) {
            $this->serviceAccount->addBalance((float) $this->amount);
        }
    }

    /**
     * Mark payment as failed.
     */
    public function fail(): void
    {
        $this->status = 'failed';
        $this->save();
    }

    /**
     * Create a top-up payment.
     */
    public static function createTopup(
        User $user,
        ServiceAccount $serviceAccount,
        float $amount,
        string $currency = 'IQD',
        ?string $description = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'user_id' => $user->id,
            'service_account_id' => $serviceAccount->id,
            'amount' => $amount,
            'currency' => $currency,
            'type' => 'topup',
            'status' => 'pending',
            'description' => $description ?? 'Account top-up',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create a refund payment.
     */
    public static function createRefund(
        User $user,
        ServiceAccount $serviceAccount,
        float $amount,
        ?string $description = null
    ): self {
        $payment = static::create([
            'user_id' => $user->id,
            'service_account_id' => $serviceAccount->id,
            'amount' => $amount,
            'currency' => $serviceAccount->currency,
            'type' => 'refund',
            'status' => 'completed',
            'description' => $description ?? 'Refund',
            'paid_at' => now(),
        ]);

        // Deduct from balance for refunds
        $serviceAccount->deductBalance($amount);

        return $payment;
    }
}
