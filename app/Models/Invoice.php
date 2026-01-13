<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'service_account_id',
        'period_start',
        'period_end',
        'total_tokens',
        'total_amount',
        'currency',
        'status',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_tokens' => 'integer',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the service account that owns this invoice.
     */
    public function serviceAccount(): BelongsTo
    {
        return $this->belongsTo(ServiceAccount::class);
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' 
            && $this->due_date 
            && $this->due_date < now();
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(): void
    {
        $this->status = 'sent';
        $this->save();
    }

    /**
     * Mark invoice as overdue.
     */
    public function markAsOverdue(): void
    {
        $this->status = 'overdue';
        $this->save();
    }

    /**
     * Generate invoice for a service account for a given period.
     */
    public static function generateForPeriod(
        ServiceAccount $serviceAccount,
        \DateTimeInterface $periodStart,
        \DateTimeInterface $periodEnd,
        ?int $dueDays = 30
    ): self {
        $tokens = $serviceAccount->getTokensUsed(null, $periodStart, $periodEnd);
        $cost = $serviceAccount->getTotalCost(null, $periodStart, $periodEnd);

        return static::create([
            'service_account_id' => $serviceAccount->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_tokens' => $tokens,
            'total_amount' => $cost,
            'currency' => $serviceAccount->currency,
            'status' => 'draft',
            'due_date' => $dueDays ? now()->addDays($dueDays) : null,
        ]);
    }
}
