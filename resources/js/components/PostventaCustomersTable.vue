<template>
  <div class="p-4">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="w-full sm:max-w-md">
        <input
          v-model="searchInput"
          type="text"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
          placeholder="Buscar cliente (nombre, contacto, empresa, documento…)"
        />
      </div>

      <div class="flex items-center gap-2">
        <input ref="importInput" type="file" accept=".csv,text/csv" class="hidden" @change="onImportFileSelected" />

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="importing"
          @click="triggerImport"
        >
          {{ importing ? 'Importando…' : 'Importar CSV' }}
        </button>

        <button
          type="button"
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-60"
          :disabled="creating"
          @click="createCustomer"
        >
          {{ creating ? 'Creando…' : 'Crear cliente' }}
        </button>

        <div class="text-sm text-slate-600 dark:text-slate-300">
          <span v-if="pagination.total">Mostrando {{ pagination.from }}–{{ pagination.to }} de {{ pagination.total }}</span>
          <span v-else>Sin resultados</span>
        </div>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
      <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-200">
        <thead class="bg-slate-50 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
          <tr>
            <th class="px-4 py-3">Cliente</th>
            <th class="px-4 py-3">Contacto</th>
            <th class="px-4 py-3">Empresa</th>
            <th class="px-4 py-3">Documento</th>
            <th class="px-4 py-3">Teléfono</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="c in customers"
            :key="c.id"
            class="border-t border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
          >
            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
              {{ c.name }}
            </td>
            <td class="px-4 py-3">{{ c.contact_name || '—' }}</td>
            <td class="px-4 py-3">{{ c.company_name || '—' }}</td>
            <td class="px-4 py-3">
              <span v-if="c.document_type || c.document_number">{{ c.document_type || '' }} {{ c.document_number || '' }}</span>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3">{{ c.contact_phone || '—' }}</td>
            <td class="px-4 py-3">{{ c.contact_email || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  @click="openIncidence(c)"
                >
                  Abrir incidencia
                </button>

                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 disabled:opacity-60 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300 dark:hover:bg-emerald-900/40"
                  @click="addPaymentDate(c)"
                >
                  Agregar fecha de pago
                </button>

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
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { confirmDialog, promptCustomerCreate, promptCustomerEdit, promptCustomerPaymentDate, toastError, toastSuccess } from '../ui/alerts';

const customers = ref([]);
const loading = ref(false);
const creating = ref(false);

const importInput = ref(null);
const importing = ref(false);

const savingIds = ref(new Set());
const deletingIds = ref(new Set());

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
    pagination.value = { ...pagination.value, ...(data.pagination || {}) };
  } finally {
    loading.value = false;
  }
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

const labelForCustomer = (c) => {
  const main = c?.company_name || c?.name || '';
  const secondary = c?.company_name ? c?.name : '';
  let label = main;
  if (secondary && secondary !== main) label += ` — ${secondary}`;
  const doc = c?.document_number ? `${c?.document_type ? c.document_type + ' ' : ''}${c.document_number}` : '';
  if (doc) label += ` (${doc})`;
  return label;
};

const openIncidence = (c) => {
  if (!c?.id) return;
  window.dispatchEvent(
    new CustomEvent('incidencias:create', {
      detail: {
        customer_id: c.id,
        customer_label: labelForCustomer(c),
      },
    })
  );
};

const createCustomer = async () => {
  const payload = await promptCustomerCreate();
  if (!payload) return;

  creating.value = true;
  try {
    await axios.post('/customers', payload);
    toastSuccess('Cliente creado');
    await fetchCustomers(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo crear el cliente.';
    toastError(msg);
  } finally {
    creating.value = false;
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

const addPaymentDate = async (c) => {
  if (!c?.id) return;

  const payload = await promptCustomerPaymentDate(c);
  if (!payload) return;

  const customerName = c.company_name || c.name || `Cliente #${c.id}`;
  const title = `Pago cliente: ${customerName}`;

  const details = [
    c.name && c.company_name && c.name !== c.company_name ? `Contacto: ${c.name}` : null,
    c.document_number ? `Documento: ${c.document_type ? `${c.document_type} ` : ''}${c.document_number}` : null,
    c.contact_phone ? `Teléfono: ${c.contact_phone}` : null,
    payload.note ? `Detalle: ${payload.note}` : null,
  ].filter(Boolean);

  try {
    await axios.post('/calendar/events', {
      event_type: 'customer_payment',
      title,
      description: details.join(' | ') || null,
      all_day: true,
      start_at: payload.date,
      end_at: null,
      reminder_minutes: 1440,
      related_type: 'customer',
      related_id: c.id,
      meta: {
        customer_name: c.name || null,
        company_name: c.company_name || null,
        payment_date: payload.date,
      },
    });

    toastSuccess('Fecha de pago agregada al calendario');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo agregar la fecha de pago al calendario.';
    toastError(msg);
  }
};

const deleteCustomer = async (c) => {
  if (!c?.id) return;
  const ok = await confirmDialog({
    title: 'Eliminar cliente',
    text: 'Se eliminará el cliente. Las incidencias asociadas quedarán sin cliente (se desvinculan).',
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

onMounted(() => {
  fetchCustomers(1);
});

onBeforeUnmount(() => {
  if (searchTimeout) clearTimeout(searchTimeout);
});
</script>
