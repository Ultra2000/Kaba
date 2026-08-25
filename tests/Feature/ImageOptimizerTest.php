<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    use RefreshDatabase;

    /** Crée un vrai fichier JPEG lourd (pas un faux) pour mesurer la compression. */
    private function realPhoto(int $w, int $h): UploadedFile
    {
        $im = imagecreatetruecolor($w, $h);
        // Du bruit coloré : compresse mal, comme une vraie photo.
        for ($i = 0; $i < 4000; $i++) {
            imagefilledellipse(
                $im, rand(0, $w), rand(0, $h), rand(10, 90), rand(10, 90),
                imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255))
            );
        }
        $path = sys_get_temp_dir() . '/kaba-test-' . uniqid() . '.jpg';
        imagejpeg($im, $path, 92);
        imagedestroy($im);

        return new UploadedFile($path, 'photo.jpg', 'image/jpeg', null, true);
    }

    public function test_large_photo_is_resized_and_converted_to_webp(): void
    {
        Storage::fake('public');

        $file = $this->realPhoto(3000, 2000);
        $originalSize = $file->getSize();

        $path = app(ImageOptimizer::class)->store($file);

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $binary = Storage::disk('public')->get($path);
        $info = getimagesizefromstring($binary);

        // Le côté le plus long est ramené à la limite.
        $this->assertSame(ImageOptimizer::MAX_DIMENSION, max($info[0], $info[1]));
        $this->assertSame(IMAGETYPE_WEBP, $info[2]);

        // Le gain de poids doit être net (au moins deux fois plus léger).
        $this->assertLessThan(
            $originalSize / 2,
            strlen($binary),
            "La compression n'a pas réduit le poids de moitié (avant : {$originalSize} o, après : " . strlen($binary) . ' o).'
        );
    }

    public function test_small_photo_is_not_upscaled(): void
    {
        Storage::fake('public');

        $path = app(ImageOptimizer::class)->store($this->realPhoto(400, 600));
        $info = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(400, $info[0]);
        $this->assertSame(600, $info[1]);
    }

    public function test_published_listing_photos_are_optimized(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::create(['name' => 'Romans', 'slug' => 'roman', 'icon' => 'fa-feather-pointed']);

        $this->actingAs($user)->post('/livres', [
            'type'        => 'vente',
            'title'       => 'Livre photographié',
            'category_id' => $category->id,
            'condition'   => 'bon',
            'language'    => 'Français',
            'city'        => 'Cotonou',
            'price'       => 2000,
            'photos'      => [$this->realPhoto(2400, 1600)],
        ]);

        $listing = \App\Models\Listing::firstWhere('title', 'Livre photographié');
        $photo = $listing->photos()->first();

        $this->assertStringEndsWith('.webp', $photo->path);
        $info = getimagesizefromstring(Storage::disk('public')->get($photo->path));
        $this->assertLessThanOrEqual(ImageOptimizer::MAX_DIMENSION, max($info[0], $info[1]));
    }

    public function test_optimized_photo_carries_no_exif_metadata(): void
    {
        Storage::fake('public');

        $path = app(ImageOptimizer::class)->store($this->realPhoto(2000, 1500));
        $binary = Storage::disk('public')->get($path);

        // Un WebP est un conteneur RIFF : on parcourt ses blocs et on vérifie
        // qu'aucun ne transporte de métadonnées (les EXIF d'un téléphone
        // contiennent les coordonnées GPS du lieu de prise de vue).
        $this->assertSame('RIFF', substr($binary, 0, 4));
        $this->assertSame('WEBP', substr($binary, 8, 4));

        $chunks = [];
        $offset = 12;
        $length = strlen($binary);
        while ($offset + 8 <= $length) {
            $fourcc = substr($binary, $offset, 4);
            $size = unpack('V', substr($binary, $offset + 4, 4))[1];
            $chunks[] = $fourcc;
            $offset += 8 + $size + ($size % 2); // les blocs sont alignés sur un octet pair
        }

        $this->assertNotContains('EXIF', $chunks, 'Le fichier transporte encore des métadonnées EXIF.');
        $this->assertNotContains('XMP ', $chunks, 'Le fichier transporte encore des métadonnées XMP.');
    }
}
