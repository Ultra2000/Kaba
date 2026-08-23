<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustTest extends TestCase
{
    use RefreshDatabase;

    private function listing(User $seller): Listing
    {
        $cat = Category::create(['name' => 'Romans', 'slug' => 'roman']);
        return Listing::create([
            'user_id' => $seller->id, 'category_id' => $cat->id, 'title' => 'Test',
            'condition' => 'bon', 'city' => 'Cotonou', 'type' => 'vente', 'price' => 1000,
            'language' => 'Français', 'status' => 'active',
        ]);
    }

    public function test_seller_pages_are_public(): void
    {
        $seller = User::factory()->create();
        $this->listing($seller);
        $this->get('/vendeurs')->assertOk();
        $this->get('/vendeurs/'.$seller->id)->assertOk();
    }

    public function test_favorite_toggle(): void
    {
        $u = User::factory()->create();
        $l = $this->listing(User::factory()->create());

        $this->actingAs($u)->post('/favoris/'.$l->id);
        $this->assertDatabaseHas('favorites', ['user_id' => $u->id, 'listing_id' => $l->id]);

        $this->actingAs($u)->post('/favoris/'.$l->id);
        $this->assertDatabaseMissing('favorites', ['user_id' => $u->id, 'listing_id' => $l->id]);
    }

    public function test_follow_toggle(): void
    {
        $u = User::factory()->create();
        $seller = User::factory()->create();
        $this->actingAs($u)->post('/vendeurs/'.$seller->id.'/suivre');
        $this->assertDatabaseHas('follows', ['follower_id' => $u->id, 'seller_id' => $seller->id]);
    }

    public function test_review_updates_seller_rating(): void
    {
        $u = User::factory()->create();
        $seller = User::factory()->create();
        $this->actingAs($u)->post('/vendeurs/'.$seller->id.'/avis', ['rating' => 4, 'comment' => 'Bien']);
        $this->assertDatabaseHas('reviews', ['author_id' => $u->id, 'seller_id' => $seller->id, 'rating' => 4]);
        $this->assertEquals('4.0', $seller->fresh()->rating_avg);
    }

    public function test_report_a_listing(): void
    {
        $u = User::factory()->create();
        $l = $this->listing(User::factory()->create());
        $this->actingAs($u)->post('/livres/'.$l->id.'/signaler', ['reason' => 'arnaque']);
        $this->assertDatabaseHas('reports', ['reporter_id' => $u->id, 'reportable_id' => $l->id, 'reason' => 'arnaque']);
    }
}
