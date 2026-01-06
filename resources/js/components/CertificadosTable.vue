<template>
  <div class="p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="w-full sm:max-w-md">
        <input
          v-model="searchInput"
          type="text"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
          placeholder="Buscar certificado (nombre, RUC, usuario…)"
        />
      </div>

      <div class="flex items-center gap-2">
        <div class="text-sm text-slate-600 dark:text-slate-300">
          <span v-if="pagination.total">Mostrando {{ pagination.from }}–{{ pagination.to }} de {{ pagination.total }}</span>
          <span v-else>Sin resultados</span>
        </div>

        <label
          class="inline-flex cursor-pointer items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :class="importingData ? 'pointer-events-none opacity-50' : ''"
        >
          <input type="file" class="hidden" accept=".csv,.txt,text/csv" @change="onImportDataChange" />
          Importar datos (CSV)
        </label>

        <label
          class="inline-flex cursor-pointer items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :class="importingImages ? 'pointer-events-none opacity-50' : ''"
        >
          <input
            type="file"
            class="hidden"
            multiple
            accept="image/*,.pdf,application/pdf"
            @change="onImportImagesChange"
          />
          Importar imágenes
        </label>
      </div>
    </div>

    <div v-if="importStatus" class="mt-2 text-sm text-slate-600 dark:text-slate-300">
      {{ importStatus }}
    </div>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
      <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-200">
        <thead class="bg-slate-50 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
          <tr>
            <th class="px-4 py-3">Nombre</th>
            <th class="px-4 py-3">RUC</th>
            <th class="px-4 py-3">Tipo</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3">Vencimiento</th>
            <th class="px-4 py-3">Imagen</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="c in certificados"
            :key="c.id"
            class="border-t border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
          >
            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ c.nombre }}</td>
            <td class="px-4 py-3">{{ c.ruc || '—' }}</td>
            <td class="px-4 py-3">{{ c.tipo || '—' }}</td>
            <td class="px-4 py-3">{{ c.estado || '—' }}</td>
            <td class="px-4 py-3">{{ formatDate(c.fecha_vencimiento) || '—' }}</td>
            <td class="px-4 py-3">
              <a v-if="c.imagen" :href="publicFileUrl(c.imagen)" target="_blank" rel="noreferrer">
                <img
                  v-if="!isPdf(c.imagen)"
                  :src="publicFileUrl(c.imagen)"
                  alt=""
                  class="h-10 w-10 rounded object-cover"
                />
                <span
                  v-else
                  class="inline-flex items-center rounded-md border border-slate-300 px-2 py-1 text-xs text-slate-700 dark:border-slate-700 dark:text-slate-200"
                >
                  PDF
                </span>
              </a>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="savingIds.has(c.id)"
                  @click="editCertificado(c)"
                >
                  Editar
                </button>
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-60 dark:border-red-900/40 dark:bg-slate-900 dark:text-red-300 dark:hover:bg-red-950/30"
                  :disabled="deletingIds.has(c.id)"
                  @click="deleteCertificado(c)"
                >
                  Eliminar
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <button
        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="pagination.current_page <= 1 || loading"
        @click="goToPage(pagination.current_page - 1)"
      >
        Anterior
      </button>

      <div class="text-sm text-slate-600 dark:text-slate-300">
        Página {{ pagination.current_page }} / {{ pagination.last_page }}
      </div>

      <button
        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="pagination.current_page >= pagination.last_page || loading"
        @click="goToPage(pagination.current_page + 1)"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { confirmDialog, promptCertificadoCreate, promptCertificadoEdit, toastError, toastSuccess } from '../ui/alerts';

const publicFileUrl = (path) => {
  if (!path) return '';
  const s = String(path);
  if (s.startsWith('http://') || s.startsWith('https://')) return s;
  return `/storage/${s.replace(/^\/+/, '')}`;
};

const isPdf = (path) => String(path || '').toLowerCase().endsWith('.pdf');

