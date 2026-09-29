<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\KabaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * La recherche doit fonctionner sans accents : sur un clavier de téléphone,
 * personne ne tape « Étranger ».
 */
class SearchNormalizationTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::firstOrCreate(['slug' => 'roman'], ['name' => 'Romans', 'icon' => 'fa-book']);
    }

    private function listing(array $attrs = []): Listing
    {
        return Listing::create(array_merge([
            'user_id'     => User::factory()->create()->id,
            'category_id' => $this->category()->id,
            'title'       => 'Titre', 'condition' => 'bon', 'language' => 'Français',
            'city'        => 'Cotonou', 'type' => 'vente', 'price' => 3000, 'status' => 'active',
        ], $attrs));
    }

    /** Titres remontés par le scope de recherche du catalogue. */
    private function titlesFor(string $term): array
    {
        return Listing::where('status', 'active')->filter(['q' => $term])->pluck('title')->all();
    }

    public function test_accents_are_ignored_whichever_side_they_are_on(): void
    {
        $this->listing(['title' => "L'Étranger", 'author' => 'Albert Camus']);

        foreach (["L'Étranger", 'etranger', 'ETRANGER', 'Étranger', "l'etranger", 'l etranger'] as $term) {
            $this->assertSame(["L'Étranger"], $this->titlesFor($term), "Terme cherché : {$term}");
        }
    }

    public function test_author_is_searchable_without_accents(): void
    {
        $this->listing(['title' => 'Germinal', 'author' => 'Émile Zola']);

        $this->assertSame(['Germinal'], $this->titlesFor('emile zola'));
        $this->assertSame(['Germinal'], $this->titlesFor('ZOLA'));
    }

    public function test_isbn_matches_with_or_without_separators(): void
    {
        $this->listing(['title' => 'Le Petit Prince', 'isbn' => '978-2-07-040850-4']);

        foreach (['978-2-07-040850-4', '9782070408504', '978 2 07 040850 4'] as $term) {
            $this->assertSame(['Le Petit Prince'], $this->titlesFor($term), "Terme cherché : {$term}");
        }
    }

    public function test_an_unrelated_term_still_finds_nothing(): void
    {
        $this->listing(['title' => "L'Étranger", 'author' => 'Albert Camus']);

        $this->assertSame([], $this->titlesFor('dune'));
        $this->assertSame([], $this->titlesFor('9999999999999'));
    }

    /** Un terme sans lettre ni chiffre vaut une recherche vide, pas un filtre cassé. */
    public function test_punctuation_only_term_behaves_like_no_search(): void
    {
        $this->listing(['title' => 'Germinal']);

        $this->assertSame(['Germinal'], $this->titlesFor('???'));
    }

    public function test_search_column_is_rebuilt_when_the_listing_changes(): void
    {
        $listing = $this->listing(['title' => 'Ancien Titre', 'author' => 'Premier Auteur']);

        $listing->update(['title' => 'Nouveau Titré', 'author' => 'Second Auteur']);

        $this->assertSame([], $this->titlesFor('ancien'));
        $this->assertSame([], $this->titlesFor('premier auteur'));
        $this->assertSame(['Nouveau Titré'], $this->titlesFor('nouveau titre'));
        $this->assertSame(['Nouveau Titré'], $this->titlesFor('second auteur'));
    }

    public function test_catalogue_page_applies_the_normalized_search(): void
    {
        $this->listing(['title' => "L'Étranger"]);
        $this->listing(['title' => 'Dune']);

        $this->get('/explorer?q=etranger')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->component('Listings/Explore')
                ->has('listings.data', 1)
                ->where('listings.data.0.title', "L'Étranger"));
    }

    public function test_admin_listing_search_ignores_accents_too(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->listing(['title' => 'Les Misérables']);
        $this->listing(['title' => 'Dune']);

        $this->actingAs($admin)->get('/admin/annonces?q=miserables')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->has('listings.data', 1)
                ->where('listings.data.0.title', 'Les Misérables'));
    }

    public function test_wanted_book_alert_matches_despite_accents(): void
    {
        Notification::fake();

        $seeker = User::factory()->create();
        $this->listing([
            'user_id' => $seeker->id, 'title' => "L'Étranger",
            'type' => 'recherche', 'price' => 0,
        ]);

        $seller = User::factory()->create();
        $this->actingAs($seller)->post('/livres', [
            'type' => 'vente', 'title' => 'l etranger', 'category_id' => $this->category()->id,
            'condition' => 'bon', 'language' => 'Français', 'city' => 'Cotonou', 'price' => 2000,
        ])->assertRedirect();

        Notification::assertSentTo($seeker, KabaNotification::class);
    }
}
