<template>
  <div v-if="normalizedColumns.length > 1" ref="root" class="relative">
    <button
      type="button"
      class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900"
      :aria-expanded="open ? 'true' : 'false'"
      @click="open = !open"
    >
      <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-slate-800 dark:text-slate-300">
        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path d="M3 5.75A1.75 1.75 0 014.75 4h10.5A1.75 1.75 0 0117 5.75v1.5A1.75 1.75 0 0115.25 9H4.75A1.75 1.75 0 013 7.25v-1.5Zm0 7A1.75 1.75 0 014.75 11h10.5A1.75 1.75 0 0117 12.75v1.5A1.75 1.75 0 0115.25 16H4.75A1.75 1.75 0 013 14.25v-1.5Z" />
        </svg>
      </span>
      <span>Columnas</span>
      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
      </svg>
    </button>

    <div
      v-if="open"
      class="absolute right-0 z-30 mt-2 w-64 rounded-2xl border border-gray-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-slate-900"
    >
      <div class="mb-2 flex items-center justify-between px-2 pt-1">
        <span class="text-xs font-semibold uppercase text-gray-500 dark:text-slate-400">Mostrar columnas</span>
        <button
          type="button"
          class="text-xs text-blue-600 hover:underline dark:text-blue-400"
          @click="$emit('reset')"
        >
          Restablecer
        </button>
      </div>

      <div class="max-h-72 overflow-y-auto pr-1">
        <label
          v-for="column in normalizedColumns"
          :key="column.key"
          class="flex items-center gap-2 rounded-xl px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-800"
        >
          <input
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            :checked="visibleKeys.includes(column.key)"
            @change="$emit('toggle', column.key)"
          />
          <span class="truncate">{{ column.label }}</span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  columns: {
    type: Array,
    default: () => []
  },
  visibleKeys: {
    type: Array,
    default: () => []
  }
});

defineEmits(['toggle', 'reset']);

const root = ref(null);
const open = ref(false);

const normalizedColumns = computed(() => {
  return (props.columns || []).filter((column) => column?.key && column?.label);
});

const handleClickOutside = (event) => {
  if (!open.value) return;
  if (!root.value?.contains(event.target)) {
    open.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
