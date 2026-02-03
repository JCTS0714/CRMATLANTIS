<template>
  <div>
    <div class="mb-4 text-sm text-gray-600 dark:text-slate-300">
      Arrastra una incidencia entre columnas para cambiar su etapa.
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
          <div v-if="(stage.incidences?.length ?? 0) === 0" class="text-sm text-gray-500 dark:text-slate-400 px-1">
            Sin incidencias.
          </div>

          <div v-for="incidence in stage.incidences" :key="incidence.id" :data-incidence-id="incidence.id">
            <article
              :data-incidence-id="incidence.id"
              class="select-none bg-gray-50 border border-gray-200 rounded-lg p-3 dark:bg-slate-800 dark:border-slate-700"
              :class="{
                'scale-105 shadow-lg opacity-90 z-50 transform': draggingId === incidence.id,
                'opacity-50': draggingId && draggingId !== incidence.id,
                'cursor-not-allowed opacity-80': isIncidenceLocked(incidence, stage),
                'cursor-grab active:cursor-grabbing hover:bg-blue-50/30 dark:hover:bg-slate-800/50': !isIncidenceLocked(incidence, stage)
              }"
              :draggable="!isIncidenceLocked(incidence, stage)"
              @dragstart="onDragStart(incidence, stage)"
              @dragend="onDragEnd"
              @dragover.prevent="onDragOverThrottled(incidence, stage, $event)"
              @drop.prevent="onDrop(stage, incidence, $event)"
              @click="openEditModal(incidence)"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-semibold text-gray-900 dark:text-slate-100 truncate">{{ incidence.title || `#${incidence.correlative}` }}</div>
                  <div v-if="incidence.customer" class="text-xs text-gray-600 dark:text-slate-300 mt-1 truncate">
                    {{ incidence.customer.company_name || incidence.customer.name }}
                  </div>
                  <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :class="getPriorityClass(incidence.priority)">
                      {{ incidence.priority || 'Normal' }}
                    </span>
                  </div>
                </div>
                
                <!-- Botón de eliminar -->
                <button
                  type="button"
                  class="opacity-0 group-hover:opacity-100 hover:opacity-100 ml-2 p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-all dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950"
                  :disabled="deletingIds.has(incidence.id)"
                  @click.stop="confirmDelete(incidence)"
                  title="Eliminar incidencia"
                >
                  <svg v-if="!deletingIds.has(incidence.id)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </button>
              </div>
            </article>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal de edición (simplificado por ahora) -->
    <BaseModal 
      ref="incidenceModal"
      title="Editar Incidencia"
      max-width="lg"
      close-on-backdrop
    >
      <div class="py-4">
        <p class="text-gray-600 dark:text-slate-400">
          Modal de edición de incidencias (por implementar)
        </p>
      </div>
      
      <template #footer>
        <div class="flex justify-end gap-3">
          <BaseButton variant="secondary" @click="closeEditModal">
            Cancelar
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import BaseModal from './base/BaseModal.vue';
import BaseButton from './base/BaseButton.vue';

// Reactive state
const loading = ref(false);
const stages = ref([]);
const moveError = ref('');

const limit = ref(50);
const dateFrom = ref('');
const dateTo = ref('');

// Drag & Drop state
const draggingId = ref(null);
const draggedIncidence = ref(null);
const draggedFromStageId = ref(null);
const previewApplied = ref(false);
const dropPerformed = ref(false);
const dragOverTarget = ref({ incidenceId: null, position: 'after', stageId: null });
const originalPosition = ref(null);
const deletingIds = ref(new Set());

// Modal state
const incidenceModal = ref(null);
const editingIncidence = ref(null);

// Computed
const gridColsClass = computed(() => {
  const count = stages.value.length;
  if (count === 1) return 'grid-cols-1';
  if (count === 2) return 'grid-cols-1 lg:grid-cols-2';
  if (count === 3) return 'grid-cols-1 lg:grid-cols-3';
  if (count === 4) return 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-4';
  if (count === 5) return 'grid-cols-1 lg:grid-cols-3 xl:grid-cols-5';
  return 'grid-cols-1 lg:grid-cols-3 2xl:grid-cols-6';
});

