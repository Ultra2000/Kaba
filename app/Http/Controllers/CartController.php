<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request): Response
    {
        $items = $request->user()->cartListings()
            ->with(['user:id,name,city,rating_avg,is_verified', 'photos', 'category:id,slug'])
            ->get();

        // Un livre vendu ou retiré entre-temps n'est plus demandable :
        // on le signale au lieu de le proposer silencieusement.
        [$available, $unavailable] = $items->partition(fn ($l) => $l->status === 'active');

        // Groupé par vendeur : une demande de disponibilité par vendeur.
        $groups = $available->groupBy('user_id')->map(fn ($listings) => [
            'seller' => $listings->first()->user,
            'items'  => $listings->values(),
            'total'  => $listings->where('type', 'vente')->sum('price'),
        ])->values();

        return Inertia::render('Cart/Index', [
            'groups'      => $groups,
            'unavailable' => $unavailable->values(),
        ]);
    }

    public function add(Request $request, Listing $listing): RedirectResponse
    {
        abort_if($listing->user_id === $request->user()->id, 403, 'Vous ne pouvez pas ajouter votre propre annonce.');
        abort_if($listing->status !== 'active', 404);
        abort_if($listing->type === 'recherche', 422, 'Une recherche ne peut pas être ajoutée au panier.');

        $request->user()->cartListings()->syncWithoutDetaching([$listing->id]);

        return back()->with('success', 'Ajouté au panier.');
    }

    public function remove(Request $request, Listing $listing): RedirectResponse
    {
        $request->user()->cartListings()->detach($listing->id);

        return back()->with('success', 'Retiré du panier.');
    }
}
