<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ListingStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_publish_page(): void
    {
        $this->get('/publier')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_publish_a_listing_with_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::create(['name' => 'Romans', 'slug' => 'roman', 'icon' => 'fa-feather-pointed']);

        $response = $this->actingAs($user)->post('/livres', [
            'type'        => 'vente',
            'title'       => 'Livre de test',
            'author'      => 'Auteur Test',
            'category_id' => $category->id,
            'condition'   => 'bon',
            'language'    => 'Français',
            'city'        => 'Cotonou',
            'price'       => 2500,
            'description' => 'Un livre en bon état.',
            'photos'      => [
                UploadedFile::fake()->image('cover1.jpg'),
                UploadedFile::fake()->image('cover2.jpg'),
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('listings', [
            'title'   => 'Livre de test',
            'user_id' => $user->id,
            'type'    => 'vente',
            'price'   => 2500,
        ]);
        $this->assertDatabaseCount('listing_photos', 2);
    }

    public function test_listing_page_receives_all_condition_photos_in_order(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::create(['name' => 'Romans', 'slug' => 'roman', 'icon' => 'fa-feather-pointed']);

        $this->actingAs($user)->post('/livres', [
            'type'        => 'vente',
            'title'       => 'Livre avec défauts',
            'category_id' => $category->id,
            'condition'   => 'moyen',
            'language'    => 'Français',
            'city'        => 'Cotonou',
            'price'       => 1500,
            'photos'      => [
                UploadedFile::fake()->image('couverture.jpg'),
                UploadedFile::fake()->image('tranche.jpg'),
                UploadedFile::fake()->image('page-cornee.jpg'),
            ],
        ]);

        $listing = \App\Models\Listing::firstWhere('title', 'Livre avec défauts');
        $this->assertSame(3, $listing->photos()->count());

        // La fiche reçoit bien les 3 photos, dans l'ordre d'envoi (galerie).
        $this->get("/livres/{$listing->id}")
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Listings/Show')
                ->has('listing.photos', 3)
                ->where('listing.photos.0.position', 0)
                ->where('listing.photos.2.position', 2));
    }

    public function test_price_is_required_for_a_sale(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Romans', 'slug' => 'roman']);

        $this->actingAs($user)
            ->post('/livres', [
                'type'        => 'vente',
                'title'       => 'Sans prix',
                'category_id' => $category->id,
                'condition'   => 'bon',
                'language'    => 'Français',
                'city'        => 'Cotonou',
            ])
            ->assertSessionHasErrors('price');
    }
}
