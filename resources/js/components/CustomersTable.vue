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

      <div class="text-sm text-slate-600 dark:text-slate-300">
        <span v-if="pagination.total">Mostrando {{ pagination.from }}–{{ pagination.to }} de {{ pagination.total }}</span>
        <span v-else>Sin resultados</span>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
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
              <span v-if="c.document_type || c.document_number">
                {{ c.document_type || '' }} {{ c.document_number || '' }}
              </span>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3">{{ c.contact_phone || '—' }}</td>
            <td class="px-4 py-3">{{ c.contact_email || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <a
                  class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :href="leadsLinkFor(c)"
                >
                  Ver leads
                </a>
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
import { ref, watch } from 'vue';
import { confirmDialog, promptCustomerEdit, toastError, toastSuccess } from '../ui/alerts';

const customers = ref([]);
const loading = ref(false);
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
    pagination.value = {
      ...pagination.value,
      ...(data.pagination || {}),
    };
  } finally {
    loading.value = false;
  }
};

const leadsLinkFor = (c) => {
  const q = c?.document_number || c?.name || '';
  const url = new URL(window.location.origin + '/leads/list');
  if (q) url.searchParams.set('q', q);
  return url.pathname + url.search;
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

fetchCustomers(1);
</script>
