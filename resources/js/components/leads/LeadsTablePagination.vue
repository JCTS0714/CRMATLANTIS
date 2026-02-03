<template>
  <div class="p-4 flex items-center justify-between gap-3">
    <button
      type="button"
      class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
      :disabled="loading || pagination.current_page <= 1"
      @click="$emit('page-change', pagination.current_page - 1)"
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
      @click="$emit('page-change', pagination.current_page + 1)"
    >
      Siguiente
    </button>
  </div>
</template>

<script setup>
defineProps({
  pagination: {
    type: Object,
    required: true,
    default: () => ({
      current_page: 1,
      last_page: 1
    })
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['page-change']);
</script>