<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'featured' => Listing::with(['user:id,name', 'category:id,name,slug', 'photos'])
                ->where('status', 'active')
                ->orderByDesc('views')
                ->take(10)
                ->get(),
            'categories' => Category::orderBy('name')->get(['id', 'name', 'slug', 'icon']),
        ]);
    }
}
