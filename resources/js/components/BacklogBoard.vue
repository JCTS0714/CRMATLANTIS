<template>
  <div>
    <div class="mb-4 text-sm text-gray-600 dark:text-slate-300">
      Arrastra una incidencia entre columnas para cambiar su estado, o dentro de una columna para reordenar.
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

      <div class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Prioridad:</label>
        <select
          v-model="priorityFilter"
          class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
        >
          <option value="">Todas</option>
          <option value="alta">Alta</option>
          <option value="media">Media</option>
          <option value="baja">Baja</option>
        </select>
      </div>

      <div class="flex items-center gap-2 ms-auto">
        <button @click="applyFilters" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700">
          Aplicar
        </button>
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
          <div
            v-if="(stage.incidences?.length ?? 0) === 0"
            class="text-sm text-gray-500 dark:text-slate-400 px-1"
          >
            Sin incidencias.
          </div>

          <div v-for="(incidence, index) in stage.incidences" :key="incidence.id" :data-incidence-id="incidence.id">
            <!-- Preview line when dragging over -->
            <div 
              v-if="dragOverStageId === stage.id && dropPreviewPosition === index && draggedIncidence?.id !== incidence.id"
              class="h-1 bg-blue-500 rounded-full mx-3 mb-2 animate-pulse transition-all duration-200"
            ></div>

            <article
              :data-incidence-id="incidence.id"
              class="select-none bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3 dark:bg-slate-800 dark:border-slate-700 will-change-transform"
              :class="{
                'opacity-60 scale-95 shadow-lg': draggedIncidence?.id === incidence.id,
                'cursor-not-allowed opacity-80': isLocked(incidence),
                'cursor-grab hover:bg-blue-50/30 dark:hover:bg-slate-800/50 hover:shadow-md': !isLocked(incidence)
              }"
              :style="draggedIncidence?.id === incidence.id ? 'transition: none !important; transform: scale(1.02);' : 'transition: transform 0.1s ease, opacity 0.1s ease;'"
              :draggable="!isLocked(incidence)"
              @dragstart="onDragStart(incidence, stage, $event)"
              @dragend="onDragEnd"
              @click="openEdit(incidence)"
            >
              <div 
                class="flex items-start justify-between gap-3 cursor-pointer"
                @click="openEdit(incidence)"
              >
                <div>
                  <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ incidence.title }}</div>
                  <div v-if="incidence.customer" class="text-xs text-gray-600 dark:text-slate-300 mt-1">
                    {{ incidence.customer.company_name || incidence.customer.name }}
                  </div>
                  <div v-else class="text-xs text-gray-500 dark:text-slate-400 mt-1">Sin cliente</div>
                </div>

                <div class="text-xs"
                  :class="getPriorityClass(incidence.priority)"
                >
                  {{ incidence.priority || 'Normal' }}
                </div>
              </div>

              <div 
                v-if="incidence.date" 
                class="mt-2 text-xs text-gray-600 dark:text-slate-300 cursor-pointer"
                @click="openEdit(incidence)"
              >
                {{ formatDate(incidence.date) }}
              </div>

              <div v-if="stage.is_done" class="mt-3 flex justify-end gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="archivingIds.has(incidence.id)"
                  @click.stop.prevent="archive(incidence, stage)"
                >
                  {{ archivingIds.has(incidence.id) ? 'Archivando…' : 'Archivar' }}
                </button>
              </div>
            </article>
          </div>
          
          <!-- Preview line at the end when dragging -->
          <div 
            v-if="dragOverStageId === stage.id && dropPreviewPosition >= (stage.incidences?.length || 0)"
            class="h-1 bg-blue-500 rounded-full mx-3 mt-2 animate-pulse transition-all duration-200"
          ></div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, nextTick } from 'vue';
import axios from 'axios';
import { confirmDialog, toastError, toastSuccess } from '../ui/alerts';

const loading = ref(true);
const stages = ref([]);
const moveError = ref('');

