<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_a_user(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/utilisateurs', [
                'name'        => 'Librairie du Port',
                'email'       => 'libraire@kaba.bj',
                'password'    => 'motdepasse123',
                'role'        => 'pro',
                'city'        => 'Cotonou',
                'is_verified' => true,
            ])
            ->assertRedirect();

        $user = User::firstWhere('email', 'libraire@kaba.bj');
        $this->assertNotNull($user);
        $this->assertSame('pro', $user->role);
        $this->assertTrue((bool) $user->is_verified);
        $this->assertTrue(Hash::check('motdepasse123', $user->password));
        // Compte créé par un admin : pas de vérification d'e-mail à demander.
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_created_user_can_log_in(): void
    {
        $this->actingAs($this->admin())->post('/admin/utilisateurs', [
            'name' => 'Nouveau', 'email' => 'nouveau@kaba.bj',
            'password' => 'motdepasse123', 'role' => 'user',
        ]);

        auth()->logout();

        $this->post('/login', ['email' => 'nouveau@kaba.bj', 'password' => 'motdepasse123'])
            ->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_user_creation_is_validated(): void
    {
        $existing = User::factory()->create(['email' => 'pris@kaba.bj']);

        $this->actingAs($this->admin())
            ->post('/admin/utilisateurs', [
                'name' => '', 'email' => 'pris@kaba.bj', 'password' => 'court', 'role' => 'roi',
            ])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_admin_can_change_a_user_role(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($this->admin())
            ->post("/admin/utilisateurs/{$member->id}/statut", ['role' => 'pro'])
            ->assertRedirect();

        $this->assertSame('pro', $member->fresh()->role);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post("/admin/utilisateurs/{$admin->id}/statut", ['role' => 'user'])
            ->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_last_admin_cannot_be_demoted(): void
    {
        $admin = $this->admin();          // le seul administrateur
        $other = User::factory()->create(['role' => 'admin']);

        // Deux admins : la rétrogradation de l'autre passe.
        $this->actingAs($admin)->post("/admin/utilisateurs/{$other->id}/statut", ['role' => 'user'])
            ->assertSessionHasNoErrors();
        $this->assertSame('user', $other->fresh()->role);

        // Il n'en reste qu'un : on ne peut plus le rétrograder.
        $this->actingAs($other)->post("/admin/utilisateurs/{$admin->id}/statut", ['role' => 'user'])
            ->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create();

        $this->actingAs($member)->post('/admin/utilisateurs', [
            'name' => 'X', 'email' => 'x@kaba.bj', 'password' => 'motdepasse123', 'role' => 'admin',
        ])->assertForbidden();

        $this->actingAs($member)
            ->post("/admin/utilisateurs/{$target->id}/statut", ['role' => 'admin'])
            ->assertForbidden();
    }
}
