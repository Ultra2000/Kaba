<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        return Inertia::render('Favorites', [
            'listings' => $user->favoriteListings()
                ->with(['user:id,name', 'category:id,name,slug', 'photos'])
                ->latest('favorites.created_at')
                ->get(),
            'sellers' => $user->following()
                ->withCount(['listings as listings_count' => fn ($q) => $q->where('status', 'active')])
                ->get(['users.id', 'name', 'city', 'role', 'rating_avg', 'sales_count', 'is_verified']),
        ]);
    }

    public function toggle(Listing $listing): RedirectResponse
    {
        Auth::user()->favoriteListings()->toggle($listing->id);

        return back();
    }
}
