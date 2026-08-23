<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            5 => ['Livre conforme, vendeur ponctuel. Je recommande !', 'Super échange, communication au top.', 'Vendeur sérieux, remise sur le campus comme convenu.', 'Parfait, livre nickel et rapide.'],
            4 => ['Bon état général, transaction rapide via Mobile Money.', 'Conforme à la description, bien emballé.', 'Très bien, petit retard mais correct.'],
        ];

        $sellers = User::where('email', 'like', 'vendeur%@kaba.bj')->get();

        // Acheteurs de démonstration (auteurs des avis)
        $buyerNames = ['Marc T.', 'Fatou A.', 'Kevin D.', 'Sarah B.', 'Chloé M.', 'Ibrahim Z.'];
        $authors = collect($buyerNames)->map(fn ($name, $i) => User::updateOrCreate(
            ['email' => 'acheteur' . ($i + 1) . '@kaba.bj'],
            ['name' => $name, 'password' => bcrypt('password'), 'city' => 'Cotonou', 'email_verified_at' => now()]
        ));

        foreach ($sellers as $seller) {
            $pool = $authors->where('id', '!=', $seller->id)->values();
            $count = min(rand(3, 5), $pool->count());

            for ($i = 0; $i < $count; $i++) {
                $author = $pool[$i % $pool->count()];
                $rating = rand(0, 4) === 0 ? 4 : 5; // majorité de 5
                Review::updateOrCreate(
                    ['author_id' => $author->id, 'seller_id' => $seller->id],
                    [
                        'rating'  => $rating,
                        'comment' => $comments[$rating][array_rand($comments[$rating])],
                    ]
                );
            }

            $seller->recalcRating();
        }
    }
}
