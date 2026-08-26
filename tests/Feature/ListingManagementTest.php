<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ListingManagementTest extends TestCase
{
    use RefreshDatabase;

    private function listing(User $owner, array $attrs = []): Listing
    {
        $category = Category::firstOrCreate(['slug' => 'roman'], ['name' => 'Romans', 'icon' => 'fa-book']);

        return Listing::create(array_merge([
            'user_id' => $owner->id, 'category_id' => $category->id,
            'title' => 'Titre original', 'condition' => 'bon', 'language' => 'Français',
            'city' => 'Cotonou', 'type' => 'vente', 'price' => 3000, 'status' => 'active',
        ], $attrs));
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'type' => 'vente', 'title' => 'Titre corrigé',
            'category_id' => Category::firstWhere('slug', 'roman')->id,
            'condition' => 'tres_bon', 'language' => 'Français',
            'city' => 'Porto-Novo', 'price' => 2000,
        ], $override);
    }

    public function test_owner_can_open_the_edit_form(): void
    {
        $owner = User::factory()->create();
        $listing = $this->listing($owner);

        $this->actingAs($owner)->get("/livres/{$listing->id}/modifier")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->component('Listings/Edit')
                ->where('listing.id', $listing->id)
                ->has('categories'));
    }

    public function test_owner_can_update_their_listing(): void
    {
        $owner = User::factory()->create();
        $listing = $this->listing($owner);

        $this->actingAs($owner)
            ->post("/livres/{$listing->id}", $this->payload(['quantity' => 3]))
            ->assertRedirect("/livres/{$listing->id}");

        $listing->refresh();
        $this->assertSame('Titre corrigé', $listing->title);
        $this->assertSame(2000, $listing->price);
        $this->assertSame('Porto-Novo', $listing->city);
        $this->assertSame(3, $listing->quantity);
    }

    public function test_someone_else_cannot_touch_the_listing(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $listing = $this->listing($owner);

        $this->actingAs($stranger)->get("/livres/{$listing->id}/modifier")->assertForbidden();
        $this->actingAs($stranger)->post("/livres/{$listing->id}", $this->payload())->assertForbidden();
        $this->actingAs($stranger)->post("/livres/{$listing->id}/statut")->assertForbidden();
        $this->actingAs($stranger)->delete("/livres/{$listing->id}")->assertForbidden();

        $this->assertSame('Titre original', $listing->fresh()->title);
    }

    public function test_guest_is_redirected(): void
    {
        $listing = $this->listing(User::factory()->create());

        $this->get("/livres/{$listing->id}/modifier")->assertRedirect('/login');
    }

    public function test_admin_can_edit_any_listing(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $listing = $this->listing($owner);

        $this->actingAs($admin)->get("/livres/{$listing->id}/modifier")->assertOk();
    }

    public function test_owner_can_mark_sold_then_republish(): void
    {
        $owner = User::factory()->create();
        $listing = $this->listing($owner, ['quantity' => 0]);

        $this->actingAs($owner)->post("/livres/{$listing->id}/statut")->assertRedirect();
        $this->assertSame('sold', $listing->fresh()->status);

        // Remise en ligne : au moins un exemplaire, sinon l'annonce serait invendable.
        $this->actingAs($owner)->post("/livres/{$listing->id}/statut")->assertRedirect();
        $listing->refresh();
        $this->assertSame('active', $listing->status);
        $this->assertSame(1, $listing->quantity);
    }

    public function test_owner_can_add_and_remove_photos(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $listing = $this->listing($owner);
        $listing->photos()->create(['path' => 'listings/ancienne.webp', 'position' => 0]);
        Storage::disk('public')->put('listings/ancienne.webp', 'contenu');

        $old = $listing->photos()->first();

        $this->actingAs($owner)->post("/livres/{$listing->id}", $this->payload([
            'photos'        => [UploadedFile::fake()->image('nouvelle.jpg', 800, 600)],
            'remove_photos' => [$old->id],
        ]))->assertRedirect();

        $listing->refresh();
        $this->assertSame(1, $listing->photos()->count());
        $this->assertNotSame($old->id, $listing->photos()->first()->id);
        // Le fichier retiré est effacé du disque, pas seulement de la base.
        Storage::disk('public')->assertMissing('listings/ancienne.webp');
    }

    public function test_deleting_a_listing_removes_its_files(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $listing = $this->listing($owner);
        $listing->photos()->create(['path' => 'listings/photo.webp', 'position' => 0]);
        Storage::disk('public')->put('listings/photo.webp', 'contenu');

        $this->actingAs($owner)->delete("/livres/{$listing->id}")->assertRedirect('/dashboard');

        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
        Storage::disk('public')->assertMissing('listings/photo.webp');
    }

    public function test_update_is_validated(): void
    {
        $owner = User::factory()->create();
        $listing = $this->listing($owner);

        $this->actingAs($owner)
            ->post("/livres/{$listing->id}", $this->payload(['title' => '', 'price' => null]))
            ->assertSessionHasErrors(['title', 'price']);
    }
}
