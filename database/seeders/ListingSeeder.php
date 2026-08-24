<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        // Vendeurs de démonstration
        $sellers = [
            ['name' => 'Aïcha K.', 'city' => 'Cotonou', 'rating_avg' => 4.9, 'sales_count' => 42, 'is_verified' => true],
            ['name' => 'Librairie Notre-Dame', 'city' => 'Cotonou', 'rating_avg' => 4.8, 'sales_count' => 1240, 'is_verified' => true, 'role' => 'pro'],
            ['name' => 'Jean-Marc H.', 'city' => 'Abomey-Calavi', 'rating_avg' => 4.6, 'sales_count' => 15, 'is_verified' => true],
            ['name' => 'Grâce D.', 'city' => 'Bohicon', 'rating_avg' => 4.4, 'sales_count' => 6, 'is_verified' => false],
            ['name' => 'Ibrahim S.', 'city' => 'Parakou', 'rating_avg' => 4.7, 'sales_count' => 19, 'is_verified' => true],
        ];

        $users = [];
        foreach ($sellers as $i => $s) {
            $users[] = User::updateOrCreate(
                ['email' => 'vendeur' . ($i + 1) . '@kaba.bj'],
                array_merge($s, [
                    'password' => Hash::make('password'),
                    'role' => $s['role'] ?? 'user',
                    'email_verified_at' => now(),
                ])
            );
        }

        $cats = Category::pluck('id', 'slug'); // slug => id

        // [titre, auteur, cat_slug, condition, ville, type, prix, note, isbn, wants, budget]
        $books = [
            ['Droit des obligations - 12e éd.', 'Bénabent, Alain', 'universitaire', 'tres_bon', 'Cotonou', 'vente', 8500, 4.5, null, null, null],
            ["L'Étranger", 'Camus, Albert', 'roman', 'bon', 'Porto-Novo', 'vente', 1500, 4.8, '9782070360024', null, null],
            ['Père riche, père pauvre', 'Kiyosaki, Robert T.', 'dev-perso', 'comme_neuf', 'Cotonou', 'vente', 4000, 4.9, '9781612680194', null, null],
            ['Mathématiques Terminale D', 'Collection CIAM', 'scolaire', 'moyen', 'Abomey-Calavi', 'don', 0, 4.2, null, null, null],
            ["L'Art de la Guerre", 'Sun Tzu', 'histoire', 'bon', 'Parakou', 'echange', 0, 4.6, null, 'Romans policiers', null],
            ['Les 48 lois du pouvoir', 'Greene, Robert', 'dev-perso', 'tres_bon', 'Cotonou', 'vente', 5000, 4.7, '9780140280197', null, null],
            ['One Piece - Tome 1', 'Oda, Eiichiro', 'manga', 'comme_neuf', 'Cotonou', 'vente', 2500, 4.9, '9781569319017', null, null],
            ['Physique Chimie 2nde', 'Collection AREX', 'scolaire', 'bon', 'Bohicon', 'don', 0, 4.0, null, null, null],
            ["Introduction à l'algorithmique", 'Cormen, Thomas', 'informatique', 'tres_bon', 'Abomey-Calavi', 'vente', 12000, 4.8, '9780262033848', null, null],
            ['Une si longue lettre', 'Bâ, Mariama', 'roman', 'bon', 'Porto-Novo', 'vente', 1800, 4.6, null, null, null],
            ['Économie politique - Tome 1', 'Généreux, Jacques', 'economie', 'moyen', 'Parakou', 'vente', 6000, 4.3, null, null, null],
            ['Le Petit Prince', 'Saint-Exupéry, A.', 'jeunesse', 'comme_neuf', 'Cotonou', 'echange', 0, 4.9, '9782070612758', 'Contes africains', null],
            ['Anatomie humaine illustrée', 'Netter, Frank', 'sante', 'tres_bon', 'Cotonou', 'vente', 18000, 4.9, '9781416059516', null, null],
            ['Grammaire anglaise', 'Murphy, Raymond', 'langues', 'bon', 'Abomey-Calavi', 'vente', 3500, 4.5, '9780521189392', null, null],
            ['Annales Concours FASEG', 'Éditions du Flamboyant', 'concours', 'bon', 'Cotonou', 'don', 0, 4.4, null, null, null],
            ['Tintin au Congo', 'Hergé', 'bd', 'moyen', 'Porto-Novo', 'echange', 0, 4.1, '9782203001015', 'Astérix', null],
            ['Sapiens - Une brève histoire', 'Harari, Yuval Noah', 'histoire', 'comme_neuf', 'Cotonou', 'vente', 7000, 4.9, '9780062316097', null, null],
            ['Biologie cellulaire', 'Alberts, Bruce', 'sciences', 'bon', 'Abomey-Calavi', 'vente', 9500, 4.6, '9780815344322', null, null],
            ['Le Grand Livre de la cuisine béninoise', 'Adjovi, Reine', 'cuisine', 'comme_neuf', 'Cotonou', 'vente', 4500, 4.7, null, null, null],
            ['Jeune Afrique - Spécial', 'Rédaction JA', 'magazine', 'bon', 'Cotonou', 'vente', 1000, 4.0, null, null, null],
            ['Atomic Habits', 'Clear, James', 'dev-perso', 'bon', 'Cotonou', 'recherche', 0, null, '9781847941831', null, 5000],
            ['Le Comte de Monte-Cristo', 'Dumas, Alexandre', 'roman', 'bon', 'Abomey-Calavi', 'recherche', 0, null, null, null, 3000],

            // --- Livres supplémentaires (ISBN réels pour de vraies couvertures) ---
            // Romans
            ['1984', 'Orwell, George', 'roman', 'bon', 'Cotonou', 'vente', 2500, 4.8, '9780451524935', null, null],
            ["L'Alchimiste", 'Coelho, Paulo', 'roman', 'tres_bon', 'Porto-Novo', 'vente', 2000, 4.7, '9780061122415', null, null],
            ["Harry Potter à l'école des sorciers", 'Rowling, J.K.', 'roman', 'comme_neuf', 'Cotonou', 'vente', 4500, 4.9, '9780590353427', null, null],
            ['Les Misérables', 'Hugo, Victor', 'roman', 'moyen', 'Abomey-Calavi', 'vente', 3000, 4.6, '9780451419439', null, null],
            ['Le Vieil Homme et la Mer', 'Hemingway, Ernest', 'roman', 'bon', 'Parakou', 'vente', 1800, 4.5, '9780684801223', null, null],

            // Développement personnel
            ['Les 7 habitudes des gens efficaces', 'Covey, Stephen R.', 'dev-perso', 'bon', 'Cotonou', 'vente', 4000, 4.7, '9781982137274', null, null],
            ["L'art subtil de s'en foutre", 'Manson, Mark', 'dev-perso', 'tres_bon', 'Cotonou', 'vente', 4200, 4.6, '9780062457714', null, null],
            ['Réfléchissez et devenez riche', 'Hill, Napoleon', 'dev-perso', 'bon', 'Bohicon', 'vente', 3000, 4.5, '9781585424333', null, null],
            ['Le pouvoir du moment présent', 'Tolle, Eckhart', 'dev-perso', 'comme_neuf', 'Cotonou', 'vente', 3800, 4.7, '9781577314806', null, null],
            ['Comment se faire des amis', 'Carnegie, Dale', 'dev-perso', 'bon', 'Porto-Novo', 'vente', 2500, 4.8, '9780671027032', null, null],

            // Histoire
            ['Homo Deus', 'Harari, Yuval Noah', 'histoire', 'tres_bon', 'Cotonou', 'vente', 7000, 4.8, '9780062464316', null, null],
            ['Une brève histoire du temps', 'Hawking, Stephen', 'histoire', 'bon', 'Abomey-Calavi', 'vente', 5000, 4.7, '9780553380163', null, null],
            ["L'Univers dans une coquille de noix", 'Hawking, Stephen', 'histoire', 'comme_neuf', 'Cotonou', 'vente', 6000, 4.6, '9780553802023', null, null],
            ['21 leçons pour le XXIe siècle', 'Harari, Yuval Noah', 'histoire', 'bon', 'Parakou', 'vente', 6500, 4.5, '9780525512172', null, null],

            // Sciences
            ['Une brève histoire de presque tout', 'Bryson, Bill', 'sciences', 'bon', 'Cotonou', 'vente', 5500, 4.7, '9780767908184', null, null],
            ['Cosmos', 'Sagan, Carl', 'sciences', 'tres_bon', 'Cotonou', 'vente', 6000, 4.8, '9780345539434', null, null],

            // Informatique
            ['Clean Code', 'Martin, Robert C.', 'informatique', 'bon', 'Cotonou', 'vente', 15000, 4.9, '9780132350884', null, null],
            ['The Pragmatic Programmer', 'Hunt, Andrew', 'informatique', 'tres_bon', 'Abomey-Calavi', 'vente', 14000, 4.8, '9780201616224', null, null],

            // Scolaire
            ['Philosophie Terminale', 'Hachette Éducation', 'scolaire', 'bon', 'Cotonou', 'vente', 3500, 4.2, null, null, null],
            ['Histoire-Géographie 3e', 'Hatier', 'scolaire', 'bon', 'Bohicon', 'don', 0, 4.0, null, null, null],
        ];

        foreach ($books as $i => $b) {
            [$title, $author, $slug, $cond, $city, $type, $price, $rating, $isbn, $wants, $budget] = $b;
            $oldPrice = $type === 'vente' ? (int) (round(($price / 0.6) / 500) * 500) : null;
            Listing::create([
                'user_id'     => $users[$i % count($users)]->id,
                'category_id' => $cats[$slug],
                'title'       => $title,
                'author'      => $author,
                'isbn'        => $isbn,
                'language'    => 'Français',
                'condition'   => $cond,
                'city'        => $city,
                'type'        => $type,
                'price'       => $price,
                'old_price'   => $oldPrice,
                'wants'       => $wants,
                'budget'      => $budget,
                'rating'      => $rating,
                'status'      => 'active',
                'views'       => rand(40, 400),
                'description' => 'Ouvrage en '.(Listing::CONDITIONS[$cond] ?? $cond).'. Remise en main propre possible à '.$city.'.',
            ]);
        }
    }
}
