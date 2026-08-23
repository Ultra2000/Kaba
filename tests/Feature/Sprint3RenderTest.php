<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\KabaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class Sprint3RenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_page_renders_with_data(): void
    {
        $user = User::factory()->create();
        $user->notify(new KabaNotification(['icon' => 'fa-bell', 'color' => 'text-brand-600 bg-brand-50', 'message' => 'Test']));

        $this->actingAs($user)->get('/notifications')
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Notifications')->has('notifications', 1));
    }

    public function test_mark_all_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new KabaNotification(['icon' => 'fa-bell', 'color' => 'x', 'message' => 'Test']));
        $this->assertEquals(1, $user->unreadNotifications()->count());

        $this->actingAs($user)->post('/notifications/lire');
        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_profile_page_renders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Profile/Edit'));
    }

    public function test_profile_update_saves_new_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/profile', [
            'name' => 'Nouveau Nom', 'email' => $user->email,
            'phone' => '+229 0100', 'city' => 'Parakou', 'bio' => 'Bonjour',
        ])->assertRedirect();

        $user->refresh();
        $this->assertEquals('Parakou', $user->city);
        $this->assertEquals('+229 0100', $user->phone);
    }
}
