@php
	$manifestPath = storage_path('app/public/settings/logo_mark.json');
	$manifestLogo = null;
	if (file_exists($manifestPath)) {
		$payload = json_decode(file_get_contents($manifestPath), true);
		if (is_array($payload) && !empty($payload['path']) && file_exists(public_path($payload['path']))) {
			$manifestLogo = asset($payload['path']);
		}
	}

	$logoPath = $manifestLogo
		?: (file_exists(public_path('storage/settings/logo_mark.png'))
			? asset('storage/settings/logo_mark.png')
			: asset('images/logo_alta_calidad.png'));
@endphp
<img src="{{ $logoPath }}" alt="{{ config('app.name') }}" {{ $attributes }} />
