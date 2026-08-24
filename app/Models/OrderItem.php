<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'listing_id', 'price', 'status'];

    protected $casts = ['price' => 'integer'];

    public const STATUSES = [
        'pending'  => 'En attente',
        'accepted' => 'Disponible',
        'declined' => 'Indisponible',
    ];

    protected $appends = ['status_label'];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
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
