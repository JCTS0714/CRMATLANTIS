<template>
  <div>
    <div class="mb-4 text-sm text-gray-600 dark:text-slate-300">
      Arrastra un lead entre columnas para cambiar su etapa.
    </div>

      <div class="mb-4 flex items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Limite:</label>
          <select
            v-model.number="limit"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
          >
            <option :value="20">20</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
            <option :value="250">250</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Desde:</label>
          <input
            v-model="dateFrom"
            type="date"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
          />
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Hasta:</label>
          <input
            v-model="dateTo"
            type="date"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
          />
        </div>

        <div class="flex items-center gap-2 ms-auto">
          <button @click="applyFilters" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1 text-sm text-white">Aplicar</button>
          <button
            @click="clearFilters"
            class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1 text-sm text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          >
            Limpiar
          </button>
        </div>
      </div>

    <div v-if="loading" class="text-sm text-gray-600 dark:text-slate-300">Cargando...</div>
    <div v-else-if="moveError" class="mb-3 text-sm text-red-600">{{ moveError }}</div>

    <div v-else class="grid gap-4" :class="gridColsClass">
      <section
        v-for="stage in stages"
        :key="stage.id"
        :data-stage-id="stage.id"
        class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800"
        @dragover.prevent
        @drop.prevent="onDrop(stage)"
      >
        <div class="p-4 border-b border-gray-200 dark:border-slate-800">
          <div class="flex items-center justify-between gap-3">
            <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ stage.name }}</div>
            <div class="text-xs text-gray-600 dark:text-slate-300">{{ stage.count }}</div>
          </div>
        </div>

        <div class="p-3 space-y-3 min-h-[140px]">
          <div v-if="(stage.leads?.length ?? 0) === 0" class="text-sm text-gray-500 dark:text-slate-400 px-1">
            Sin leads.
          </div>

          <div v-for="lead in stage.leads" :key="lead.id" :data-lead-id="lead.id">
            <!-- preview handled in-memory; remove visual dashed placeholder -->

            <article
              :data-lead-id="lead.id"
              class="select-none bg-gray-50 border border-gray-200 rounded-lg p-3 transition-all duration-150 dark:bg-slate-800 dark:border-slate-700"
              :class="{
                'scale-105 shadow-2xl opacity-95 z-50 -rotate-1 transform': draggingId === lead.id,
                'opacity-40': draggingId && draggingId !== lead.id,
                'cursor-not-allowed opacity-80': isLeadLocked(lead, stage),
                'cursor-grab active:cursor-grabbing hover:bg-blue-50/50 dark:hover:bg-slate-800/80': !isLeadLocked(lead, stage)
              }"
              :draggable="!isLeadLocked(lead, stage)"
              @dragstart="onDragStart(lead, stage)"
              @dragend="onDragEnd"
              @dragover.prevent="onDragOver(lead, stage, $event)"
              @drop.prevent="onDrop(stage, lead, $event)"
              @click="openEditModal(lead)"
            >
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ lead.name }}</div>
                <div v-if="lead.company_name" class="text-xs text-gray-600 dark:text-slate-300 mt-1">
                  {{ lead.company_name }}
                </div>
                <div v-else-if="lead.contact_name" class="text-xs text-gray-600 dark:text-slate-300 mt-1">
                  {{ lead.contact_name }}
                </div>
              </div>

              <div v-if="lead.amount !== null && lead.amount !== undefined" class="text-xs text-gray-700 dark:text-slate-200">
                {{ formatMoney(lead.amount, lead.currency) }}
              </div>
            </div>

            <div v-if="lead.contact_phone || lead.contact_email" class="mt-2 text-xs text-gray-600 dark:text-slate-300">
              <div v-if="lead.contact_phone">{{ lead.contact_phone }}</div>
              <div v-if="lead.contact_email">{{ lead.contact_email }}</div>
            </div>

            <div v-if="stage.is_won" class="mt-3 flex justify-end">
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="archivingIds.has(lead.id)"
                @click.stop.prevent="archiveLead(lead, stage)"
              >
                {{ archivingIds.has(lead.id) ? 'Archivando…' : 'Archivar' }}
              </button>
            </div>
            </article>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Quick Lead Modal (Kommo-like) -->
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-show="quickOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
      role="dialog"
      aria-modal="true"
      @click.self="hideQuickModal"
    >
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-show="quickOpen" class="relative w-full max-w-lg">
          <div class="relative bg-white rounded-lg shadow dark:bg-slate-900">
            <div class="p-4 md:p-5 border-b rounded-t dark:border-slate-800">
              <div class="text-xs font-semibold text-gray-500 dark:text-slate-300 uppercase tracking-wider">Contacto inicial</div>
              <div class="mt-1 text-sm text-gray-600 dark:text-slate-300">
                0 Clientes potenciales: 0 S/
              </div>
              <div class="mt-3 text-lg font-semibold text-gray-900 dark:text-slate-100 text-center">Lead rápido</div>
            </div>

            <form class="p-4 md:p-5" @submit.prevent="submitQuick">
              <div class="grid gap-3">
                <input
                  v-model.trim="quickForm.name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Nombre"
                  required
                />

                <div class="flex gap-3">
                  <input
                    v-model.number="quickForm.amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="0"
                  />
                  <select
                    v-model="quickForm.currency"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  >
                    <option value="PEN">S/</option>
                    <option value="USD">USD</option>
                  </select>
                </div>

                <input
                  v-model.trim="quickForm.contact_name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Nombre"
                />

                <input
                  v-model.trim="quickForm.contact_phone"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Teléfono"
                />

                <input
                  v-model.trim="quickForm.contact_email"
                  type="email"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Correo"
                />

                <input
                  v-model.trim="quickForm.company_name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Compañía: Nombre"
                />

                <input
                  v-model.trim="quickForm.company_address"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Compañía: Dirección"
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <select
                    v-model="quickForm.document_type"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  >
                    <option value="">Documento: (opcional)</option>
                    <option value="dni">DNI</option>
                    <option value="ruc">RUC</option>
                  </select>

                  <input
                    v-model.trim="quickForm.document_number"
                    type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    :placeholder="documentPlaceholder"
                  />
                </div>

                <p v-if="quickError" class="text-sm text-red-600">{{ quickError }}</p>
              </div>

              <div class="mt-5 flex items-center justify-between">
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
                  :disabled="quickSaving"
                >
                  {{ quickSaving ? 'Creando...' : 'Crear' }}
                </button>

                <div class="flex items-center gap-3">
                  <button
                    type="button"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-slate-100"
                    @click="hideQuickModal"
                  >
                    Cancelar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>

  <!-- Edit Lead Modal -->
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-show="editOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
      role="dialog"
      aria-modal="true"
      @click.self="closeEditModal"
    >
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-show="editOpen" class="relative w-full max-w-lg">
          <div class="relative bg-white rounded-lg shadow dark:bg-slate-900">
            <div class="p-4 md:p-5 border-b rounded-t dark:border-slate-800">
              <div class="text-lg font-semibold text-gray-900 dark:text-slate-100 text-center">Editar lead</div>
            </div>

            <form class="p-4 md:p-5" @submit.prevent="submitEdit">
              <div class="grid gap-3">
                <input
                  v-model.trim="editForm.name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Nombre"
                  required
                />

                <div class="flex gap-3">
                  <input
                    v-model.number="editForm.amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="0"
                  />
                  <select
                    v-model="editForm.currency"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  >
                    <option value="PEN">S/</option>
                    <option value="USD">USD</option>
                  </select>
                </div>

                <input
                  v-model.trim="editForm.contact_name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Nombre"
                />

                <input
                  v-model.trim="editForm.contact_phone"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Teléfono"
                />

                <input
                  v-model.trim="editForm.contact_email"
                  type="email"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Correo"
                />

                <input
                  v-model.trim="editForm.company_name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Compañía: Nombre"
                />

                <input
                  v-model.trim="editForm.company_address"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Compañía: Dirección"
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <select
                    v-model="editForm.document_type"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  >
                    <option value="">Documento: (opcional)</option>
                    <option value="dni">DNI</option>
                    <option value="ruc">RUC</option>
                  </select>

                  <input
                    v-model.trim="editForm.document_number"
                    type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="Documento: Número (opcional)"
                  />
                
                <textarea
                  v-model.trim="editForm.observacion"
                  rows="3"
                  placeholder="Observación (motivo de desistimiento o notas)"
                  class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                ></textarea>
                </div>

                <p v-if="editError" class="text-sm text-red-600">{{ editError }}</p>
              </div>

              <div class="mt-5 flex items-center justify-between">
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
                  :disabled="editSaving"
                >
                  {{ editSaving ? 'Guardando...' : 'Guardar' }}
                </button>

                <div class="flex items-center gap-3">
                  <button
                    type="button"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    :disabled="editSaving"
                    @click.prevent="sendToEspera"
                  >
                    Enviar a zona de espera
                  </button>

                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-800"
                    :disabled="editSaving"
                    @click.prevent="markDesistido"
                  >
                    Marcar como desistido
                  </button>

                  <button
                    type="button"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-slate-100"
                    @click="closeEditModal"
                  >
                    Cancelar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';
