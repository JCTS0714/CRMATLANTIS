<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Photo') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            {{ __('Update your profile photo.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.photo.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf

        <div class="flex items-center gap-4">
            @php
                $photoUrl = $user->profile_photo_path
                    ? '/storage/' . ltrim($user->profile_photo_path, '/')
                    : null;
            @endphp

            @if ($photoUrl)
                <img
                    src="{{ $photoUrl }}"
                    alt="{{ $user->name }}"
                    class="h-12 w-12 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700"
                />
            @else
                <div
                    class="h-12 w-12 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-100 flex items-center justify-center text-xs font-semibold ring-1 ring-gray-200 dark:ring-gray-600"
                >
                    {{ collect(preg_split('/\s+/', trim((string) $user->name)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                        ->implode('') ?: 'U' }}
                </div>
            @endif

            <div class="flex-1">
                <x-input-label for="photo" :value="__('New photo')" />
                <input
                    id="photo"
                    name="photo"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-700 dark:file:text-gray-100 dark:hover:file:bg-gray-600"
                    required
                />
                <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-300">JPG, PNG o WebP. Máximo 2MB.</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-photo-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-300"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
