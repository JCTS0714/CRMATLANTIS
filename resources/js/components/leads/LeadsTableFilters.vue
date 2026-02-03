<template>
  <section>
    <!-- Search and Controls Row -->
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex-1">
        <label class="sr-only" for="leads-search">Buscar</label>
        <input
          id="leads-search"
          :value="modelValue"
          type="search"
          placeholder="Buscar por nombre, empresa, contacto, email, teléfono, documento…"
          class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500"
          @input="$emit('update:modelValue', $event.target.value)"
        />
      </div>

      <div class="flex items-center gap-2">
        <input
          ref="importInput"
          type="file"
          accept=".csv,text/csv"
          class="hidden"
          @change="$emit('import-file', $event)"
        />

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="importing"
          @click="$refs.importInput.click()"
        >
          {{ importing ? 'Importando…' : 'Importar CSV' }}
        </button>

        <select
          :value="perPage"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
          @change="$emit('update:per-page', Number($event.target.value))"
        >
          <option :value="10">10</option>
          <option :value="15">15</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
      </div>
    </div>

    <!-- Stage Filter Tabs -->
    <div class="mb-4 overflow-x-auto">
      <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <button
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition"
          :class="activeStageId === null ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-800'"
          @click="$emit('stage-filter', null)"
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
          @click="$emit('stage-filter', stage.id)"
        >
          {{ stage.name }}
          <span class="ml-2 rounded bg-black/5 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-slate-200">{{ stage.count ?? 0 }}</span>
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    required: true
  },
  stages: {
    type: Array,
    required: true
  },
  activeStageId: {
    type: [Number, null],
    default: null
  },
  totalCount: {
    type: Number,
    default: 0
  },
  perPage: {
    type: Number,
    default: 25
  },
  importing: {
    type: Boolean,
    default: false
  }
});

defineEmits([
  'update:modelValue',
  'update:per-page', 
  'stage-filter',
  'import-file'
]);
</script>