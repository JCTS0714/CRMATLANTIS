<template>
  <div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-slate-900 shadow sm:rounded-lg p-6">
      <h2 class="text-lg font-semibold mb-4">Personalización</h2>

      <div v-if="status" class="mb-4 text-sm text-green-600">{{ status }}</div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <section class="rounded-lg border border-slate-200/60 dark:border-slate-800 p-4">
          <h3 class="font-medium">Ícono (logo puro)</h3>
          <p class="text-sm text-slate-500">Se usa en el login y en el menú contraído.</p>
          <p class="mt-1 text-xs text-slate-500">
            Recomendado: PNG con fondo transparente, 256×256 (mín. 128×128). Se mostrará aprox. a 56×56 en el menú y 56×56 en login.
          </p>

          <div class="mt-4">
            <img :src="markPreviewUrl" alt="Ícono actual" class="w-28 h-28 object-contain rounded-md border bg-white p-2" />
          </div>

          <form @submit.prevent="uploadMark" class="mt-4">
            <input id="file-logo-mark" type="file" ref="fileMark" accept="image/*" class="sr-only" @change="onMarkFileChange" />
            <label
              for="file-logo-mark"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white/90 dark:bg-slate-950 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900 cursor-pointer"
            >
              <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M3 4a2 2 0 0 1 2-2h6a1 1 0 1 1 0 2H5v12h10V9a1 1 0 1 1 2 0v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4Z" />
                <path d="M14 2a1 1 0 0 1 1 1v2h2a1 1 0 1 1 0 2h-2v2a1 1 0 1 1-2 0V7h-2a1 1 0 1 1 0-2h2V3a1 1 0 0 1 1-1Z" />
              </svg>
              <span>Seleccionar archivo</span>
            </label>
            <p class="mt-2 text-xs text-slate-500 break-all">
              {{ markFileName ? markFileName : 'Ningún archivo seleccionado' }}
            </p>
            <div v-if="errorMark" class="text-sm text-red-600">{{ errorMark }}</div>

            <div class="mt-4">
              <button
                type="submit"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 disabled:opacity-60"
              >
                Subir ícono
              </button>
            </div>
          </form>
        </section>

        <section class="rounded-lg border border-slate-200/60 dark:border-slate-800 p-4">
          <h3 class="font-medium">Logo grande (ícono + nombre)</h3>
          <p class="text-sm text-slate-500">Se usa en el menú expandido.</p>
          <p class="mt-1 text-xs text-slate-500">
            Recomendado: PNG transparente, proporción 4:1 (ej. 640×160 o 800×200). Se mostrará aprox. a 224×56 en el menú.
          </p>

          <div class="mt-4">
            <img :src="fullPreviewUrl" alt="Logo grande actual" class="w-full h-24 object-contain rounded-md border bg-white p-2" />
          </div>

          <form @submit.prevent="uploadFull" class="mt-4">
            <input id="file-logo-full" type="file" ref="fileFull" accept="image/*" class="sr-only" @change="onFullFileChange" />
            <label
              for="file-logo-full"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white/90 dark:bg-slate-950 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900 cursor-pointer"
            >
              <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M3 4a2 2 0 0 1 2-2h6a1 1 0 1 1 0 2H5v12h10V9a1 1 0 1 1 2 0v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4Z" />
                <path d="M14 2a1 1 0 0 1 1 1v2h2a1 1 0 1 1 0 2h-2v2a1 1 0 1 1-2 0V7h-2a1 1 0 1 1 0-2h2V3a1 1 0 0 1 1-1Z" />
              </svg>
              <span>Seleccionar archivo</span>
            </label>
            <p class="mt-2 text-xs text-slate-500 break-all">
              {{ fullFileName ? fullFileName : 'Ningún archivo seleccionado' }}
            </p>
            <div v-if="errorFull" class="text-sm text-red-600">{{ errorFull }}</div>

            <div class="mt-4">
              <button
                type="submit"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 disabled:opacity-60"
              >
                Subir logo grande
              </button>
            </div>
          </form>
        </section>
      </div>
    </div>

    <!-- Sección de Notificaciones -->
    <div class="bg-white dark:bg-slate-900 shadow sm:rounded-lg p-6 mt-6">
      <div class="border-l-4 border-blue-600 pl-4 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">🔔 Notificaciones</h2>
        <p class="text-sm text-gray-600 dark:text-slate-300">Configura las notificaciones del calendario y recordatorios</p>
      </div>
      
      <NotificationSettings />
    </div>
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount, onMounted } from 'vue';
import NotificationSettings from './NotificationSettings.vue';

