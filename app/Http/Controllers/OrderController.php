<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
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

    /** Tout accepter : les livres encore en attente passent à « disponible ». */
    public function accept(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->seller_id === $request->user()->id, 403);
        abort_unless($order->status === 'pending', 422);

        $order->items()->where('status', 'pending')->update(['status' => 'accepted']);
        $order->syncStatusFromItems();

        $order->buyer->notify(new KabaNotification([
            'icon'    => 'fa-circle-check',
            'color'   => 'green',
            'kind'    => 'order',
            'message' => "{$request->user()->name} a accepté votre demande. Contactez-le pour convenir de la remise.",
            'url'     => '/demandes',
        ]));

        return back()->with('success', 'Demande acceptée.');
    }

    /** Tout refuser : les livres encore en attente passent à « indisponible ». */
    public function decline(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->seller_id === $request->user()->id, 403);
        abort_unless($order->status === 'pending', 422);

        $order->items()->where('status', 'pending')->update(['status' => 'declined']);
        $order->syncStatusFromItems();

        $notif = $order->fresh()->status === 'accepted'
            ? "{$request->user()->name} a répondu : certains livres sont disponibles, d'autres non. Consultez le détail."
            : "{$request->user()->name} a refusé votre demande de disponibilité.";

        $order->buyer->notify(new KabaNotification([
            'icon'    => 'fa-circle-xmark',
            'color'   => 'red',
            'kind'    => 'order',
            'message' => $notif,
            'url'     => '/demandes',
        ]));

        return back()->with('success', 'Réponse envoyée.');
    }

    /** Réponse livre par livre (accepter ou refuser un seul article). */
    public function respondItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($order->seller_id === $request->user()->id, 403);
        abort_unless($item->order_id === $order->id, 404);
        abort_unless($order->status === 'pending' && $item->status === 'pending', 422);

        $accepted = $request->routeIs('orders.items.accept');
        $item->update(['status' => $accepted ? 'accepted' : 'declined']);
        $order->syncStatusFromItems();

        // On ne notifie l'acheteur que lorsque le vendeur a répondu à tout.
        if ($order->fresh()->status !== 'pending') {
            $summary = $order->items()->where('status', 'accepted')->count();
            $count = $order->items()->count();
            $order->buyer->notify(new KabaNotification([
                'icon'    => $summary > 0 ? 'fa-circle-check' : 'fa-circle-xmark',
                'color'   => $summary > 0 ? 'green' : 'red',
                'kind'    => 'order',
                'message' => $summary > 0
                    ? "{$request->user()->name} a répondu : {$summary} livre".($summary > 1 ? 's' : '')." sur {$count} disponible".($summary > 1 ? 's' : '')."."
                    : "{$request->user()->name} a refusé votre demande de disponibilité.",
                'url'     => '/demandes',
            ]));
        }

        return back()->with('success', $accepted ? 'Livre marqué disponible.' : 'Livre marqué indisponible.');
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

    /**
     * Ouvre la messagerie entre l'acheteur et le vendeur à propos de cette demande.
     * Réutilise une conversation existante entre les deux ; sinon en crée une,
     * amorcée avec un récapitulatif des livres disponibles.
     */
    public function discuss(Request $request, Order $order): RedirectResponse
    {
        $me = $request->user();
        abort_unless(in_array($me->id, [$order->buyer_id, $order->seller_id]), 403);

        // Conversation déjà ouverte entre ces deux personnes (peu importe l'annonce) ?
        $conversation = \App\Models\Conversation::where(function ($q) use ($order) {
            $q->where('buyer_id', $order->buyer_id)->where('seller_id', $order->seller_id);
        })->orWhere(function ($q) use ($order) {
            $q->where('buyer_id', $order->seller_id)->where('seller_id', $order->buyer_id);
        })->latest('last_message_at')->first();

        if (! $conversation) {
            $available = $order->items()->with('listing:id,title')->where('status', '!=', 'declined')->get();

            $conversation = \App\Models\Conversation::create([
                'listing_id' => $available->first()?->listing_id,
                'buyer_id'   => $order->buyer_id,
                'seller_id'  => $order->seller_id,
            ]);

            // Premier message de contexte, envoyé au nom de l'acheteur.
            $titles = $available->map(fn ($it) => '« '.($it->listing->title ?? 'Livre').' »')->implode(', ');
            $conversation->messages()->create([
                'sender_id' => $order->buyer_id,
                'body'      => "Bonjour ! Suite à ma demande de disponibilité ({$titles}), comment organise-t-on la remise ?",
            ]);
            $conversation->update(['last_message_at' => now()]);

            if ($me->id === $order->buyer_id) {
                $order->seller->notify(new KabaNotification([
                    'kind'    => 'message',
                    'icon'    => 'fa-comment',
                    'color'   => 'brand',
                    'message' => "{$me->name} vous a écrit au sujet de sa demande.",
                    'url'     => '/messagerie',
                ]));
            }
        }

        return redirect()->route('messages.show', $conversation);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, ['pending', 'accepted']), 422);

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Demande annulée.');
    }
}
