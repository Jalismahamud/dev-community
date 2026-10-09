<?php

use Carbon\CarbonInterface;

if (! function_exists('format_datetime')) {
    function format_datetime(CarbonInterface|string|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        $value = $date instanceof CarbonInterface ? $date : Carbon\Carbon::parse($date);

        return $value->formatDhaka();
    }
}

if (! function_exists('format_money')) {
    function format_money(float|int|string|null $amount): string
    {
        $formatted = number_format((float) ($amount ?? 0), 2, '.', ',');
        [$integer, $decimal] = explode('.', $formatted);
        $integer = str_replace(',', '', $integer);
        $lastThree = substr($integer, -3);
        $remaining = substr($integer, 0, -3);

        if ($remaining !== '') {
            $lastThree = ',' . $lastThree;
            $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
        }

        return '৳ ' . ($remaining . $lastThree) . '.' . $decimal;
    }
}

if (! function_exists('compress_to_webp')) {
    function compress_to_webp(\Illuminate\Http\UploadedFile $file, string $folder = 'uploads', ?int $maxWidth = 1600, int $quality = 80): string
    {
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($file->getRealPath());

        if ($maxWidth !== null && $image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $path = trim($folder, '/') . '/' . str()->uuid() . '.webp';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, (string) $image->toWebp($quality));

        return $path;
    }
}

if (! function_exists('delete_media')) {
    function delete_media(?string $path, string $disk = 'public'): void
    {
        if ($path !== null && \Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk($disk)->delete($path);
        }
    }
}