<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    private function logoMarkPublicUrl(): string
    {
        $manifestPath = $this->readLogoManifest('logo_mark');
        if ($manifestPath) {
            return asset($manifestPath);
        }

        return file_exists(public_path('storage/settings/logo_mark.png'))
            ? asset('storage/settings/logo_mark.png')
            : asset('images/logo_alta_calidad.png');
    }

    private function logoFullPublicUrl(): string
    {
        $manifestPath = $this->readLogoManifest('logo_full');
        if ($manifestPath) {
            return asset($manifestPath);
        }

        return file_exists(public_path('storage/settings/logo_full.png'))
            ? asset('storage/settings/logo_full.png')
            : '';
    }

    public function index()
    {
        return view('settings.personalizacion');
    }

    public function uploadLogo(Request $request)
    {
        // Backwards-compatible: treat as mark/logo icon.
        return $this->uploadLogoMark($request);
    }

    public function uploadLogoMark(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
        ]);

        Storage::disk('public')->makeDirectory('settings');
        // Process image: produce a 112x112 PNG (centered, maintain aspect ratio)
        $file = $request->file('logo');
        $filename = 'logo_mark_' . now()->format('Ymd_His') . '.png';
        $destPath = storage_path('app/public/settings/' . $filename);
        $publicPath = 'storage/settings/' . $filename;
        try {
            $processed = $this->processLogoMark($file->getRealPath(), $destPath);
        } catch (\Throwable $e) {
            $processed = false;
            \Log::warning('Logo mark processing failed: ' . $e->getMessage());
        }
        if (!$processed) {
            $file->storeAs('settings', $filename, 'public');
        }

        $this->writeLogoManifest('logo_mark', $publicPath);
        $this->cleanupOldLogos('logo_mark_*.png', $destPath);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Ícono actualizado correctamente.',
                'path' => asset($publicPath),
            ]);
        }

        return back()->with('status', 'Ícono actualizado correctamente.');
    }

    public function uploadLogoFull(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:4096',
        ]);

        Storage::disk('public')->makeDirectory('settings');
        // Process image: height 112px, max width 392px, maintain aspect ratio, save as PNG
        $file = $request->file('logo');
        $filename = 'logo_full_' . now()->format('Ymd_His') . '.png';
        $destPath = storage_path('app/public/settings/' . $filename);
        $publicPath = 'storage/settings/' . $filename;
        try {
            $processed = $this->processLogoFull($file->getRealPath(), $destPath);
        } catch (\Throwable $e) {
            $processed = false;
            \Log::warning('Logo full processing failed: ' . $e->getMessage());
        }
        if (!$processed) {
            $file->storeAs('settings', $filename, 'public');
        }

        $this->writeLogoManifest('logo_full', $publicPath);
        $this->cleanupOldLogos('logo_full_*.png', $destPath);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Logo grande actualizado correctamente.',
                'path' => asset($publicPath),
            ]);
        }

        return back()->with('status', 'Logo grande actualizado correctamente.');
    }

    public function logoPath()
    {
        // Backwards-compatible: return mark path only.
        return response()->json(['path' => $this->logoMarkPublicUrl()]);
    }

    public function logoPaths()
    {
        return response()->json([
            'mark' => $this->logoMarkPublicUrl(),
            'full' => $this->logoFullPublicUrl(),
        ]);
    }

    private function logoManifestPath(string $key): string
    {
        return storage_path('app/public/settings/' . $key . '.json');
    }

    private function readLogoManifest(string $key): ?string
    {
        $manifestPath = $this->logoManifestPath($key);
        if (!file_exists($manifestPath)) {
            return null;
        }

        $raw = @file_get_contents($manifestPath);
        if (!$raw) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['path'])) {
            return null;
        }

        $publicPath = public_path($data['path']);
        if (!file_exists($publicPath)) {
            return null;
        }

        return $data['path'];
    }

    private function writeLogoManifest(string $key, string $relativePath): void
    {
        $payload = json_encode([
            'path' => $relativePath,
            'updated_at' => now()->toIso8601String(),
        ], JSON_UNESCAPED_SLASHES);

        @file_put_contents($this->logoManifestPath($key), $payload);
    }

    private function cleanupOldLogos(string $pattern, string $keepPath, int $keep = 3): void
    {
        $files = glob(storage_path('app/public/settings/' . $pattern)) ?: [];
        if (empty($files)) {
            return;
        }

        usort($files, static function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $kept = 0;
        foreach ($files as $file) {
            if ($file === $keepPath) {
                $kept++;
                continue;
            }

            if ($kept < $keep) {
                $kept++;
                continue;
            }

            @unlink($file);
        }
    }

    private function processLogoMark(string $sourcePath, string $destPath): bool
    {
        if (!\function_exists('imagecreatefromstring') || !\function_exists('imagepng')) {
            return false;
        }
        $data = @file_get_contents($sourcePath);
        if ($data === false) {
            return false;
        }

        $src = @imagecreatefromstring($data);
        if (!$src) {
            return false;
        }

        $trimmed = $this->trimTransparent($src);
        if ($trimmed !== $src) {
            imagedestroy($src);
            $src = $trimmed;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $targetSize = 112;

        $target = imagecreatetruecolor($targetSize, $targetSize);
        imagesavealpha($target, true);
        $trans_colour = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $trans_colour);

        $scale = min($targetSize / $srcW, $targetSize / $srcH);
        if ($scale > 1) $scale = 1; // avoid upscaling

        $newW = max(1, (int) round($srcW * $scale));
        $newH = max(1, (int) round($srcH * $scale));

        $dstX = (int) round(($targetSize - $newW) / 2);
        $dstY = (int) round(($targetSize - $newH) / 2);

        imagecopyresampled($target, $src, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);

        @mkdir(dirname($destPath), 0755, true);
        $saved = imagepng($target, $destPath);

        imagedestroy($src);
        imagedestroy($target);

        return (bool) $saved;
    }

    private function processLogoFull(string $sourcePath, string $destPath): bool
    {
        if (!\function_exists('imagecreatefromstring') || !\function_exists('imagepng')) {
            return false;
        }
        $data = @file_get_contents($sourcePath);
        if ($data === false) {
            return false;
        }

        $src = @imagecreatefromstring($data);
        if (!$src) {
            return false;
        }

        $trimmed = $this->trimTransparent($src);
        if ($trimmed !== $src) {
            imagedestroy($src);
            $src = $trimmed;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $targetH = 112;
        $maxW = 392;

        // scale down but don't upscale
        $scale = min($targetH / $srcH, $maxW / $srcW, 1);
        $newW = max(1, (int) round($srcW * $scale));
        $newH = max(1, (int) round($srcH * $scale));

        $target = imagecreatetruecolor($newW, $newH);
        imagesavealpha($target, true);
        $trans_colour = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $trans_colour);

        imagecopyresampled($target, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

        @mkdir(dirname($destPath), 0755, true);
        $saved = imagepng($target, $destPath);

        imagedestroy($src);
        imagedestroy($target);

        return (bool) $saved;
    }

    private function trimTransparent($src)
    {
        $width = imagesx($src);
        $height = imagesy($src);

        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($src, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha < 127) {
                    if ($x < $minX) $minX = $x;
                    if ($y < $minY) $minY = $y;
                    if ($x > $maxX) $maxX = $x;
                    if ($y > $maxY) $maxY = $y;
                }
            }
        }

        if ($maxX < $minX || $maxY < $minY) {
            return $src;
        }

        $newW = $maxX - $minX + 1;
        $newH = $maxY - $minY + 1;

        if ($newW === $width && $newH === $height) {
            return $src;
        }

        $cropped = imagecreatetruecolor($newW, $newH);
        imagesavealpha($cropped, true);
        $trans = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
        imagefill($cropped, 0, 0, $trans);

        imagecopy($cropped, $src, 0, 0, $minX, $minY, $newW, $newH);

        return $cropped;
    }
}
