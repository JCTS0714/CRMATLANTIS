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
        @dragover.prevent='onDragOver'
        @drop.prevent='onDropOnStage(stage, $event)'
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

          <div v-for="(lead, index) in stage.leads" :key="lead.id" :data-lead-id="lead.id">
            <!-- Preview line when dragging over -->
            <div 
              v-if="dragOverStageId === stage.id && dropPreviewPosition === index && draggedLead?.id !== lead.id"
              class="h-1 bg-blue-500 rounded-full mx-3 mb-2 animate-pulse transition-all duration-200"
            ></div>

            <article
              :data-lead-id="lead.id"
              class="select-none bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3 dark:bg-slate-800 dark:border-slate-700 will-change-transform"
              :class="{
                'opacity-60 scale-95 shadow-lg': draggedLead?.id === lead.id,
                'cursor-not-allowed opacity-80': isLeadLocked(lead, stage),
                'cursor-grab hover:bg-blue-50/30 dark:hover:bg-slate-800/50 hover:shadow-md': !isLeadLocked(lead, stage)
              }"
              :style="draggedLead?.id === lead.id ? 'transition: none !important; transform: scale(1.02);' : 'transition: transform 0.1s ease, opacity 0.1s ease;'"
              :draggable="!isLeadLocked(lead, stage)"
              @dragstart="onDragStart(lead, stage, $event)"
              @dragend="onDragEnd"
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
          
          <!-- Preview line at the end when dragging -->
          <div 
            v-if="dragOverStageId === stage.id && dropPreviewPosition >= (stage.leads?.length || 0)"
            class="h-1 bg-blue-500 rounded-full mx-3 mt-2 animate-pulse transition-all duration-200"
          ></div>
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
                  <svg v-if="!editSaving" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  {{ editSaving ? 'Guardando...' : 'Guardar' }}
                </button>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-300 dark:hover:bg-blue-900"
                    :disabled="editSaving"
                    @click.prevent="sendToEspera"
                  >
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    En Espera
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 dark:border-red-800 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900"
                    :disabled="editSaving"
                    @click.prevent="markDesistido"
                  >
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Desistir
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
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

// 🚀 NUEVO DRAG & DROP SIMPLE
const draggedLead = ref(null);
const draggedFromStage = ref(null);
const dragOverStageId = ref(null);
const dropPreviewPosition = ref(null);

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
  return !!(lead?.archived_at || lead?.won_at || stage?.is_won);
};

// ========================================
// 🚀 NUEVO DRAG & DROP SIMPLE Y LIMPIO
// ========================================

const onDragStart = (lead, stage, event) => {
  if (isLeadLocked(lead, stage)) return;
  
  draggedLead.value = lead;
  draggedFromStage.value = stages.value.find(s => s.id === lead.stage_id);
  
  // Visual feedback optimizado - sin transiciones lentas
  if (event?.target) {
    event.target.style.transform = 'scale(1.02)';
    event.target.style.opacity = '0.7';
    event.target.style.transition = 'transform 0.1s ease';
  }
};

const onDragEnd = (event) => {
  // Reset visual feedback - inmediato
  if (event?.target) {
    event.target.style.transform = '';
    event.target.style.opacity = '';
    event.target.style.transition = '';
  }
  
  // Limpiar preview inmediatamente
  dragOverStageId.value = null;
  dropPreviewPosition.value = null;
  
  // Clean up inmediato
  draggedLead.value = null;
  draggedFromStage.value = null;
};

const onDragOver = (event) => {
  if (!draggedLead.value) return;
  event.preventDefault(); // Allow drop
  
  // Encontrar la etapa sobre la que se está arrastrando directamente
  const stageElement = event.currentTarget.closest('section[data-stage-id]');
  if (!stageElement) return;
  
  const stageId = parseInt(stageElement.getAttribute('data-stage-id'));
  const targetStage = stages.value.find(s => s.id === stageId);
  if (!targetStage) return;
  
  dragOverStageId.value = stageId;
  
  // Calcular posición de preview directamente
  const dropY = event.clientY;
  dropPreviewPosition.value = calculateDropPosition(targetStage, dropY, draggedLead.value.id);
};

