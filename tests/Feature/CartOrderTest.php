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

    public function test_seller_can_respond_per_item(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $a = $this->makeListing($seller, ['title' => 'Livre A', 'price' => 2000]);
        $b = $this->makeListing($seller, ['title' => 'Livre B', 'price' => 3000]);

        $this->actingAs($buyer)->post("/panier/{$a->id}");
        $this->actingAs($buyer)->post("/panier/{$b->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();
        [$itemA, $itemB] = $order->items()->orderBy('id')->get();

        // Accepte A : la demande reste en attente (B non répondu), pas encore de notification.
        $this->actingAs($seller)->post("/demandes/{$order->id}/livres/{$itemA->id}/accepter")->assertRedirect();
        $this->assertSame('accepted', $itemA->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, $buyer->notifications()->count());

        // Refuse B : la demande passe à acceptée (1 dispo sur 2) + notification.
        $this->actingAs($seller)->post("/demandes/{$order->id}/livres/{$itemB->id}/refuser")->assertRedirect();
        $this->assertSame('declined', $itemB->fresh()->status);
        $this->assertSame('accepted', $order->fresh()->status);
        $this->assertSame(1, $buyer->notifications()->count());

        // Le total ne compte que les livres disponibles.
        $this->assertSame(2000, $order->fresh()->load('items')->total());
    }

    public function test_all_items_declined_declines_order(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $a = $this->makeListing($seller, ['price' => 2000]);

        $this->actingAs($buyer)->post("/panier/{$a->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();
        $item = $order->items()->first();

        $this->actingAs($seller)->post("/demandes/{$order->id}/livres/{$item->id}/refuser");
        $this->assertSame('declined', $order->fresh()->status);
    }

    public function test_global_accept_sets_all_pending_items_accepted(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $a = $this->makeListing($seller, ['price' => 2000]);
        $b = $this->makeListing($seller, ['price' => 3000]);

        $this->actingAs($buyer)->post("/panier/{$a->id}");
        $this->actingAs($buyer)->post("/panier/{$b->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();

        $this->actingAs($seller)->post("/demandes/{$order->id}/accepter");
        $this->assertSame('accepted', $order->fresh()->status);
        $this->assertSame(2, $order->items()->where('status', 'accepted')->count());
    }

    public function test_non_seller_cannot_respond_item(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $a = $this->makeListing($seller, ['price' => 2000]);

        $this->actingAs($buyer)->post("/panier/{$a->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();
        $item = $order->items()->first();

        $this->actingAs($buyer)->post("/demandes/{$order->id}/livres/{$item->id}/accepter")->assertForbidden();
    }

    public function test_buyer_can_discuss_after_seller_responds(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $a = $this->makeListing($seller, ['title' => 'Livre A', 'price' => 2000]);
        $b = $this->makeListing($seller, ['title' => 'Livre B', 'price' => 3000]);

        $this->actingAs($buyer)->post("/panier/{$a->id}");
        $this->actingAs($buyer)->post("/panier/{$b->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();

        [$itemA, $itemB] = $order->items()->orderBy('id')->get();
        $this->actingAs($seller)->post("/demandes/{$order->id}/livres/{$itemA->id}/accepter");
        $this->actingAs($seller)->post("/demandes/{$order->id}/livres/{$itemB->id}/refuser");

        // L'acheteur ouvre la discussion : conversation créée, amorcée avec le récap des dispo.
        $response = $this->actingAs($buyer)->post("/demandes/{$order->id}/discuter");
        $conversation = \App\Models\Conversation::first();
        $this->assertNotNull($conversation);
        $response->assertRedirect(route('messages.show', $conversation));

        $first = $conversation->messages()->first();
        $this->assertSame($buyer->id, $first->sender_id);
        $this->assertStringContainsString('Livre A', $first->body);
        $this->assertStringNotContainsString('Livre B', $first->body); // refusé → pas dans le récap

        // Réutilise la même conversation au 2e clic (pas de doublon).
        $this->actingAs($buyer)->post("/demandes/{$order->id}/discuter");
        $this->assertSame(1, \App\Models\Conversation::count());

        // Le vendeur peut aussi ouvrir la discussion.
        $this->actingAs($seller)->post("/demandes/{$order->id}/discuter")
            ->assertRedirect(route('messages.show', $conversation));

        // Un tiers non.
        $other = User::factory()->create();
        $this->actingAs($other)->post("/demandes/{$order->id}/discuter")->assertForbidden();
    }

    public function test_completed_order_marks_single_copy_listing_sold(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $single = $this->makeListing($seller, ['title' => 'Exemplaire unique', 'quantity' => 1]);
        $multi  = $this->makeListing($seller, ['title' => 'Trois exemplaires', 'quantity' => 3]);
        $refused = $this->makeListing($seller, ['title' => 'Refusé', 'quantity' => 1]);

        foreach ([$single, $multi, $refused] as $l) {
            $this->actingAs($buyer)->post("/panier/{$l->id}");
        }
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();

        // Le vendeur refuse un livre puis accepte le reste.
        $refusedItem = $order->items()->where('listing_id', $refused->id)->first();
        $this->actingAs($seller)->post("/demandes/{$order->id}/livres/{$refusedItem->id}/refuser");
        $this->actingAs($seller)->post("/demandes/{$order->id}/accepter");
        $this->actingAs($seller)->post("/demandes/{$order->id}/remise");

        // Exemplaire unique → vendu ; multi-exemplaires → toujours actif, stock -1 ; refusé → intact.
        $this->assertSame('sold', $single->fresh()->status);
        $this->assertSame(0, $single->fresh()->quantity);
        $this->assertSame('active', $multi->fresh()->status);
        $this->assertSame(2, $multi->fresh()->quantity);
        $this->assertSame('active', $refused->fresh()->status);
        $this->assertSame(1, $refused->fresh()->quantity);
    }

    public function test_buyer_can_make_price_offers(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $vente = $this->makeListing($seller, ['title' => 'Vente', 'price' => 3000]);
        $don   = $this->makeListing($seller, ['title' => 'Don', 'type' => 'don', 'price' => 0]);
        $cher  = $this->makeListing($seller, ['title' => 'Cher', 'price' => 5000]);

        foreach ([$vente, $don, $cher] as $l) {
            $this->actingAs($buyer)->post("/panier/{$l->id}");
        }

        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}", [
            'offers' => [
                $vente->id => 2000,  // offre valide (< prix)
                $don->id   => 1000,  // ignorée : ce n'est pas une vente
                $cher->id  => 6000,  // ignorée : ≥ prix affiché
            ],
        ]);

        $order = Order::first();
        $itemVente = $order->items()->where('listing_id', $vente->id)->first();
        $itemDon   = $order->items()->where('listing_id', $don->id)->first();
        $itemCher  = $order->items()->where('listing_id', $cher->id)->first();

        $this->assertSame(2000, $itemVente->offered_price);
        $this->assertNull($itemDon->offered_price);
        $this->assertNull($itemCher->offered_price);

        // Total au prix convenu : 2000 (offre) + 0 (don) + 5000 (prix affiché) = 7000.
        $this->assertSame(7000, $order->fresh()->load('items')->total());
        // La notification mentionne l'offre.
        $this->assertStringContainsString('offre de prix', $seller->notifications()->first()->data['message']);
    }

    public function test_completion_increments_seller_sales_count(): void
    {
        $seller = User::factory()->create(['sales_count' => 4]);
        $buyer = User::factory()->create();
        $a = $this->makeListing($seller, ['price' => 2000]);
        $b = $this->makeListing($seller, ['price' => 3000]);
        $refused = $this->makeListing($seller, ['price' => 1000]);

        foreach ([$a, $b, $refused] as $l) {
            $this->actingAs($buyer)->post("/panier/{$l->id}");
        }
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();

        $refusedItem = $order->items()->where('listing_id', $refused->id)->first();
        $this->actingAs($seller)->post("/demandes/{$order->id}/livres/{$refusedItem->id}/refuser");
        $this->actingAs($seller)->post("/demandes/{$order->id}/accepter");
        $this->actingAs($seller)->post("/demandes/{$order->id}/remise");

        // 2 livres remis (le refusé ne compte pas) → 4 + 2 = 6.
        $this->assertSame(6, $seller->fresh()->sales_count);
    }

    public function test_both_parties_can_review_after_completion(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller, ['price' => 2000]);

        $this->actingAs($buyer)->post("/panier/{$listing->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();

        // Avant la remise : refusé.
        $this->actingAs($buyer)->post("/demandes/{$order->id}/avis", ['rating' => 5])->assertStatus(422);

        $this->actingAs($seller)->post("/demandes/{$order->id}/accepter");
        $this->actingAs($seller)->post("/demandes/{$order->id}/remise");

        // L'acheteur évalue le vendeur.
        $this->actingAs($buyer)->post("/demandes/{$order->id}/avis", [
            'rating' => 5, 'comment' => 'Livre conforme, vendeur ponctuel.',
        ])->assertRedirect();

        $review = \App\Models\Review::where('author_id', $buyer->id)->first();
        $this->assertSame($seller->id, $review->seller_id);
        $this->assertSame($order->id, $review->order_id);
        $this->assertSame(5.0, (float) $seller->fresh()->rating_avg);

        // Le vendeur évalue l'acheteur.
        $this->actingAs($seller)->post("/demandes/{$order->id}/avis", [
            'rating' => 4, 'comment' => 'Acheteur sérieux.',
        ])->assertRedirect();

        $back = \App\Models\Review::where('author_id', $seller->id)->first();
        $this->assertSame($buyer->id, $back->seller_id);
        $this->assertSame(4.0, (float) $buyer->fresh()->rating_avg);

        // Chacun a été notifié de l'avis reçu.
        $this->assertTrue($seller->notifications()->get()
            ->contains(fn ($n) => ($n->data['kind'] ?? null) === 'review'));

        // Un tiers ne peut pas évaluer cette transaction.
        $other = User::factory()->create();
        $this->actingAs($other)->post("/demandes/{$order->id}/avis", ['rating' => 1])->assertForbidden();
    }

    public function test_orders_page_exposes_reviewed_ids(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller, ['price' => 2000]);

        $this->actingAs($buyer)->post("/panier/{$listing->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");
        $order = Order::first();
        $this->actingAs($seller)->post("/demandes/{$order->id}/accepter");
        $this->actingAs($seller)->post("/demandes/{$order->id}/remise");
        $this->actingAs($buyer)->post("/demandes/{$order->id}/avis", ['rating' => 5]);

        $this->actingAs($buyer)->get('/demandes')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Orders/Index')
                ->has('reviewed', 1));
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
