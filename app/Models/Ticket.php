<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'ticket_id',
        'tenant_id',
        'channel',
        'status',
        'priority',
        'category',
        'subject',
        'summary',
        'created_by_type',
        'created_by_id',
        'assigned_to',
        'call_session_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['created_by'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->ticket_id)) {
                $model->ticket_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get created_by as array.
     */
    public function getCreatedByAttribute(): array
    {
        return [
            'type' => $this->created_by_type ?? 'system',
            'id' => $this->created_by_id ?? 'system',
        ];
    }

    /**
     * Get the creator of the ticket.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the assigned agent.
     */
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Check if ticket is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if ticket is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if ticket is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
