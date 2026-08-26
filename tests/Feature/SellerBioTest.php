<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SellerBioTest extends TestCase
{
    use RefreshDatabase;

    public function test_bio_saved_from_profile_reaches_the_public_page(): void
    {
        $user = User::factory()->create();
        $bio = 'Étudiante en droit à Cotonou, je revends mes manuels après chaque semestre.';

        // Le membre renseigne sa présentation…
        $this->actingAs($user)->patch('/profile', [
            'name'  => $user->name,
            'email' => $user->email,
            'bio'   => $bio,
        ])->assertRedirect();

        $this->assertSame($bio, $user->fresh()->bio);

        // …et elle doit être visible sur son profil public (sinon elle ne sert à rien).
        $this->get("/vendeurs/{$user->id}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $p) => $p
                ->component('Sellers/Show')
                ->where('seller.bio', $bio));
    }

    public function test_bio_is_limited_to_500_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name'  => $user->name,
            'email' => $user->email,
            'bio'   => str_repeat('a', 501),
        ])->assertSessionHasErrors('bio');
    }
}
