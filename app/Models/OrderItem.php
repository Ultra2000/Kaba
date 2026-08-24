<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'listing_id', 'price', 'offered_price', 'status'];

    protected $casts = ['price' => 'integer', 'offered_price' => 'integer'];

    public const STATUSES = [
        'pending'  => 'En attente',
        'accepted' => 'Disponible',
        'declined' => 'Indisponible',
    ];

    protected $appends = ['status_label', 'effective_price'];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Prix réellement convenu : l'offre de l'acheteur si présente, sinon le prix affiché. */
    public function getEffectivePriceAttribute(): int
    {
        return $this->offered_price ?? $this->price ?? 0;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