// Filtros
const limit = ref(50);
const dateFrom = ref('');
const dateTo = ref('');
const priorityFilter = ref('');

// 🚀 NUEVO DRAG & DROP SIMPLE 
const draggedIncidence = ref(null);
const draggedFromStage = ref(null);
const draggedFromStageId = ref(null);
const dragOverStageId = ref(null);
const dropPreviewPosition = ref(null);

// Drag & Drop state variables
const draggingId = ref(null);
const dropPerformed = ref(false);
const previewApplied = ref(false);
const originalPosition = ref(null);

const archivingIds = ref(new Set());

const gridColsClass = computed(() => {
  const n = stages.value.length || 1;
  if (n <= 1) return 'grid-cols-1';
  if (n === 2) return 'grid-cols-1 md:grid-cols-2';
  if (n === 3) return 'grid-cols-1 md:grid-cols-3';
  return 'grid-cols-1 md:grid-cols-2 xl:grid-cols-4';
});

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

const getPriorityClass = (priority) => {
  switch (priority?.toLowerCase()) {
    case 'alta':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 px-2 py-0.5 rounded font-medium';
    case 'media':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 px-2 py-0.5 rounded font-medium';
    case 'baja':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 px-2 py-0.5 rounded font-medium';
    default:
      return 'text-gray-700 dark:text-slate-200';
  }
};

