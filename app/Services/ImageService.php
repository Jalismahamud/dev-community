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
        $image = (new ImageManager(new Driver()))->read($file->getRealPath());

        if ($width !== null && $height !== null) {
            $image->resize($width, $height);
        } elseif ($width !== null) {
            $image->scaleDown(width: $width);
        } elseif ($height !== null) {
            $image->scaleDown(height: $height);
        }

        $path = trim($folder, '/') . '/' . str()->uuid() . '.webp';
        Storage::disk('public')->put($path, (string) $image->toWebp(85));

        return $path;
    }

    public function deleteImage(?string $path): void
    {
        if ($path !== null && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function replaceImage(UploadedFile $file, ?string $oldPath, string $folder): string
    {
        $this->deleteImage($oldPath);

        return $this->uploadImage($file, $folder);
    }
}