<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Compresse les photos envoyées par les vendeurs avant stockage.
 *
 * Une photo prise au téléphone pèse 3 à 8 Mo ; avec 10 photos par annonce,
 * cela sature vite l'hébergement et rend les fiches inutilisables sur une
 * connexion mobile. On redimensionne, on convertit en WebP, et on supprime
 * les métadonnées EXIF (qui contiennent souvent les coordonnées GPS du
 * domicile du vendeur).
 */
class ImageOptimizer
{
    /** Côté le plus long, en pixels. Suffisant pour un affichage plein écran. */
    public const MAX_DIMENSION = 1400;

    /** Qualité WebP (0-100). 82 : bon compromis netteté / poids. */
    public const QUALITY = 82;

    /**
     * Optimise une image envoyée et la stocke sur le disque public.
     *
     * @return string Chemin relatif du fichier stocké (ex. "listings/ab12.webp").
     */
    public function store(UploadedFile $file, string $directory = 'listings'): string
    {
        $image = $this->read($file);

        // Sans GD ou format non géré : on retombe sur un stockage brut,
        // mieux vaut une photo lourde que pas de photo du tout.
        if ($image === null) {
            return $file->store($directory, 'public');
        }

        $image = $this->applyExifOrientation($image, $file);
        $image = $this->resize($image);

        $path = $directory . '/' . Str::random(24) . '.webp';

        ob_start();
        imagewebp($image, null, self::QUALITY);
        $binary = ob_get_clean();
        imagedestroy($image);

        // imagewebp() ne recopie pas les EXIF : les données GPS disparaissent.
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /** @return \GdImage|null */
    private function read(UploadedFile $file)
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $contents = @file_get_contents($file->getRealPath());
        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        return $image === false ? null : $image;
    }

    /**
     * Redresse l'image selon l'orientation EXIF : sans cela, les photos
     * prises en portrait s'affichent couchées.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function applyExifOrientation($image, UploadedFile $file)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        // exif_read_data() n'accepte que le JPEG/TIFF et émet un warning sinon.
        $mime = $file->getMimeType();
        if (! in_array($mime, ['image/jpeg', 'image/tiff'], true)) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = $exif['Orientation'] ?? 1;

        $rotated = match ((int) $orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated !== false && $rotated !== null) {
            imagedestroy($image);
            return $rotated;
        }

        return $image;
    }

    /**
     * Réduit l'image si elle dépasse MAX_DIMENSION, en conservant les proportions.
     * Une image déjà petite est laissée telle quelle (pas d'agrandissement).
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function resize($image)
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $longest = max($w, $h);

        if ($longest <= self::MAX_DIMENSION) {
            return $image;
        }

        $ratio = self::MAX_DIMENSION / $longest;
        $nw = max(1, (int) round($w * $ratio));
        $nh = max(1, (int) round($h * $ratio));

        $resized = imagecreatetruecolor($nw, $nh);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($image);

        return $resized;
    }
}
