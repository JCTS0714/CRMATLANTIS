<template>
  <section>
    <!-- Filters Component -->
    <LeadsTableFilters
      v-model="searchInput"
      :stages="stages"
      :active-stage-id="activeStageId"
      :total-count="totalCount"
      :per-page="perPage"
      :importing="importing"
      @update:per-page="changePerPage"
      @stage-filter="handleStageFilter"
      @import-file="handleImportFile"
      @period-filter="handlePeriodFilter"
    />

    <!-- Error Display -->
    <div v-if="error" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
      {{ error }}
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-slate-900 dark:border-slate-800">
      <!-- Table Header -->
      <div class="p-4 border-b border-gray-200 dark:border-slate-800 flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">Leads</div>
          <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
            <span v-if="paginationInfo">{{ paginationInfo }}</span>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <TableColumnsDropdown
            :columns="columns"
            :visible-keys="visibleKeys"
            @toggle="toggleColumn"
            @reset="resetColumns"
          />
          <div v-if="loading" class="text-xs text-gray-600 dark:text-slate-300">Cargando…</div>
        </div>
      </div>

      <!-- Table -->
      <div ref="tableScrollRef" class="overflow-x-auto">
        <table ref="tableRef" class="min-w-[1400px] w-full text-sm text-left text-gray-700 dark:text-slate-200">
          <colgroup>
            <col
              v-for="column in columns"
              :key="column.key"
              :style="{ display: isColumnVisible(column.key) ? '' : 'none' }"
            />
          </colgroup>
          <thead class="text-xs uppercase bg-gray-50 text-gray-700 dark:bg-slate-800 dark:text-slate-200">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">Nombre</th>
              <th class="px-4 py-3 text-right">Monto</th>
              <th class="px-4 py-3">Moneda</th>
              <th class="px-4 py-3">Contacto</th>
              <th class="px-4 py-3">Teléfono</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Empresa</th>
              <th class="px-4 py-3">Dirección</th>
              <th class="px-4 py-3">Doc. tipo</th>
              <th class="px-4 py-3">Doc. nro</th>
              <th class="px-4 py-3">Customer ID</th>
              <th class="px-4 py-3">Estado</th>
              <th class="px-4 py-3">Creado por</th>
              <th class="px-4 py-3">Won at</th>
              <th class="px-4 py-3">Creado</th>
              <th class="px-4 py-3">Actualizado</th>
            </tr>
          </thead>

          <tbody>
            <LeadsTableRow
              v-for="lead in items"
              :key="lead.id"
              :lead="lead"
              :stages="stages"
              :is-moving="movingLeadIds.has(lead.id)"
              @change-stage="handleStageChange"
            />

            <tr v-if="isEmpty">
              <td colspan="17" class="px-4 py-10 text-center text-sm text-gray-600 dark:text-slate-300">
                Sin leads.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        v-show="showStickyXScroll"
        ref="stickyScrollRef"
        class="sticky bottom-0 z-20 mt-2 overflow-x-auto rounded-lg border border-slate-200 bg-white/95 dark:border-slate-700 dark:bg-slate-900/95"
      >
        <div :style="{ width: `${stickyScrollWidth}px`, height: '1px' }"></div>
      </div>

      <!-- Pagination -->
      <LeadsTablePagination
        :pagination="pagination"
        :loading="loading"
        @page-change="changePage"
      />
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Components
import LeadsTableFilters from './leads/LeadsTableFilters.vue';
import LeadsTableRow from './leads/LeadsTableRow.vue';
import LeadsTablePagination from './leads/LeadsTablePagination.vue';
import TableColumnsDropdown from './base/TableColumnsDropdown.vue';

// Composables
import { useTableData } from '../composables/useTableData.js';
import { useColumnVisibility } from '../composables/useColumnVisibility';
import { useStickyHorizontalScroll } from '../composables/useStickyHorizontalScroll';

// Use composable for table data management
const {
  loading,
  error,
  items,
  stagesData,
  pagination,
  filters,
  isEmpty,
  paginationInfo,
  fetchData,
  applyFilters,
  changePage,
  changePerPage: updatePerPage,
  debouncedSearch
} = useTableData({
  endpoint: '/leads/data',
  defaultPerPage: 25
});

// Component state - use stagesData from composable
const stages = computed(() => stagesData.value || []);
const importing = ref(false);
const movingLeadIds = ref(new Set());

const searchInput = ref('');
const activeStageId = ref(null);
const perPage = computed(() => pagination.per_page);
const totalCount = computed(() => pagination.total || 0);

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Nombre' },
  { key: 'amount', label: 'Monto' },
  { key: 'currency', label: 'Moneda' },
  { key: 'contact_name', label: 'Contacto' },
  { key: 'contact_phone', label: 'Teléfono' },
  { key: 'contact_email', label: 'Email' },
  { key: 'company_name', label: 'Empresa' },
  { key: 'company_address', label: 'Dirección' },
  { key: 'document_type', label: 'Doc. tipo' },
  { key: 'document_number', label: 'Doc. nro' },
  { key: 'customer_id', label: 'Customer ID' },
  { key: 'stage', label: 'Estado' },
  { key: 'created_by', label: 'Creado por' },
  { key: 'won_at', label: 'Won at' },
  { key: 'created_at', label: 'Creado' },
  { key: 'updated_at', label: 'Actualizado' },
];

