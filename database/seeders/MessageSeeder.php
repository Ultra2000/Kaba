<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::where('email', 'vendeur1@kaba.bj')->first();
        $buyer  = User::where('email', 'acheteur1@kaba.bj')->first();
        if (! $seller || ! $buyer) {
            return;
        }

        $listing = $seller->listings()->first();

        $conversation = Conversation::create([
            'listing_id' => $listing?->id,
            'buyer_id'   => $buyer->id,
            'seller_id'  => $seller->id,
        ]);

        // [expéditeur, texte]  (b = acheteur, s = vendeur)
        $script = [
            ['b', 'Bonjour ! Ce livre est-il toujours disponible ?'],
            ['s', 'Bonjour 😊 Oui, il est disponible et en très bon état.'],
            ['b', 'Super. Le prix est-il un peu négociable ?'],
            ['s', 'Je peux faire un petit geste si vous prenez un autre titre 👍'],
            ['b', 'Ça marche ! On peut se voir au campus cette semaine ?'],
        ];

        foreach ($script as $i => [$who, $text]) {
            $senderId = $who === 'b' ? $buyer->id : $seller->id;
            $conversation->messages()->create([
                'sender_id' => $senderId,
                'body'      => $text,
                // Les messages du vendeur sont lus par l'acheteur ; le dernier message de l'acheteur reste non lu
                'read_at'   => $who === 's' ? now() : ($i < count($script) - 1 ? now() : null),
                'created_at' => now()->subMinutes((count($script) - $i) * 5),
            ]);
        }

        $conversation->update(['last_message_at' => now()]);
    }
}
