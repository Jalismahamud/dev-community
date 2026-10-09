<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    public function uploadImage(UploadedFile $file, string $folder, ?int $width = null, ?int $height = null): string
    {
        if ($height !== null) {
            $image = (new ImageManager(new Driver()))->read($file->getRealPath());
            if ($width !== null) {
                $image->resize($width, $height);
            } else {
                $image->scaleDown(height: $height);
            }
            $path = trim($folder, '/') . '/' . str()->uuid() . '.webp';
            Storage::disk('public')->put($path, (string) $image->toWebp(80));

            return $path;
        }

        return compress_to_webp($file, $folder, $width ?? 1600, 80);
    }

    public function deleteImage(?string $path): void
    {
        delete_media($path);
    }

    public function replaceImage(UploadedFile $file, ?string $oldPath, string $folder): string
    {
        $this->deleteImage($oldPath);

        return $this->uploadImage($file, $folder);
    }
}