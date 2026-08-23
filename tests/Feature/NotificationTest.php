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

    public function test_notifications_page_requires_auth(): void
    {
        $this->get('/notifications')->assertRedirect('/login');
    }
}
