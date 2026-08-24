<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_a_matching_book_notifies_the_searcher(): void
    {
        $cat = Category::create(['name' => 'Dév. perso', 'slug' => 'dev-perso']);
        $searcher = User::factory()->create();
        $seller = User::factory()->create();

        // Le chercheur publie une annonce "recherche"
        Listing::create([
            'user_id' => $searcher->id, 'category_id' => $cat->id, 'title' => 'Atomic Habits',
            'condition' => 'bon', 'city' => 'Cotonou', 'type' => 'recherche', 'language' => 'Français', 'status' => 'active',
        ]);

        // Le vendeur publie le livre correspondant
        $this->actingAs($seller)->post('/livres', [
            'type' => 'vente', 'title' => 'Atomic Habits', 'category_id' => $cat->id,
            'condition' => 'bon', 'language' => 'Français', 'city' => 'Cotonou', 'price' => 5000,
        ]);

        $this->assertEquals(1, $searcher->fresh()->notifications()->count());
    }

    public function test_followers_are_notified_when_seller_publishes(): void
    {
        $seller = User::factory()->create(['name' => 'Aïcha K.']);
        $follower = User::factory()->create();
        $stranger = User::factory()->create();
        $category = Category::create(['name' => 'Romans', 'slug' => 'roman', 'icon' => 'fa-feather-pointed']);

        // Le follower suit le vendeur.
        $this->actingAs($follower)->post("/vendeurs/{$seller->id}/suivre");

        $this->actingAs($seller)->post('/livres', [
            'type'        => 'vente',
            'title'       => 'Sapiens',
            'category_id' => $category->id,
            'condition'   => 'bon',
            'language'    => 'Français',
            'city'        => 'Cotonou',
            'price'       => 5000,
        ]);

        // L'abonné est prévenu, pas les autres.
        $notification = $follower->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('follow', $notification->data['kind']);
        $this->assertStringContainsString('Sapiens', $notification->data['message']);
        $this->assertStringContainsString('Aïcha K.', $notification->data['message']);
        $this->assertSame(0, $stranger->notifications()->count());
        $this->assertSame(0, $seller->notifications()->count());
    }

    public function test_followers_are_not_notified_for_search_listings(): void
    {
        $seller = User::factory()->create();
        $follower = User::factory()->create();
        $category = Category::create(['name' => 'Romans', 'slug' => 'roman', 'icon' => 'fa-feather-pointed']);

        $this->actingAs($follower)->post("/vendeurs/{$seller->id}/suivre");

        $this->actingAs($seller)->post('/livres', [
            'type'        => 'recherche',
            'title'       => 'Un livre cherché',
            'category_id' => $category->id,
            'condition'   => 'bon',
            'language'    => 'Français',
            'city'        => 'Cotonou',
        ]);

        $this->assertSame(0, $follower->notifications()->count());
    }

    public function test_notifications_page_requires_auth(): void
    {
        $this->get('/notifications')->assertRedirect('/login');
    }
}
