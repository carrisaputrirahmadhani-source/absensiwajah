<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GambarBase64
{
    /** Simpan data URL (jpeg/png) ke storage/public/{folder}. Return path atau null. */
    public static function simpan(?string $dataUrl, string $folder): ?string
    {
        if (! $dataUrl || ! preg_match('#^data:image/(jpeg|png);base64,#', $dataUrl, $m)) {
            return null;
        }

        $biner = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);

        if ($biner === false || strlen($biner) > 2_000_000 || @getimagesizefromstring($biner) === false) {
            return null;
        }

        $path = $folder . '/' . Str::uuid() . '.' . ($m[1] === 'png' ? 'png' : 'jpg');
        Storage::disk('public')->put($path, $biner);

        return $path;
    }
}