import { confirmDialog, toastSuccess, toastError } from '../ui/alerts';

const loading = ref(true);
const stages = ref([]);
const limit = ref(20);
const dateFrom = ref('');
const dateTo = ref('');

const moveError = ref('');

const draggedLead = ref(null);
const draggedFromStageId = ref(null);
const dragOverTarget = ref({ leadId: null, position: 'after', stageId: null });
const draggingId = ref(null);
const previewApplied = ref(false);
const originalPosition = ref(null);
const dropPerformed = ref(false);

const gridColsClass = computed(() => {
  const n = stages.value.length || 1;
  if (n <= 1) return 'grid-cols-1';
  if (n === 2) return 'grid-cols-1 md:grid-cols-2';
  if (n === 3) return 'grid-cols-1 md:grid-cols-3';
  return 'grid-cols-1 md:grid-cols-2 xl:grid-cols-4';
});

const formatMoney = (amount, currency) => {
  const number = Number(amount);
  if (Number.isNaN(number)) return '';
  if (currency === 'USD') return `$${number.toFixed(2)}`;
  return `S/ ${number.toFixed(2)}`;
};

const load = async ({ showLoading = true } = {}) => {
  if (showLoading) loading.value = true;
  try {
    const params = {
      limit: limit.value || undefined,
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
    };

    const response = await axios.get('/leads/board-data', { params });
    stages.value = response?.data?.data?.stages ?? [];
  } catch (e) {
    stages.value = [];
    const msg = e?.response?.data?.message ?? 'No se pudo cargar el pipeline.';
    toastError(msg);
    // also log to console for debugging
    // eslint-disable-next-line no-console
    console.error('Leads board load error', e);
  } finally {
    if (showLoading) loading.value = false;
  }
};

