<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $listings = $user->listings()
            ->with(['category:id,name,slug', 'photos'])
            ->latest()
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'active'    => $listings->where('status', 'active')->count(),
                'total'     => $listings->count(),
                'views'     => (int) $listings->sum('views'),
                'sales'     => (int) $user->sales_count,
                'favorites' => $user->favoriteListings()->count(),
                'following' => $user->following()->count(),
            ],
            'listings' => $listings,
        ]);
    }
}
