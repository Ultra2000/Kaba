<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Les données partagées à chaque page (cœur, panier, suivi, badges) coûtaient
 * six requêtes par chargement. Ces tests verrouillent le coût obtenu et, surtout,
 * la fraîcheur : un compteur ou un cœur en retard est un bug visible.
 */
class SharedStateTest extends TestCase
{
    use RefreshDatabase;

    private function listing(?User $seller = null, array $attrs = []): Listing
    {
        $category = Category::firstOrCreate(['slug' => 'roman'], ['name' => 'Romans', 'icon' => 'fa-book']);

        return Listing::create(array_merge([
            'user_id'     => ($seller ?? User::factory()->create())->id,
            'category_id' => $category->id,
            'title'       => 'Test', 'condition' => 'bon', 'language' => 'Français',
            'city'        => 'Cotonou', 'type' => 'vente', 'price' => 1000, 'status' => 'active',
        ], $attrs));
    }

    /** Requêtes SQL émises pendant l'exécution de l'action. */
    private function queriesFor(callable $action): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $action();
        $log = DB::getQueryLog();
        DB::disableQueryLog();

        return $log;
    }

    /** État d'interaction et badges tels que le front les reçoit. */
    private function sharedAuth(User $user): array
    {
        $auth = [];
        $this->actingAs($user)->get('/explorer')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (&$auth) {
                $auth = $page->toArray()['props']['auth'];
            });

        return $auth;
    }

    /* ---------- Coût ---------- */

    public function test_all_badge_counters_come_from_a_single_query(): void
    {
        $user = User::factory()->create();

        $this->assertCount(1, $this->queriesFor(fn () => $user->badgeCounts()));
    }

    public function test_admin_moderation_counters_stay_in_the_same_query(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $counts = null;
        $queries = $this->queriesFor(function () use ($admin, &$counts) {
            $counts = $admin->badgeCounts();
        });

        $this->assertCount(1, $queries);
        $this->assertArrayHasKey('reports', $counts);
        $this->assertArrayHasKey('pendingListings', $counts);
    }

    public function test_interaction_lists_are_read_from_cache_on_repeat_calls(): void
    {
        $user = User::factory()->create();

        $this->queriesFor(fn () => $user->interactionIds());

        $this->assertCount(0, $this->queriesFor(fn () => $user->interactionIds()));
    }

    /* ---------- Exactitude des compteurs ---------- */

    public function test_counters_report_the_real_numbers(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        $seller->notify(new \App\Notifications\KabaNotification(['message' => 'Coucou', 'url' => '/']));

        $conversation = Conversation::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $conversation->messages()->create(['sender_id' => $buyer->id, 'body' => 'Bonjour']);

        Order::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id, 'status' => 'pending']);

        $counts = $seller->badgeCounts();

        $this->assertSame(1, $counts['unread']);
        $this->assertSame(1, $counts['unreadMessages']);
        $this->assertSame(1, $counts['pendingOrders']);
    }

    public function test_a_members_own_messages_are_not_counted_as_unread(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        $conversation = Conversation::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $conversation->messages()->create(['sender_id' => $seller->id, 'body' => 'Je réponds']);

        $this->assertSame(0, $seller->badgeCounts()['unreadMessages']);
    }

    public function test_admin_counters_match_the_moderation_queue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->listing(null, ['status' => 'pending']);
        Report::create([
            'reporter_id'     => User::factory()->create()->id,
            'reportable_type' => Listing::class,
            'reportable_id'   => $this->listing()->id,
            'reason'          => 'arnaque',
            'status'          => 'open',
        ]);

        $counts = $admin->badgeCounts();

        $this->assertSame(1, $counts['reports']);
        $this->assertSame(1, $counts['pendingListings']);
    }

    /* ---------- Fraîcheur : le cache ne doit jamais retarder l'interface ---------- */

    public function test_adding_and_removing_a_favorite_shows_up_at_once(): void
    {
        $user = User::factory()->create();
        $listing = $this->listing();

        // Première visite : le cache se remplit sans le favori.
        $this->assertSame([], $this->sharedAuth($user)['favorites']);

        $this->actingAs($user)->post("/favoris/{$listing->id}")->assertRedirect();
        $this->assertSame([$listing->id], $this->sharedAuth($user)['favorites']);

        $this->actingAs($user)->post("/favoris/{$listing->id}")->assertRedirect();
        $this->assertSame([], $this->sharedAuth($user)['favorites']);
    }

    public function test_cart_changes_show_up_at_once(): void
    {
        $user = User::factory()->create();
        $listing = $this->listing();

        $this->assertSame([], $this->sharedAuth($user)['cart']);

        $this->actingAs($user)->post("/panier/{$listing->id}")->assertRedirect();
        $this->assertSame([$listing->id], $this->sharedAuth($user)['cart']);

        $this->actingAs($user)->delete("/panier/{$listing->id}")->assertRedirect();
        $this->assertSame([], $this->sharedAuth($user)['cart']);
    }

    public function test_following_a_seller_shows_up_at_once(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();

        $this->assertSame([], $this->sharedAuth($user)['following']);

        $this->actingAs($user)->post("/vendeurs/{$seller->id}/suivre")->assertRedirect();
        $this->assertSame([$seller->id], $this->sharedAuth($user)['following']);

        $this->actingAs($user)->post("/vendeurs/{$seller->id}/suivre")->assertRedirect();
        $this->assertSame([], $this->sharedAuth($user)['following']);
    }

    public function test_requesting_availability_empties_the_shared_cart(): void
    {
        $buyer  = User::factory()->create();
        $seller = User::factory()->create();
        $listing = $this->listing($seller);

        $this->actingAs($buyer)->post("/panier/{$listing->id}")->assertRedirect();
        $this->assertSame([$listing->id], $this->sharedAuth($buyer)['cart']);

        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}")->assertRedirect();

        $this->assertSame([], $this->sharedAuth($buyer)['cart']);
    }

    public function test_a_new_request_lights_up_the_sellers_badge_immediately(): void
    {
        $buyer  = User::factory()->create();
        $seller = User::factory()->create();
        $listing = $this->listing($seller);

        $this->assertSame(0, $this->sharedAuth($seller)['pendingOrders']);

        $this->actingAs($buyer)->post("/panier/{$listing->id}")->assertRedirect();
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}")->assertRedirect();

        $this->assertSame(1, $this->sharedAuth($seller)['pendingOrders']);
    }

    public function test_one_members_cache_never_leaks_into_another(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $listing = $this->listing();

        $this->actingAs($alice)->post("/favoris/{$listing->id}")->assertRedirect();

        $this->assertSame([$listing->id], $this->sharedAuth($alice)['favorites']);
        $this->assertSame([], $this->sharedAuth($bob)['favorites']);
    }
}
