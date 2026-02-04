<template>
  <div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="w-full sm:max-w-xl">
        <label class="sr-only" for="waitinglead-search">Buscar</label>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path
                fill-rule="evenodd"
                d="M9 3a6 6 0 104.472 10.03l2.249 2.248a1 1 0 001.414-1.414l-2.248-2.249A6 6 0 009 3zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <input
            id="waitinglead-search"
            v-model="q"
            @keyup.enter="load(1)"
            placeholder="Buscar (nombre, contacto, empresa, documento, observación...)"
            class="block w-full rounded-lg border border-slate-700 bg-slate-900/40 pl-9 pr-3 py-2 text-sm text-slate-100 placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
          />
        </div>

        <div class="mt-2 text-xs text-slate-300">
          <span v-if="pagination.total">Mostrando {{ rangeText }}</span>
          <span v-else>Sin resultados</span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input ref="importInput" type="file" accept=".csv,text/csv" class="hidden" @change="onImportFileSelected" />
        <button
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-lg border border-slate-700 text-slate-200 hover:bg-slate-800 disabled:opacity-50"
          :disabled="importing"
          @click="triggerImport"
        >
          {{ importing ? 'Importando…' : 'Importar CSV' }}
        </button>

        <button
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-lg border border-slate-700 text-slate-200 hover:bg-slate-800 disabled:opacity-50"
          :disabled="loading || pagination.current_page <= 1"
          @click="load(pagination.current_page - 1)"
        >
          Anterior
        </button>
        <button
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-lg border border-slate-700 text-slate-200 hover:bg-slate-800 disabled:opacity-50"
          :disabled="loading || pagination.current_page >= pagination.last_page"
          @click="load(pagination.current_page + 1)"
        >
          Siguiente
        </button>
      </div>
    </div>

    <div class="bg-slate-900/40 border border-slate-800 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-900/70 border-b border-slate-800">
            <tr class="text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">
              <th class="px-4 py-3">Lead</th>
              <th class="px-4 py-3">Empresa</th>
              <th class="px-4 py-3">Documento</th>
              <th class="px-4 py-3">Teléfono</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Fecha</th>
              <th class="px-4 py-3">Acciones</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-800">
            <tr v-if="loading">
              <td class="px-4 py-6 text-slate-300" colspan="7">Cargando zona de espera…</td>
            </tr>

            <tr v-else-if="items.length === 0">
              <td class="px-4 py-10 text-slate-300" colspan="7">
                No hay leads en zona de espera.
              </td>
            </tr>

            <tr v-else v-for="item in items" :key="item.id" class="text-slate-100">
              <td class="px-4 py-3">
                <div class="font-semibold">{{ item.name || '—' }}</div>
                <div class="text-xs text-slate-300">{{ item.contact_name || '—' }}</div>
                <div v-if="item.observacion" class="mt-2 text-xs text-slate-300 line-clamp-2">{{ item.observacion }}</div>
              </td>
              <td class="px-4 py-3 text-slate-200">{{ item.company_name || '—' }}</td>
              <td class="px-4 py-3 text-slate-200">
                <span v-if="item.document_type || item.document_number">
                  {{ item.document_type || '' }} {{ item.document_number || '' }}
                </span>
                <span v-else>—</span>
              </td>
              <td class="px-4 py-3 text-slate-200">{{ item.contact_phone || '—' }}</td>
              <td class="px-4 py-3 text-slate-200">
                <span class="break-all">{{ item.contact_email || '—' }}</span>
              </td>
              <td class="px-4 py-3 text-slate-300 whitespace-nowrap">{{ formatDate(item.created_at) }}</td>

              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-slate-700 bg-slate-900/40 px-3 py-2 text-xs font-medium text-slate-200 hover:bg-slate-800"
                    @click="openModal(item.id)"
                  >
                    Ver
                  </button>
                  
                  <button
                    type="button"
                    class="inline-flex items-center rounded-lg bg-green-600 px-3 py-2 text-xs font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 disabled:opacity-60"
                    :disabled="reactivating.has(item.id)"
                    @click="reactivateLead(item)"
                  >
                    {{ reactivating.has(item.id) ? 'Reactivando...' : 'Reactivar' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Observación Modal -->
    <Transition name="fade">
      <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>

        <Transition name="pop">
          <div class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-slate-900">
            <div class="p-5 border-b border-gray-200 dark:border-slate-800">
              <div class="text-lg font-semibold text-gray-900 dark:text-slate-100">Zona de espera</div>
              <div class="mt-1 text-sm text-gray-600 dark:text-slate-300">
                {{ modalItem?.name || '—' }}
                <span v-if="modalItem?.company_name">· {{ modalItem.company_name }}</span>
              </div>
            </div>

            <div class="p-5">
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                  <div class="text-xs font-medium text-gray-600 dark:text-slate-300">Contacto</div>
                  <div class="mt-1 text-sm text-gray-900 dark:text-slate-100">{{ modalItem?.contact_name || '—' }}</div>
                </div>
                <div>
                  <div class="text-xs font-medium text-gray-600 dark:text-slate-300">Teléfono</div>
                  <div class="mt-1 text-sm text-gray-900 dark:text-slate-100">{{ modalItem?.contact_phone || '—' }}</div>
                </div>
                <div>
                  <div class="text-xs font-medium text-gray-600 dark:text-slate-300">Email</div>
                  <div class="mt-1 text-sm text-gray-900 dark:text-slate-100 break-all">{{ modalItem?.contact_email || '—' }}</div>
                </div>
                <div>
                  <div class="text-xs font-medium text-gray-600 dark:text-slate-300">Documento</div>
                  <div class="mt-1 text-sm text-gray-900 dark:text-slate-100">
                    <span v-if="modalItem?.document_type || modalItem?.document_number">
                      {{ modalItem?.document_type || '' }} {{ modalItem?.document_number || '' }}
                    </span>
                    <span v-else>—</span>
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Observación</label>
                <textarea
                  ref="observacionEl"
                  v-model="observacion"
                  rows="5"
                  class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  placeholder="Escribe la observación..."
                ></textarea>
              </div>
            </div>

            <div class="p-5 border-t border-gray-200 dark:border-slate-800 flex items-center justify-end gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="saving"
                @click="closeModal"
              >
                Cerrar
              </button>

              <button
                type="button"
                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-60"
                :disabled="saving || !modalItem"
                @click="saveObservacion"
              >
                {{ saving ? 'Guardando…' : 'Guardar' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import axios from 'axios';
import { confirmDialog, toastError, toastSuccess } from '../ui/alerts';

const q = ref('');
const items = ref([]);
const loading = ref(false);
const reactivating = ref(new Set());

const importInput = ref(null);
const importing = ref(false);

const modalOpen = ref(false);
const modalItem = ref(null);
const observacion = ref('');
const saving = ref(false);
const observacionEl = ref(null);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 25,
  total: 0,
});

const rangeText = computed(() => {
  const total = pagination.value.total || 0;
  if (!total) return '';
  const current = pagination.value.current_page || 1;
  const perPage = pagination.value.per_page || 25;
  const start = (current - 1) * perPage + 1;
  const end = Math.min(current * perPage, total);
  return `${start}-${end} de ${total}`;
});

const load = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get('/espera/data', {
      params: {
        q: q.value,
        page,
        per_page: pagination.value.per_page,
      },
    });
    items.value = res?.data?.data?.items ?? [];
    pagination.value = {
      current_page: res?.data?.data?.pagination?.current_page ?? 1,
      last_page: res?.data?.data?.pagination?.last_page ?? 1,
      per_page: res?.data?.data?.pagination?.per_page ?? pagination.value.per_page,
      total: res?.data?.data?.pagination?.total ?? 0,
    };
  } catch (e) {
    console.error(e);
    items.value = [];
    pagination.value = { current_page: 1, last_page: 1, per_page: pagination.value.per_page, total: 0 };
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
  try {
    const fd = new FormData();
    fd.append('csv', file);
    const { data } = await axios.post('/espera/import', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    toastSuccess(data?.output?.trim() ? 'Importación finalizada' : 'Importación finalizada');
    await load(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo importar el CSV.';
    toastError(msg);
  } finally {
    importing.value = false;
    if (importInput.value) importInput.value.value = '';
  }
};

const formatDate = (d) => (d ? new Date(d).toLocaleString() : '');

const closeModal = () => {
  modalOpen.value = false;
  modalItem.value = null;
  observacion.value = '';
};

const focusObservacionIfRequested = async () => {
  const params = new URLSearchParams(window.location.search);
  const focus = params.get('focus');
  if (focus !== 'observacion') return;
  await nextTick();
  observacionEl.value?.focus?.();
};

const openModal = async (id) => {
  const numericId = Number(id);
  if (!Number.isFinite(numericId) || numericId <= 0) return;

  try {
    const res = await axios.get(`/espera/${numericId}`);
    modalItem.value = res?.data?.data ?? null;
    observacion.value = modalItem.value?.observacion ?? '';
    modalOpen.value = true;
    await focusObservacionIfRequested();
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo abrir el registro.';
    toastError(msg);
  }
};

const saveObservacion = async () => {
  if (!modalItem.value?.id) return;
  saving.value = true;
  try {
    const res = await axios.patch(`/espera/${modalItem.value.id}`, { observacion: observacion.value || null });
    const updated = res?.data?.data;
    if (updated) {
      modalItem.value = updated;
      observacion.value = updated.observacion ?? '';
      const idx = items.value.findIndex((x) => x.id === updated.id);
      if (idx !== -1) items.value.splice(idx, 1, { ...items.value[idx], ...updated });
    }
    toastSuccess('Observación guardada');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo guardar la observación.';
    toastError(msg);
  } finally {
    saving.value = false;
  }
};

const reactivateLead = async (item) => {
  if (!item?.id) return;
  
  const ok = await confirmDialog({
    title: 'Reactivar lead',
    text: `¿Estás seguro que deseas reactivar el lead "${item.name}"? Esto lo moverá de vuelta al pipeline de leads.`,
    confirmText: 'Sí, reactivar',
    cancelText: 'Cancelar',
    icon: 'question',
  });
  
  if (!ok) return;
  
  reactivating.value.add(item.id);
  
  try {
    const response = await axios.post(`/espera/${item.id}/reactivate`);
    
    // Remover de la lista
    const index = items.value.findIndex(i => i.id === item.id);
    if (index !== -1) {
      items.value.splice(index, 1);
      // Actualizar paginación
      pagination.value.total = Math.max(0, pagination.value.total - 1);
    }
    
    toastSuccess('Lead reactivado exitosamente');
    
    // Opcional: redirigir al pipeline de leads
    setTimeout(() => {
      window.location.assign('/leads');
    }, 1500);
    
  } catch (error) {
    console.error('Error reactivating lead:', error);
    const msg = error?.response?.data?.message ?? 'No se pudo reactivar el lead';
    toastError(msg);
  } finally {
    reactivating.value.delete(item.id);
  }
};

onMounted(async () => {
  console.log('WaitingLeadsList mounted - reactivation functionality loaded');
  await load(1);

  const params = new URLSearchParams(window.location.search);
  const open = params.get('open');
  if (open) {
    await openModal(open);
    // Avoid reopening if user refreshes
    try {
      window.history.replaceState({}, '', '/espera');
    } catch (_) {
      // ignore
    }
  }
});
</script>
