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
          <div
            v-if="(stage.incidences?.length ?? 0) === 0"
            class="text-sm text-gray-500 dark:text-slate-400 px-1"
          >
            Sin incidencias.
          </div>

          <div v-for="incidence in stage.incidences" :key="incidence.id" :data-incidence-id="incidence.id">
            <article
              :data-incidence-id="incidence.id"
              class="select-none bg-gray-50 border border-gray-200 rounded-lg p-3 dark:bg-slate-800 dark:border-slate-700"
              :class="{
                'scale-105 shadow-lg opacity-90 z-50 transform': draggingId === incidence.id,
                'opacity-50': draggingId && draggingId !== incidence.id,
                'cursor-not-allowed opacity-80': isLocked(incidence, stage),
                'cursor-grab active:cursor-grabbing hover:bg-blue-50/30 dark:hover:bg-slate-800/50': !isLocked(incidence, stage)
              }"
              :draggable="!isLocked(incidence, stage)"
              @dragstart="onDragStart(incidence, stage)"
              @dragend="onDragEnd"
              @dragover.prevent="onDragOverThrottled(incidence, stage, $event)"
              @drop.prevent="onDrop(stage, incidence, $event)"
              @click="openEdit(incidence)"
            >
              <div class="flex items-start justify-between gap-3">
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

              <div v-if="incidence.date" class="mt-2 text-xs text-gray-600 dark:text-slate-300">
                {{ formatDate(incidence.date) }}
              </div>

              <div v-if="stage.is_done" class="mt-3 flex justify-end gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="isLocked(incidence)"
                  @mousedown.stop
                  @click.stop.prevent="openEdit(incidence)"
                >
                  Editar
                </button>

                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="archivingIds.has(incidence.id)"
                  @click.stop.prevent="archive(incidence, stage)"
                >
                  {{ archivingIds.has(incidence.id) ? 'Archivando…' : 'Archivar' }}
                </button>
              </div>

              <div v-else class="mt-3 flex justify-end">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="isLocked(incidence)"
                  @mousedown.stop
                  @click.stop.prevent="openEdit(incidence)"
                >
                  Editar
                </button>
              </div>
            </article>
          </div>
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

// Drag & Drop avanzado
const draggingId = ref(null);
const draggedIncidence = ref(null);
const draggedFromStageId = ref(null);
const previewApplied = ref(false);
const dropPerformed = ref(false);
const dragOverTarget = ref({ incidenceId: null, position: 'after', stageId: null });
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

const removeFromStage = (stageId, id) => {
  const s = stages.value.find((x) => x.id === stageId);
  if (!s?.incidences) return null;
  const idx = s.incidences.findIndex((x) => x.id === id);
  if (idx === -1) return null;
  const [removed] = s.incidences.splice(idx, 1);
  s.count = Math.max(0, (s.count ?? s.incidences.length) - 1);
  return removed;
};

const addToStageAtIndex = (stageId, item, index = 0) => {
  const s = stages.value.find((x) => x.id === stageId);
  if (!s) return;
  if (!Array.isArray(s.incidences)) s.incidences = [];
  const idx = Math.max(0, Math.min(index, s.incidences.length));
  s.incidences.splice(idx, 0, item);
  s.count = (s.count ?? 0) + 1;
};

const isLocked = (it) => !!it?.archived_at;

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
const onDragStart = (incidence, stage) => {
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
  if (!draggedIncidence.value) return;
  
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

const onDrop = async (targetStage, targetIncidence = null, event = null) => {
  const incidence = draggedIncidence.value;
  const fromStageId = draggedFromStageId.value;

  if (!incidence?.id || !fromStageId || !targetStage?.id) {
    // Reset drag state
    draggedIncidence.value = null;
    draggedFromStageId.value = null;
    draggingId.value = null;
    return;
  }

  // Handle in-column reordering and cross-column moves
  if (previewApplied.value) {
    dropPerformed.value = true;
    const incidenceId = incidence.id;
    const targetStageObj = stages.value.find((s) => s.id === targetStage.id);
    if (!targetStageObj) return;

    try {
      // If moved across stages, call move-stage first
      if (fromStageId !== targetStage.id) {
        await axios.patch(`/incidencias/${incidenceId}/move-stage`, { stage_id: targetStage.id });
      }

      // Always call reorder to update positions
      const orderedIds = targetStageObj.incidences.map((i) => i.id);
      await axios.patch('/incidencias/reorder', { stage_id: targetStage.id, ordered_ids: orderedIds });

      previewApplied.value = false;
      dragOverTarget.value = { incidenceId: null, position: 'after', stageId: null };
      draggedIncidence.value = null;
      draggedFromStageId.value = null;
      draggingId.value = null;
      originalPosition.value = null;
      dropPerformed.value = false;
      moveError.value = '';
      toastSuccess('Incidencia actualizada');
    } catch (error) {
      moveError.value = 'Error al mover la incidencia';
      await load({ showLoading: false });
    }
    return;
  }

  // Fallback for simple cross-column moves
  if (fromStageId === targetStage.id) return;

  moveError.value = '';

  // optimistic UI move
  const removed = removeFromStage(fromStageId, incidence.id);
  if (removed) {
    removed.stage_id = targetStage.id;
    addToStageAtIndex(targetStage.id, removed, 0);
  }

  try {
    const res = await axios.patch(`/incidencias/${incidence.id}/move-stage`, { stage_id: targetStage.id });
    const updated = res?.data?.data;
    if (updated && typeof updated === 'object') {
      const target = stages.value
        .find((s) => s.id === targetStage.id)
        ?.incidences?.find((x) => x.id === incidence.id);
      if (target) Object.assign(target, updated);
    }
    toastSuccess('Incidencia actualizada');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo mover la incidencia.';
    moveError.value = msg;
    toastError(msg);
    await load({ showLoading: false });
  } finally {
    draggedIncidence.value = null;
    draggedFromStageId.value = null;
    draggingId.value = null;
  }
};

const deleteIncidence = async (incidence) => {
  if (!incidence?.id) return;

  try {
    const result = await confirmDialog({
      title: '¿Seguro que quieres eliminar la incidencia?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      confirmButtonText: 'Eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    await axios.delete(`/incidencias/${incidence.id}`);

    // Remove from UI
    for (const stage of stages.value) {
      const idx = stage?.incidences?.findIndex?.(x => x.id === incidence.id) ?? -1;
      if (idx !== -1) {
        stage.incidences.splice(idx, 1);
        stage.count = Math.max(0, (stage.count ?? 0) - 1);
        break;
      }
    }

    toastSuccess('Incidencia eliminada correctamente');
  } catch (error) {
    toastError('Error al eliminar la incidencia');
  }
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
    removeFromStage(stage.id, it.id);
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

onMounted(async () => {
  window.addEventListener('incidencias:created', onCreated);
  window.addEventListener('incidencias:archived', onCreated);
  window.addEventListener('incidencias:updated', onUpdated);
  await load();
});

onBeforeUnmount(() => {
  window.removeEventListener('incidencias:created', onCreated);
  window.removeEventListener('incidencias:archived', onCreated);
  window.removeEventListener('incidencias:updated', onUpdated);
  if (dragOverTimeout) {
    clearTimeout(dragOverTimeout);
  }
});
</script>