// Methods
const load = async ({ showLoading = true } = {}) => {
  if (showLoading) loading.value = true;

  try {
    const res = await axios.get('/incidencias/board-data', {
      params: {
        limit: limit.value,
        date_from: dateFrom.value || null,
        date_to: dateTo.value || null,
      },
    });

    stages.value = res.data?.stages ?? [];
  } catch (error) {
    console.error('Error loading incidences:', error);
  } finally {
    if (showLoading) loading.value = false;
  }
};

const applyFilters = () => {
  load();
};

const clearFilters = () => {
  dateFrom.value = '';
  dateTo.value = '';
  limit.value = 50;
  load();
};

const isIncidenceLocked = (incidence, stage) => {
  // Por ejemplo, incidencias resueltas no se pueden mover
  if (stage?.is_resolved) return true;
  return false;
};

const getPriorityClass = (priority) => {
  switch (priority?.toLowerCase()) {
    case 'alta':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    case 'media':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    case 'baja':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
  }
};

// Drag & Drop functionality
const onDragStart = (incidence, stage) => {
  if (isIncidenceLocked(incidence, stage)) return;
  draggedIncidence.value = incidence;
  draggedFromStageId.value = incidence?.stage_id ?? null;
  draggingId.value = incidence.id;
  dropPerformed.value = false;

  const stageObj = stages.value.find((s) => s.id === (incidence?.stage_id ?? null));
  if (stageObj && Array.isArray(stageObj.incidences)) {
    const idx = stageObj.incidences.findIndex((i) => i.id === incidence.id);
    originalPosition.value = { stageId: stageObj.id, index: idx };
  } else {
    originalPosition.value = null;
  }
  previewApplied.value = false;
};

const clearPreview = (revert = false) => {
  if (!previewApplied.value) return;
  const movingId = draggedIncidence.value?.id ?? null;
  if (movingId == null) return;

  for (const s of stages.value) {
    if (!Array.isArray(s.incidences)) continue;
    const idx = s.incidences.findIndex((i) => i.id === movingId);
    if (idx !== -1) s.incidences.splice(idx, 1);
  }

  if (revert && originalPosition.value) {
    const s = stages.value.find((x) => x.id === originalPosition.value.stageId);
    if (s) {
      if (!Array.isArray(s.incidences)) s.incidences = [];
      const insertIdx = Math.max(0, Math.min(originalPosition.value.index ?? s.incidences.length, s.incidences.length));
      s.incidences.splice(insertIdx, 0, draggedIncidence.value);
    }
  }

  previewApplied.value = false;
  dragOverTarget.value = { incidenceId: null, position: 'after', stageId: null };
};

const onDragEnd = () => {
  if (!dropPerformed.value && previewApplied.value) {
    clearPreview(true);
  }

  draggedIncidence.value = null;
  draggedFromStageId.value = null;
  draggingId.value = null;
  previewApplied.value = false;
  dropPerformed.value = false;
  dragOverTarget.value = { incidenceId: null, position: 'after', stageId: null };
  originalPosition.value = null;
};

let dragOverTimeout = null;
const onDragOverThrottled = (targetIncidence, stage, event) => {
  if (!draggedIncidence.value) return;
  
  if (dragOverTimeout) {
    clearTimeout(dragOverTimeout);
  }
  
  dragOverTimeout = setTimeout(() => {
    onDragOver(targetIncidence, stage, event);
  }, 16);
};

const onDragOver = (targetIncidence, stage, event) => {
  if (!draggedIncidence.value) return;

  const sectionEl = event.currentTarget.closest('section[data-stage-id]');
  if (!sectionEl) return;
  const container = sectionEl.querySelector('.p-3');
  if (!container) return;

  const articles = container.querySelectorAll('article[data-incidence-id]');
  const childElements = Array.from(articles).filter(el => {
    const incidenceId = Number(el.getAttribute('data-incidence-id'));
    return incidenceId !== draggedIncidence.value.id;
  });

  if (childElements.length === 0) {
    applyPreviewOptimized(stage.id, 0);
    dragOverTarget.value = { incidenceId: null, position: 'top', stageId: stage.id };
    return;
  }

  const y = event.clientY;
  let insertIdx = childElements.length;
  
  for (let i = 0; i < childElements.length; i++) {
    const rect = childElements[i].getBoundingClientRect();
    const mid = rect.top + rect.height / 2;
    if (y < mid) {
      insertIdx = i;
      break;
    }
  }

  applyPreviewOptimized(stage.id, insertIdx);
  const targetIncidenceId = insertIdx > 0 ? Number(childElements[insertIdx - 1]?.getAttribute('data-incidence-id')) : null;
  dragOverTarget.value = { 
    incidenceId: targetIncidenceId, 
    position: insertIdx === 0 ? 'before' : 'after', 
    stageId: stage.id 
  };
};

