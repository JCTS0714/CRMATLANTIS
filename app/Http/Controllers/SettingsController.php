<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    private function logoMarkPublicUrl(): string
    {
        return file_exists(public_path('storage/settings/logo_mark.png'))
            ? asset('storage/settings/logo_mark.png')
            : asset('images/logo_alta_calidad.png');
    }

    private function logoFullPublicUrl(): string
    {
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
        // Process image: produce a 40x40 PNG (centered, maintain aspect ratio)
        $file = $request->file('logo');
        $this->processLogoMark($file->getRealPath(), storage_path('app/public/settings/logo_mark.png'));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Ícono actualizado correctamente.',
                'path' => asset('storage/settings/logo_mark.png') . '?v=' . time(),
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
        // Process image: height 40px, max width 176px, maintain aspect ratio, save as PNG
        $file = $request->file('logo');
        $this->processLogoFull($file->getRealPath(), storage_path('app/public/settings/logo_full.png'));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Logo grande actualizado correctamente.',
                'path' => asset('storage/settings/logo_full.png') . '?v=' . time(),
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

    private function processLogoMark(string $sourcePath, string $destPath): void
    {
        $data = @file_get_contents($sourcePath);
        if ($data === false) {
            @copy($sourcePath, $destPath);
            return;
        }

        $src = @imagecreatefromstring($data);
        if (!$src) {
            @copy($sourcePath, $destPath);
            return;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $targetSize = 40;

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
        imagepng($target, $destPath);

        imagedestroy($src);
        imagedestroy($target);
    }

    private function processLogoFull(string $sourcePath, string $destPath): void
    {
        $data = @file_get_contents($sourcePath);
        if ($data === false) {
            @copy($sourcePath, $destPath);
            return;
        }

        $src = @imagecreatefromstring($data);
        if (!$src) {
            @copy($sourcePath, $destPath);
            return;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $targetH = 40;
        $maxW = 176;

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
        imagepng($target, $destPath);

        imagedestroy($src);
        imagedestroy($target);
    }
}