const applyFilters = async () => {
  await load();
};

const clearFilters = async () => {
  limit.value = 100;
  dateFrom.value = '';
  dateTo.value = '';
  await load();
};

const archivingIds = ref(new Set());

const isLeadLocked = (lead, stage) => {
  if (lead?.archived_at) return true;
  if (lead?.won_at) return true;
  if (stage?.is_won) return true;
  return false;
};

const onDragStart = (lead, stage) => {
  if (isLeadLocked(lead, stage)) return;
  draggedLead.value = lead;
  draggedFromStageId.value = lead?.stage_id ?? null;
  draggingId.value = lead.id;
  dropPerformed.value = false;

  // remember original index to allow revert
  const stageObj = stages.value.find((s) => s.id === (lead?.stage_id ?? null));
  if (stageObj && Array.isArray(stageObj.leads)) {
    const idx = stageObj.leads.findIndex((l) => l.id === lead.id);
    originalPosition.value = { stageId: stageObj.id, index: idx };
  } else {
    originalPosition.value = null;
  }
  previewApplied.value = false;
};

const clearPreview = (revert = false) => {
  if (!previewApplied.value) return;
  const movingId = draggedLead.value?.id ?? null;
  if (movingId == null) return;

  // remove any instance of movingId from stages
  for (const s of stages.value) {
    if (!Array.isArray(s.leads)) continue;
    const idx = s.leads.findIndex((l) => l.id === movingId);
    if (idx !== -1) s.leads.splice(idx, 1);
  }

  if (revert && originalPosition.value) {
    const s = stages.value.find((x) => x.id === originalPosition.value.stageId);
    if (s) {
      if (!Array.isArray(s.leads)) s.leads = [];
      const insertIdx = Math.max(0, Math.min(originalPosition.value.index ?? s.leads.length, s.leads.length));
      s.leads.splice(insertIdx, 0, draggedLead.value);
    }
  }

  previewApplied.value = false;
  dragOverTarget.value = { leadId: null, position: 'after', stageId: null };
};

