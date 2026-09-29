<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Listing extends Model
{
    /**
     * La liste des catégories affichées dans la navigation dépend des annonces
     * existantes : on invalide son cache dès qu'une annonce bouge, sinon une
     * catégorie qui reçoit son premier livre resterait masquée jusqu'à une heure.
     */
    protected static function booted(): void
    {
        $forget = fn () => \Illuminate\Support\Facades\Cache::forget('nav.categories');

        static::created($forget);
        static::deleted($forget);
        static::updated(function (self $listing) use ($forget) {
            if ($listing->wasChanged(['status', 'category_id', 'type'])) {
                $forget();
            }
        });

        // La colonne de recherche est dérivée : elle se recalcule à chaque écriture
        // pour ne jamais diverger du titre, de l'auteur ou de l'ISBN affichés.
        static::saving(function (self $listing) {
            $listing->search_text = static::searchTextFor(
                $listing->title, $listing->author, $listing->isbn
            );
        });
    }

    protected $fillable = [
        'user_id', 'category_id', 'title', 'author', 'isbn', 'language',
        'publisher', 'year', 'condition', 'description', 'price', 'old_price', 'type',
        'wants', 'budget', 'city', 'quantity', 'status', 'views', 'rating',
    ];

    protected $appends = ['condition_label', 'cover_url'];

    /** Colonne technique de recherche : inutile au front, on évite de l'envoyer. */
    protected $hidden = ['search_text'];

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

    /* ---------- Recherche texte ---------- */

    /**
     * Forme comparable d'un texte : sans accents, en minuscules, la ponctuation
     * réduite à des espaces. « L'Étranger » et « l etranger » se rejoignent ici.
     */
    public static function normalizeSearch(?string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii((string) $value))));
    }

    /**
     * Contenu de la colonne search_text : titre et auteur normalisés, suivis de
     * l'ISBN compacté — ce qui rend l'ISBN trouvable avec ou sans tirets.
     */
    public static function searchTextFor(?string $title, ?string $author, ?string $isbn): string
    {
        $compactIsbn = str_replace(' ', '', static::normalizeSearch($isbn));

        return trim(static::normalizeSearch("{$title} {$author}") . ' ' . $compactIsbn);
    }

    /**
     * Les deux formes sous lesquelles chercher un terme saisi : avec ses
     * séparateurs (« harry potter ») et compactée (« 9782070360024 »).
     *
     * @return list<string>
     */
    public static function searchNeedles(?string $term): array
    {
        $spaced = static::normalizeSearch($term);

        return array_values(array_unique(array_filter([$spaced, str_replace(' ', '', $spaced)])));
    }

    /* ---------- Scopes de filtrage ---------- */
    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when(static::searchNeedles($f['q'] ?? null), fn ($q, $needles) =>
                $q->where(function ($w) use ($needles) {
                    foreach ($needles as $needle) {
                        $w->orWhere('search_text', 'like', "%{$needle}%");
                    }
                }))
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
