<?php

declare(strict_types=1);

namespace App\Services\Book;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class CoverImageService
{
    private const COLORS = [
        ['bg' => [44, 62, 80],   'text' => [236, 240, 241]],
        ['bg' => [52, 73, 94],   'text' => [236, 240, 241]],
        ['bg' => [22, 160, 133], 'text' => [255, 255, 255]],
        ['bg' => [39, 174, 96],  'text' => [255, 255, 255]],
        ['bg' => [41, 128, 185], 'text' => [255, 255, 255]],
        ['bg' => [142, 68, 173], 'text' => [255, 255, 255]],
        ['bg' => [192, 57, 43],  'text' => [255, 255, 255]],
        ['bg' => [211, 84, 0],   'text' => [255, 255, 255]],
        ['bg' => [26, 188, 156], 'text' => [255, 255, 255]],
        ['bg' => [52, 152, 219], 'text' => [255, 255, 255]],
    ];

    public function generatePlaceholder(string $title, int $seed): string
    {
        $width = 400;
        $height = 600;

        $image = imagecreatetruecolor($width, $height);

        $colorSet = self::COLORS[$seed % count(self::COLORS)];
        [$r, $g, $b] = $colorSet['bg'];
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);

        // Gradient overlay
        for ($y = 0; $y < $height; $y++) {
            $alpha = (int) (60 * $y / $height);
            $overlay = imagecolorallocatealpha($image, 0, 0, 0, 127 - $alpha);
            imageline($image, 0, $y, $width, $y, $overlay);
        }

        // Title text
        [$tr, $tg, $tb] = $colorSet['text'];
        $textColor = imagecolorallocate($image, $tr, $tg, $tb);

        $words = explode(' ', strtoupper($title));
        $lines = [];
        $line = '';
        $maxChars = 14;

        foreach ($words as $word) {
            if (strlen($line.' '.$word) > $maxChars && $line !== '') {
                $lines[] = trim($line);
                $line = $word;
            } else {
                $line .= ($line ? ' ' : '').$word;
            }
        }
        if ($line) {
            $lines[] = trim($line);
        }
        $lines = array_slice($lines, 0, 5);

        $fontSize = 4;
        $lineHeight = imageFontHeight($fontSize) + 6;
        $totalHeight = count($lines) * $lineHeight;
        $startY = ($height - $totalHeight) / 2;

        foreach ($lines as $i => $lineText) {
            $lineWidth = imageFontWidth($fontSize) * strlen($lineText);
            $x = ($width - $lineWidth) / 2;
            $y = (int) ($startY + $i * $lineHeight);
            imagestring($image, $fontSize, (int) $x, $y, $lineText, $textColor);
        }

        // Bottom label
        $label = 'SMARTBOOK';
        $labelW = imageFontWidth(2) * strlen($label);
        imagestring($image, 2, ($width - $labelW) / 2, $height - 30, $label, $textColor);

        ob_start();
        imagejpeg($image, null, 90);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $filename = 'covers/'.Str::uuid().'.jpg';
        Storage::disk('s3')->put($filename, $imageData, 'public');

        return $filename;
    }

    public function uploadFromUrl(string $url, int $seed): string
    {
        $response = \Illuminate\Support\Facades\Http::timeout(15)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to download image from: {$url}");
        }

        $filename = 'covers/'.Str::uuid().'.jpg';
        Storage::disk('s3')->put($filename, $response->body(), 'public');

        return $filename;
    }

    public function delete(string $path): void
    {
        Storage::disk('s3')->delete($path);
    }

    public function url(string $path): string
    {
        return Storage::disk('s3')->url($path);
    }
}