const onDropOnStage = async (targetStage, event) => {
  event.preventDefault();
  
  if (!draggedLead.value || !targetStage) return;
  
  const lead = draggedLead.value;
  const fromStage = draggedFromStage.value;
  
  // Validations
  if (isLeadLocked(lead, fromStage)) {
    moveError.value = 'Este lead está bloqueado y no se puede mover.';
    return;
  }
  
  if (targetStage.is_won && !lead.customer_id) {
    const ok = await confirmDialog({
      title: 'Confirmar GANADO',
      text: '¿Marcar este lead como GANADO? Esto lo convertirá en cliente automáticamente.',
      confirmText: 'Sí, marcar como ganado',
      cancelText: 'Cancelar',
      icon: 'warning',
    });
    if (!ok) return;
  }
  
  moveError.value = '';
  
  try {
    const isSameStage = fromStage.id === targetStage.id;
    
    if (isSameStage) {
      // CASO 1: Reordenamiento dentro de la misma columna
      const dropY = event.clientY;
      const newPosition = calculateDropPosition(targetStage, dropY, lead.id);
      // No usar await - que sea inmediato
      reorderLeadsInStage(targetStage.id, lead.id, newPosition);
      
    } else {
      // CASO 2: Movimiento entre columnas diferentes - Optimistic UI Update
      const dropY = event.clientY;
      const newPosition = calculateDropPosition(targetStage, dropY, lead.id);
      
      // 1. Update UI inmediatamente (optimistic)
      removeLeadFromStage(fromStage.id, lead.id);
      addLeadToStage(targetStage.id, { ...lead, stage_id: targetStage.id }, newPosition);
      
      // 2. Sync con backend en background
      axios.patch(`/leads/${lead.id}/move-stage`, { 
        stage_id: targetStage.id 
      }).then(() => {
        // 3. Mantener orden actual que ya está en la UI
        const orderedIds = targetStage.leads.map(l => l.id);
        return axios.patch('/leads/reorder', {
          stage_id: targetStage.id,
          ordered_ids: orderedIds
        });
      }).catch(error => {
        console.error('Error moving lead:', error);
        moveError.value = 'Error al mover el lead. Recargando...';
        // En caso de error, recargar datos
        load({ showLoading: false });
      });
    }
    
  } catch (error) {
    moveError.value = 'Error al mover el lead. Inténtalo de nuevo.';
    
    // Reload to get fresh data
    await load({ showLoading: false });
  }
};

// Cache para elementos DOM
const stageElementCache = new Map();

const calculateDropPosition = (stage, dropY, draggedLeadId = null) => {
  let stageElement = stageElementCache.get(stage.id);
  if (!stageElement || !document.contains(stageElement)) {
    stageElement = document.querySelector(`[data-stage-id="${stage.id}"] .p-3`);
    if (!stageElement) return 0;
    stageElementCache.set(stage.id, stageElement);
  }
  
  const leadCards = stageElement.querySelectorAll('article[data-lead-id]');
  let position = 0;
  
  // Optimización: usar for loop directo sin Array.from
  for (let i = 0; i < leadCards.length; i++) {
    const card = leadCards[i];
    const leadId = parseInt(card.getAttribute('data-lead-id'));
    
    // Skip si es la tarjeta que se está arrastrando
    if (draggedLeadId && leadId === draggedLeadId) continue;
    
    const rect = card.getBoundingClientRect();
    if (dropY < rect.top + rect.height / 2) {
      return position;
    }
    position++;
  }
  
  return position; // Insert at end
};

const reorderLeadsInStage = async (stageId, leadId, position) => {
  const stage = stages.value.find(s => s.id === stageId);
  if (!stage || !Array.isArray(stage.leads)) return;
  
  // Encontrar el lead actual
  const currentIndex = stage.leads.findIndex(l => l.id === leadId);
  if (currentIndex === -1) return;
  
  // Si la posición es la misma, no hacer nada
  if (currentIndex === position) return;
  
  // Remover de posición actual
  const [lead] = stage.leads.splice(currentIndex, 1);
  
  // Calcular nueva posición ajustada
  const newIndex = Math.min(Math.max(0, position), stage.leads.length);
  
  // Insertar en nueva posición
  stage.leads.splice(newIndex, 0, lead);
  
  // Actualizar conteo inmediatamente para UI responsiva
  stage.count = stage.leads.length;
  
  // Enviar nuevo orden al backend (sin await para no bloquear UI)
  const orderedIds = stage.leads.map(l => l.id);
  
  // Ejecutar en background para no bloquear la UI
  axios.patch('/leads/reorder', {
    stage_id: stageId,
    ordered_ids: orderedIds
  }).catch(error => {
    console.error('Error reordering leads:', error);
    // En caso de error, recargar datos
    load({ showLoading: false });
  });
};

// ========================================
// 🛠 FUNCIONES AUXILIARES SIMPLES
// ========================================

const removeLeadFromStage = (stageId, leadId) => {
  const stage = stages.value.find(s => s.id === stageId);
  if (!stage?.leads) return;
  
  const index = stage.leads.findIndex(l => l.id === leadId);
  if (index !== -1) {
    stage.leads.splice(index, 1);
    stage.count = stage.leads.length;
  }
};

const addLeadToStage = (stageId, lead, position = 0) => {
  const stage = stages.value.find(s => s.id === stageId);
  if (!stage) return;
  
  if (!Array.isArray(stage.leads)) stage.leads = [];
  
  // Remove if already exists
  const existingIndex = stage.leads.findIndex(l => l.id === lead.id);
  if (existingIndex !== -1) {
    stage.leads.splice(existingIndex, 1);
  }
  
  // Insert at specific position
  const insertIndex = Math.min(Math.max(0, position), stage.leads.length);
  stage.leads.splice(insertIndex, 0, lead);
  stage.count = stage.leads.length;
};

// Esta función fue eliminada - ahora usamos onDropOnStage

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