const onDragEnd = () => {
  // if drop did not happen, revert preview
  if (!dropPerformed.value && previewApplied.value) {
    clearPreview(true);
  }

  draggedLead.value = null;
  draggedFromStageId.value = null;
  draggingId.value = null;
  previewApplied.value = false;
  dropPerformed.value = false;
  dragOverTarget.value = { leadId: null, position: 'after', stageId: null };
  originalPosition.value = null;
};

const onDragOver = (targetLead, stage, event) => {
  if (!draggedLead.value) return;

  // locate the section container for this stage
  const sectionEl = event.currentTarget.closest('section[data-stage-id]');
  if (!sectionEl) return;
  const container = sectionEl.querySelector('.p-3');
  if (!container) return;

  const children = Array.from(container.querySelectorAll(':scope > div'));

  // Build a list of child lead ids excluding the dragged one
  const childLeadIds = children.map((child) => {
    const idAttr = child.querySelector('article')?.getAttribute('data-lead-id') || child.dataset.leadId || null;
    return idAttr ? Number(idAttr) : null;
  }).filter((id) => id !== null && id !== draggedLead.value.id);

  if (childLeadIds.length === 0) {
    // empty column or only dragged element: preview at top
    applyPreview(stage.id, 0);
    dragOverTarget.value = { leadId: null, position: 'top', stageId: stage.id };
    return;
  }

  // find insertion index by midpoint of visible children (skip dragged element)
  const y = event.clientY;
  let insertIdx = childLeadIds.length; // default append
  for (let i = 0, ci = 0; i < children.length; i++) {
    const child = children[i];
    const idAttr = child.querySelector('article')?.getAttribute('data-lead-id') || child.dataset.leadId || null;
    const idNum = idAttr ? Number(idAttr) : null;
    if (idNum === draggedLead.value.id) continue; // skip dragged element
    const rect = child.getBoundingClientRect();
    const mid = rect.top + rect.height / 2;
    if (y < mid) {
      insertIdx = ci;
      break;
    }
    ci++;
  }

  // apply optimistic preview insertion
  applyPreview(stage.id, insertIdx);
  dragOverTarget.value = { leadId: childLeadIds[Math.max(0, Math.min(insertIdx - 1, childLeadIds.length - 1))] ?? null, position: insertIdx === 0 ? 'before' : 'after', stageId: stage.id };
};

