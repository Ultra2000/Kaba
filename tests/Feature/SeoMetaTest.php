<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_default_open_graph_tags(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:site_name" content="KABA"', false)
            ->assertSee('name="twitter:card"', false);
    }

    public function test_listing_page_has_book_specific_open_graph_tags(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Romans', 'slug' => 'roman', 'icon' => 'fa-feather-pointed']);

        $listing = Listing::create([
            'user_id'     => $user->id,
            'category_id' => $category->id,
            'title'       => 'L\'Aventure ambiguë',
            'author'      => 'Cheikh Hamidou Kane',
            'condition'   => 'bon',
            'language'    => 'Français',
            'city'        => 'Cotonou',
            'type'        => 'vente',
            'price'       => 3000,
            'status'      => 'active',
            'description' => 'Un classique de la littérature africaine.',
        ]);

        $response = $this->get("/livres/{$listing->id}");

        $response->assertOk()
            ->assertSee('og:title', false)
            ->assertSee('L&#039;Aventure ambiguë', false)
            ->assertSee('3 000 FCFA', false)
            ->assertSee('og:type" content="product"', false);
    }
}
