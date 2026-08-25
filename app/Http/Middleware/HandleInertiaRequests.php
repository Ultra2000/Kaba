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
        return [
            ...parent::share($request),
            'auth' => [
                'user'      => $request->user(),
                'favorites' => $request->user()
                    ? $request->user()->favoriteListings()->pluck('listings.id')
                    : [],
                'following' => $request->user()
                    ? $request->user()->following()->pluck('users.id')
                    : [],
                'unread' => $request->user()
                    ? $request->user()->unreadNotifications()->count()
                    : 0,
                'unreadMessages' => $request->user()
                    ? $request->user()->unreadMessagesCount()
                    : 0,
                'cart' => $request->user()
                    ? $request->user()->cartListings()->pluck('listings.id')
                    : [],
                // Demandes reçues en attente de réponse (indicateur pour le vendeur).
                'pendingOrders' => $request->user()
                    ? $request->user()->ordersReceived()->where('status', 'pending')->count()
                    : 0,
            ],
            'nav' => [
                'categories' => Cache::remember('nav.categories', 3600, fn () =>
                    Category::orderBy('name')->get(['name', 'slug', 'icon', 'image'])),
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
