<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Personalización</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white dark:bg-slate-900 shadow sm:rounded-lg">
                @if(session('status'))
                    <div class="mb-4 text-sm text-green-700">{{ session('status') }}</div>
                @endif

                <div class="space-y-6">
                    <div>
                        <h3 class="font-medium">Logo del sistema</h3>
                        <p class="text-sm text-slate-500">Sube una imagen que se mostrará en la pantalla de login y en el header del menú.</p>

                        <div class="mt-4">
                            @php($current = file_exists(public_path('storage/settings/logo.png')) ? asset('storage/settings/logo.png') : asset('images/logo_alta_calidad.png'))
                            <img src="{{ $current }}" alt="Logo actual" class="w-24 h-24 object-contain rounded-md border" />
                        </div>

                        <form action="{{ route('settings.logo.upload') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                            @csrf

                            <div>
                                <input type="file" name="logo" accept="image/*" />
                                @error('logo')
                                    <div class="text-sm text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Subir logo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
