<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'image'];

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * Catégories réellement parcourables : au moins une annonce disponible
     * (les « recherche » sont des demandes, pas des livres à prendre).
     *
     * À n'utiliser que pour la navigation — le formulaire de publication et
     * l'administration doivent voir toutes les catégories, sinon il serait
     * impossible de publier le premier livre d'une catégorie.
     */
    public function scopeBrowsable($query)
    {
        return $query->whereHas('listings', fn ($q) => $q
            ->where('status', 'active')
            ->where('type', '!=', 'recherche'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
