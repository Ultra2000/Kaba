<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'author', 'isbn', 'language',
        'publisher', 'year', 'condition', 'description', 'price', 'old_price', 'type',
        'wants', 'budget', 'city', 'quantity', 'status', 'views', 'rating',
    ];

    protected $appends = ['condition_label', 'cover_url'];

    protected $casts = [
        'price'     => 'integer',
        'old_price' => 'integer',
        'budget'   => 'integer',
        'year'     => 'integer',
        'views'    => 'integer',
        'quantity' => 'integer',
        'rating'   => 'decimal:1',
    ];

    /* ---------- Relations ---------- */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ListingPhoto::class)->orderBy('position');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /* ---------- Libellés lisibles ---------- */
    public const CONDITIONS = [
        'comme_neuf' => 'Comme neuf',
        'tres_bon'   => 'Très bon état',
        'bon'        => 'Bon état',
        'moyen'      => 'État moyen',
    ];

    public function getConditionLabelAttribute(): ?string
    {
        return $this->condition
            ? (self::CONDITIONS[$this->condition] ?? $this->condition)
            : null;
    }

    /** URL de la première photo uploadée, sinon null (le front bascule vers ISBN/placeholder). */
    public function getCoverUrlAttribute(): ?string
    {
        $photo = $this->relationLoaded('photos') ? $this->photos->first() : $this->photos()->first();
        return $photo ? asset('storage/' . $photo->path) : null;
    }

    /* ---------- Scopes de filtrage ---------- */
    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['q'] ?? null, fn ($q, $v) =>
                $q->where(fn ($w) => $w->where('title', 'like', "%{$v}%")
                    ->orWhere('author', 'like', "%{$v}%")
                    ->orWhere('isbn', 'like', "%{$v}%")))
            ->when(($f['type'] ?? 'all') !== 'all', fn ($q) => $q->where('type', $f['type']))
            ->when(($f['category'] ?? 'all') !== 'all', fn ($q) =>
                $q->whereHas('category', fn ($c) => $c->where('slug', $f['category'])))
            ->when(($f['city'] ?? 'all') !== 'all', fn ($q) => $q->where('city', $f['city']))
            ->when(($f['condition'] ?? 'all') !== 'all', fn ($q) => $q->where('condition', $f['condition']))
            ->when(($f['language'] ?? 'all') !== 'all', fn ($q) => $q->where('language', $f['language']))
            ->when($f['price_max'] ?? null, fn ($q, $v) =>
                $q->where(fn ($w) => $w->where('type', '!=', 'vente')->orWhere('price', '<=', $v)));
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price-asc'  => $query->orderBy('price'),
            'price-desc' => $query->orderByDesc('price'),
            default      => $query->orderByDesc('views'),
        };
    }
}
