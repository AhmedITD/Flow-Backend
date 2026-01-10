<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PhoneVerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'code',
        'expires_at',
        'verified_at',
        'is_used',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Check if the code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the code is valid (not expired and not used)
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Mark code as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Generate a new 6-digit verification code
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new verification code for registration
     */
    public static function createForPhone(
        string $phoneNumber,
        ?string $ipAddress = null,
        int $expiresInMinutes = 10
    ): self {
        // Invalidate any existing unused codes for this phone
        static::where('phone_number', $phoneNumber)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true]);

        return static::create([
            'phone_number' => $phoneNumber,
            'code' => static::generateCode(),
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Find a valid code for verification
     */
    public static function findValidCode(string $phoneNumber, string $code): ?self
    {
        return static::where('phone_number', $phoneNumber)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Clean up expired codes (can be called by a scheduled task)
     */
    public static function cleanupExpired(): int
    {
        return static::where('expires_at', '<', now()->subDays(7))
            ->orWhere(function ($query) {
                $query->where('is_used', true)
                    ->where('verified_at', '<', now()->subDays(30));
            })
            ->delete();
    }
}