const status = ref('');
const errorMark = ref('');
const errorFull = ref('');

const markLogo = ref('/images/logo_alta_calidad.png');
const fullLogo = ref('/images/logo_alta_calidad.png');

// What the UI shows right now (either persisted logo URL or local preview URL)
const markPreviewUrl = ref(markLogo.value);
const fullPreviewUrl = ref(fullLogo.value);

const markObjectUrl = ref('');
const fullObjectUrl = ref('');

const fileMark = ref(null);
const fileFull = ref(null);

const markFileName = ref('');
const fullFileName = ref('');

onMounted(async () => {
  // fetch current logo paths
  try {
    const res = await fetch('/configuracion/logo-paths');
    if (res.ok) {
      const json = await res.json();
      if (json.mark) markLogo.value = json.mark;
      if (json.full) fullLogo.value = json.full || fullLogo.value;

      markPreviewUrl.value = markLogo.value;
      fullPreviewUrl.value = fullLogo.value;
    }
  } catch (e) {
    // ignore
  }
});

onBeforeUnmount(() => {
  if (markObjectUrl.value) URL.revokeObjectURL(markObjectUrl.value);
  if (fullObjectUrl.value) URL.revokeObjectURL(fullObjectUrl.value);
});

const onMarkFileChange = (event) => {
  errorMark.value = '';
  const f = event?.target?.files?.[0];
  markFileName.value = f?.name ?? '';

  if (!f) {
    markPreviewUrl.value = markLogo.value;
    return;
  }

  if (markObjectUrl.value) URL.revokeObjectURL(markObjectUrl.value);
  markObjectUrl.value = URL.createObjectURL(f);
  markPreviewUrl.value = markObjectUrl.value;
};

const onFullFileChange = (event) => {
  errorFull.value = '';
  const f = event?.target?.files?.[0];
  fullFileName.value = f?.name ?? '';

  if (!f) {
    fullPreviewUrl.value = fullLogo.value;
    return;
  }

  if (fullObjectUrl.value) URL.revokeObjectURL(fullObjectUrl.value);
  fullObjectUrl.value = URL.createObjectURL(f);
  fullPreviewUrl.value = fullObjectUrl.value;
};

const uploadFile = async ({ endpoint, inputRef, setError, onSuccess }) => {
  setError('');
  status.value = '';

  const input = inputRef.value;
  if (!input || !input.files || input.files.length === 0) {
    setError('Selecciona una imagen.');
    return;
  }

  const form = new FormData();
  form.append('logo', input.files[0]);

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' },
      body: form,
    });

    if (res.ok) {
      const json = await res.json().catch(() => null);
      status.value = json?.message ?? 'Actualizado correctamente.';
      onSuccess(json);
      return;
    }

    if (res.status === 422) {
      const json = await res.json().catch(() => null);
      const first = json?.errors ? Object.values(json.errors).flat()?.[0] : null;
      setError(first ?? 'La imagen no es válida.');
      return;
    }

    setError('Error al subir imagen.');
  } catch (e) {
    setError('Error de conexión.');
  }
};

const uploadMark = async () => {
  await uploadFile({
    endpoint: '/configuracion/logo-mark',
    inputRef: fileMark,
    setError: (msg) => (errorMark.value = msg),
    onSuccess: (json) => {
      markLogo.value = (json?.path ?? '/storage/settings/logo_mark.png') + '?ts=' + Date.now();
      markPreviewUrl.value = markLogo.value;

      if (markObjectUrl.value) {
        URL.revokeObjectURL(markObjectUrl.value);
        markObjectUrl.value = '';
      }

      // keep SPA sidebar in sync without reload
      window.__APP_LOGO_MARK__ = (json?.path ?? '/storage/settings/logo_mark.png');
      markFileName.value = '';
      if (fileMark.value) fileMark.value.value = '';
    },
  });
};

const uploadFull = async () => {
  await uploadFile({
    endpoint: '/configuracion/logo-full',
    inputRef: fileFull,
    setError: (msg) => (errorFull.value = msg),
    onSuccess: (json) => {
      fullLogo.value = (json?.path ?? '/storage/settings/logo_full.png') + '?ts=' + Date.now();
      fullPreviewUrl.value = fullLogo.value;

      if (fullObjectUrl.value) {
        URL.revokeObjectURL(fullObjectUrl.value);
        fullObjectUrl.value = '';
      }

      window.__APP_LOGO_FULL__ = (json?.path ?? '/storage/settings/logo_full.png');
      fullFileName.value = '';
      if (fileFull.value) fileFull.value.value = '';
    },
  });
};
</script>