const applyPreview = (stageId, insertIdx) => {
  if (!draggedLead.value) return;
  const moving = draggedLead.value;

  // remove existing instances of moving id
  for (const s of stages.value) {
    if (!Array.isArray(s.leads)) continue;
    const idx = s.leads.findIndex((l) => l.id === moving.id);
    if (idx !== -1) s.leads.splice(idx, 1);
  }

  const stageObj = stages.value.find((s) => s.id === stageId);
  if (!stageObj) return;
  if (!Array.isArray(stageObj.leads)) stageObj.leads = [];

  const idx = Math.max(0, Math.min(insertIdx, stageObj.leads.length));
  stageObj.leads.splice(idx, 0, moving);
  stageObj.count = stageObj.leads.length;
  applyLeadPatch(moving, { stage_id: stageId });
  previewApplied.value = true;
};

const removeLeadFromStage = (stageId, leadId) => {
  const stage = stages.value.find((s) => s.id === stageId);
  if (!stage || !Array.isArray(stage.leads)) return null;
  const idx = stage.leads.findIndex((l) => l.id === leadId);
  if (idx === -1) return null;
  const [removed] = stage.leads.splice(idx, 1);
  stage.count = Math.max(0, (stage.count ?? 0) - 1);
  return removed ?? null;
};

const insertLeadIntoStage = (stageId, lead, { toTop = true } = {}) => {
  const stage = stages.value.find((s) => s.id === stageId);
  if (!stage) return;
  if (!Array.isArray(stage.leads)) stage.leads = [];

  const existingIdx = stage.leads.findIndex((l) => l.id === lead.id);
  if (existingIdx !== -1) stage.leads.splice(existingIdx, 1);

  if (toTop) stage.leads.unshift(lead);
  else stage.leads.push(lead);

  stage.count = (stage.count ?? stage.leads.length);
  stage.count = stage.leads.length;
};

const applyLeadPatch = (lead, patch) => {
  if (!lead || typeof lead !== 'object') return;
  for (const [k, v] of Object.entries(patch ?? {})) {
    lead[k] = v;
  }
};

