<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    private function listingBy(User $seller): Listing
    {
        $cat = Category::create(['name' => 'Romans', 'slug' => 'roman']);
        return Listing::create([
            'user_id' => $seller->id, 'category_id' => $cat->id, 'title' => 'Test',
            'condition' => 'bon', 'city' => 'Cotonou', 'type' => 'vente', 'price' => 1000, 'language' => 'Français', 'status' => 'active',
        ]);
    }

    public function test_guest_redirected_from_messages(): void
    {
        $this->get('/messagerie')->assertRedirect('/login');
    }

    public function test_buyer_starts_a_conversation_from_listing(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();
        $listing = $this->listingBy($seller);

        $this->actingAs($buyer)->post("/messagerie/demarrer/{$listing->id}")->assertRedirect();
        $this->assertDatabaseHas('conversations', [
            'listing_id' => $listing->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
        ]);
    }

    public function test_cannot_message_own_listing(): void
    {
        $seller = User::factory()->create();
        $listing = $this->listingBy($seller);
        $this->actingAs($seller)->post("/messagerie/demarrer/{$listing->id}")->assertForbidden();
    }

    public function test_sending_a_message_notifies_the_other(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();
        $listing = $this->listingBy($seller);
        $conv = Conversation::create(['listing_id' => $listing->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id]);

        $this->actingAs($buyer)->post("/messagerie/{$conv->id}", ['body' => 'Bonjour !'])->assertRedirect();
        $this->assertDatabaseHas('messages', ['conversation_id' => $conv->id, 'sender_id' => $buyer->id, 'body' => 'Bonjour !']);
        $this->assertEquals(1, $seller->fresh()->notifications()->count());
    }

    public function test_non_participant_cannot_view_conversation(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();
        $intruder = User::factory()->create();
        $conv = Conversation::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);

        $this->actingAs($intruder)->get("/messagerie/{$conv->id}")->assertForbidden();
    }

    public function test_viewing_marks_received_messages_read(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();
        $conv = Conversation::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $conv->messages()->create(['sender_id' => $buyer->id, 'body' => 'Coucou']);

        $this->assertEquals(1, $seller->unreadMessagesCount());
        $this->actingAs($seller)->get("/messagerie/{$conv->id}")->assertOk();
        $this->assertEquals(0, $seller->fresh()->unreadMessagesCount());
    }

    public function test_show_conversation_with_listing_renders(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();
        $listing = $this->listingBy($seller);
        $conv = Conversation::create(['listing_id' => $listing->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $conv->messages()->create(['sender_id' => $buyer->id, 'body' => 'Salut']);

        $this->actingAs($seller)->get("/messagerie/{$conv->id}")->assertOk();
    }
}