const applyPreviewOptimized = (stageId, insertIdx) => {
  if (!draggedIncidence.value || previewApplied.value) return;
  
  const moving = draggedIncidence.value;
  const stageObj = stages.value.find(s => s.id === stageId);
  if (!stageObj) return;
  
  const currentIdx = stageObj.incidences?.findIndex(i => i.id === moving.id) ?? -1;
  if (currentIdx === insertIdx) return;
  
  stages.value.forEach(s => {
    if (!Array.isArray(s.incidences)) return;
    const idx = s.incidences.findIndex(i => i.id === moving.id);
    if (idx !== -1) s.incidences.splice(idx, 1);
  });

  if (!Array.isArray(stageObj.incidences)) stageObj.incidences = [];
  const idx = Math.max(0, Math.min(insertIdx, stageObj.incidences.length));
  stageObj.incidences.splice(idx, 0, moving);
  
  previewApplied.value = true;
};

const onDrop = async (stage, targetIncidence = null) => {
  if (!draggedIncidence.value?.id) return;
  if (!stage?.id) return;

  if (previewApplied.value) {
    dropPerformed.value = true;
    const incidenceId = draggedIncidence.value.id;
    const originalStageId = originalPosition.value?.stageId ?? null;
    const targetStageObj = stages.value.find((s) => s.id === stage.id);
    if (!targetStageObj) return;

    try {
      if (originalStageId && originalStageId !== stage.id) {
        await axios.patch(`/incidencias/${incidenceId}/move-stage`, { stage_id: stage.id });
      }

      const orderedIds = targetStageObj.incidences.map((i) => i.id);
      await axios.patch('/incidencias/reorder', { stage_id: stage.id, ordered_ids: orderedIds });

      previewApplied.value = false;
      dragOverTarget.value = { incidenceId: null, position: 'after', stageId: null };
      draggedIncidence.value = null;
      draggedFromStageId.value = null;
      draggingId.value = null;
      originalPosition.value = null;
      dropPerformed.value = false;
      moveError.value = '';
    } catch (error) {
      moveError.value = 'Error al mover la incidencia';
      await load({ showLoading: false });
    }

    return;
  }
};

// Modal functionality
const openEditModal = (incidence) => {
  editingIncidence.value = incidence;
  incidenceModal.value?.open();
};

const closeEditModal = () => {
  editingIncidence.value = null;
  incidenceModal.value?.close();
};

// Delete functionality
const confirmDelete = async (incidence) => {
  const result = await Swal.fire({
    title: '¿Eliminar incidencia?',
    text: `Se eliminará permanentemente "${incidence.title || `#${incidence.correlative}`}"`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  });

  if (result.isConfirmed) {
    await deleteIncidence(incidence);
  }
};

const deleteIncidence = async (incidence) => {
  deletingIds.value.add(incidence.id);

  try {
    await axios.delete(`/incidencias/${incidence.id}`);
    
    // Remove from local state
    stages.value.forEach(stage => {
      if (Array.isArray(stage.incidences)) {
        const index = stage.incidences.findIndex(i => i.id === incidence.id);
        if (index !== -1) {
          stage.incidences.splice(index, 1);
          stage.count = (stage.count || 0) - 1;
        }
      }
    });

    await Swal.fire({
      title: 'Eliminada',
      text: 'La incidencia ha sido eliminada exitosamente',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
    });

  } catch (error) {
    console.error('Error deleting incidence:', error);
    await Swal.fire({
      title: 'Error',
      text: 'No se pudo eliminar la incidencia',
      icon: 'error',
    });
  } finally {
    deletingIds.value.delete(incidence.id);
  }
};

// Initialize
onMounted(() => {
  load();
});
</script>