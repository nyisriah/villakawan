<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',
        'proof',
        'payment_method',
        'doku_transaction_id',
        'status',
        'webhook_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
        'webhook_data' => 'array',
        'created_at' => 'datetime',
    ];

    const UPDATED_AT = null;

    // ===== RELATIONSHIPS =====

    /**
     * The booking this payment is for
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // ===== HELPERS =====

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark payment as successful
     */
    public function markAsSuccess(): void
    {
        $this->update(['status' => 'success']);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }
}
