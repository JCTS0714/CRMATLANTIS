<template>
  <section>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex-1">
        <label class="sr-only" for="leads-search">Buscar</label>
        <input
          id="leads-search"
          v-model="searchInput"
          type="search"
          placeholder="Buscar por nombre, empresa, contacto, email, teléfono, documento…"
          class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500"
        />
      </div>

      <div class="flex items-center gap-2">
        <input
          ref="importInput"
          type="file"
          accept=".csv,text/csv"
          class="hidden"
          @change="onImportFileSelected"
        />

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
          <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">Leads</div>
          <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
            <span v-if="pagination.total !== null">
              Mostrando {{ pagination.from ?? 0 }}–{{ pagination.to ?? 0 }} de {{ pagination.total ?? 0 }}
            </span>
          </div>
        </div>

        <div v-if="loading" class="text-xs text-gray-600 dark:text-slate-300">Cargando…</div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-[1400px] w-full text-sm text-left text-gray-700 dark:text-slate-200">
          <thead class="text-xs uppercase bg-gray-50 text-gray-700 dark:bg-slate-800 dark:text-slate-200">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">Etapa</th>
              <th class="px-4 py-3">Acciones</th>
              <th class="px-4 py-3">Nombre</th>
              <th class="px-4 py-3 text-right">Monto</th>
              <th class="px-4 py-3">Moneda</th>
              <th class="px-4 py-3">Contacto</th>
              <th class="px-4 py-3">Teléfono</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Empresa</th>
              <th class="px-4 py-3">Dirección</th>
              <th class="px-4 py-3">Doc. tipo</th>
              <th class="px-4 py-3">Doc. nro</th>
              <th class="px-4 py-3">Customer ID</th>
              <th class="px-4 py-3">Creado por</th>
              <th class="px-4 py-3">Won at</th>
              <th class="px-4 py-3">Archivado</th>
              <th class="px-4 py-3">Creado</th>
              <th class="px-4 py-3">Actualizado</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="lead in leads"
              :key="lead.id"
              class="border-b border-gray-100 bg-white hover:bg-blue-50/40 transition-colors dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60"
            >
              <td class="px-4 py-3 font-medium text-gray-900 dark:text-slate-100">{{ lead.id }}</td>

              <td class="px-4 py-3">
                <select
                  :value="lead.stage_id"
                  class="inline-block w-auto min-w-[120px] rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  :disabled="savingStageIds.has(lead.id) || isLeadLocked(lead)"
                  @change="onChangeStage(lead, $event)"
                >
                    <option
                      v-if="!stageNameById.get(lead.stage_id)"
                      selected
                      disabled
                      class="text-sm text-gray-600"
                    >
                      Etapa: {{ lead.stage_name ?? 'Cargando…' }}
                    </option>
                  <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                    {{ stage.name }}
                  </option>
                </select>
              </td>

              <td class="px-4 py-3">
                <button
                  v-if="canArchive(lead)"
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="archivingIds.has(lead.id)"
                  @click="archiveLead(lead)"
                >
                  {{ archivingIds.has(lead.id) ? 'Archivando…' : 'Archivar' }}
                </button>
                <span v-else class="text-xs text-gray-500 dark:text-slate-400">—</span>
              </td>

              <td class="px-4 py-3">{{ lead.name ?? '' }}</td>
              <td class="px-4 py-3 text-right">{{ formatMoney(lead.amount, lead.currency) }}</td>
              <td class="px-4 py-3">{{ lead.currency ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.contact_name ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.contact_phone ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.contact_email ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.company_name ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.company_address ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.document_type ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.document_number ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.customer_id ?? '' }}</td>
              <td class="px-4 py-3">{{ lead.created_by ?? '' }}</td>
              <td class="px-4 py-3">{{ formatDateTime(lead.won_at) }}</td>
              <td class="px-4 py-3">{{ formatDateTime(lead.archived_at) }}</td>
              <td class="px-4 py-3">{{ formatDateTime(lead.created_at) }}</td>
              <td class="px-4 py-3">{{ formatDateTime(lead.updated_at) }}</td>
            </tr>

            <tr v-if="!loading && leads.length === 0">
              <td colspan="19" class="px-4 py-10 text-center text-sm text-gray-600 dark:text-slate-300">
                Sin leads.
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

    <!-- Quick Lead Modal (reused UX) -->
    <Transition name="fade">
      <div v-if="quickOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="hideQuickModal"></div>

        <Transition name="pop">
          <div class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-slate-900">
            <div class="p-5 border-b border-gray-200 dark:border-slate-800">
              <div class="text-lg font-semibold text-gray-900 dark:text-slate-100">Lead rápido</div>
              <div class="mt-1 text-sm text-gray-600 dark:text-slate-300">Crea un lead sin salir de la lista.</div>
            </div>

            <form class="p-5" @submit.prevent="submitQuick">
              <div v-if="quickError" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
                {{ quickError }}
              </div>

              <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Nombre</label>
                  <input
                    v-model="quickForm.name"
                    type="text"
                    required
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Monto</label>
                  <input
                    v-model.number="quickForm.amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Moneda</label>
                  <select
                    v-model="quickForm.currency"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  >
                    <option value="PEN">PEN</option>
                    <option value="USD">USD</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Contacto</label>
                  <input
                    v-model="quickForm.contact_name"
                    type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Teléfono</label>
                  <input
                    v-model="quickForm.contact_phone"
                    type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Email</label>
                  <input
                    v-model="quickForm.contact_email"
                    type="email"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Empresa</label>
                  <input
                    v-model="quickForm.company_name"
                    type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Dirección</label>
                  <input
                    v-model="quickForm.company_address"
                    type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Tipo doc</label>
                  <select
                    v-model="quickForm.document_type"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  >
                    <option value="">(opcional)</option>
                    <option value="dni">DNI</option>
                    <option value="ruc">RUC</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Número doc</label>
                  <input
                    v-model="quickForm.document_number"
                    type="text"
                    :placeholder="documentPlaceholder"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500"
                  />
                </div>
              </div>

              <div class="mt-5 flex items-center justify-end gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  @click="hideQuickModal"
                >
                  Cancelar
                </button>

                <button
                  type="submit"
                  class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"
                  :disabled="quickSaving"
                >
                  {{ quickSaving ? 'Guardando…' : 'Crear lead' }}
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { confirmDialog, toastSuccess, toastError } from '../ui/alerts';

