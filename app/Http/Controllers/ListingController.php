<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Notifications\KabaNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q'         => $request->input('q', ''),
            'type'      => $request->input('type', 'all'),
            'category'  => $request->input('category', 'all'),
            'city'      => $request->input('city', 'all'),
            'condition' => $request->input('condition', 'all'),
            'language'  => $request->input('language', 'all'),
            'price_max' => $request->input('price_max'),
            'sort'      => $request->input('sort', 'popular'),
        ];

        $listings = Listing::with(['user:id,name', 'category:id,name,slug', 'photos'])
            ->where('status', 'active')
            ->filter($filters)
            ->sort($filters['sort'])
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Listings/Explore', [
            'listings'   => $listings,
            'categories' => Category::orderBy('name')->get(['name', 'slug']),
            'cities'     => $this->cities(),
            'conditions' => Listing::CONDITIONS,
            'languages'  => $this->languages(),
            'filters'    => $filters,
        ]);
    }

    public function show(Listing $listing): Response
    {
        $listing->increment('views');
        $listing->load(['user:id,name,city,rating_avg,sales_count,is_verified', 'category:id,name,slug', 'photos']);

        $similar = Listing::with(['user:id,name', 'photos'])
            ->where('status', 'active')
            ->where('category_id', $listing->category_id)
            ->whereKeyNot($listing->id)
            ->take(5)
            ->get();

        return Inertia::render('Listings/Show', [
            'listing' => $listing,
            'similar' => $similar,
            'meta'    => $this->listingMeta($listing),
        ]);
    }

    /** Métadonnées Open Graph pour le partage d'une fiche livre (WhatsApp, réseaux). */
    private function listingMeta(Listing $listing): array
    {
        $priceLabel = match ($listing->type) {
            'don'       => 'Don gratuit',
            'echange'   => 'Échange',
            'recherche' => 'Recherché',
            default     => number_format((int) $listing->price, 0, ',', ' ') . ' FCFA',
        };

        $author = $listing->author ? ' de ' . $listing->author : '';
        $image  = $listing->cover_url
            ?: ($listing->isbn
                ? "https://covers.openlibrary.org/b/isbn/{$listing->isbn}-L.jpg"
                : url('/images/logo.png'));

        return [
            'title'       => "{$listing->title}{$author} · {$priceLabel} — KABA",
            'description' => trim($listing->description
                ? mb_strimwidth($listing->description, 0, 155, '…')
                : "{$listing->title} disponible sur KABA à {$listing->city}. {$priceLabel}."),
            'image'       => $image,
            'url'         => route('listings.show', $listing),
            'type'        => 'product',
        ];
    }

    public function create(): Response
    {
        return Inertia::render('Listings/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'cities'     => $this->cities(),
            'conditions' => Listing::CONDITIONS,
            'languages'  => $this->languages(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'        => 'required|in:vente,don,echange,recherche',
            'title'       => 'required|string|max:255',
            'author'      => 'nullable|string|max:255',
            'isbn'        => 'nullable|string|max:20',
            'category_id' => 'required|exists:categories,id',
            'condition'   => 'required|in:comme_neuf,tres_bon,bon,moyen',
            'language'    => 'required|string|max:50',
            'city'        => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'price'       => 'nullable|integer|min:0|required_if:type,vente',
            'wants'       => 'nullable|string|max:255',
            'budget'      => 'nullable|integer|min:0',
            'photos'      => 'nullable|array|max:10',
            'photos.*'    => 'image|max:5120', // 5 Mo
        ]);

        $listing = Listing::create([
            'user_id'     => Auth::id(),
            'category_id' => $data['category_id'],
            'title'       => $data['title'],
            'author'      => $data['author'] ?? null,
            'isbn'        => $data['isbn'] ?? null,
            'language'    => $data['language'],
            'condition'   => $data['condition'],
            'city'        => $data['city'],
            'description' => $data['description'] ?? null,
            'type'        => $data['type'],
            'price'       => $data['type'] === 'vente' ? ($data['price'] ?? 0) : 0,
            'wants'       => $data['type'] === 'echange' ? ($data['wants'] ?? null) : null,
            'budget'      => $data['type'] === 'recherche' ? ($data['budget'] ?? null) : null,
            'status'      => 'active',
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $file) {
                $path = $file->store('listings', 'public');
                $listing->photos()->create(['path' => $path, 'position' => $i]);
            }
        }

        $this->notifyMatchingSearches($listing);
        $this->notifyFollowers($listing);

        return redirect()->route('listings.show', $listing)->with('success', 'Annonce publiée avec succès !');
    }

    /** Prévient les abonnés du vendeur qu'il vient de publier un livre. */
    private function notifyFollowers(Listing $listing): void
    {
        if ($listing->type === 'recherche') {
            return;
        }

        $verb = match ($listing->type) {
            'don'     => 'propose en don',
            'echange' => 'propose en échange',
            default   => 'vient de publier',
        };

        foreach ($listing->user->followers as $follower) {
            $follower->notify(new KabaNotification([
                'kind'    => 'follow',
                'icon'    => 'fa-user-group',
                'color'   => 'brand',
                'message' => "{$listing->user->name} {$verb} « {$listing->title} ».",
                'url'     => "/livres/{$listing->id}",
            ]));
        }
    }

    /** Notifie les membres dont une annonce « recherche » correspond au livre publié. */
    private function notifyMatchingSearches(Listing $listing): void
    {
        if ($listing->type === 'recherche') {
            return;
        }

        $searches = Listing::with('user')
            ->where('type', 'recherche')
            ->where('status', 'active')
            ->where('user_id', '!=', $listing->user_id)
            ->get();

        foreach ($searches as $search) {
            if (stripos($listing->title, $search->title) !== false
                || stripos($search->title, $listing->title) !== false) {
                $search->user->notify(new KabaNotification([
                    'kind'    => 'recherche',
                    'icon'    => 'fa-magnifying-glass',
                    'color'   => 'green',
                    'message' => "Le livre recherché « {$search->title} » vient d'être publié !",
                    'url'     => "/livres/{$listing->id}",
                ]));
            }
        }
    }

    private function cities(): array
    {
        return ['Cotonou', 'Abomey-Calavi', 'Porto-Novo', 'Parakou', 'Bohicon', 'Natitingou'];
    }

    private function languages(): array
    {
        return ['Français', 'Anglais', 'Espagnol', 'Allemand'];
    }
}
