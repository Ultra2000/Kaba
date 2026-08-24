<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['buyer_id', 'seller_id', 'status', 'message'];

    public const STATUSES = [
        'pending'   => 'En attente',
        'accepted'  => 'Acceptée',
        'declined'  => 'Refusée',
        'completed' => 'Remise effectuée',
        'cancelled' => 'Annulée',
    ];

    protected $appends = ['status_label'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Total des livres non refusés, au prix convenu (offre acceptée sinon prix affiché). */
    public function total(): int
    {
        return (int) $this->items->where('status', '!=', 'declined')
            ->sum(fn ($item) => $item->offered_price ?? $item->price ?? 0);
    }

    /**
     * Déduit le statut de la demande depuis les réponses par livre.
     * Tous refusés → declined ; plus d'attente et ≥1 accepté → accepted ; sinon pending.
     * Ne touche pas aux états finaux (completed / cancelled).
     */
    public function syncStatusFromItems(): void
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return;
        }

        $items = $this->items()->get();

        if ($items->where('status', 'pending')->isNotEmpty()) {
            $this->update(['status' => 'pending']);
        } elseif ($items->where('status', 'accepted')->isNotEmpty()) {
            $this->update(['status' => 'accepted']);
        } else {
            $this->update(['status' => 'declined']);
        }
    }
}
