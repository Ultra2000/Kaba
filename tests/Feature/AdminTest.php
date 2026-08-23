<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User { return User::factory()->create(['role' => 'admin']); }

    private function listing(): Listing
    {
        $cat = Category::create(['name' => 'Romans', 'slug' => 'roman']);
        return Listing::create([
            'user_id' => User::factory()->create()->id, 'category_id' => $cat->id, 'title' => 'Test',
            'condition' => 'bon', 'city' => 'Cotonou', 'type' => 'vente', 'price' => 1000, 'language' => 'Français', 'status' => 'active',
        ]);
    }

    public function test_non_admin_is_forbidden(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_can_view_all_sections(): void
    {
        $this->listing();
        $admin = $this->admin();
        foreach (['/admin', '/admin/signalements', '/admin/annonces', '/admin/utilisateurs', '/admin/categories'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_resolves_a_report(): void
    {
        $l = $this->listing();
        $report = $l->morphMany(Report::class, 'reportable')->create([
            'reporter_id' => User::factory()->create()->id, 'reason' => 'arnaque', 'status' => 'open',
        ]);
        $this->actingAs($this->admin())->post("/admin/signalements/{$report->id}/resoudre");
        $this->assertEquals('resolved', $report->fresh()->status);
    }

    public function test_admin_creates_and_deletes_category(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/categories', ['name' => 'Poésie', 'icon' => 'fa-feather']);
        $this->assertDatabaseHas('categories', ['slug' => 'poesie']);

        $cat = Category::where('slug', 'poesie')->first();
        $this->actingAs($admin)->delete("/admin/categories/{$cat->id}");
        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }

    public function test_admin_can_hide_and_delete_listing(): void
    {
        $l = $this->listing();
        $admin = $this->admin();
        $this->actingAs($admin)->post("/admin/annonces/{$l->id}/masquer");
        $this->assertEquals('hidden', $l->fresh()->status);
        $this->actingAs($admin)->delete("/admin/annonces/{$l->id}");
        $this->assertModelMissing($l);
    }

    public function test_admin_toggles_user_verified(): void
    {
        $u = User::factory()->create(['is_verified' => false]);
        $this->actingAs($this->admin())->post("/admin/utilisateurs/{$u->id}/verifier");
        $this->assertTrue((bool) $u->fresh()->is_verified);
    }
}
