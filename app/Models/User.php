<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        return $this->unreadMessagesQuery()->count();
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

    /* ---------- État d'interaction partagé à chaque page ---------- */

    /**
     * Identifiants qui donnent leur état aux boutons présents sur toutes les pages :
     * cœur rempli, « déjà au panier », « vendeur suivi ».
     *
     * Ces trois listes étaient relues à chaque chargement de page. Elles ne changent
     * que par les méthodes de mutation ci-dessous, qui invalident le cache : la
     * durée de vie n'est qu'un filet de sécurité, pas le mécanisme de fraîcheur.
     */
    public function interactionIds(): array
    {
        return Cache::remember($this->interactionCacheKey(), 300, fn () => [
            'favorites' => $this->favoriteListings()->pluck('listings.id')->all(),
            'cart'      => $this->cartListings()->pluck('listings.id')->all(),
            'following' => $this->following()->pluck('users.id')->all(),
        ]);
    }

    public function forgetInteractionIds(): void
    {
        Cache::forget($this->interactionCacheKey());
    }

    private function interactionCacheKey(): string
    {
        return "user.{$this->id}.interactions";
    }

    /**
     * Les mutations passent par ces méthodes plutôt que par les relations
     * directement : impossible de modifier une de ces listes sans vider le cache.
     */
    public function toggleFavorite(int $listingId): void
    {
        $this->favoriteListings()->toggle($listingId);
        $this->forgetInteractionIds();
    }

    public function addToCart(int $listingId): void
    {
        $this->cartListings()->syncWithoutDetaching([$listingId]);
        $this->forgetInteractionIds();
    }

    public function removeFromCart(mixed $listingIds): void
    {
        $this->cartListings()->detach($listingIds);
        $this->forgetInteractionIds();
    }

    public function toggleFollow(int $sellerId): void
    {
        $this->following()->toggle($sellerId);
        $this->forgetInteractionIds();
    }

    /**
     * Compteurs des badges de l'en-tête, obtenus en une seule requête.
     *
     * Volontairement jamais mis en cache : un badge en retard ferait manquer un
     * message ou une demande de disponibilité. Les clés de modération ne sont
     * présentes que pour un administrateur.
     */
    public function badgeCounts(): array
    {
        $counters = [
            'unread' => DB::table('notifications')
                ->selectRaw('count(*)')
                ->where('notifiable_type', $this->getMorphClass())
                ->where('notifiable_id', $this->id)
                ->whereNull('read_at'),
            'unreadMessages' => $this->unreadMessagesQuery()->selectRaw('count(*)'),
            'pendingOrders'  => DB::table('orders')
                ->selectRaw('count(*)')
                ->where('seller_id', $this->id)
                ->where('status', 'pending'),
        ];

        if ($this->isAdmin()) {
            $counters['reports'] = DB::table('reports')
                ->selectRaw('count(*)')->where('status', 'open');
            $counters['pendingListings'] = DB::table('listings')
                ->selectRaw('count(*)')->where('status', 'pending');
        }

        $query = DB::query();
        foreach ($counters as $alias => $sub) {
            $query->selectSub($sub, $alias);
        }

        return array_map('intval', (array) $query->first());
    }

    /** Messages non lus reçus par ce membre, tous fils de discussion confondus. */
    private function unreadMessagesQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where(fn ($q) => $q->where('conversations.buyer_id', $this->id)
                ->orWhere('conversations.seller_id', $this->id))
            ->where('messages.sender_id', '!=', $this->id)
            ->whereNull('messages.read_at');
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
