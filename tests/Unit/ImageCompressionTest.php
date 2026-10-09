<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCompressionTest extends TestCase
{
    public function test_compress_to_webp_creates_valid_webp_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);
        $path = compress_to_webp($file, 'posts', 600, 80);

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        delete_media($path);
        Storage::disk('public')->assertMissing($path);
    }
}
