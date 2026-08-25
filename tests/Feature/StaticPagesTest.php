<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function pageProvider(): array
    {
        return [
            'à propos'        => ['/a-propos', 'About'],
            'centre d\'aide'  => ['/aide', 'Faq'],
            'sécurité'        => ['/securite', 'Security'],
            'contact'         => ['/contact', 'Contact'],
            'CGU'             => ['/cgu', 'Legal/Terms'],
            'confidentialité' => ['/confidentialite', 'Legal/Privacy'],
        ];
    }

    /** @dataProvider pageProvider */
    public function test_information_pages_are_public(string $uri, string $component): void
    {
        $this->get($uri)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component($component));
    }

    public function test_about_page_shows_real_platform_stats(): void
    {
        $this->get('/a-propos')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('About')
                ->has('stats.listings')
                ->has('stats.members')
                ->has('stats.donations')
                ->has('stats.exchanges'));
    }

    public function test_contact_form_notifies_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create();

        $this->post('/contact', [
            'name'    => 'Koffi A.',
            'email'   => 'koffi@example.bj',
            'subject' => 'probleme',
            'message' => 'Une annonce me semble frauduleuse.',
        ])->assertRedirect();

        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(0, $other->notifications()->count());

        $data = $admin->notifications()->first()->data;
        $this->assertSame('contact', $data['kind']);
        $this->assertStringContainsString('Koffi A.', $data['message']);
        $this->assertStringContainsString('Problème signalé', $data['message']);
    }

    public function test_contact_form_validates_input(): void
    {
        $this->post('/contact', ['name' => '', 'email' => 'pas-un-email', 'subject' => 'inconnu', 'message' => ''])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_legal_pages_expose_update_date(): void
    {
        foreach (['/cgu', '/confidentialite'] as $uri) {
            $this->get($uri)->assertInertia(fn (AssertableInertia $page) => $page->has('updated'));
        }
    }
}
