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
        $request->file('logo')->storeAs('settings', 'logo_mark.png', 'public');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Ícono actualizado correctamente.',
                'path' => asset('storage/settings/logo_mark.png'),
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
        $request->file('logo')->storeAs('settings', 'logo_full.png', 'public');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Logo grande actualizado correctamente.',
                'path' => asset('storage/settings/logo_full.png'),
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
}