const onDrop = async (stage, targetLead = null) => {
  if (!draggedLead.value?.id) return;
  if (!stage?.id) return;

  // If we have an applied preview, use the current in-memory order as final
  if (previewApplied.value) {
    dropPerformed.value = true;
    const leadId = draggedLead.value.id;
    const originalStageId = originalPosition.value?.stageId ?? null;
    const targetStageObj = stages.value.find((s) => s.id === stage.id);
    if (!targetStageObj) return;

    // validation checks
    const fromStage = stages.value.find((s) => s.id === originalStageId);
    if (fromStage?.is_won || draggedLead.value?.won_at) {
      moveError.value = 'Este lead ya está GANADO: no se puede mover de columna. Solo se puede archivar.';
      clearPreview(true);
      return;
    }

    if (stage?.is_won && !draggedLead.value?.customer_id) {
      const ok = await confirmDialog({
        title: 'Confirmar GANADO',
        text: '¿Marcar este lead como GANADO? Esto lo convertirá en cliente automáticamente.',
        confirmText: 'Sí, marcar como ganado',
        cancelText: 'Cancelar',
        icon: 'warning',
      });
      if (!ok) {
        clearPreview(true);
        return;
      }
    }

    try {
      // if moved across stages, call move-stage first
      if (originalStageId && originalStageId !== stage.id) {
        await axios.patch(`/leads/${leadId}/move-stage`, { stage_id: stage.id });
      }

      // persist ordering for the target stage
      const orderedIds = targetStageObj.leads.map((l) => l.id);
      await axios.patch('/leads/reorder', { stage_id: stage.id, ordered_ids: orderedIds });

      // keep the optimistic preview (already applied)
      previewApplied.value = false;
      dragOverTarget.value = { leadId: null, position: 'after', stageId: null };
      draggedLead.value = null;
      draggedFromStageId.value = null;
      draggingId.value = null;
      originalPosition.value = null;
      dropPerformed.value = false;
    } catch (error) {
      // on failure, resync entire board
      await load({ showLoading: false });
    }

    return;
  }

  // fallback: no preview applied — keep previous behaviour (move between columns to top)
  if (draggedLead.value.stage_id === stage.id) return;

  const fromStage = stages.value.find((s) => s.id === (draggedFromStageId.value ?? draggedLead.value.stage_id));
  if (fromStage?.is_won || draggedLead.value?.won_at) {
    moveError.value = 'Este lead ya está GANADO: no se puede mover de columna. Solo se puede archivar.';
    draggedLead.value = null;
    draggedFromStageId.value = null;
    return;
  }

  if (stage?.is_won && !draggedLead.value?.customer_id) {
    const ok = await confirmDialog({
      title: 'Confirmar GANADO',
      text: '¿Marcar este lead como GANADO? Esto lo convertirá en cliente automáticamente.',
      confirmText: 'Sí, marcar como ganado',
      cancelText: 'Cancelar',
      icon: 'warning',
    });
    if (!ok) {
      draggedLead.value = null;
      draggedFromStageId.value = null;
      return;
    }
  }

  const leadId = draggedLead.value.id;
  const fromStageId = draggedFromStageId.value;
  const dragged = draggedLead.value;

  draggedLead.value = null;
  draggedFromStageId.value = null;
  moveError.value = '';

  // Optimistic UI update
  const removed = fromStageId ? removeLeadFromStage(fromStageId, leadId) : null;
  const movingLead = removed ?? dragged;
  applyLeadPatch(movingLead, { stage_id: stage.id });
  insertLeadIntoStage(stage.id, movingLead, { toTop: true });

  try {
    const response = await axios.patch(`/leads/${leadId}/move-stage`, { stage_id: stage.id });
    const updated = response?.data?.data;
    if (updated && typeof updated === 'object') {
      // Keep UI in sync with backend changes (won_at/customer_id/etc.)
      applyLeadPatch(movingLead, updated);
    }

    // After cross-stage move, assign persistent position at top of target stage
    const targetStageObj = stages.value.find((s) => s.id === stage.id);
    if (targetStageObj && Array.isArray(targetStageObj.leads)) {
      const orderedIds = targetStageObj.leads.map((l) => l.id);
      try {
        await axios.patch('/leads/reorder', { stage_id: stage.id, ordered_ids: orderedIds });
      } catch (e) {
        // ignore - server resync below will correct
      }
    }
  } catch (error) {
    moveError.value = error?.response?.data?.message ?? 'No se pudo mover el lead.';
    // Resync silently to undo any optimistic mismatch
    await load({ showLoading: false });
  }
};

