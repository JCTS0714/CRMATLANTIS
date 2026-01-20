<template>
  <div>
    <div class="mb-4 text-sm text-gray-600 dark:text-slate-300">
      Arrastra una incidencia entre columnas para cambiar su estado.
    </div>

    <div v-if="loading" class="text-sm text-gray-600 dark:text-slate-300">Cargando...</div>
    <div v-else-if="moveError" class="mb-3 text-sm text-red-600">{{ moveError }}</div>

    <div v-else class="grid gap-4" :class="gridColsClass">
      <section
        v-for="stage in stages"
        :key="stage.id"
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

          <article
            v-for="it in stage.incidences"
            :key="it.id"
            class="select-none bg-gray-50 border border-gray-200 rounded-lg p-3 transition-colors dark:bg-slate-800 dark:border-slate-700"
            :class="isLocked(it, stage)
              ? 'cursor-not-allowed opacity-80'
              : 'cursor-grab active:cursor-grabbing hover:bg-blue-50/50 dark:hover:bg-slate-800/80'"
            :draggable="!isLocked(it, stage)"
            @dragstart="onDragStart(it, stage)"
            @click="openEdit(it)"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ it.title }}</div>
                <div v-if="it.customer" class="text-xs text-gray-600 dark:text-slate-300 mt-1">
                  {{ it.customer.company_name || it.customer.name }}
                </div>
                <div v-else class="text-xs text-gray-500 dark:text-slate-400 mt-1">Sin cliente</div>
              </div>

              <div class="text-xs text-gray-700 dark:text-slate-200">
                {{ it.priority }}
              </div>
            </div>

            <div v-if="it.date" class="mt-2 text-xs text-gray-600 dark:text-slate-300">
              {{ formatDate(it.date) }}
            </div>

            <div v-if="stage.is_done" class="mt-3 flex justify-end gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="isLocked(it)"
                @mousedown.stop
                @click.stop.prevent="openEdit(it)"
              >
                Editar
              </button>

              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="archivingIds.has(it.id)"
                @click.stop.prevent="archive(it, stage)"
              >
                {{ archivingIds.has(it.id) ? 'Archivando…' : 'Archivar' }}
              </button>
            </div>

            <div v-else class="mt-3 flex justify-end">
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="isLocked(it)"
                @mousedown.stop
                @click.stop.prevent="openEdit(it)"
              >
                Editar
              </button>
            </div>
          </article>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';
import { confirmDialog, toastError, toastSuccess } from '../ui/alerts';

const loading = ref(true);
const stages = ref([]);
const moveError = ref('');

const dragged = ref(null);
const draggedFromStageId = ref(null);

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

const load = async ({ showLoading = true } = {}) => {
  if (showLoading) loading.value = true;
  try {
    const response = await axios.get('/backlog/board-data');
    stages.value = response?.data?.data?.stages ?? [];
  } finally {
    if (showLoading) loading.value = false;
  }
};

const removeFromStage = (stageId, id) => {
  const s = stages.value.find((x) => x.id === stageId);
  if (!s?.incidences) return null;
  const idx = s.incidences.findIndex((x) => x.id === id);
  if (idx === -1) return null;
  const [removed] = s.incidences.splice(idx, 1);
  s.count = (s.count ?? s.incidences.length);
  if (typeof s.count === 'number') s.count = Math.max(0, s.count - 1);
  return removed;
};

const addToStage = (stageId, item) => {
  const s = stages.value.find((x) => x.id === stageId);
  if (!s) return;
  if (!Array.isArray(s.incidences)) s.incidences = [];
  s.incidences.unshift(item);
  s.count = (s.count ?? s.incidences.length);
  if (typeof s.count === 'number') s.count += 1;
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

const onDragStart = (it, stage) => {
  if (isLocked(it)) return;
  dragged.value = it;
  draggedFromStageId.value = stage?.id ?? null;
};

const onDrop = async (targetStage) => {
  const it = dragged.value;
  const fromId = draggedFromStageId.value;

  dragged.value = null;
  draggedFromStageId.value = null;

  if (!it?.id || !fromId || !targetStage?.id) return;
  if (fromId === targetStage.id) return;

  moveError.value = '';

  // optimistic UI move
  const removed = removeFromStage(fromId, it.id);
  if (removed) {
    removed.stage_id = targetStage.id;
    addToStage(targetStage.id, removed);
  }

  try {
    const res = await axios.patch(`/incidencias/${it.id}/move-stage`, { stage_id: targetStage.id });
    const updated = res?.data?.data;
    if (updated && typeof updated === 'object') {
      // sync updated fields into card
      const target = stages.value
        .find((s) => s.id === targetStage.id)
        ?.incidences?.find((x) => x.id === it.id);
      if (target) Object.assign(target, updated);
    }
    toastSuccess('Incidencia actualizada');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo mover la incidencia.';
    moveError.value = msg;
    toastError(msg);
    await load({ showLoading: false });
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
});
</script>