const load = async ({ showLoading = true } = {}) => {
  if (showLoading) loading.value = true;
  try {
    const response = await axios.get('/backlog/board-data', {
      params: {
        limit: limit.value,
        date_from: dateFrom.value || null,
        date_to: dateTo.value || null,
        priority: priorityFilter.value || null,
      },
    });
    stages.value = response?.data?.data?.stages ?? [];
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
  priorityFilter.value = '';
  limit.value = 50;
  load();
};

// ========================================
// 🛠 FUNCIONES AUXILIARES SIMPLES
// ========================================

const isLocked = (incidence) => {
  return !!(incidence?.archived_at);
};

const openEdit = (it) => {
  if (!it?.id) return;
  window.dispatchEvent(new CustomEvent('incidencias:edit', { detail: { incidence: it } }));
};

const onUpdated = (e) => {
  const updated = e?.detail?.incidence;
  if (!updated?.id) return;
  for (const stage of stages.value) {
    const idx = stage?.incidences?.findIndex?.((x) => x.id === updated.id) ?? -1;
    if (idx !== -1) {
      stage.incidences.splice(idx, 1, { ...stage.incidences[idx], ...updated });
      break;
    }
  }
};

// Drag & Drop avanzado
const onDragStart = (incidence, stage, event) => {
  if (isLocked(incidence)) return;
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
  dragOverStageId.value = null;
  dropPreviewPosition.value = null;
};

const onDragEnd = () => {
  // Limpiar preview si no se completó el drop
  if (!dropPerformed.value && previewApplied.value) {
    clearPreview(true);
  }

  // Clean up inmediato
  draggedIncidence.value = null;
  draggedFromStageId.value = null;
  draggingId.value = null;
  previewApplied.value = false;
  dropPerformed.value = false;
  dragOverStageId.value = null;
  dropPreviewPosition.value = null;
  originalPosition.value = null;
};

// Function called by template for dropping on stage
const onDropOnStage = async (targetStage, event) => {
  event.preventDefault();
  
  if (!draggedIncidence.value || !targetStage) return;
  
  // Marcar como performed inmediatamente para prevenir cleanup prematuro
  dropPerformed.value = true;
  
  const incidence = draggedIncidence.value;
  const fromStageId = draggedFromStageId.value;
  
  // Validations
  if (isLocked(incidence)) {
    moveError.value = 'Esta incidencia está bloqueada y no se puede mover.';
    return;
  }

  moveError.value = '';
  
  try {
    // Calcular nueva posición basada en donde se soltó
    const dropY = event.clientY;
    const newPosition = calculateDropPosition(targetStage, dropY, incidence.id);
    
    if (fromStageId === targetStage.id) {
      // Reordenar dentro de la misma etapa - sin await para ser inmediato
      reorderIncidencesInStage(targetStage.id, incidence.id, newPosition);
      toastSuccess('Incidencia reordenada');
    } else {
      // Mover a diferente etapa - Optimistic UI Update
      // 1. Update UI inmediatamente (optimistic)
      removeIncidenceFromStage(fromStageId, incidence.id);
      const updatedIncidence = { ...incidence, stage_id: targetStage.id };
      addIncidenceToStageAtIndex(targetStage.id, updatedIncidence, newPosition);
      
      // 2. Sync con backend en background
      axios.patch(`/incidencias/${incidence.id}/move-stage`, {
        stage_id: targetStage.id
      }).then((response) => {
        // Actualizar con datos del servidor si es necesario
        const serverIncidence = response?.data?.data;
        if (serverIncidence) {
          const stage = stages.value.find(s => s.id === targetStage.id);
          if (stage) {
            const idx = stage.incidences.findIndex(i => i.id === incidence.id);
            if (idx !== -1) {
              Object.assign(stage.incidences[idx], serverIncidence);
            }
          }
        }
        
        // 3. Reordenar en background
        const orderedIds = targetStage.incidences?.map(i => i.id) || [];
        return axios.patch('/incidencias/reorder', {
          stage_id: targetStage.id,
          ordered_ids: orderedIds
        });
      }).then(() => {
        toastSuccess('Incidencia movida correctamente');
      }).catch(error => {
        console.error('Error moving incidence:', error);
        const msg = error?.response?.data?.message ?? 'No se pudo mover la incidencia.';
        moveError.value = msg;
        toastError(msg);
        // En caso de error, recargar datos
        load({ showLoading: false });
      });
    }
  } catch (error) {
    console.error('Error in onDropOnStage:', error);
    const msg = error?.response?.data?.message ?? 'No se pudo mover la incidencia.';
    moveError.value = msg;
    toastError(msg);
    await load({ showLoading: false });
  }
  
  // Reset drag state al final siempre
  draggedIncidence.value = null;
  draggedFromStageId.value = null;
  draggingId.value = null;
  dragOverStageId.value = null;
  dropPreviewPosition.value = null;
};

const removeIncidenceFromStage = (stageId, incidenceId) => {
  const stage = stages.value.find(s => s.id === stageId);
  if (!stage?.incidences) return null;
  
  const index = stage.incidences.findIndex(i => i.id === incidenceId);
  if (index !== -1) {
    const removed = stage.incidences.splice(index, 1)[0];
    stage.count = stage.incidences.length;
    return removed;
  }
  return null;
};

const addIncidenceToStageAtIndex = (stageId, incidence, position = 0) => {
  const stage = stages.value.find(s => s.id === stageId);
  if (!stage) return;
  
  if (!Array.isArray(stage.incidences)) stage.incidences = [];
  
  // Remove if already exists
  const existingIndex = stage.incidences.findIndex(i => i.id === incidence.id);
  if (existingIndex !== -1) {
    stage.incidences.splice(existingIndex, 1);
  }
  
  // Insert at specified position
  const insertIndex = Math.min(Math.max(0, position), stage.incidences.length);
  stage.incidences.splice(insertIndex, 0, incidence);
  stage.count = stage.incidences.length;
};

// Function called by template for drag over
const onDragOver = (event) => {
  if (!draggedIncidence.value) return;
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
  dropPreviewPosition.value = calculateDropPosition(targetStage, dropY, draggedIncidence.value.id);
};

// Cache para elementos DOM en BacklogBoard
const backlogStageElementCache = new Map();

const calculateDropPosition = (stage, dropY, draggedIncidenceId = null) => {
  let stageElement = backlogStageElementCache.get(stage.id);
  if (!stageElement || !document.contains(stageElement)) {
    stageElement = document.querySelector(`[data-stage-id="${stage.id}"] .p-3`);
    if (!stageElement) return 0;
    backlogStageElementCache.set(stage.id, stageElement);
  }
  
  const incidenceCards = stageElement.querySelectorAll('article[data-incidence-id]');
  let position = 0;
  
  // Optimización: loop directo sin Array.from para mejor performance
  for (let i = 0; i < incidenceCards.length; i++) {
    const card = incidenceCards[i];
    const incidenceId = parseInt(card.getAttribute('data-incidence-id'));
    
    // Skip si es la tarjeta que se está arrastrando
    if (draggedIncidenceId && incidenceId === draggedIncidenceId) continue;
    
    const rect = card.getBoundingClientRect();
    if (dropY < rect.top + rect.height / 2) {
      return position;
    }
    position++;
  }
  
  return position; // Insert at end
};

const reorderIncidencesInStage = async (stageId, incidenceId, position) => {
  const stage = stages.value.find(s => s.id === stageId);
  if (!stage || !Array.isArray(stage.incidences)) return;
  
  // Encontrar la incidencia actual
  const currentIndex = stage.incidences.findIndex(i => i.id === incidenceId);
  if (currentIndex === -1) return;
  
  // Si la posición es la misma, no hacer nada
  if (currentIndex === position) return;
  
  // Remover de posición actual
  const [incidence] = stage.incidences.splice(currentIndex, 1);
  
  // Calcular nueva posición ajustada
  const newIndex = Math.min(Math.max(0, position), stage.incidences.length);
  
  // Insertar en nueva posición
  stage.incidences.splice(newIndex, 0, incidence);
  
  // Actualizar conteo inmediatamente para UI responsiva
  stage.count = stage.incidences.length;
  
  // Enviar nuevo orden al backend (sin await para no bloquear UI)
  const orderedIds = stage.incidences.map(i => i.id);
  
  // Ejecutar en background para no bloquear la UI
  axios.patch('/incidencias/reorder', {
    stage_id: stageId,
    ordered_ids: orderedIds
  }).catch(error => {
    console.error('Error reordering incidences:', error);
    // En caso de error, recargar datos
    load({ showLoading: false });
  });
};

const archive = async (it, stage) => {
  if (!it?.id) return;
  if (!stage?.is_done) return;

  const ok = await confirmDialog({
    title: 'Archivar incidencia',
    text: 'Se ocultará del backlog, pero seguirá visible en la tabla (historial).',
    confirmText: 'Archivar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  archivingIds.value.add(it.id);
  moveError.value = '';
  try {
    await axios.patch(`/incidencias/${it.id}/archive`);
    removeIncidenceFromStage(stage.id, it.id);
    toastSuccess('Incidencia archivada');
    window.dispatchEvent(new CustomEvent('incidencias:archived'));
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo archivar la incidencia.';
    moveError.value = msg;
    toastError(msg);
  } finally {
    archivingIds.value.delete(it.id);
  }
};

const onCreated = () => load({ showLoading: false });
const onDeleted = (e) => {
  const id = e?.detail?.id ?? e?.detail?.incidence?.id ?? e?.detail?.incidenceId ?? null;
  if (!id) {
    load({ showLoading: false });
    return;
  }

  for (const stage of stages.value) {
    const idx = stage?.incidences?.findIndex?.(x => x.id === id) ?? -1;
    if (idx !== -1) {
      stage.incidences.splice(idx, 1);
      stage.count = Math.max(0, (stage.count ?? 0) - 1);
      break;
    }
  }
};

onMounted(async () => {
  window.addEventListener('incidencias:created', onCreated);
  window.addEventListener('incidencias:archived', onCreated);
  window.addEventListener('incidencias:deleted', onDeleted);
  window.addEventListener('incidencias:updated', onUpdated);
  await load();
});

onBeforeUnmount(() => {
  window.removeEventListener('incidencias:created', onCreated);
  window.removeEventListener('incidencias:archived', onCreated);
  window.removeEventListener('incidencias:deleted', onDeleted);
  window.removeEventListener('incidencias:updated', onUpdated);
  
  // Clean up drag state
  draggedIncidence.value = null;
  draggedFromStage.value = null;
});
</script>
