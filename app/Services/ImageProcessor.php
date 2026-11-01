<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Handles image thumbnail generation using GD library.
 */
class ImageProcessor
{
    /**
     * Generate a thumbnail for an uploaded image.
     *
     * @return string|null The thumbnail path, or null if generation failed.
     */
    public function generate(UploadedFile $file, string $originalPath, int $contestId): ?string
    {
        $source = $file->getRealPath();
        $meta = getimagesize($source);

        if ($meta === false) {
            return null;
        }

        $width = $meta[0];
        $height = $meta[1];
        $type = $meta[2];

        $sourceImage = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($source),
            IMAGETYPE_PNG => imagecreatefrompng($source),
            IMAGETYPE_WEBP => imagecreatefromwebp($source),
            IMAGETYPE_GIF => imagecreatefromgif($source),
            default => null,
        };

        if ($sourceImage === false) {
            return null;
        }

        $maxSize = (int) config('media.thumbnail.max_size', 600);
        $quality = (int) config('media.thumbnail.quality', 95);
        $twoPass = (bool) config('media.thumbnail.two_pass', true);

        // Preserve aspect ratio: fit within max_size box
        if ($width > $height) {
            $newWidth = min($width, $maxSize);
            $newHeight = (int) round($height * ($newWidth / $width));
        } else {
            $newHeight = min($height, $maxSize);
            $newWidth = (int) round($width * ($newHeight / $height));
        }

        $thumb = imagecreatetruecolor(max(1, $newWidth), max(1, $newHeight));
        imagealphablending($thumb, true);
        imagesavealpha($thumb, true);

        if ($type === IMAGETYPE_PNG) {
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            if ($transparent !== false) {
                imagefilledrectangle($thumb, 0, 0, max(1, $newWidth), max(1, $newHeight), $transparent);
            }
        } elseif ($type === IMAGETYPE_JPEG || $type === IMAGETYPE_WEBP) {
            $white = imagecolorallocate($thumb, 255, 255, 255);
            if ($white !== false) {
                imagefilledrectangle($thumb, 0, 0, max(1, $newWidth), max(1, $newHeight), $white);
            }
        }

        if ($twoPass) {
            // Two-pass resize for better quality
            $intermediate = imagecreatetruecolor(max(1, $newWidth), max(1, $newHeight));
            imagecopyresampled($intermediate, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagecopyresampled($thumb, $intermediate, 0, 0, 0, 0, $newWidth, $newHeight, $newWidth, $newHeight);
            imagedestroy($intermediate);
        } else {
            imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        }

        imagedestroy($sourceImage);

        $fileName = pathinfo($originalPath, PATHINFO_FILENAME);
        $thumbName = ($fileName !== false ? $fileName : 'thumb').'_thumb.jpg';
        $thumbPath = 'media/'.$contestId.'/thumbs/'.$thumbName;

        $thumbDir = dirname(Storage::disk('public')->path($thumbPath));
        if (! is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        imagejpeg($thumb, Storage::disk('public')->path($thumbPath), $quality);
        imagedestroy($thumb);

        return $thumbPath;
    }
}