const {
  tableRef,
  visibleKeys,
  isColumnVisible,
  toggleColumn,
  resetColumns,
} = useColumnVisibility({
  tableId: 'leads-table',
  columns,
});

const {
  tableScrollRef,
  stickyScrollRef,
  stickyScrollWidth,
  showStickyXScroll,
} = useStickyHorizontalScroll({ tableRef });


// Watch search input for debounced search
watch(searchInput, (newValue) => {
  debouncedSearch(newValue);
});

// Methods
const changePerPage = (newPerPage) => {
  updatePerPage(newPerPage);
};

const handleStageFilter = (stageId) => {
  activeStageId.value = stageId;
  applyFilters({ stage_id: stageId, page: 1 });
};

const handlePeriodFilter = ({ date_from, date_to } = {}) => {
  applyFilters({
    date_from: date_from || undefined,
    date_to: date_to || undefined,
    page: 1,
  });
};

const adjustStageCount = (stageId, delta) => {
  const normalizedStageId = Number(stageId);
  if (!Number.isFinite(normalizedStageId)) return;

  const stage = stagesData.value.find((item) => Number(item.id) === normalizedStageId);
  if (!stage) return;

  const currentCount = Number(stage.count || 0);
  stage.count = Math.max(0, currentCount + delta);
};

const decrementPaginationForRemoval = () => {
  if (Number.isFinite(pagination.total) && pagination.total > 0) {
    pagination.total -= 1;
  }
  if (Number.isFinite(pagination.to) && pagination.to > 0) {
    pagination.to -= 1;
  }
};

const restorePaginationAfterRollback = () => {
  if (Number.isFinite(pagination.total)) {
    pagination.total += 1;
  }
  if (Number.isFinite(pagination.to)) {
    pagination.to += 1;
  }
};

const handleStageChange = async ({ leadId, stageId }) => {
  if (!leadId || !stageId) return;

  const leadIndex = items.value.findIndex((item) => item.id === leadId);
  const lead = leadIndex !== -1 ? items.value[leadIndex] : null;
  if (!lead) return;

  const previousStageId = Number(lead.stage_id);
  const nextStageId = Number(stageId);
  if (previousStageId === nextStageId) return;

  const selectedStage = stages.value.find((stage) => Number(stage.id) === nextStageId);
  const previousStageName = lead.stage_name;
  const activeFilterStage = activeStageId.value === null ? null : Number(activeStageId.value);
  const shouldRemoveFromCurrentList = activeFilterStage !== null && activeFilterStage !== nextStageId;
  const removedLeadSnapshot = { ...lead };

  movingLeadIds.value.add(leadId);
  lead.stage_id = nextStageId;
  if (selectedStage?.name) {
    lead.stage_name = selectedStage.name;
  }

  adjustStageCount(previousStageId, -1);
  adjustStageCount(nextStageId, 1);

  if (shouldRemoveFromCurrentList && leadIndex !== -1) {
    items.value.splice(leadIndex, 1);
    decrementPaginationForRemoval();
  }

  try {
    const response = await axios.patch(`/leads/${leadId}/move-stage`, { stage_id: nextStageId });
    const updatedLead = response?.data?.data;
    if (!shouldRemoveFromCurrentList && updatedLead && typeof updatedLead === 'object') {
      Object.assign(lead, updatedLead);
    }
  } catch (err) {
    lead.stage_id = previousStageId;
    lead.stage_name = previousStageName;

    adjustStageCount(nextStageId, -1);
    adjustStageCount(previousStageId, 1);

    if (shouldRemoveFromCurrentList) {
      const insertionIndex = Math.min(leadIndex, items.value.length);
      items.value.splice(insertionIndex, 0, removedLeadSnapshot);
      restorePaginationAfterRollback();
    }

    Swal.fire({
      title: 'No se pudo cambiar de etapa',
      text: err.response?.data?.message || 'Error desconocido',
      icon: 'error'
    });
  } finally {
    movingLeadIds.value.delete(leadId);
  }
};


const handleImportFile = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  importing.value = true;

  try {
    const formData = new FormData();
    formData.append('file', file);

    const response = await axios.post('/leads/import/prospectos', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    const result = response.data;
    const importData = result?.data ?? {};
    const processed = result?.processed ?? (
      (importData.created || 0) +
      (importData.updated || 0) +
      (importData.skipped || 0) +
      (importData.invalid || 0)
    );
    const imported = result?.imported ?? ((importData.created || 0) + (importData.updated || 0));
    const errors = result?.errors ?? (importData.invalid || 0);
    
    Swal.fire({
      title: '¡Importación completada!',
      html: `
        <div class="text-left">
          <p><strong>Procesados:</strong> ${processed}</p>
          <p><strong>Importados:</strong> ${imported}</p>
          <p><strong>Errores:</strong> ${errors}</p>
        </div>
      `,
      icon: 'success'
    });
    
    // Refresh data
    await fetchData();
  } catch (err) {
    console.error(err);
    Swal.fire({
      title: 'Error en la importación',
      text: err.response?.data?.message || 'Error desconocido',
      icon: 'error'
    });
  } finally {
    importing.value = false;
    // Clear file input
    event.target.value = '';
  }
};

// Initialize component
onMounted(async () => {
  await fetchData();
});
</script>