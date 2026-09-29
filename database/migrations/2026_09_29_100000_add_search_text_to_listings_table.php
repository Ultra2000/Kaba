<?php

use App\Models\Listing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Colonne de recherche normalisée : titre et auteur sans accents ni ponctuation.
 *
 * Sans elle, « etranger » ne trouvait pas « L'Étranger » — or personne ne tape
 * les accents sur un clavier de téléphone. Une colonne dédiée donne le même
 * résultat sous SQLite et sous MySQL, sans dépendre de la collation du serveur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // En text plutôt qu'en varchar(255) : titre et auteur font 255 chacun et
            // la translittération peut allonger un caractère (« Œ » devient « oe »).
            // Sous MySQL en mode strict, une valeur trop longue ferait échouer l'écriture.
            $table->text('search_text')->nullable()->after('author');
        });

        // Les annonces déjà publiées doivent rester trouvables.
        Listing::query()->select('id', 'title', 'author')->chunkById(200, function ($listings) {
            foreach ($listings as $listing) {
                DB::table('listings')->where('id', $listing->id)->update([
                    'search_text' => Listing::normalizeSearch("{$listing->title} {$listing->author}"),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });
    }
};
