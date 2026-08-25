<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'city',
        'bio',
        'avatar_path',
        'role',
        'is_verified',
        'rating_avg',
        'sales_count',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function favoriteListings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'favorites')->withTimestamps();
    }

    /** Vendeurs que cet utilisateur suit. */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'seller_id')->withTimestamps();
    }

    /** Abonnés de ce vendeur. */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'seller_id', 'follower_id')->withTimestamps();
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'seller_id');
    }

    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'author_id');
    }

    /** Recalcule la note moyenne à partir des avis reçus. */
    public function recalcRating(): void
    {
        $avg = $this->reviewsReceived()->avg('rating');
        $this->update(['rating_avg' => $avg ? round($avg, 1) : 0]);
    }

    /** Conversations où l'utilisateur est acheteur ou vendeur. */
    public function conversations()
    {
        return Conversation::where('buyer_id', $this->id)->orWhere('seller_id', $this->id);
    }

    public function unreadMessagesCount(): int
    {
        return Message::whereHas('conversation', fn ($q) => $q->where('buyer_id', $this->id)->orWhere('seller_id', $this->id))
            ->where('sender_id', '!=', $this->id)
            ->whereNull('read_at')
            ->count();
    }

    /** Livres mis au panier (via cart_items). */
    public function cartListings()
    {
        return $this->belongsToMany(Listing::class, 'cart_items')->withTimestamps();
    }

    /** Demandes envoyées (acheteur) / reçues (vendeur). */
    public function ordersSent()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function ordersReceived()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /** Statuts d'un membre, du plus courant au plus élevé. */
    public const ROLES = [
        'user'  => 'Membre',
        'pro'   => 'Vendeur pro',
        'admin' => 'Administrateur',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Initiales pour l'avatar (ex. "Aïcha K." -> "AK"). */
    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $last  = mb_substr(end($parts) ?: '', 0, 1);
        return mb_strtoupper($first . $last);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'rating_avg' => 'decimal:1',
        ];
    }
}
