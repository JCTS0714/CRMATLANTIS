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

    <div
      ref="tableScrollRef"
      class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
    >
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
        <tbody>
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
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { confirmDialog, promptCustomerEdit, toastError, toastSuccess } from '../ui/alerts';
import TableColumnsDropdown from './base/TableColumnsDropdown.vue';
import { useColumnVisibility } from '../composables/useColumnVisibility';
import { useStickyHorizontalScroll } from '../composables/useStickyHorizontalScroll';

const customers = ref([]);
const loading = ref(false);
const savingIds = ref(new Set());
const deletingIds = ref(new Set());

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

const fetchCustomers = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await axios.get('/customers/data', {
      params: {
        q: searchInput.value || '',
        per_page: pagination.value.per_page,
        page,
      },
    });

    customers.value = data.customers || [];
    pagination.value = {
      ...pagination.value,
      ...(data.pagination || {}),
    };
  } finally {
    loading.value = false;
  }
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
