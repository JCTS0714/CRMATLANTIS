<template>
  <div class="mx-auto max-w-5xl space-y-6">
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-200">
              Identidad visual
            </span>
            <h2 class="mt-3 text-xl font-semibold text-slate-900 dark:text-slate-100">Personalización del sistema</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
              Gestiona los recursos visuales que aparecen en login, menú lateral y superficies clave del CRM.
            </p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
            Formatos recomendados: PNG transparente, contraste alto y fondo limpio.
          </div>
        </div>
      </div>

      <div class="px-6 py-6">
        <div v-if="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-200">{{ status }}</div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
          <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/40">
          <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Ícono principal</h3>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Se usa en login y en el menú contraído.</p>
          <p class="mt-1 text-xs text-slate-500">
            Recomendado: PNG con fondo transparente, 256×256 (mín. 128×128). Se mostrará aprox. a 56×56 en el menú y 56×56 en login.
          </p>

          <div class="mt-4">
            <img :src="markPreviewUrl" alt="Ícono actual" class="h-28 w-28 rounded-2xl border border-slate-200 bg-white p-3 object-contain shadow-sm dark:border-slate-700 dark:bg-slate-900" />
          </div>

          <form @submit.prevent="uploadMark" class="mt-4">
            <input id="file-logo-mark" type="file" ref="fileMark" accept="image/*" class="sr-only" @change="onMarkFileChange" />
            <label
              for="file-logo-mark"
              class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
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
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-60"
              >
                Subir ícono
              </button>
            </div>
          </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/40">
          <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Logo extendido</h3>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Se usa en el menú expandido.</p>
          <p class="mt-1 text-xs text-slate-500">
            Recomendado: PNG transparente, proporción 4:1 (ej. 640×160 o 800×200). Se mostrará aprox. a 224×56 en el menú.
          </p>

          <div class="mt-4">
            <img :src="fullPreviewUrl" alt="Logo grande actual" class="h-24 w-full rounded-2xl border border-slate-200 bg-white p-3 object-contain shadow-sm dark:border-slate-700 dark:bg-slate-900" />
          </div>

          <form @submit.prevent="uploadFull" class="mt-4">
            <input id="file-logo-full" type="file" ref="fileFull" accept="image/*" class="sr-only" @change="onFullFileChange" />
            <label
              for="file-logo-full"
              class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
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
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-60"
              >
                Subir logo grande
              </button>
            </div>
          </form>
        </section>
      </div>
      </div>
    </section>

    <!-- Sección de Notificaciones -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
        <div class="flex items-start gap-4">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-200">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path d="M10 2a5 5 0 00-5 5v2.382l-.724 1.447A1 1 0 005.171 12h9.658a1 1 0 00.895-1.447L15 9.382V7a5 5 0 00-5-5Zm0 16a2.5 2.5 0 002.45-2h-4.9A2.5 2.5 0 0010 18Z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Notificaciones y recordatorios</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Configura alertas del calendario, sonido y tiempos por defecto desde una sola superficie.</p>
          </div>
        </div>
      </div>

      <div class="px-6 py-6">
        <NotificationSettings />
      </div>
    </section>
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
