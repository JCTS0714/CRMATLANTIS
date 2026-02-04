<template>
  <section>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex-1">
        <label class="sr-only" for="inc-search">Buscar</label>
        <input
          id="inc-search"
          v-model="searchInput"
          type="search"
          placeholder="Buscar por título, correlativo, cliente…"
          class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500"
        />
      </div>

      <div class="flex items-center gap-2">
        <input ref="importInput" type="file" accept=".csv,text/csv" class="hidden" @change="onImportFileSelected" />
        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="importing"
          @click="triggerImport"
        >
          {{ importing ? 'Importando…' : 'Importar CSV' }}
        </button>

        <select
          v-model.number="perPage"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
          @change="applyFilters({ page: 1 })"
        >
          <option :value="10">10</option>
          <option :value="15">15</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
      </div>
    </div>

    <div class="mb-4 overflow-x-auto">
      <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <button
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition"
          :class="activeStageId === null ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-800'"
          @click="applyFilters({ stageId: null, page: 1 })"
        >
          Todos
          <span class="ml-2 rounded bg-black/5 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-slate-200">{{ totalCount }}</span>
        </button>

        <button
          v-for="stage in stages"
          :key="stage.id"
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition"
          :class="activeStageId === stage.id ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-800'"
          @click="applyFilters({ stageId: stage.id, page: 1 })"
        >
          {{ stage.name }}
          <span class="ml-2 rounded bg-black/5 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-slate-200">{{ stage.count ?? 0 }}</span>
        </button>
      </div>
    </div>

    <div v-if="error" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
      {{ error }}
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-slate-900 dark:border-slate-800">
      <div class="p-4 border-b border-gray-200 dark:border-slate-800 flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">Incidencias</div>
          <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
            <span v-if="pagination.total !== null">
              Mostrando {{ pagination.from ?? 0 }}–{{ pagination.to ?? 0 }} de {{ pagination.total ?? 0 }}
            </span>
          </div>
        </div>

        <div v-if="loading" class="text-xs text-gray-600 dark:text-slate-300">Cargando…</div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-[1300px] w-full text-sm text-left text-gray-700 dark:text-slate-200">
          <thead class="text-xs uppercase bg-gray-50 text-gray-700 dark:bg-slate-800 dark:text-slate-200">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">Correlativo</th>
              <th class="px-4 py-3">Acciones</th>
              <th class="px-4 py-3">Título</th>
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Prioridad</th>
              <th class="px-4 py-3">Fecha</th>
              <th class="px-4 py-3">Archivado</th>
              <th class="px-4 py-3">Actualizado</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="it in incidences"
              :key="it.id"
              class="border-b border-gray-100 bg-white hover:bg-blue-50/40 transition-colors dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60"
            >
              <td class="px-4 py-3 font-medium text-gray-900 dark:text-slate-100">{{ it.id }}</td>
              <td class="px-4 py-3">{{ it.correlative || '—' }}</td>

              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    :disabled="isLocked(it)"
                    @click="openEdit(it)"
                  >
                    Editar
                  </button>

                  <button
                    v-if="canArchive(it)"
                    type="button"
                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    :disabled="archivingIds.has(it.id)"
                    @click="archive(it)"
                  >
                    {{ archivingIds.has(it.id) ? 'Archivando…' : 'Archivar' }}
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 disabled:opacity-60 dark:border-red-800 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900"
                    :disabled="deletingIds.has(it.id)"
                    @click="confirmDelete(it)"
                  >
                    <svg v-if="!deletingIds.has(it.id)" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <svg v-else class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ deletingIds.has(it.id) ? 'Eliminando…' : 'Eliminar' }}
                  </button>
                </div>
              </td>

              <td class="px-4 py-3">{{ it.title ?? '' }}</td>
              <td class="px-4 py-3">
                <span v-if="it.customer">{{ it.customer.company_name || it.customer.name }}</span>
                <span v-else class="text-gray-500 dark:text-slate-400">—</span>
              </td>
              <td class="px-4 py-3">{{ it.priority ?? '' }}</td>
              <td class="px-4 py-3">{{ formatDate(it.date) }}</td>
              <td class="px-4 py-3">{{ formatDateTime(it.archived_at) }}</td>
              <td class="px-4 py-3">{{ formatDateTime(it.updated_at) }}</td>
            </tr>

            <tr v-if="!loading && incidences.length === 0">
              <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-600 dark:text-slate-300">
                Sin incidencias.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="p-4 flex items-center justify-between gap-3">
        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="loading || pagination.current_page <= 1"
          @click="applyFilters({ page: pagination.current_page - 1 })"
        >
          Anterior
        </button>

        <div class="text-xs text-gray-600 dark:text-slate-300">
          Página {{ pagination.current_page }} / {{ pagination.last_page }}
        </div>

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="loading || pagination.current_page >= pagination.last_page"
          @click="applyFilters({ page: pagination.current_page + 1 })"
        >
          Siguiente
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { confirmDialog, toastError, toastSuccess } from '../ui/alerts';

const stages = ref([]);
const incidences = ref([]);
const totalCount = ref(0);

const loading = ref(false);
const error = ref('');

const archivingIds = ref(new Set());
const deletingIds = ref(new Set());

const importInput = ref(null);
const importing = ref(false);

const searchInput = ref('');
const searchQuery = ref('');
let searchTimer = null;

const perPage = ref(15);
const activeStageId = ref(null);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const readInitialFilters = () => {
  const url = new URL(window.location.href);
  const q = url.searchParams.get('q');
  if (q) {
    searchInput.value = q;
    searchQuery.value = q;
  }
};

const applyFilters = ({ stageId = activeStageId.value, page = pagination.value.current_page } = {}) => {
  activeStageId.value = stageId === undefined ? activeStageId.value : stageId;
  pagination.value.current_page = page;
  load();
};

