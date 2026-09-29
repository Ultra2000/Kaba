<?php

namespace App\Http\Middleware;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Deux accès en tout pour un visiteur connecté : une lecture de cache pour
        // les listes d'interaction, une requête pour tous les compteurs de badges.
        $interactions = $user?->interactionIds() ?? [];
        $counts       = $user?->badgeCounts() ?? [];

        return [
            ...parent::share($request),
            'auth' => [
                'user'      => $user,
                'favorites' => $interactions['favorites'] ?? [],
                'following' => $interactions['following'] ?? [],
                'cart'      => $interactions['cart'] ?? [],
                'unread'         => $counts['unread'] ?? 0,
                'unreadMessages' => $counts['unreadMessages'] ?? 0,
                // Demandes reçues en attente de réponse (indicateur pour le vendeur).
                'pendingOrders'  => $counts['pendingOrders'] ?? 0,
            ],
            // Messages de confirmation après une action (back()->with('success', …)).
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
            // Compteurs affichés dans le menu de l'administration.
            'admin' => $user?->isAdmin() ? [
                'reports'         => $counts['reports'] ?? 0,
                'pendingListings' => $counts['pendingListings'] ?? 0,
            ] : null,
            'nav' => [
                'categories' => Cache::remember('nav.categories', 3600, fn () =>
                    Category::browsable()->orderBy('name')->get(['name', 'slug', 'icon', 'image'])),
            ],
            // Métadonnées SEO / Open Graph par défaut — surchargeables par contrôleur.
            'meta' => [
                'title'       => 'KABA — La marketplace du livre d\'occasion au Bénin',
                'description' => 'Achetez, vendez, donnez ou échangez vos livres d\'occasion au Bénin. Livres scolaires, universitaires, romans… à petits prix, partout au Bénin.',
                'image'       => url('/images/logo.png'),
                'url'         => $request->fullUrl(),
                'type'        => 'website',
            ],
        ];
    }
}