const loading = ref(false);
const error = ref('');

const stages = ref([]);
const totalCount = ref(0);
const leads = ref([]);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: null,
  to: null,
});

const activeStageId = ref(null);
const perPage = ref(15);

const searchInput = ref('');
const searchQuery = ref('');
let searchTimer = null;

const savingStageIds = ref(new Set());
const archivingIds = ref(new Set());

// CSV import
const importing = ref(false);
const importInput = ref(null);

const triggerImport = () => {
  const el = importInput.value;
  if (!el) return;
  el.value = '';
  el.click();
};

const onImportFileSelected = async (evt) => {
  const file = evt?.target?.files?.[0];
  if (!file) return;

  importing.value = true;
  error.value = '';

  try {
    const fd = new FormData();
    fd.append('file', file);

    const res = await axios.post('/leads/import/prospectos', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    const data = res?.data?.data;
    const created = data?.created ?? 0;
    const skipped = data?.skipped ?? 0;
    const invalid = data?.invalid ?? 0;

    toastSuccess(`Importación lista: ${created} creados, ${skipped} saltados, ${invalid} inválidos`);
    await load();
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo importar el CSV.';
    error.value = msg;
    toastError(msg);
  } finally {
    importing.value = false;
  }
};

const isLeadLocked = (lead) => {
  if (!lead) return false;
  if (lead.archived_at) return true;
  if (lead.won_at) return true;
  const currentStage = stages.value.find((s) => s.id === lead.stage_id);
  return !!currentStage?.is_won;
};

const canArchive = (lead) => {
  if (!lead) return false;
  if (lead.archived_at) return false;
  if (lead.won_at) return true;
  const currentStage = stages.value.find((s) => s.id === lead.stage_id);
  return !!currentStage?.is_won;
};

const formatMoney = (amount, currency) => {
  const number = Number(amount);
  if (amount === null || amount === undefined || Number.isNaN(number)) return '';
  if (currency === 'USD') return `$${number.toFixed(2)}`;
  return `S/ ${number.toFixed(2)}`;
};

const formatDateTime = (value) => {
  if (!value) return '';
  const dt = new Date(value);
  if (Number.isNaN(dt.getTime())) return String(value);
  return dt.toLocaleString();
};

const stageNameById = computed(() => {
  const map = new Map();
  for (const s of stages.value) map.set(s.id, s.name);
  return map;
});

const syncUrl = () => {
  const url = new URL(window.location.href);
  if (activeStageId.value) url.searchParams.set('stage_id', String(activeStageId.value));
  else url.searchParams.delete('stage_id');

  if (searchQuery.value) url.searchParams.set('q', searchQuery.value);
  else url.searchParams.delete('q');

  url.searchParams.set('page', String(pagination.value.current_page || 1));
  url.searchParams.set('per_page', String(perPage.value || 15));

  window.history.replaceState({}, '', url.toString());
};

const readInitialFilters = () => {
  const params = new URLSearchParams(window.location.search);
  const stageId = params.get('stage_id');
  const q = params.get('q');
  const page = params.get('page');
  const pp = params.get('per_page');

  activeStageId.value = stageId ? Number(stageId) : null;
  searchInput.value = q ?? '';
  searchQuery.value = q ?? '';
  pagination.value.current_page = page ? Math.max(1, Number(page)) : 1;
  perPage.value = pp ? Math.min(100, Math.max(5, Number(pp))) : 15;
};

const load = async () => {
  loading.value = true;
  error.value = '';

  try {
    const response = await axios.get('/leads/data', {
      params: {
        stage_id: activeStageId.value ?? undefined,
        q: searchQuery.value || undefined,
        page: pagination.value.current_page,
        per_page: perPage.value,
      },
    });

    const data = response?.data?.data;
    stages.value = data?.stages ?? [];
    totalCount.value = data?.total_count ?? 0;
    leads.value = data?.leads ?? [];
    pagination.value = {
      ...pagination.value,
      ...(data?.pagination ?? {}),
    };

    syncUrl();
  } catch (e) {
    error.value = e?.response?.data?.message ?? 'No se pudo cargar la lista de leads.';
  } finally {
    loading.value = false;
  }
};

const applyFilters = ({ stageId, page } = {}) => {
  if (stageId !== undefined) activeStageId.value = stageId;
  if (page !== undefined) pagination.value.current_page = page;
  load();
};

const onChangeStage = async (lead, evt) => {
  const newStageId = Number(evt?.target?.value);
  if (!lead?.id || !newStageId || lead.stage_id === newStageId) return;

  if (isLeadLocked(lead)) {
    if (evt?.target) evt.target.value = String(lead.stage_id);
    error.value = 'Este lead está GANADO o archivado: no se puede mover de etapa. Solo se puede archivar.';
    return;
  }

  const targetStage = stages.value.find((s) => s.id === newStageId);
  if (targetStage?.is_won && !lead?.customer_id) {
    const ok = await confirmDialog({
      title: 'Confirmar GANADO',
      text: '¿Marcar este lead como GANADO? Esto lo convertirá en cliente automáticamente.',
      confirmText: 'Sí, marcar como ganado',
      cancelText: 'Cancelar',
      icon: 'warning',
    });
    if (!ok) {
      if (evt?.target) evt.target.value = String(lead.stage_id);
      return;
    }
  }

  const oldStageId = lead.stage_id;

  savingStageIds.value.add(lead.id);
  error.value = '';

  // Optimistic
  lead.stage_id = newStageId;
  lead.stage_name = stageNameById.value.get(newStageId) ?? lead.stage_name;

  try {
    const res = await axios.patch(`/leads/${lead.id}/move-stage`, { stage_id: newStageId });
    const updated = res?.data?.data;
    if (updated && typeof updated === 'object') {
      Object.assign(lead, updated);
    }

    // If we are filtering by a specific stage and the lead moved out, remove it.
    if (activeStageId.value && newStageId !== activeStageId.value) {
      leads.value = leads.value.filter((l) => l.id !== lead.id);
    }

    // Refresh counts (lightweight)
    await load();
  } catch (e) {
    // rollback
    lead.stage_id = oldStageId;
    lead.stage_name = stageNameById.value.get(oldStageId) ?? lead.stage_name;
    const msg = e?.response?.data?.message ?? 'No se pudo cambiar la etapa.';
    error.value = msg;
    toastError(msg);
  } finally {
    savingStageIds.value.delete(lead.id);
  }
};

const archiveLead = async (lead) => {
  if (!lead?.id) return;
  if (!canArchive(lead)) return;

  const ok = await confirmDialog({
    title: 'Archivar lead',
    text: 'Se ocultará del pipeline, pero seguirá visible en la tabla (historial).',
    confirmText: 'Archivar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  archivingIds.value.add(lead.id);
  error.value = '';
  try {
    const res = await axios.patch(`/leads/${lead.id}/archive`);
    const updated = res?.data?.data;
    if (updated && typeof updated === 'object') {
      Object.assign(lead, updated);
    } else {
      lead.archived_at = new Date().toISOString();
    }
    toastSuccess('Lead archivado');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo archivar el lead.';
    error.value = msg;
    toastError(msg);
  } finally {
    archivingIds.value.delete(lead.id);
  }
};

// Quick lead modal
const quickOpen = ref(false);
const quickSaving = ref(false);
const quickError = ref('');

const quickForm = ref({
  name: '',
  amount: null,
  currency: 'PEN',
  contact_name: '',
  contact_phone: '',
  contact_email: '',
  company_name: '',
  company_address: '',
  document_type: '',
  document_number: '',
});

const documentPlaceholder = computed(() => {
  if (quickForm.value.document_type === 'dni') return 'Documento: DNI (8 dígitos)';
  if (quickForm.value.document_type === 'ruc') return 'Documento: RUC (11 dígitos)';
  return 'Documento: Número (opcional)';
});

const firstValidationMessage = (err) => {
  const errors = err?.response?.data?.errors;
  if (!errors || typeof errors !== 'object') return null;
  const firstKey = Object.keys(errors)[0];
  const first = firstKey ? errors[firstKey]?.[0] : null;
  return typeof first === 'string' ? first : null;
};

const showQuickModal = () => {
  quickError.value = '';
  quickForm.value = {
    name: '',
    amount: null,
    currency: 'PEN',
    contact_name: '',
    contact_phone: '',
    contact_email: '',
    company_name: '',
    company_address: '',
    document_type: '',
    document_number: '',
  };
  quickOpen.value = true;
};

const hideQuickModal = () => {
  quickOpen.value = false;
};

const submitQuick = async () => {
  quickSaving.value = true;
  quickError.value = '';

  const payload = {
    name: quickForm.value.name,
    amount: quickForm.value.amount,
    currency: quickForm.value.currency,
    contact_name: quickForm.value.contact_name || null,
    contact_phone: quickForm.value.contact_phone || null,
    contact_email: quickForm.value.contact_email || null,
    company_name: quickForm.value.company_name || null,
    company_address: quickForm.value.company_address || null,
    document_type: quickForm.value.document_type || null,
    document_number: quickForm.value.document_number || null,
  };

  try {
    await axios.post('/leads', payload);
    hideQuickModal();
    await load();
  } catch (e) {
    quickError.value = firstValidationMessage(e) ?? e?.response?.data?.message ?? 'No se pudo crear el lead.';
  } finally {
    quickSaving.value = false;
  }
};

const onExternalCreateQuick = () => showQuickModal();

onMounted(async () => {
  readInitialFilters();

  window.addEventListener('leads:create-quick', onExternalCreateQuick);
  await load();
});

watch(
  searchInput,
  (value) => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      searchQuery.value = value;
      pagination.value.current_page = 1;
      load();
    }, 350);
  },
  { flush: 'post' }
);

onBeforeUnmount(() => {
  window.removeEventListener('leads:create-quick', onExternalCreateQuick);

  if (searchTimer) {
    clearTimeout(searchTimer);
    searchTimer = null;
  }

});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.pop-enter-active,
.pop-leave-active {
  transition: transform 0.15s ease, opacity 0.15s ease;
}
.pop-enter-from,
.pop-leave-to {
  transform: translateY(8px) scale(0.98);
  opacity: 0;
}
</style>
