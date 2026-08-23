<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SellerController extends Controller
{
    public function index(Request $request): Response
    {
        $type = $request->input('type', 'all');   // all | particulier | pro
        $city = $request->input('city', 'all');
        $sort = $request->input('sort', 'rating'); // rating | sales | listings

        $sellers = User::query()
            ->withCount(['listings' => fn ($q) => $q->where('status', 'active')])
            ->whereHas('listings', fn ($q) => $q->where('status', 'active'))
            ->when($type === 'pro', fn ($q) => $q->where('role', 'pro'))
            ->when($type === 'particulier', fn ($q) => $q->where('role', 'user'))
            ->when($city !== 'all', fn ($q) => $q->where('city', $city))
            ->when($sort === 'sales', fn ($q) => $q->orderByDesc('sales_count'))
            ->when($sort === 'listings', fn ($q) => $q->orderByDesc('listings_count'))
            ->when($sort === 'rating', fn ($q) => $q->orderByDesc('rating_avg'))
            ->get(['id', 'name', 'city', 'role', 'rating_avg', 'sales_count', 'is_verified']);

        return Inertia::render('Sellers/Index', [
            'sellers' => $sellers,
            'cities'  => ['Cotonou', 'Abomey-Calavi', 'Porto-Novo', 'Parakou', 'Bohicon', 'Natitingou'],
            'filters' => compact('type', 'city', 'sort'),
        ]);
    }

    public function show(User $user): Response
    {
        $user->loadCount([
            'listings as active_count' => fn ($q) => $q->where('status', 'active'),
            'reviewsReceived as reviews_count',
            'followers as followers_count',
        ]);

        $listings = $user->listings()
            ->with(['category:id,name,slug', 'photos'])
            ->where('status', 'active')
            ->latest()
            ->get();

        $reviews = $user->reviewsReceived()
            ->with('author:id,name')
            ->latest()
            ->get();

        $isFollowing = Auth::check() && Auth::user()->following()->whereKey($user->id)->exists();
        $canReview = Auth::check() && Auth::id() !== $user->id
            && ! $user->reviewsReceived()->where('author_id', Auth::id())->exists();

        return Inertia::render('Sellers/Show', [
            'seller'      => $user,
            'listings'    => $listings,
            'reviews'     => $reviews,
            'isFollowing' => $isFollowing,
            'canReview'   => $canReview,
        ]);
    }

    public function follow(User $user): RedirectResponse
    {
        abort_if(Auth::id() === $user->id, 403);
        Auth::user()->following()->toggle($user->id);

        return back();
    }
}