const formatDate = (value) => {
  if (!value) return '';
  const s = String(value).trim();
  if (s === '') return '';

  // Handles "YYYY-MM-DD" reliably without timezone shifts.
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (m) return `${m[3]}/${m[2]}/${m[1]}`;

  const d = new Date(s);
  if (Number.isNaN(d.getTime())) return s;
  return new Intl.DateTimeFormat('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(d);
};

const certificados = ref([]);
const loading = ref(false);
const savingIds = ref(new Set());
const deletingIds = ref(new Set());

const searchInput = ref('');
let searchTimeout = null;

const importingData = ref(false);
const importingImages = ref(false);
const importStatus = ref('');

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const fetchRows = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await axios.get('/postventa/certificados/data', {
      params: {
        q: searchInput.value || '',
        per_page: pagination.value.per_page,
        page,
      },
    });

    certificados.value = data.certificados || [];
    pagination.value = {
      ...pagination.value,
      ...(data.pagination || {}),
    };
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => {
  const p = Math.max(1, Math.min(pagination.value.last_page || 1, page));
  fetchRows(p);
};

const onImportDataChange = async (evt) => {
  const file = evt?.target?.files?.[0];
  if (evt?.target) evt.target.value = '';
  if (!file) return;

  importingData.value = true;
  importStatus.value = 'Importando datos...';
  try {
    const fd = new FormData();
    fd.append('csv', file);
    const { data } = await axios.post('/postventa/certificados/import-data', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const output = (data?.output ?? '').trim();
    importStatus.value = output !== '' ? output : 'Importación de datos finalizada.';
    toastSuccess('Datos importados');
    fetchRows(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo importar el CSV.';
    importStatus.value = msg;
    toastError(msg);
  } finally {
    importingData.value = false;
  }
};

const onImportImagesChange = async (evt) => {
  const files = Array.from(evt?.target?.files ?? []);
  if (evt?.target) evt.target.value = '';
  if (!files.length) return;

  importingImages.value = true;
  importStatus.value = 'Importando imágenes...';
  try {
    const fd = new FormData();
    files.forEach((f) => fd.append('images[]', f));
    const { data } = await axios.post('/postventa/certificados/import-images', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    if (typeof data?.updated === 'number') {
      importStatus.value = `Importación de imágenes finalizada. Actualizados: ${data.updated} · Saltados: ${data.skipped ?? 0} · Sin match: ${data.unmatched ?? 0}`;
    } else {
      const output = (data?.output ?? '').trim();
      importStatus.value = output !== '' ? output : 'Importación de imágenes finalizada.';
    }
    toastSuccess('Imágenes importadas');
    fetchRows(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo importar el ZIP.';
    importStatus.value = msg;
    toastError(msg);
  } finally {
    importingImages.value = false;
  }
};

const createCertificado = async () => {
  const payload = await promptCertificadoCreate();
  if (!payload) return;

  try {
    if (payload?.imagen instanceof File) {
      const form = new FormData();
      Object.entries(payload).forEach(([key, value]) => {
        if (value === undefined || value === null) return;
        form.append(key, value);
      });

      await axios.post('/postventa/certificados', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } else {
      await axios.post('/postventa/certificados', payload);
    }
    toastSuccess('Certificado creado');
    fetchRows(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo crear el certificado.';
    toastError(msg);
  }
};

const editCertificado = async (c) => {
  if (!c?.id) return;
  const payload = await promptCertificadoEdit(c);
  if (!payload) return;

  savingIds.value.add(c.id);
  try {
    let res;
    if (payload?.imagen instanceof File) {
      const form = new FormData();
      form.append('_method', 'PUT');
      Object.entries(payload).forEach(([key, value]) => {
        if (value === undefined || value === null) return;
        form.append(key, value);
      });

      res = await axios.post(`/postventa/certificados/${c.id}`, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } else {
      res = await axios.put(`/postventa/certificados/${c.id}`, payload);
    }

    const updated = res?.data?.data;
    Object.assign(c, updated && typeof updated === 'object' ? updated : payload);
    toastSuccess('Certificado actualizado');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo actualizar el certificado.';
    toastError(msg);
  } finally {
    savingIds.value.delete(c.id);
  }
};

const deleteCertificado = async (c) => {
  if (!c?.id) return;
  const ok = await confirmDialog({
    title: 'Eliminar certificado',
    text: 'Se eliminará el certificado.',
    confirmText: 'Eliminar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  deletingIds.value.add(c.id);
  try {
    await axios.delete(`/postventa/certificados/${c.id}`);
    certificados.value = certificados.value.filter((x) => x.id !== c.id);
    toastSuccess('Certificado eliminado');
    fetchRows(Math.min(pagination.value.current_page, pagination.value.last_page || 1));
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo eliminar el certificado.';
    toastError(msg);
  } finally {
    deletingIds.value.delete(c.id);
  }
};

const onCreateEvent = () => createCertificado();

onMounted(() => {
  window.addEventListener('certificados:create', onCreateEvent);
});

onBeforeUnmount(() => {
  window.removeEventListener('certificados:create', onCreateEvent);
});

watch(
  searchInput,
  () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      fetchRows(1);
    }, 300);
  },
  { flush: 'post' }
);

fetchRows(1);
</script>
