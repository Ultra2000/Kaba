<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /* ---------------- Vue d'ensemble ---------------- */
    public function index(): Response
    {
        $types = Listing::selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type');
        $total = max($types->sum(), 1);

        return Inertia::render('Admin/Overview', [
            'stats' => [
                'users'    => User::count(),
                'listings' => Listing::where('status', 'active')->count(),
                'reviews'  => Review::count(),
                'reports'  => Report::where('status', 'open')->count(),
            ],
            'repartition' => collect(['vente', 'don', 'echange', 'recherche'])->map(fn ($t) => [
                'type'    => $t,
                'percent' => round(($types[$t] ?? 0) / $total * 100),
            ]),
        ]);
    }

    /* ---------------- Signalements ---------------- */
    public function reports(): Response
    {
        $reports = Report::with(['reporter:id,name', 'reportable'])->latest()->get()->map(function ($r) {
            $target = $r->reportable;
            return [
                'id'         => $r->id,
                'reason'     => Report::REASONS[$r->reason] ?? $r->reason,
                'details'    => $r->details,
                'reporter'   => $r->reporter?->name ?? '—',
                'status'     => $r->status,
                'created_at' => $r->created_at->toIso8601String(),
                'target'     => $target instanceof Listing ? $target->title : ($target->name ?? 'Élément supprimé'),
                'target_url' => $target instanceof Listing ? "/livres/{$target->id}" : null,
            ];
        });

        return Inertia::render('Admin/Reports', ['reports' => $reports]);
    }

    public function resolveReport(Report $report): RedirectResponse
    {
        $report->update(['status' => 'resolved']);
        return back();
    }

    public function dismissReport(Report $report): RedirectResponse
    {
        $report->update(['status' => 'dismissed']);
        return back();
    }

    /* ---------------- Annonces ---------------- */
    public function listings(): Response
    {
        $listings = Listing::with(['user:id,name', 'category:id,name'])->latest()->get()->map(fn ($l) => [
            'id'       => $l->id,
            'title'    => $l->title,
            'category' => $l->category?->name,
            'seller'   => $l->user?->name,
            'type'     => $l->type,
            'price'    => $l->price,
            'status'   => $l->status,
        ]);

        return Inertia::render('Admin/Listings', ['listings' => $listings]);
    }

    public function approveListing(Listing $listing): RedirectResponse
    {
        $listing->update(['status' => 'active']);
        return back();
    }

    public function toggleListing(Listing $listing): RedirectResponse
    {
        $listing->update(['status' => $listing->status === 'hidden' ? 'active' : 'hidden']);
        return back();
    }

    public function destroyListing(Listing $listing): RedirectResponse
    {
        $listing->delete();
        return back();
    }

    /* ---------------- Utilisateurs ---------------- */
    public function users(): Response
    {
        $users = User::withCount('listings')->latest()->get(['id', 'name', 'email', 'role', 'city', 'is_verified', 'created_at']);

        return Inertia::render('Admin/Users', ['users' => $users]);
    }

    public function toggleUserVerified(User $user): RedirectResponse
    {
        $user->update(['is_verified' => ! $user->is_verified]);
        return back();
    }

    /* ---------------- Catégories ---------------- */
    public function categories(): Response
    {
        return Inertia::render('Admin/Categories', [
            'categories' => Category::withCount('listings')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:60',
        ]);
        Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => $data['icon'] ?? 'fa-book',
        ]);
        return back();
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:60',
        ]);
        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => $data['icon'] ?? $category->icon,
        ]);
        return back();
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        if ($category->listings()->exists()) {
            return back()->withErrors(['category' => 'Catégorie non vide : déplacez d’abord ses annonces.']);
        }
        $category->delete();
        return back();
    }
}
