<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pwa_files_exist(): void
    {
        foreach ([
            'manifest.webmanifest',
            'sw.js',
            'offline.html',
            'icons/icon-192.png',
            'icons/icon-512.png',
            'icons/maskable-192.png',
            'icons/maskable-512.png',
            'icons/apple-touch-icon.png',
        ] as $file) {
            $this->assertFileExists(public_path($file), "Fichier PWA manquant : {$file}");
        }
    }

    public function test_manifest_is_valid_and_installable(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

        $this->assertIsArray($manifest, 'Le manifeste n\'est pas un JSON valide.');
        $this->assertSame('KABA', $manifest['short_name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#7C3AED', $manifest['theme_color']);

        // Critères d'installabilité : une icône 192 et une 512, plus une maskable.
        $sizes = array_column($manifest['icons'], 'sizes');
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);
        $this->assertNotEmpty(array_filter($manifest['icons'], fn ($i) => ($i['purpose'] ?? '') === 'maskable'));

        // Chaque fichier référencé doit exister.
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_layout_declares_pwa_tags(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('name="theme-color" content="#7C3AED"', false)
            ->assertSee('rel="apple-touch-icon"', false);
    }

    public function test_service_worker_never_caches_sensitive_traffic(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        // Garde-fous : ni écritures, ni requêtes Inertia, ni espace admin en cache.
        $this->assertStringContainsString("request.method !== 'GET'", $sw);
        $this->assertStringContainsString("X-Inertia", $sw);
        $this->assertStringContainsString("'/admin'", $sw);
    }
}
