<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Notifications\KabaNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /** Mes demandes : envoyées (acheteur) et reçues (vendeur). */
    public function index(Request $request): Response
    {
        $with = ['items.listing:id,title,author,isbn,type,price,status', 'items.listing.photos'];

        return Inertia::render('Orders/Index', [
            'sent' => $request->user()->ordersSent()
                ->with(array_merge($with, ['seller:id,name,city,is_verified']))
                ->latest()->get(),
            'received' => $request->user()->ordersReceived()
                ->with(array_merge($with, ['buyer:id,name,city']))
                ->latest()->get(),
        ]);
    }

    /** Demander la disponibilité des livres d'UN vendeur présents dans le panier. */
    public function store(Request $request, User $seller): RedirectResponse
    {
        $data = $request->validate(['message' => 'nullable|string|max:500']);

        $listings = $request->user()->cartListings()
            ->where('listings.user_id', $seller->id)
            ->where('listings.status', 'active')
            ->get();

        abort_if($listings->isEmpty(), 422, 'Aucun livre de ce vendeur dans votre panier.');

        $order = Order::create([
            'buyer_id'  => $request->user()->id,
            'seller_id' => $seller->id,
            'message'   => $data['message'] ?? null,
        ]);

        foreach ($listings as $l) {
            $order->items()->create([
                'listing_id' => $l->id,
                'price'      => $l->type === 'vente' ? (int) $l->price : 0,
            ]);
        }

        // Les livres demandés sortent du panier.
        $request->user()->cartListings()->detach($listings->pluck('id'));

        $count = $listings->count();
        $seller->notify(new KabaNotification([
            'icon'    => 'fa-basket-shopping',
            'color'   => 'brand',
            'kind'    => 'order',
            'message' => "{$request->user()->name} demande la disponibilité de {$count} livre".($count > 1 ? 's' : '').".",
            'url'     => '/demandes',
        ]));

        return redirect()->route('orders.index')->with('success', 'Demande envoyée au vendeur.');
    }

    public function accept(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->seller_id === $request->user()->id, 403);
        abort_unless($order->status === 'pending', 422);

        $order->update(['status' => 'accepted']);
        $order->buyer->notify(new KabaNotification([
            'icon'    => 'fa-circle-check',
            'color'   => 'green',
            'kind'    => 'order',
            'message' => "{$request->user()->name} a accepté votre demande. Contactez-le pour convenir de la remise.",
            'url'     => '/demandes',
        ]));

        return back()->with('success', 'Demande acceptée.');
    }

    public function decline(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->seller_id === $request->user()->id, 403);
        abort_unless($order->status === 'pending', 422);

        $order->update(['status' => 'declined']);
        $order->buyer->notify(new KabaNotification([
            'icon'    => 'fa-circle-xmark',
            'color'   => 'red',
            'kind'    => 'order',
            'message' => "{$request->user()->name} a refusé votre demande de disponibilité.",
            'url'     => '/demandes',
        ]));

        return back()->with('success', 'Demande refusée.');
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->seller_id === $request->user()->id, 403);
        abort_unless($order->status === 'accepted', 422);

        $order->update(['status' => 'completed']);
        $order->buyer->notify(new KabaNotification([
            'icon'    => 'fa-handshake',
            'color'   => 'brand',
            'kind'    => 'order',
            'message' => "Remise confirmée par {$request->user()->name}. Bonne lecture !",
            'url'     => '/demandes',
        ]));

        return back()->with('success', 'Remise confirmée.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, ['pending', 'accepted']), 422);

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Demande annulée.');
    }
}
