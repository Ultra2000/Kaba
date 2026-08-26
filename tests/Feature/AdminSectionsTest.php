<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AdminSectionsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function listing(User $seller, array $attrs = []): Listing
    {
        $category = Category::firstOrCreate(['slug' => 'roman'], ['name' => 'Romans', 'icon' => 'fa-book']);

        return Listing::create(array_merge([
            'user_id' => $seller->id, 'category_id' => $category->id,
            'title' => 'Un livre', 'condition' => 'bon', 'language' => 'Français',
            'city' => 'Cotonou', 'type' => 'vente', 'price' => 2000, 'status' => 'active',
        ], $attrs));
    }

    public function test_dashboard_exposes_actionable_data(): void
    {
        $seller = User::factory()->create();
        $this->listing($seller, ['status' => 'pending']);

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->component('Admin/Overview')
                ->has('stats.users')->has('stats.orders')->has('stats.completed')
                ->has('trends.users.percent')
                ->where('todo.pendingListings', 1)
                ->has('activity')
                ->has('topCities')
                ->has('repartition', 4));
    }

    public function test_listings_are_searchable_and_filterable(): void
    {
        $seller = User::factory()->create(['name' => 'Aïcha K.']);
        $this->listing($seller, ['title' => 'Sapiens']);
        $this->listing($seller, ['title' => 'Le Petit Prince', 'status' => 'hidden']);

        $admin = $this->admin();

        // Recherche par titre
        $this->actingAs($admin)->get('/admin/annonces?q=Sapiens')
            ->assertInertia(fn (AssertableInertia $p) => $p->has('listings.data', 1)
                ->where('listings.data.0.title', 'Sapiens'));

        // Recherche par nom de vendeur
        $this->actingAs($admin)->get('/admin/annonces?q=Aïcha')
            ->assertInertia(fn (AssertableInertia $p) => $p->has('listings.data', 2));

        // Filtre par statut
        $this->actingAs($admin)->get('/admin/annonces?status=hidden')
            ->assertInertia(fn (AssertableInertia $p) => $p->has('listings.data', 1)
                ->where('counts.hidden', 1));
    }

    public function test_listings_are_paginated(): void
    {
        $seller = User::factory()->create();
        for ($i = 0; $i < 25; $i++) {
            $this->listing($seller, ['title' => "Livre {$i}"]);
        }

        // Sans pagination, l'admin s'effondrerait sur un gros catalogue.
        $this->actingAs($this->admin())->get('/admin/annonces')
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->has('listings.data', 20)
                ->where('listings.total', 25)
                ->where('listings.last_page', 2));
    }

    public function test_orders_section_lists_transactions(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->listing($seller);

        $this->actingAs($buyer)->post("/panier/{$listing->id}");
        $this->actingAs($buyer)->post("/demandes/vendeur/{$seller->id}");

        $this->actingAs($this->admin())->get('/admin/demandes')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->component('Admin/Orders')
                ->has('orders.data', 1)
                ->where('orders.data.0.status', 'pending')
                ->where('orders.data.0.total', 2000));

        // Filtre par statut
        $this->actingAs($this->admin())->get('/admin/demandes?status=completed')
            ->assertInertia(fn (AssertableInertia $p) => $p->has('orders.data', 0));
    }

    public function test_admin_can_moderate_reviews(): void
    {
        $author = User::factory()->create();
        $seller = User::factory()->create();
        $review = Review::create([
            'author_id' => $author->id, 'seller_id' => $seller->id,
            'rating' => 1, 'comment' => 'Commentaire injurieux',
        ]);
        $seller->recalcRating();
        $this->assertSame(1.0, (float) $seller->fresh()->rating_avg);

        $this->actingAs($this->admin())->get('/admin/avis')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->component('Admin/Reviews')
                ->has('reviews.data', 1)
                ->has('average'));

        // Suppression : la note du membre évalué est recalculée.
        $this->actingAs($this->admin())->delete("/admin/avis/{$review->id}")->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertSame(0.0, (float) $seller->fresh()->rating_avg);
    }

    public function test_reports_are_filtered_by_status(): void
    {
        $reporter = User::factory()->create();
        $seller = User::factory()->create();
        $listing = $this->listing($seller);

        $this->actingAs($reporter)->post("/livres/{$listing->id}/signaler", [
            'reason' => 'arnaque', 'details' => 'Suspect',
        ]);

        // Par défaut, on ne voit que ce qui reste à traiter.
        $this->actingAs($this->admin())->get('/admin/signalements')
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->has('reports.data', 1)
                ->where('counts.open', 1)
                // L'annonce visée est identifiée pour pouvoir agir directement.
                ->where('reports.data.0.listing_id', $listing->id)
                ->where('reports.data.0.listing_hidden', false));

        $this->actingAs($this->admin())->get('/admin/signalements?status=resolved')
            ->assertInertia(fn (AssertableInertia $p) => $p->has('reports.data', 0));
    }

    public function test_users_are_searchable_and_paginated(): void
    {
        User::factory()->create(['name' => 'Koffi Ahouansou', 'city' => 'Parakou']);
        User::factory()->count(24)->create();

        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/utilisateurs?q=Koffi')
            ->assertInertia(fn (AssertableInertia $p) => $p->has('users.data', 1)
                ->where('users.data.0.name', 'Koffi Ahouansou'));

        $this->actingAs($admin)->get('/admin/utilisateurs')
            ->assertInertia(fn (AssertableInertia $p) => $p->has('users.data', 20));
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $seller = User::factory()->create();
        $listing = $this->listing($seller);
        $category = $listing->category;

        // Supprimer une catégorie utilisée casserait les annonces liées.
        $this->actingAs($this->admin())->delete("/admin/categories/{$category->id}")
            ->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        // Une catégorie vide se supprime normalement.
        $empty = Category::create(['name' => 'Vide', 'slug' => 'vide', 'icon' => 'fa-book']);
        $this->actingAs($this->admin())->delete("/admin/categories/{$empty->id}")
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('categories', ['id' => $empty->id]);
    }

    public function test_admin_sections_are_protected(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        foreach (['/admin', '/admin/demandes', '/admin/avis', '/admin/annonces', '/admin/utilisateurs'] as $uri) {
            $this->actingAs($member)->get($uri)->assertForbidden();
        }
    }
}
