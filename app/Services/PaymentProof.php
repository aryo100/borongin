<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentProof
{
    public function store(UploadedFile $file): string
    {
        $bytes = $file->getContent();
        $size = @getimagesizefromstring($bytes);

        if (! $size || $size[0] * $size[1] > 16_000_000 || ! in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw ValidationException::withMessages(['proof' => 'Gunakan gambar JPG, PNG, atau WebP hingga 16 megapiksel.']);
        }

        $source = @imagecreatefromstring($bytes);
        if (! $source) {
            throw ValidationException::withMessages(['proof' => 'Gambar tidak dapat dibaca. Silakan pilih file lain.']);
        }

        [$width, $height] = $size;
        $scale = min(1, 2200 / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $image = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagecopyresampled($image, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        try {
            $encoded = imagejpeg($image, quality: 90);
            $jpeg = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        $path = 'payment-proofs/'.Str::uuid().'.jpg';
        if (! $encoded || ! $jpeg || ! Storage::disk('local')->put($path, $jpeg, 'private')) {
            throw new RuntimeException('Payment proof could not be stored.');
        }

        return $path;
    }
}
