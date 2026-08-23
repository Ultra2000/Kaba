<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Listing;
use App\Notifications\KabaNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Messages/Index', [
            'conversations' => $this->conversationList(),
            'active'        => null,
        ]);
    }

    public function show(Conversation $conversation): Response
    {
        $me = Auth::user();
        abort_unless($conversation->isParticipant($me), 403);

        // Marque comme lus les messages reçus
        $conversation->messages()->where('sender_id', '!=', $me->id)->whereNull('read_at')->update(['read_at' => now()]);

        $conversation->load(['listing:id,title,type,price', 'buyer:id,name', 'seller:id,name']);
        $other = $conversation->otherUser($me);

        return Inertia::render('Messages/Index', [
            'conversations' => $this->conversationList(),
            'active' => [
                'id'      => $conversation->id,
                'other'   => ['id' => $other->id, 'name' => $other->name],
                'listing' => $conversation->listing,
                'messages' => $conversation->messages()->orderBy('id')->get()->map(fn ($m) => [
                    'id'   => $m->id,
                    'body' => $m->body,
                    'mine' => $m->sender_id === $me->id,
                    'time' => $m->created_at->format('H:i'),
                ]),
            ],
        ]);
    }

    public function start(Listing $listing): RedirectResponse
    {
        $me = Auth::user();
        abort_if($listing->user_id === $me->id, 403, 'Vous ne pouvez pas vous contacter vous-même.');

        $conversation = Conversation::firstOrCreate(
            ['listing_id' => $listing->id, 'buyer_id' => $me->id],
            ['seller_id' => $listing->user_id],
        );

        return redirect()->route('messages.show', $conversation);
    }

    public function storeMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $me = Auth::user();
        abort_unless($conversation->isParticipant($me), 403);

        $data = $request->validate(['body' => 'required|string|max:2000']);

        $conversation->messages()->create([
            'sender_id' => $me->id,
            'body'      => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        $conversation->otherUser($me)->notify(new KabaNotification([
            'kind'    => 'message',
            'icon'    => 'fa-comment',
            'color'   => 'text-brand-600 bg-brand-50',
            'message' => "{$me->name} vous a envoyé un message.",
            'url'     => "/messagerie/{$conversation->id}",
        ]));

        return redirect()->route('messages.show', $conversation);
    }

    /** Liste des conversations de l'utilisateur, la plus récente en premier. */
    private function conversationList()
    {
        $me = Auth::user();

        return $me->conversations()
            ->with(['listing:id,title', 'buyer:id,name', 'seller:id,name', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($c) use ($me) {
                $other = $c->otherUser($me);
                return [
                    'id'      => $c->id,
                    'other'   => ['name' => $other->name],
                    'listing' => $c->listing?->title,
                    'last'    => $c->lastMessage?->body,
                    'unread'  => $c->messages()->where('sender_id', '!=', $me->id)->whereNull('read_at')->count(),
                    'time'    => optional($c->last_message_at ?? $c->created_at)->format('d/m'),
                ];
            });
    }
}
