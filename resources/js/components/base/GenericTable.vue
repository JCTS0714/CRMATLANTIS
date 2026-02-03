<template>
  <BaseCard :title="title" :subtitle="subtitle" no-padding>
    <!-- Header with Search and Actions -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ title }}</h3>
          <p v-if="paginationInfo" class="mt-1 text-xs text-gray-600 dark:text-slate-300">{{ paginationInfo }}</p>
        </div>
        <div class="flex items-center gap-3">
          <slot name="headerActions" />
          <div v-if="loading" class="text-xs text-gray-600 dark:text-slate-300">Cargando…</div>
        </div>
      </div>
    </template>

    <!-- Filters Section -->
    <div v-if="showFilters" class="p-4 border-b border-gray-200 dark:border-slate-800">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <!-- Search Input -->
        <div v-if="searchable" class="flex-1">
          <label class="sr-only" :for="`${tableId}-search`">Buscar</label>
          <input
            :id="`${tableId}-search`"
            :value="searchQuery"
            type="search"
            :placeholder="searchPlaceholder"
            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500"
            @input="$emit('search', $event.target.value)"
          />
        </div>

        <!-- Additional Filters -->
        <div class="flex items-center gap-2">
          <slot name="filters" />
          
          <!-- Per Page Selector -->
          <select
            v-if="showPerPageSelector"
            :value="perPage"
            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
            @change="$emit('per-page-change', Number($event.target.value))"
          >
            <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>
      </div>

      <!-- Secondary Filters (Tabs, Badges, etc.) -->
      <div v-if="$slots.secondaryFilters" class="mt-4">
        <slot name="secondaryFilters" />
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="p-4 border-b border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/30">
      <div class="text-sm text-red-700 dark:text-red-200">{{ error }}</div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-700 dark:text-slate-200" :class="tableClass">
        <!-- Table Header -->
        <thead class="text-xs uppercase bg-gray-50 text-gray-700 dark:bg-slate-800 dark:text-slate-200">
          <tr>
            <th v-for="column in columns" :key="column.key" class="px-4 py-3" :class="column.headerClass">
              <div class="flex items-center gap-2">
                {{ column.label }}
                <button 
                  v-if="column.sortable && sortable"
                  type="button"
                  class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300"
                  @click="$emit('sort', column.key)"
                >
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 12l5 5 5-5H5z"/>
                  </svg>
                </button>
              </div>
            </th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
          <slot 
            name="row" 
            v-for="(item, index) in items" 
            :key="getRowKey(item, index)"
            :item="item" 
            :index="index"
            :columns="columns"
          />

          <!-- Empty State -->
          <tr v-if="isEmpty">
            <td :colspan="columns.length" class="px-4 py-10 text-center text-sm text-gray-600 dark:text-slate-300">
              <slot name="empty">
                {{ emptyMessage }}
              </slot>
            </td>
          </tr>

          <!-- Loading State -->
          <tr v-if="loading && items.length === 0">
            <td :colspan="columns.length" class="px-4 py-10 text-center text-sm text-gray-600 dark:text-slate-300">
              <div class="flex items-center justify-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Cargando…
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="showPagination && pagination && pagination.last_page > 1" class="p-4 border-t border-gray-200 dark:border-slate-800">
      <div class="flex items-center justify-between gap-3">
        <BaseButton 
          variant="secondary" 
          size="sm"
          :disabled="loading || pagination.current_page <= 1"
          @click="$emit('page-change', pagination.current_page - 1)"
        >
          Anterior
        </BaseButton>

        <div class="text-xs text-gray-600 dark:text-slate-300">
          <slot name="paginationInfo">
            Página {{ pagination.current_page }} / {{ pagination.last_page }}
          </slot>
        </div>

        <BaseButton 
          variant="secondary" 
          size="sm"
          :disabled="loading || pagination.current_page >= pagination.last_page"
          @click="$emit('page-change', pagination.current_page + 1)"
        >
          Siguiente
        </BaseButton>
      </div>
    </div>
  </BaseCard>
</template>

<script setup>
import { computed } from 'vue';
import BaseCard from '../base/BaseCard.vue';
import BaseButton from '../base/BaseButton.vue';

const props = defineProps({
  // Table identification
  tableId: {
    type: String,
    default: () => `table-${Date.now()}`
  },
  
  // Content
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  columns: {
    type: Array,
    required: true,
    validator: (columns) => columns.every(col => col.key && col.label)
  },
  items: {
    type: Array,
    default: () => []
  },
  
  // State
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  },
  
  // Pagination
  pagination: {
    type: Object,
    default: () => ({
      current_page: 1,
      last_page: 1,
      total: 0,
      from: null,
      to: null
    })
  },
  showPagination: {
    type: Boolean,
    default: true
  },
  paginationInfo: {
    type: String,
    default: ''
  },
  
  // Search
  searchable: {
    type: Boolean,
    default: true
  },
  searchQuery: {
    type: String,
    default: ''
  },
  searchPlaceholder: {
    type: String,
    default: 'Buscar...'
  },
  
  // Sorting
  sortable: {
    type: Boolean,
    default: false
  },
  
  // Filters
  showFilters: {
    type: Boolean,
    default: true
  },
  showPerPageSelector: {
    type: Boolean,
    default: true
  },
  perPage: {
    type: Number,
    default: 25
  },
  perPageOptions: {
    type: Array,
    default: () => [10, 15, 25, 50]
  },
  
  // Styling
  tableClass: {
    type: String,
    default: ''
  },
  
  // Misc
  emptyMessage: {
    type: String,
    default: 'No hay datos para mostrar'
  },
  rowKey: {
    type: [String, Function],
    default: 'id'
  }
});

defineEmits([
  'search',
  'sort', 
  'page-change',
  'per-page-change'
]);

// Computed
const isEmpty = computed(() => !props.loading && props.items.length === 0);

const getRowKey = (item, index) => {
  if (typeof props.rowKey === 'function') {
    return props.rowKey(item, index);
  }
  return item[props.rowKey] || index;
};
</script>