const load = async () => {
  loading.value = true;
  error.value = '';

  try {
    const res = await axios.get('/incidencias/data', {
      params: {
        stage_id: activeStageId.value,
        q: searchQuery.value || '',
        page: pagination.value.current_page,
        per_page: perPage.value,
      },
    });

    const data = res?.data?.data ?? {};
    stages.value = data.stages ?? [];
    totalCount.value = data.total_count ?? 0;
    incidences.value = data.incidences ?? [];
    pagination.value = { ...pagination.value, ...(data.pagination || {}) };
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo cargar incidencias.';
    error.value = msg;
  } finally {
    loading.value = false;
  }
};

const triggerImport = () => {
  importInput.value?.click?.();
};

const onImportFileSelected = async (ev) => {
  const file = ev?.target?.files?.[0];
  if (!file) return;

  importing.value = true;
  error.value = '';
  try {
    const fd = new FormData();
    fd.append('csv', file);
    const { data } = await axios.post('/incidencias/import', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    toastSuccess(data?.output?.trim() ? 'Importación finalizada' : 'Importación finalizada');
    pagination.value.current_page = 1;
    await load();
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo importar el CSV.';
    error.value = msg;
    toastError(msg);
  } finally {
    importing.value = false;
    if (importInput.value) importInput.value.value = '';
  }
};

const stageById = computed(() => {
  const m = new Map();
  for (const s of stages.value) m.set(s.id, s);
  return m;
});

const isLocked = (it) => !!it?.archived_at;

const openEdit = (it) => {
  if (!it?.id) return;
  window.dispatchEvent(new CustomEvent('incidencias:edit', { detail: { incidence: it } }));
};

const canArchive = (it) => {
  if (!it || isLocked(it)) return false;
  const stage = stageById.value.get(it.stage_id);
  return !!stage?.is_done;
};

const archive = async (it) => {
  if (!it?.id) return;
  if (!canArchive(it)) return;

  const ok = await confirmDialog({
    title: 'Archivar incidencia',
    text: 'Se ocultará del backlog, pero seguirá visible en la tabla (historial).',
    confirmText: 'Archivar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  archivingIds.value.add(it.id);
  error.value = '';
  try {
    const res = await axios.patch(`/incidencias/${it.id}/archive`);
    const updated = res?.data?.data;
    if (updated && typeof updated === 'object') {
      Object.assign(it, updated);
    } else {
      it.archived_at = new Date().toISOString();
    }
    toastSuccess('Incidencia archivada');
    window.dispatchEvent(new CustomEvent('incidencias:archived'));
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo archivar la incidencia.';
    error.value = msg;
    toastError(msg);
  } finally {
    archivingIds.value.delete(it.id);
  }
};

const confirmDelete = async (incidence) => {
  const confirmed = await confirmDialog({
    title: '¿Eliminar incidencia?',
    text: `Se eliminará permanentemente "${incidence.title || `#${incidence.correlative}`}"`,
    confirmText: 'Sí, eliminar',
    cancelText: 'Cancelar',
    icon: 'warning',
    confirmButtonColor: '#ef4444'
  });

  if (confirmed) {
    await deleteIncidence(incidence);
  }
};

const deleteIncidence = async (incidence) => {
  deletingIds.value.add(incidence.id);
  error.value = '';

  try {
    await axios.delete(`/incidencias/${incidence.id}`);
    
    // Remove from local state
    const index = incidences.value.findIndex(i => i.id === incidence.id);
    if (index !== -1) {
      incidences.value.splice(index, 1);
      totalCount.value = Math.max(0, totalCount.value - 1);
      
      // Update pagination if needed
      if (incidences.value.length === 0 && pagination.value.current_page > 1) {
        pagination.value.current_page--;
        load();
        return;
      }
    }
    
    toastSuccess('Incidencia eliminada exitosamente');
    window.dispatchEvent(new CustomEvent('incidencias:deleted'));
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo eliminar la incidencia.';
    error.value = msg;
    toastError(msg);
  } finally {
    deletingIds.value.delete(incidence.id);
  }
};

const formatDate = (iso) => {
  if (!iso) return '';
  try {
    const d = new Date(String(iso));
    if (Number.isNaN(d.getTime())) return String(iso);
    return d.toLocaleDateString();
  } catch {
    return String(iso);
  }
};

const formatDateTime = (iso) => {
  if (!iso) return '';
  try {
    const d = new Date(String(iso));
    if (Number.isNaN(d.getTime())) return String(iso);
    return d.toLocaleString();
  } catch {
    return String(iso);
  }
};

const onCreated = () => {
  pagination.value.current_page = 1;
  load();
};

const onUpdated = (e) => {
  const updated = e?.detail?.incidence;
  if (!updated?.id) return;
  const idx = incidences.value.findIndex((x) => x.id === updated.id);
  if (idx !== -1) {
    incidences.value.splice(idx, 1, { ...incidences.value[idx], ...updated });
  }
};

onMounted(async () => {
  readInitialFilters();
  window.addEventListener('incidencias:created', onCreated);
  window.addEventListener('incidencias:archived', onCreated);
  window.addEventListener('incidencias:updated', onUpdated);
  await load();
});

onBeforeUnmount(() => {
  window.removeEventListener('incidencias:created', onCreated);
  window.removeEventListener('incidencias:archived', onCreated);
  window.removeEventListener('incidencias:updated', onUpdated);
  if (searchTimer) window.clearTimeout(searchTimer);
});

watch(
  searchInput,
  (value) => {
    if (searchTimer) window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
      searchQuery.value = value;
      pagination.value.current_page = 1;
      load();
    }, 350);
  },
  { flush: 'post' }
);
</script>
