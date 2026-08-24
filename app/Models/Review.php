<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = ['author_id', 'seller_id', 'listing_id', 'order_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
