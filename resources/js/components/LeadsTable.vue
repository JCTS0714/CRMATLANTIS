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
        <div v-if="loading" class="text-xs text-gray-600 dark:text-slate-300">Cargando…</div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-[1400px] w-full text-sm text-left text-gray-700 dark:text-slate-200">
          <thead class="text-xs uppercase bg-gray-50 text-gray-700 dark:bg-slate-800 dark:text-slate-200">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">Etapa</th>
              <th class="px-4 py-3">Acciones</th>
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
              <th class="px-4 py-3">Creado por</th>
              <th class="px-4 py-3">Won at</th>
              <th class="px-4 py-3">Archivado</th>
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
              :stage-name-by-id="stageNameById"
              :saving-stage="savingStageIds.has(lead.id)"
              :archiving="archivingIds.has(lead.id)"
              @stage-change="handleStageChange"
              @archive="handleArchive"
            />

            <tr v-if="isEmpty">
              <td colspan="19" class="px-4 py-10 text-center text-sm text-gray-600 dark:text-slate-300">
                Sin leads.
              </td>
            </tr>
          </tbody>
        </table>
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

// Composables
import { useTableData } from '../composables/useTableData.js';

// Use composable for table data management
const {
  loading,
  error,
  items,
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

// Component state
const stages = ref([]);
const importing = ref(false);
const savingStageIds = ref(new Set());
const archivingIds = ref(new Set());

const searchInput = ref('');
const activeStageId = ref(null);
const perPage = computed(() => pagination.per_page);
const totalCount = computed(() => pagination.total || 0);

// Stage name mapping for quick lookup
const stageNameById = computed(() => {
  const map = new Map();
  stages.value.forEach(stage => {
    map.set(stage.id, stage.name);
  });
  return map;
});

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
  applyFilters({ stageId, page: 1 });
};

const handleStageChange = async (lead, event) => {
  const newStageId = parseInt(event.target.value);
  if (newStageId === lead.stage_id) return;

  savingStageIds.value.add(lead.id);

  try {
    await axios.patch(`/leads/${lead.id}`, { stage_id: newStageId });
    
    // Update local data
    lead.stage_id = newStageId;
    lead.stage_name = stageNameById.value.get(newStageId);
    
    Swal.fire({
      title: '¡Actualizado!',
      text: 'Etapa del lead actualizada correctamente',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (err) {
    console.error(err);
    Swal.fire({
      title: 'Error',
      text: 'No se pudo actualizar la etapa del lead',
      icon: 'error'
    });
  } finally {
    savingStageIds.value.delete(lead.id);
  }
};

const handleArchive = async (lead) => {
  const result = await Swal.fire({
    title: '¿Archivar lead?',
    text: `Se archivará el lead "${lead.name}"`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, archivar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (!result.isConfirmed) return;

  archivingIds.value.add(lead.id);

  try {
    await axios.patch(`/leads/${lead.id}/archive`);
    
    Swal.fire({
      title: '¡Archivado!',
      text: 'Lead archivado correctamente',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
    
    // Refresh data
    await fetchData();
  } catch (err) {
    console.error(err);
    Swal.fire({
      title: 'Error',
      text: 'No se pudo archivar el lead',
      icon: 'error'
    });
  } finally {
    archivingIds.value.delete(lead.id);
  }
};

const handleImportFile = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  importing.value = true;

  try {
    const formData = new FormData();
    formData.append('file', file);

    const response = await axios.post('/leads/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    const result = response.data;
    
    Swal.fire({
      title: '¡Importación completada!',
      html: `
        <div class="text-left">
          <p><strong>Procesados:</strong> ${result.processed || 0}</p>
          <p><strong>Importados:</strong> ${result.imported || 0}</p>
          <p><strong>Errores:</strong> ${result.errors || 0}</p>
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

const loadStages = async () => {
  try {
    const response = await axios.get('/leads/stages');
    stages.value = response.data?.data || [];
  } catch (err) {
    console.error('Error loading stages:', err);
  }
};

// Initialize component
onMounted(async () => {
  await Promise.all([
    loadStages(),
    fetchData()
  ]);
});
</script>