const archiveLead = async (lead, stage) => {
  if (!lead?.id) return;
  if (!stage?.is_won && !lead?.won_at) return;

  const ok = await confirmDialog({
    title: 'Archivar lead',
    text: 'Se ocultará del pipeline, pero seguirá visible en la tabla (historial).',
    confirmText: 'Archivar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  archivingIds.value.add(lead.id);
  moveError.value = '';
  try {
    await axios.patch(`/leads/${lead.id}/archive`);

    // Remove from UI
    const removed = removeLeadFromStage(stage.id, lead.id);
    if (removed) {
      // nothing else needed
    }
    toastSuccess('Lead archivado');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo archivar el lead.';
    moveError.value = msg;
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

// Edit modal form already declared below as editForm; ensure observacion included

const documentPlaceholder = computed(() => {
  if (quickForm.value.document_type === 'dni') return 'Documento: DNI (8 dígitos)';
  if (quickForm.value.document_type === 'ruc') return 'Documento: RUC (11 dígitos)';
  return 'Documento: Número (opcional)';
});

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

const firstValidationMessage = (error) => {
  const errors = error?.response?.data?.errors;
  if (!errors || typeof errors !== 'object') return null;
  const firstKey = Object.keys(errors)[0];
  const first = firstKey ? errors[firstKey]?.[0] : null;
  return typeof first === 'string' ? first : null;
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
  } catch (error) {
    quickError.value = firstValidationMessage(error) ?? error?.response?.data?.message ?? 'No se pudo crear el lead.';
  } finally {
    quickSaving.value = false;
  }
};

// Edit Lead modal
const editOpen = ref(false);
const editSaving = ref(false);
const editError = ref('');
const editForm = ref({
  id: null,
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
  observacion: '',
});

const openEditModal = (lead) => {
  editError.value = '';
  editForm.value = {
    id: lead.id,
    name: lead.name || '',
    amount: lead.amount ?? null,
    currency: lead.currency ?? 'PEN',
    contact_name: lead.contact_name ?? '',
    contact_phone: lead.contact_phone ?? '',
    contact_email: lead.contact_email ?? '',
    company_name: lead.company_name ?? '',
    company_address: lead.company_address ?? '',
    document_type: lead.document_type ?? '',
    document_number: lead.document_number ?? '',
    observacion: lead.observacion ?? '',
  };
  editOpen.value = true;
};

const markDesistido = async () => {
  if (!editForm.value?.id) return;
  const ok = await confirmDialog({
    title: 'Marcar como desistido',
    text: '¿Estás seguro? Esto archivará el lead y lo moverá a la lista de desistidos.',
    confirmText: 'Sí, marcar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  try {
    const res = await axios.post(`/leads/${editForm.value.id}/desist`, { observacion: editForm.value.observacion || '' });
    const loc = res?.data?.location || '/desistidos';
    window.location.assign(loc);
  } catch (e) {
    const msg = firstValidationMessage(e) ?? e?.response?.data?.message ?? 'No se pudo marcar como desistido.';
    toastError(msg);
  }
};

const sendToEspera = async () => {
  if (!editForm.value?.id) return;
  const ok = await confirmDialog({
    title: 'Enviar a zona de espera',
    text: '¿Estás seguro? Esto archivará el lead y lo moverá a la zona de espera.',
    confirmText: 'Sí, enviar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  try {
    const res = await axios.post(`/leads/${editForm.value.id}/wait`, { observacion: editForm.value.observacion || '' });
    const loc = res?.data?.location || '/espera';
    window.location.assign(loc);
  } catch (e) {
    const msg = firstValidationMessage(e) ?? e?.response?.data?.message ?? 'No se pudo enviar a zona de espera.';
    toastError(msg);
  }
};

const closeEditModal = () => {
  editOpen.value = false;
};

const submitEdit = async () => {
  editSaving.value = true;
  editError.value = '';

  const payload = {
    name: editForm.value.name,
    amount: editForm.value.amount,
    currency: editForm.value.currency,
    observacion: editForm.value.observacion || null,
    contact_name: editForm.value.contact_name || null,
    contact_phone: editForm.value.contact_phone || null,
    contact_email: editForm.value.contact_email || null,
    company_name: editForm.value.company_name || null,
    company_address: editForm.value.company_address || null,
    document_type: editForm.value.document_type || null,
    document_number: editForm.value.document_number || null,
  };

  try {
    const res = await axios.put(`/leads/${editForm.value.id}`, payload);
    const updated = res?.data?.data;
    if (updated) {
      // find and update in stages
      applyLeadPatch(updated, updated);
      // ensure lead exists in UI: replace where found
      for (const s of stages.value) {
        if (!Array.isArray(s.leads)) continue;
        const idx = s.leads.findIndex((l) => l.id === updated.id);
        if (idx !== -1) {
          s.leads.splice(idx, 1, updated);
          break;
        }
      }
    }
    toastSuccess('Lead actualizado');
    closeEditModal();
  } catch (e) {
    editError.value = firstValidationMessage(e) ?? e?.response?.data?.message ?? 'No se pudo actualizar el lead.';
  } finally {
    editSaving.value = false;
  }
};

const onExternalCreateQuick = () => showQuickModal();

onMounted(async () => {
  window.addEventListener('leads:create-quick', onExternalCreateQuick);
  await load();
});

onBeforeUnmount(() => {
  window.removeEventListener('leads:create-quick', onExternalCreateQuick);
});
</script>
