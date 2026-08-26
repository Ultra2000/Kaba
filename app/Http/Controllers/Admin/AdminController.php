<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    private const PER_PAGE = 20;

    /* ---------------- Vue d'ensemble ---------------- */
    public function index(): Response
    {
        $now = Carbon::now();
        $monthAgo = $now->copy()->subDays(30);
        $twoMonthsAgo = $now->copy()->subDays(60);

        // Évolution sur 30 jours vs les 30 jours précédents.
        $trend = function (string $model) use ($monthAgo, $twoMonthsAgo) {
            $current = $model::where('created_at', '>=', $monthAgo)->count();
            $previous = $model::whereBetween('created_at', [$twoMonthsAgo, $monthAgo])->count();
            $percent = $previous > 0 ? round(($current - $previous) / $previous * 100) : ($current > 0 ? 100 : 0);

            return ['count' => $current, 'percent' => $percent];
        };

        $types = Listing::selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type');
        $total = max($types->sum(), 1);

        return Inertia::render('Admin/Overview', [
            'stats' => [
                'users'      => User::count(),
                'listings'   => Listing::where('status', 'active')->count(),
                'orders'     => Order::count(),
                'completed'  => Order::where('status', 'completed')->count(),
                'reviews'    => Review::count(),
            ],
            'trends' => [
                'users'    => $trend(User::class),
                'listings' => $trend(Listing::class),
                'orders'   => $trend(Order::class),
            ],
            // Ce qui demande une intervention : le cœur d'un tableau de bord admin.
            'todo' => [
                'reports'        => Report::where('status', 'open')->count(),
                'pendingListings' => Listing::where('status', 'pending')->count(),
                'pendingOrders'  => Order::where('status', 'pending')->count(),
                'lowRatedUsers'  => User::where('rating_avg', '>', 0)->where('rating_avg', '<', 3)->count(),
            ],
            'repartition' => collect(['vente', 'don', 'echange', 'recherche'])->map(fn ($t) => [
                'type'    => $t,
                'count'   => $types[$t] ?? 0,
                'percent' => round(($types[$t] ?? 0) / $total * 100),
            ]),
            'activity'  => $this->recentActivity(),
            'topCities' => Listing::where('status', 'active')
                ->selectRaw('city, count(*) as c')
                ->groupBy('city')->orderByDesc('c')->take(5)
                ->get()->map(fn ($r) => ['city' => $r->city, 'count' => $r->c]),
        ]);
    }

    /** Dernières actions sur la plateforme, toutes sources confondues. */
    private function recentActivity(): array
    {
        $listings = Listing::with('user:id,name')->latest()->take(6)->get()
            ->map(fn ($l) => [
                'icon' => 'fa-book', 'color' => 'brand',
                'text' => ($l->user?->name ?? 'Quelqu\'un') . ' a publié « ' . Str::limit($l->title, 40) . ' »',
                'url'  => "/livres/{$l->id}",
                'at'   => $l->created_at->toIso8601String(),
            ]);

        $users = User::latest()->take(6)->get()
            ->map(fn ($u) => [
                'icon' => 'fa-user-plus', 'color' => 'green',
                'text' => $u->name . ' a rejoint KABA',
                'url'  => "/vendeurs/{$u->id}",
                'at'   => $u->created_at->toIso8601String(),
            ]);

        $orders = Order::with(['buyer:id,name', 'seller:id,name'])->latest()->take(6)->get()
            ->map(fn ($o) => [
                'icon' => 'fa-basket-shopping', 'color' => 'amber',
                'text' => ($o->buyer?->name ?? '?') . ' a fait une demande à ' . ($o->seller?->name ?? '?'),
                'url'  => '/admin/demandes',
                'at'   => $o->created_at->toIso8601String(),
            ]);

        return $listings->concat($users)->concat($orders)
            ->sortByDesc('at')->take(10)->values()->all();
    }

    /* ---------------- Signalements ---------------- */
    public function reports(Request $request): Response
    {
        $status = $request->input('status', 'open');

        $reports = Report::with(['reporter:id,name', 'reportable'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(function ($r) {
                $target = $r->reportable;
                $isListing = $target instanceof Listing;

                return [
                    'id'         => $r->id,
                    'reason'     => Report::REASONS[$r->reason] ?? $r->reason,
                    'details'    => $r->details,
                    'reporter'   => $r->reporter?->name ?? '—',
                    'status'     => $r->status,
                    'created_at' => $r->created_at->toIso8601String(),
                    'target'     => $isListing ? $target->title : ($target->name ?? 'Élément supprimé'),
                    'target_url' => $isListing ? "/livres/{$target->id}" : null,
                    // Permet d'agir directement sur l'annonce depuis le signalement.
                    'listing_id'     => $isListing ? $target->id : null,
                    'listing_hidden' => $isListing ? $target->status === 'hidden' : null,
                ];
            });

        return Inertia::render('Admin/Reports', [
            'reports' => $reports,
            'filters' => ['status' => $status],
            'counts'  => [
                'open'      => Report::where('status', 'open')->count(),
                'resolved'  => Report::where('status', 'resolved')->count(),
                'dismissed' => Report::where('status', 'dismissed')->count(),
            ],
        ]);
    }

    public function resolveReport(Report $report): RedirectResponse
    {
        $report->update(['status' => 'resolved']);
        return back()->with('success', 'Signalement traité.');
    }

    public function dismissReport(Report $report): RedirectResponse
    {
        $report->update(['status' => 'dismissed']);
        return back()->with('success', 'Signalement ignoré.');
    }

    /* ---------------- Annonces ---------------- */
    public function listings(Request $request): Response
    {
        $filters = [
            'q'      => $request->input('q', ''),
            'status' => $request->input('status', 'all'),
            'type'   => $request->input('type', 'all'),
        ];

        $listings = Listing::with(['user:id,name', 'category:id,name'])
            ->when($filters['q'], fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$v}%")
                ->orWhere('author', 'like', "%{$v}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$v}%"))))
            ->when($filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['type'] !== 'all', fn ($q) => $q->where('type', $filters['type']))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn ($l) => [
                'id'         => $l->id,
                'title'      => $l->title,
                'category'   => $l->category?->name,
                'seller'     => $l->user?->name,
                'seller_id'  => $l->user_id,
                'type'       => $l->type,
                'price'      => $l->price,
                'status'     => $l->status,
                'views'      => $l->views,
                'created_at' => $l->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Listings', [
            'listings' => $listings,
            'filters'  => $filters,
            'counts'   => [
                'all'     => Listing::count(),
                'active'  => Listing::where('status', 'active')->count(),
                'pending' => Listing::where('status', 'pending')->count(),
                'hidden'  => Listing::where('status', 'hidden')->count(),
                'sold'    => Listing::where('status', 'sold')->count(),
            ],
        ]);
    }

    public function approveListing(Listing $listing): RedirectResponse
    {
        $listing->update(['status' => 'active']);
        return back()->with('success', 'Annonce validée.');
    }

    public function toggleListing(Listing $listing): RedirectResponse
    {
        $listing->update(['status' => $listing->status === 'hidden' ? 'active' : 'hidden']);
        return back()->with('success', $listing->status === 'hidden' ? 'Annonce masquée.' : 'Annonce réaffichée.');
    }

    public function destroyListing(Listing $listing): RedirectResponse
    {
        $listing->delete();
        return back()->with('success', 'Annonce supprimée.');
    }

    /* ---------------- Demandes (transactions) ---------------- */
    public function orders(Request $request): Response
    {
        $status = $request->input('status', 'all');

        $orders = Order::with(['buyer:id,name', 'seller:id,name', 'items'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn ($o) => [
                'id'         => $o->id,
                'buyer'      => $o->buyer?->name ?? '—',
                'buyer_id'   => $o->buyer_id,
                'seller'     => $o->seller?->name ?? '—',
                'seller_id'  => $o->seller_id,
                'status'     => $o->status,
                'label'      => $o->status_label,
                'items'      => $o->items->count(),
                'total'      => $o->total(),
                'created_at' => $o->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Orders', [
            'orders'  => $orders,
            'filters' => ['status' => $status],
            'counts'  => collect(Order::STATUSES)->map(fn ($label, $key) => [
                'label' => $label,
                'count' => Order::where('status', $key)->count(),
            ])->all(),
        ]);
    }

    /* ---------------- Avis ---------------- */
    public function reviews(Request $request): Response
    {
        $filters = ['q' => $request->input('q', ''), 'rating' => $request->input('rating', 'all')];

        $reviews = Review::with(['author:id,name', 'seller:id,name', 'listing:id,title'])
            ->when($filters['q'], fn ($q, $v) => $q->where('comment', 'like', "%{$v}%"))
            ->when($filters['rating'] !== 'all', fn ($q) => $q->where('rating', (int) $filters['rating']))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn ($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'comment'    => $r->comment,
                'author'     => $r->author?->name ?? '—',
                'seller'     => $r->seller?->name ?? '—',
                'seller_id'  => $r->seller_id,
                'listing'    => $r->listing?->title,
                'created_at' => $r->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Reviews', [
            'reviews' => $reviews,
            'filters' => $filters,
            'average' => round((float) Review::avg('rating'), 2),
        ]);
    }

    /** Supprime un avis abusif et recalcule la note du membre concerné. */
    public function destroyReview(Review $review): RedirectResponse
    {
        $seller = $review->seller;
        $review->delete();
        $seller?->recalcRating();

        return back()->with('success', 'Avis supprimé.');
    }

    /* ---------------- Utilisateurs ---------------- */
    public function users(Request $request): Response
    {
        $filters = [
            'q'    => $request->input('q', ''),
            'role' => $request->input('role', 'all'),
        ];

        $users = User::withCount('listings')
            ->when($filters['q'], fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$v}%")
                ->orWhere('email', 'like', "%{$v}%")
                ->orWhere('city', 'like', "%{$v}%")))
            ->when($filters['role'] !== 'all', fn ($q) => $q->where('role', $filters['role']))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn ($u) => [
                'id'             => $u->id,
                'name'           => $u->name,
                'email'          => $u->email,
                'role'           => $u->role,
                'city'           => $u->city,
                'is_verified'    => (bool) $u->is_verified,
                'rating_avg'     => (float) $u->rating_avg,
                'sales_count'    => (int) $u->sales_count,
                'listings_count' => $u->listings_count,
                'created_at'     => $u->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Users', [
            'users'   => $users,
            'roles'   => User::ROLES,
            'filters' => $filters,
            'counts'  => [
                'all'      => User::count(),
                'verified' => User::where('is_verified', true)->count(),
            ],
        ]);
    }

    public function toggleUserVerified(User $user): RedirectResponse
    {
        $user->update(['is_verified' => ! $user->is_verified]);
        return back()->with('success', $user->is_verified ? 'Membre vérifié.' : 'Vérification retirée.');
    }

    /** Crée un compte depuis l'administration (amorçage, comptes pro, équipe). */
    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|max:150|unique:users,email',
            'password'    => 'required|string|min:8',
            'role'        => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'city'        => 'nullable|string|max:100',
            'phone'       => 'nullable|string|max:30',
            'is_verified' => 'boolean',
        ]);

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => $data['role'],
            'city'        => $data['city'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'is_verified' => $data['is_verified'] ?? false,
        ]);

        // Compte créé par un administrateur : l'adresse est considérée validée.
        // (email_verified_at n'est pas assignable en masse, d'où le forceFill.)
        $user->forceFill(['email_verified_at' => now()])->save();

        return back()->with('success', 'Compte créé.');
    }

    /** Change le statut d'un membre (utilisateur, vendeur pro, administrateur). */
    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => 'required|in:' . implode(',', array_keys(User::ROLES)),
        ]);

        // Garde-fou : ne jamais se retirer soi-même les droits, ni supprimer
        // le dernier administrateur — on se retrouverait enfermé dehors.
        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Vous ne pouvez pas retirer vos propres droits d\'administrateur.']);
        }
        if ($user->role === 'admin' && $data['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['role' => 'Il doit rester au moins un administrateur.']);
        }

        $user->update(['role' => $data['role']]);

        return back()->with('success', 'Statut mis à jour.');
    }

    /* ---------------- Catégories ---------------- */
    public function categories(): Response
    {
        return Inertia::render('Admin/Categories', [
            'categories' => Category::withCount([
                'listings',
                'listings as active_count' => fn ($q) => $q->where('status', 'active'),
            ])->orderBy('name')->get(),
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

        return back()->with('success', 'Catégorie créée.');
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

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        // Supprimer une catégorie utilisée casserait les annonces liées.
        if ($category->listings()->exists()) {
            return back()->withErrors([
                'category' => 'Impossible : des annonces utilisent encore cette catégorie.',
            ]);
        }

        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
