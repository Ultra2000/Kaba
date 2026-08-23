<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = ['listing_id', 'buyer_id', 'seller_id', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /** L'autre participant (celui qui n'est pas $me). */
    public function otherUser(User $me): ?User
    {
        return $this->buyer_id === $me->id ? $this->seller : $this->buyer;
    }

    public function isParticipant(User $me): bool
    {
        return in_array($me->id, [$this->buyer_id, $this->seller_id], true);
    }
}
