<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EmptyCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $slug, string $name): Category
    {
        return Category::create(['name' => $name, 'slug' => $slug, 'icon' => 'fa-book']);
    }

    private function listing(Category $c, array $attrs = []): Listing
    {
        return Listing::create(array_merge([
            'user_id'     => User::factory()->create()->id,
            'category_id' => $c->id,
            'title'       => 'Un livre',
            'condition'   => 'bon',
            'language'    => 'Français',
            'city'        => 'Cotonou',
            'type'        => 'vente',
            'price'       => 2000,
            'status'      => 'active',
        ], $attrs));
    }

    public function test_empty_categories_are_hidden_from_navigation(): void
    {
        $pleine = $this->category('roman', 'Romans');
        $this->category('vide', 'Catégorie vide');
        $this->listing($pleine);

        // Accueil
        $this->get('/')->assertInertia(fn (AssertableInertia $p) => $p
            ->has('categories', 1)
            ->where('categories.0.slug', 'roman')
            ->has('nav.categories', 1));

        // Filtres de l'explorateur
        $this->get('/explorer')->assertInertia(fn (AssertableInertia $p) => $p
            ->has('categories', 1)
            ->where('categories.0.slug', 'roman'));
    }

    public function test_publish_form_still_offers_every_category(): void
    {
        $pleine = $this->category('roman', 'Romans');
        $this->category('vide', 'Catégorie vide');
        $this->listing($pleine);

        // Sans cela, il serait impossible de publier le premier livre d'une catégorie.
        $this->actingAs(User::factory()->create())
            ->get('/publier')
            ->assertInertia(fn (AssertableInertia $p) => $p->has('categories', 2));
    }

    public function test_category_with_only_hidden_or_search_listings_is_not_browsable(): void
    {
        $recherchee = $this->category('recherche-only', 'Recherches seules');
        $masquee = $this->category('masquee', 'Masquée');

        // Une « recherche » est une demande, pas un livre disponible.
        $this->listing($recherchee, ['type' => 'recherche', 'price' => 0]);
        // Une annonce masquée par la modération ne compte pas non plus.
        $this->listing($masquee, ['status' => 'hidden']);

        $this->assertSame(0, Category::browsable()->count());
    }

    public function test_category_appears_as_soon_as_a_book_is_published(): void
    {
        $categorie = $this->category('manga', 'Mangas');

        // Au départ, elle est absente de la navigation.
        $this->get('/')->assertInertia(fn (AssertableInertia $p) => $p->has('nav.categories', 0));

        // Un membre y publie un livre : le cache de navigation doit être invalidé.
        $this->actingAs(User::factory()->create())->post('/livres', [
            'type'        => 'vente',
            'title'       => 'One Piece',
            'category_id' => $categorie->id,
            'condition'   => 'bon',
            'language'    => 'Français',
            'city'        => 'Cotonou',
            'price'       => 2500,
        ]);

        $this->get('/')->assertInertia(fn (AssertableInertia $p) => $p
            ->has('nav.categories', 1)
            ->where('nav.categories.0.slug', 'manga'));
    }
}
