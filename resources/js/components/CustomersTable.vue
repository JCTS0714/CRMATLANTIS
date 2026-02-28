<template>
  <div class="p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="w-full sm:max-w-md">
        <input
          v-model="searchInput"
          type="text"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
          placeholder="Buscar cliente (nombre, contacto, empresa, documento…)"
        />
      </div>

      <div class="flex items-center gap-3">
        <input ref="importInput" type="file" accept=".csv,text/csv" class="hidden" @change="onImportFileSelected" />
        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="importing"
          @click="triggerImport"
        >
          {{ importing ? 'Importando…' : 'Importar CSV' }}
        </button>

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="showAdvancedFilters = !showAdvancedFilters"
        >
          {{ showAdvancedFilters ? 'Ocultar filtros' : 'Filtros avanzados' }}
        </button>

        <button
          v-if="isLocalOnlyActionsEnabled"
          type="button"
          class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 disabled:opacity-60 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-900/40"
          :disabled="clearingTable"
          @click="clearCustomersTableLocal"
        >
          {{ clearingTable ? 'Limpiando…' : 'Borrar tabla (local)' }}
        </button>

        <div class="text-sm text-slate-600 dark:text-slate-300">
          <span v-if="pagination.total">Mostrando {{ pagination.from }}–{{ pagination.to }} de {{ pagination.total }}</span>
          <span v-else>Sin resultados</span>
        </div>

        <TableColumnsDropdown
          :columns="columns"
          :visible-keys="visibleKeys"
          @toggle="toggleColumn"
          @reset="resetColumns"
        />
      </div>
    </div>

    <div v-if="showAdvancedFilters" class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50">
      <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Servidor</label>
          <select v-model="advancedFilters.servidor" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in serverOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Menbresia</label>
          <select v-model="advancedFilters.menbresia" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todas</option>
            <option v-for="option in menbresiaOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Tipo documento</label>
          <select v-model="advancedFilters.document_type" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in documentTypeOptions" :key="option" :value="option">{{ option.toUpperCase() }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Rubro</label>
          <input v-model="advancedFilters.rubro" type="text" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Ej. restaurante" />
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Mes contacto</label>
          <select v-model="advancedFilters.fecha_contacto_mes" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="month in monthOptions" :key="month.value" :value="month.value">{{ month.label }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Año contacto</label>
          <input v-model="advancedFilters.fecha_contacto_anio" type="number" min="2000" max="2100" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="2026" />
        </div>
      </div>

      <div class="mt-3 flex items-center gap-2">
        <button type="button" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="applyAdvancedFilters">
          Aplicar filtros
        </button>
        <button type="button" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" :disabled="!hasActiveAdvancedFilters" @click="clearAdvancedFilters">
          Limpiar
        </button>
      </div>
    </div>

    <div
      ref="tableScrollRef"
      class="relative mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
      :class="{ 'table-updated-flash': tableJustUpdated }"
    >
      <div v-show="loading" class="search-loading-track">
        <div class="search-loading-bar"></div>
      </div>

      <table ref="tableRef" class="min-w-full text-left text-sm text-slate-700 dark:text-slate-200">
        <colgroup>
          <col
            v-for="column in columns"
            :key="column.key"
            :style="{ display: isColumnVisible(column.key) ? '' : 'none' }"
          />
        </colgroup>
        <thead class="bg-slate-50 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
          <tr>
            <th class="px-4 py-3">N°</th>
            <th class="px-4 py-3">Cliente</th>
            <th class="px-4 py-3">Empresa</th>
            <th class="px-4 py-3">Precio</th>
            <th class="px-4 py-3">Rubro</th>
            <th class="px-4 py-3">Documento</th>
            <th class="px-4 py-3">Teléfono</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Link</th>
            <th class="px-4 py-3">Usuario</th>
            <th class="px-4 py-3">Contraseña</th>
            <th class="px-4 py-3">Servidor</th>
            <th class="px-4 py-3">Mes contacto</th>
            <th class="px-4 py-3">Año contacto</th>
            <th class="px-4 py-3">F. creación</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody class="transition-opacity duration-200" :class="{ 'opacity-55': loading }">
          <tr
            v-for="(c, index) in customers"
            :key="c.id"
            class="border-t border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
          >
            <td class="px-4 py-3">{{ getRowNumber(index) }}</td>
            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
              {{ c.contact_name || c.name || '—' }}
            </td>
            <td class="px-4 py-3">{{ c.company_name || '—' }}</td>
            <td class="px-4 py-3">{{ c.precio ?? '—' }}</td>
            <td class="px-4 py-3">{{ c.rubro || '—' }}</td>
            <td class="px-4 py-3">
              <span v-if="c.document_type || c.document_number">
                {{ c.document_type || '' }} {{ c.document_number || '' }}
              </span>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3">{{ c.contact_phone || '—' }}</td>
            <td class="px-4 py-3">{{ c.contact_email || '—' }}</td>
            <td class="px-4 py-3">{{ c.link || '—' }}</td>
            <td class="px-4 py-3">{{ c.usuario || '—' }}</td>
            <td class="px-4 py-3">{{ c.contrasena || '—' }}</td>
            <td class="px-4 py-3">{{ c.servidor || '—' }}</td>
            <td class="px-4 py-3">{{ c.fecha_contacto_mes || '—' }}</td>
            <td class="px-4 py-3">{{ c.fecha_contacto_anio || '—' }}</td>
            <td class="px-4 py-3">{{ c.fecha_creacion || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="savingIds.has(c.id)"
                  @click="editCustomer(c)"
                >
                  Editar
                </button>
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-60 dark:border-red-900/40 dark:bg-slate-900 dark:text-red-300 dark:hover:bg-red-950/30"
                  :disabled="deletingIds.has(c.id)"
                  @click="deleteCustomer(c)"
                >
                  Eliminar
                </button>
              </div>
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

    <div class="mt-4 flex items-center justify-between">
      <button
        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="pagination.current_page <= 1 || loading"
        @click="goToPage(pagination.current_page - 1)"
      >
        Anterior
      </button>

      <div class="text-sm text-slate-600 dark:text-slate-300">
        Página {{ pagination.current_page }} / {{ pagination.last_page }}
      </div>

      <button
        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="pagination.current_page >= pagination.last_page || loading"
        @click="goToPage(pagination.current_page + 1)"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { confirmDialog, promptCustomerEdit, toastError, toastSuccess } from '../ui/alerts';
import TableColumnsDropdown from './base/TableColumnsDropdown.vue';
import { useColumnVisibility } from '../composables/useColumnVisibility';
import { useStickyHorizontalScroll } from '../composables/useStickyHorizontalScroll';

const customers = ref([]);
const loading = ref(false);
const savingIds = ref(new Set());
const deletingIds = ref(new Set());
const tableJustUpdated = ref(false);

const importInput = ref(null);
const importing = ref(false);
const clearingTable = ref(false);

const isLocalOnlyActionsEnabled = (() => {
  if (typeof window === 'undefined') return false;
  const host = window.location.hostname;
  return import.meta.env.DEV || host === 'localhost' || host === '127.0.0.1' || host === '::1';
})();

const searchInput = ref('');
let searchTimeout = null;
const showAdvancedFilters = ref(false);

const advancedFilters = ref({
  servidor: '',
  menbresia: '',
  rubro: '',
  document_type: '',
  fecha_contacto_mes: '',
  fecha_contacto_anio: '',
});

const serverOptions = ['ATLANTIS ONLINE', 'ATLANTIS VIP', 'ATLANTIS POS', 'ATLANTIS FAST', 'LORITO'];
const menbresiaOptions = ['Mensual', 'Trimestral', 'Semestral', 'Anual'];
const documentTypeOptions = ['dni', 'ruc', 'otro'];
const monthOptions = [
  { value: 1, label: 'Enero' },
  { value: 2, label: 'Febrero' },
  { value: 3, label: 'Marzo' },
  { value: 4, label: 'Abril' },
  { value: 5, label: 'Mayo' },
  { value: 6, label: 'Junio' },
  { value: 7, label: 'Julio' },
  { value: 8, label: 'Agosto' },
  { value: 9, label: 'Septiembre' },
  { value: 10, label: 'Octubre' },
  { value: 11, label: 'Noviembre' },
  { value: 12, label: 'Diciembre' },
];

const hasActiveAdvancedFilters = computed(() => Object.values(advancedFilters.value).some((value) => String(value).trim() !== ''));

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const columns = [
  { key: 'csv_numero', label: 'N°' },
  { key: 'customer', label: 'Cliente' },
  { key: 'company', label: 'Empresa' },
  { key: 'precio', label: 'Precio' },
  { key: 'rubro', label: 'Rubro' },
  { key: 'document', label: 'Documento' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'email', label: 'Email' },
  { key: 'link', label: 'Link' },
  { key: 'usuario', label: 'Usuario' },
  { key: 'contrasena', label: 'Contraseña' },
  { key: 'servidor', label: 'Servidor' },
  { key: 'fecha_contacto_mes', label: 'Mes contacto' },
  { key: 'fecha_contacto_anio', label: 'Año contacto' },
  { key: 'fecha_creacion', label: 'F. creación' },
  { key: 'actions', label: 'Acciones' },
];

const {
  tableRef,
  visibleKeys,
  isColumnVisible,
  toggleColumn,
  resetColumns,
} = useColumnVisibility({
  tableId: 'customers-table',
  columns,
});

const {
  tableScrollRef,
  stickyScrollRef,
  stickyScrollWidth,
  showStickyXScroll,
  refreshStickyScroll,
} = useStickyHorizontalScroll({ tableRef });

const buildAdvancedParams = () => {
  const params = {};

  if (advancedFilters.value.servidor) params.servidor = advancedFilters.value.servidor;
  if (advancedFilters.value.menbresia) params.menbresia = advancedFilters.value.menbresia;
  if (advancedFilters.value.rubro) params.rubro = advancedFilters.value.rubro;
  if (advancedFilters.value.document_type) params.document_type = advancedFilters.value.document_type;
  if (advancedFilters.value.fecha_contacto_mes) params.fecha_contacto_mes = advancedFilters.value.fecha_contacto_mes;
  if (advancedFilters.value.fecha_contacto_anio) params.fecha_contacto_anio = advancedFilters.value.fecha_contacto_anio;

  return params;
};

const fetchCustomers = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await axios.get('/customers/data', {
      params: {
        q: searchInput.value || '',
        per_page: pagination.value.per_page,
        page,
        ...buildAdvancedParams(),
      },
    });

    customers.value = data.customers || [];
    pagination.value = {
      ...pagination.value,
      ...(data.pagination || {}),
    };

    tableJustUpdated.value = true;
    setTimeout(() => {
      tableJustUpdated.value = false;
    }, 220);
  } finally {
    loading.value = false;
  }
};

const applyAdvancedFilters = async () => {
  await fetchCustomers(1);
};

const clearAdvancedFilters = async () => {
  advancedFilters.value = {
    servidor: '',
    menbresia: '',
    rubro: '',
    document_type: '',
    fecha_contacto_mes: '',
    fecha_contacto_anio: '',
  };
  await fetchCustomers(1);
};

const getRowNumber = (index) => {
  const from = Number(pagination.value.from);
  if (Number.isFinite(from) && from > 0) {
    return from + index;
  }

  const currentPage = Number(pagination.value.current_page) || 1;
  const perPage = Number(pagination.value.per_page) || 15;
  return ((currentPage - 1) * perPage) + index + 1;
};

const triggerImport = () => {
  importInput.value?.click?.();
};

const onImportFileSelected = async (ev) => {
  const file = ev?.target?.files?.[0];
  if (!file) return;

  importing.value = true;
  try {
    const fd = new FormData();
    fd.append('csv', file);
    const { data } = await axios.post('/customers/import', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    toastSuccess(data?.output?.trim() ? 'Importación finalizada' : 'Importación finalizada');
    await fetchCustomers(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo importar el CSV.';
    toastError(msg);
  } finally {
    importing.value = false;
    if (importInput.value) importInput.value.value = '';
  }
};

const clearCustomersTableLocal = async () => {
  const ok = await confirmDialog({
    title: 'Borrar tabla de clientes (solo local)',
    text: 'Se eliminarán todos los clientes de la tabla en tu entorno local. Esta acción no se puede deshacer.',
    confirmText: 'Sí, borrar todo',
    cancelText: 'Cancelar',
    icon: 'warning',
  });

  if (!ok) return;

  clearingTable.value = true;
  try {
    const { data } = await axios.post('/customers/clear-local');
    toastSuccess(data?.message || 'Tabla de clientes limpiada.');
    await fetchCustomers(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo limpiar la tabla de clientes.';
    toastError(msg);
  } finally {
    clearingTable.value = false;
  }
};

const editCustomer = async (c) => {
  if (!c?.id) return;
  const payload = await promptCustomerEdit(c);
  if (!payload) return;

  savingIds.value.add(c.id);
  try {
    const res = await axios.put(`/customers/${c.id}`, payload);
    const updated = res?.data?.data;
    if (updated && typeof updated === 'object') {
      Object.assign(c, updated);
    } else {
      Object.assign(c, payload);
    }
    toastSuccess('Cliente actualizado');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo actualizar el cliente.';
    toastError(msg);
  } finally {
    savingIds.value.delete(c.id);
  }
};

const deleteCustomer = async (c) => {
  if (!c?.id) return;
  const ok = await confirmDialog({
    title: 'Eliminar cliente',
    text: 'Se eliminará el cliente. Los leads asociados quedarán sin cliente (se desvinculan).',
    confirmText: 'Eliminar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  deletingIds.value.add(c.id);
  try {
    await axios.delete(`/customers/${c.id}`);
    customers.value = customers.value.filter((x) => x.id !== c.id);
    toastSuccess('Cliente eliminado');
    // Refresh pagination counters if needed
    fetchCustomers(Math.min(pagination.value.current_page, pagination.value.last_page || 1));
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo eliminar el cliente.';
    toastError(msg);
  } finally {
    deletingIds.value.delete(c.id);
  }
};

const goToPage = (page) => {
  const p = Math.max(1, Math.min(pagination.value.last_page || 1, page));
  fetchCustomers(p);
};

const consumeAutoEditCustomerId = () => {
  const url = new URL(window.location.href);
  const rawId = url.searchParams.get('edit_customer_id');
  if (!rawId) return null;

  const id = Number(rawId);
  url.searchParams.delete('edit_customer_id');
  url.searchParams.delete('source');

  const next = `${url.pathname}${url.search ? url.search : ''}${url.hash || ''}`;
  window.history.replaceState({}, '', next || '/customers');

  return Number.isInteger(id) && id > 0 ? id : null;
};

const autoOpenEditFromQuery = async () => {
  const customerId = consumeAutoEditCustomerId();
  if (!customerId) return;

  let customer = customers.value.find((item) => Number(item.id) === customerId) || null;

  if (!customer) {
    try {
      const res = await axios.get(`/customers/${customerId}`);
      customer = res?.data?.data ?? null;
      if (customer) {
        customers.value.unshift(customer);
      }
    } catch (e) {
      toastError('No se pudo abrir el cliente convertido para edición.');
      return;
    }
  }

  if (!customer) {
    toastError('No se encontró el cliente convertido.');
    return;
  }

  await editCustomer(customer);
};

watch(
  searchInput,
  () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      fetchCustomers(1);
    }, 300);
  },
  { flush: 'post' }
);

watch(visibleKeys, async () => {
  await nextTick();
  refreshStickyScroll();
}, { deep: true });

watch(customers, async () => {
  await nextTick();
  refreshStickyScroll();
});

onMounted(async () => {
  await fetchCustomers(1);
  await autoOpenEditFromQuery();
  await nextTick();
  refreshStickyScroll();
});

onBeforeUnmount(() => {
  if (searchTimeout) clearTimeout(searchTimeout);
});
</script>

<style scoped>
.search-loading-track {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  overflow: hidden;
  background: transparent;
  z-index: 10;
}

.search-loading-bar {
  width: 35%;
  height: 100%;
  background: linear-gradient(90deg, transparent 0%, #38bdf8 40%, transparent 100%);
  animation: table-search-slide 0.85s ease-in-out infinite;
}

.table-updated-flash {
  animation: table-flash 220ms ease-out;
}

@keyframes table-search-slide {
  from {
    transform: translateX(-120%);
  }
  to {
    transform: translateX(340%);
  }
}

@keyframes table-flash {
  from {
    box-shadow: inset 0 0 0 9999px rgba(56, 189, 248, 0.08);
  }
  to {
    box-shadow: inset 0 0 0 9999px rgba(56, 189, 248, 0);
  }
}
</style>
