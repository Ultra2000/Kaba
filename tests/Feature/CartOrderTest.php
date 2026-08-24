<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class CartOrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeListing(User $seller, array $attrs = []): Listing
    {
        $category = Category::firstOrCreate(['slug' => 'roman'], ['name' => 'Romans', 'icon' => 'fa-feather-pointed']);

        return Listing::create(array_merge([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'title'       => 'Livre test',
            'condition'   => 'bon',
            'language'    => 'Français',
            'city'        => 'Cotonou',
            'type'        => 'vente',
            'price'       => 2000,
            'status'      => 'active',
        ], $attrs));
    }

    public function test_guest_redirected_from_cart(): void
    {
        $this->get('/panier')->assertRedirect('/login');
    }

    public function test_add_and_remove_cart_item(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->actingAs($buyer)->post("/panier/{$listing->id}")->assertRedirect();
        $this->assertTrue($buyer->cartListings()->where('listings.id', $listing->id)->exists());

        $this->actingAs($buyer)->delete("/panier/{$listing->id}")->assertRedirect();
        $this->assertFalse($buyer->cartListings()->where('listings.id', $listing->id)->exists());
    }

    public function test_cannot_add_own_listing(): void
    {
        $seller = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->actingAs($seller)->post("/panier/{$listing->id}")->assertForbidden();
    }

    public function test_cannot_add_recherche_listing(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller, ['type' => 'recherche', 'price' => 0]);

        $this->actingAs($buyer)->post("/panier/{$listing->id}")->assertStatus(422);
    }

    public function test_request_availability_creates_order_and_notifies_seller(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $a = $this->makeListing($seller, ['title' => 'Livre A', 'price' => 2000]);
        $b = $this->makeListing($seller, ['title' => 'Livre B', 'type' => 'don', 'price' => 0]);

        $this->actingAs($buyer)->post("/panier/{$a->id}");
        $this->actingAs($buyer)->post("/panier/{$b->id}");

        $this->actingAs($buyer)
            ->post("/demandes/vendeur/{$seller->id}", ['message' => 'Dispo ce week-end ?'])
            ->assertRedirect('/demandes');

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertSame(2, $order->items()->count());
        $this->assertSame(2000, $order->total());
        // Le panier est vidé pour ce vendeur.
        $this->assertSame(0, $buyer->cartListings()->count());
        // Le vendeur est notifié.
        $this->assertSame(1, $seller->notifications()->count());
    }

    public function test_seller_accepts_then_completes(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->actingAs($buyer)->post("/panier/{$listing->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();

        // Un tiers ne peut pas accepter.
        $other = User::factory()->create();
        $this->actingAs($other)->post("/demandes/{$order->id}/accepter")->assertForbidden();

        $this->actingAs($seller)->post("/demandes/{$order->id}/accepter")->assertRedirect();
        $this->assertSame('accepted', $order->fresh()->status);

        $this->actingAs($seller)->post("/demandes/{$order->id}/remise")->assertRedirect();
        $this->assertSame('completed', $order->fresh()->status);
        // L'acheteur a reçu les notifications (acceptée + remise).
        $this->assertSame(2, $buyer->notifications()->count());
    }

    public function test_buyer_can_cancel_pending_order(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->actingAs($buyer)->post("/panier/{$listing->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();

        $this->actingAs($buyer)->post("/demandes/{$order->id}/annuler")->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_orders_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/demandes')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Orders/Index')
                ->has('sent')
                ->has('received'));
    }

    public function test_cart_page_renders_grouped_by_seller(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);
        $this->actingAs($buyer)->post("/panier/{$listing->id}");

        $this->actingAs($buyer)->get('/panier')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Cart/Index')
                ->has('groups', 1));
    }
}
