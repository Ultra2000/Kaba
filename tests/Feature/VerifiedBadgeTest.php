<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Confirmer son adresse est volontaire : ça ne débloque aucun accès, ça donne le
 * badge « Vérifié », qui met les ventes en avant. Les dons et les échanges sont
 * mis en avant pour tout le monde, badge ou pas.
 */
class VerifiedBadgeTest extends TestCase
{
    use RefreshDatabase;

    private function listing(User $seller, string $type = 'vente', int $views = 0, array $attrs = []): Listing
    {
        $category = Category::firstOrCreate(['slug' => 'roman'], ['name' => 'Romans', 'icon' => 'fa-book']);

        return Listing::create(array_merge([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'title'       => ucfirst($type) . " de {$seller->name}",
            'condition'   => 'bon', 'language' => 'Français', 'city' => 'Cotonou',
            'type'        => $type, 'price' => $type === 'vente' ? 2000 : 0,
            'status'      => 'active', 'views' => $views,
        ], $attrs));
    }

    private function verifiedSeller(): User
    {
        return User::factory()->create(['is_verified' => true, 'email_verified_at' => now()]);
    }

    private function plainSeller(): User
    {
        return User::factory()->create(['is_verified' => false, 'email_verified_at' => null]);
    }

    /** Annonces du catalogue, dans l'ordre du tri demandé. */
    private function catalogue(string $sort = 'popular'): array
    {
        return Listing::withPromoted()->where('status', 'active')->sort($sort)->pluck('title')->all();
    }

    /* ---------- La confirmation ne bloque rien ---------- */

    public function test_registering_invites_the_member_to_confirm(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Aïcha', 'email' => 'aicha@example.test',
            'password' => 'motdepasse123', 'password_confirmation' => 'motdepasse123',
        ]);

        Notification::assertSentTo(User::firstWhere('email', 'aicha@example.test'), VerifyEmail::class);
    }

    public function test_an_unconfirmed_member_can_use_the_whole_site(): void
    {
        $member = $this->plainSeller();
        $someoneElse = $this->listing($this->verifiedSeller());

        $this->actingAs($member)->get('/dashboard')->assertOk();
        $this->actingAs($member)->get('/publier')->assertOk();

        $this->actingAs($member)->post('/livres', [
            'type' => 'vente', 'title' => 'Mon livre', 'condition' => 'bon',
            'category_id' => Category::firstWhere('slug', 'roman')->id,
            'language' => 'Français', 'city' => 'Cotonou', 'price' => 1500,
        ])->assertRedirect();

        $this->actingAs($member)->post("/panier/{$someoneElse->id}")->assertRedirect();
        $this->actingAs($member)->post("/messagerie/demarrer/{$someoneElse->id}")->assertRedirect();

        $this->assertDatabaseHas('listings', ['title' => 'Mon livre', 'user_id' => $member->id]);
    }

    /* ---------- La confirmation donne le badge ---------- */

    public function test_confirming_the_address_grants_the_badge(): void
    {
        $member = $this->plainSeller();

        $this->actingAs($member)->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $member->id, 'hash' => sha1($member->email),
        ]))->assertRedirect();

        $member->refresh();
        $this->assertNotNull($member->email_verified_at);
        $this->assertTrue($member->is_verified);
    }

    public function test_an_admin_can_still_revoke_the_badge(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $member = $this->verifiedSeller();

        $this->actingAs($admin)->post("/admin/utilisateurs/{$member->id}/verifier")->assertRedirect();

        $this->assertFalse($member->fresh()->is_verified);
    }

    /* ---------- Qui est mis en avant ---------- */

    public function test_gifts_and_swaps_are_promoted_without_any_badge(): void
    {
        $seller  = $this->plainSeller();
        $don     = $this->listing($seller, 'don');
        $echange = $this->listing($seller, 'echange');

        $promoted = Listing::withPromoted()->get()->keyBy('id');

        $this->assertTrue($promoted[$don->id]->is_promoted);
        $this->assertTrue($promoted[$echange->id]->is_promoted);
    }

    public function test_a_sale_is_promoted_only_when_the_seller_is_verified(): void
    {
        $ordinary = $this->listing($this->plainSeller(), 'vente');
        $badged   = $this->listing($this->verifiedSeller(), 'vente');

        $promoted = Listing::withPromoted()->get()->keyBy('id');

        $this->assertFalse($promoted[$ordinary->id]->is_promoted);
        $this->assertTrue($promoted[$badged->id]->is_promoted);
    }

    public function test_the_badge_lifts_the_sales_a_seller_had_already_published(): void
    {
        $seller = $this->plainSeller();
        $sale = $this->listing($seller, 'vente');

        $this->assertFalse(Listing::withPromoted()->find($sale->id)->is_promoted);

        $seller->update(['is_verified' => true]);

        $this->assertTrue(Listing::withPromoted()->find($sale->id)->is_promoted);
    }

    public function test_promoted_listings_come_first_even_with_fewer_views(): void
    {
        $plain = $this->plainSeller();
        $this->listing($plain, 'vente', views: 5000, attrs: ['title' => 'Vente très vue']);
        $this->listing($plain, 'don', views: 2, attrs: ['title' => 'Don peu vu']);
        $this->listing($this->verifiedSeller(), 'vente', views: 3, attrs: ['title' => 'Vente vérifiée']);

        $this->assertSame(
            ['Vente vérifiée', 'Don peu vu', 'Vente très vue'],
            $this->catalogue()
        );
    }

    public function test_an_unpromoted_listing_stays_visible(): void
    {
        $this->listing($this->plainSeller(), 'vente', attrs: ['title' => 'Vente ordinaire']);

        $this->assertContains('Vente ordinaire', $this->catalogue());
    }

    public function test_sorting_by_price_is_not_reshuffled_by_promotion(): void
    {
        $this->listing($this->plainSeller(), 'vente', attrs: ['title' => 'Moins chère', 'price' => 500]);
        $this->listing($this->verifiedSeller(), 'vente', attrs: ['title' => 'Plus chère', 'price' => 9000]);

        $this->assertSame(['Moins chère', 'Plus chère'], $this->catalogue('price-asc'));
    }

    /* ---------- Ce que le front reçoit ---------- */

    public function test_catalogue_exposes_the_badge_so_cards_can_show_it(): void
    {
        $this->listing($this->verifiedSeller(), 'vente');

        $this->get('/explorer')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->where('listings.data.0.is_promoted', true)
                ->where('listings.data.0.user.is_verified', true));
    }

    public function test_dashboard_invites_only_those_who_have_not_confirmed(): void
    {
        $this->actingAs($this->plainSeller())->get('/dashboard')
            ->assertInertia(fn (AssertableInertia $p) => $p->where('auth.user.email_verified_at', null));

        $this->actingAs($this->verifiedSeller())->get('/dashboard')
            ->assertInertia(fn (AssertableInertia $p) => $p->whereNot('auth.user.email_verified_at', null));
    }
}
