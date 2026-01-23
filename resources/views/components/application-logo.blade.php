@php($logoPath = file_exists(public_path('storage/settings/logo_mark.png')) ? asset('storage/settings/logo_mark.png') : asset('images/logo_alta_calidad.png'))
<img src="{{ $logoPath }}" alt="{{ config('app.name') }}" {{ $attributes }} />
