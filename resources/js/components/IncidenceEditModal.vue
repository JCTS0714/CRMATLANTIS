<template>
  <Transition name="fade">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="hide"></div>

      <Transition name="pop">
        <div class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-slate-900">
          <div class="p-5 border-b border-gray-200 dark:border-slate-800">
            <div class="text-lg font-semibold text-gray-900 dark:text-slate-100">Editar incidencia</div>
            <div class="mt-1 text-sm text-gray-600 dark:text-slate-300">
              Actualiza título, cliente, prioridad, fecha y notas.
            </div>
          </div>

          <form class="p-5" @submit.prevent="submit">
            <div v-if="error" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
              {{ error }}
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Nombre de la incidencia</label>
                <input
                  v-model.trim="form.title"
                  type="text"
                  required
                  class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Fecha</label>
                <input
                  v-model="form.date"
                  type="date"
                  class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Prioridad</label>
                <select
                  v-model="form.priority"
                  class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                >
                  <option value="alta">Alta</option>
                  <option value="media">Media</option>
                  <option value="baja">Baja</option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Cliente</label>
                <div class="relative">
                  <input
                    v-model.trim="customerQuery"
                    type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                    placeholder="Buscar cliente…"
                    @input="onCustomerInput"
                    @focus="onCustomerInput"
                  />

                  <div
                    v-if="customerResultsOpen && customerResults.length"
                    class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white p-1 shadow-lg dark:border-slate-800 dark:bg-slate-950"
                  >
                    <button
                      v-for="it in customerResults"
                      :key="it.id"
                      type="button"
                      class="flex w-full items-center justify-between rounded-md px-2 py-2 text-left text-sm text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:text-slate-100 dark:hover:bg-slate-900 dark:focus:ring-blue-500/30"
                      @click="selectCustomer(it)"
                    >
                      <span class="truncate">{{ it.label }}</span>
                      <span class="ml-3 text-xs text-gray-500 dark:text-slate-400">#{{ it.id }}</span>
                    </button>
                  </div>

                  <div v-if="selectedCustomerLabel" class="mt-1 text-xs text-gray-600 dark:text-slate-300">
                    Seleccionado: {{ selectedCustomerLabel }}
                    <button
                      type="button"
                      class="ml-2 text-xs font-medium text-blue-600 hover:underline"
                      @click="clearCustomer"
                    >
                      Quitar
                    </button>
                  </div>
                </div>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Observaciones</label>
                <textarea
                  v-model.trim="form.notes"
                  rows="4"
                  class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                  placeholder="Detalle / notas…"
                ></textarea>
              </div>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="hide"
              >
                Cancelar
              </button>

              <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"
                :disabled="saving || !form.id"
              >
                {{ saving ? 'Guardando…' : 'Guardar cambios' }}
              </button>
            </div>
          </form>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';
import { toastError, toastSuccess } from '../ui/alerts';

const open = ref(false);
const saving = ref(false);
const error = ref('');

const form = ref({
  id: null,
  title: '',
  date: '',
  priority: 'media',
  notes: '',
  customer_id: null,
});

const customerQuery = ref('');
const selectedCustomerLabel = ref('');
const customerResults = ref([]);
const customerResultsOpen = ref(false);
let customerTimer = null;

const reset = () => {
  error.value = '';
  form.value = {
    id: null,
    title: '',
    date: '',
    priority: 'media',
    notes: '',
    customer_id: null,
  };
  customerQuery.value = '';
  selectedCustomerLabel.value = '';
  customerResults.value = [];
  customerResultsOpen.value = false;
};

const hide = () => {
  open.value = false;
  customerResultsOpen.value = false;
};

const clearCustomer = () => {
  form.value.customer_id = null;
  selectedCustomerLabel.value = '';
};

const selectCustomer = (it) => {
  form.value.customer_id = Number(it.id);
  selectedCustomerLabel.value = String(it.label || '');
  customerQuery.value = String(it.label || '');
  customerResultsOpen.value = false;
};

const lookupCustomerById = async (id) => {
  try {
    const res = await axios.get('/related-lookup', { params: { type: 'customer', id } });
    const label = res?.data?.data?.label;
    if (label) {
      selectedCustomerLabel.value = String(label);
      customerQuery.value = String(label);
    } else {
      selectedCustomerLabel.value = `#${id}`;
      customerQuery.value = `#${id}`;
    }
  } catch {
    selectedCustomerLabel.value = `#${id}`;
    customerQuery.value = `#${id}`;
  }
};

const doCustomerSearch = async () => {
  const q = (customerQuery.value || '').trim();
  if (!q || q.length < 2) {
    customerResults.value = [];
    customerResultsOpen.value = false;
    return;
  }

  try {
    const res = await axios.get('/related-lookup', { params: { type: 'customer', q, limit: 8 } });
    const items = res?.data?.data ?? [];
    customerResults.value = Array.isArray(items) ? items : [];
    customerResultsOpen.value = customerResults.value.length > 0;
  } catch {
    customerResults.value = [];
    customerResultsOpen.value = false;
  }
};

const onCustomerInput = () => {
  form.value.customer_id = null;
  selectedCustomerLabel.value = '';

  if (customerTimer) window.clearTimeout(customerTimer);
  customerTimer = window.setTimeout(() => {
    doCustomerSearch();
  }, 250);
};

const firstValidationMessage = (err) => {
  const errors = err?.response?.data?.errors;
  if (!errors || typeof errors !== 'object') return null;
  const firstKey = Object.keys(errors)[0];
  const first = firstKey ? errors[firstKey]?.[0] : null;
  return typeof first === 'string' ? first : null;
};

const parseIsoToDateInput = (iso) => {
  if (!iso) return '';
  try {
    // expects YYYY-MM-DD or ISO datetime
    const s = String(iso);
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
    const d = new Date(s);
    if (Number.isNaN(d.getTime())) return '';
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  } catch {
    return '';
  }
};

const show = async (detail = {}) => {
  reset();

  const incidence = detail?.incidence ?? detail?.item ?? detail;
  const id = incidence?.id ?? detail?.id ?? null;
  if (!id) return;

  form.value.id = Number(id);
  form.value.title = String(incidence?.title ?? '');
  form.value.date = parseIsoToDateInput(incidence?.date ?? '');
  form.value.priority = String(incidence?.priority ?? 'media') || 'media';
  form.value.notes = String(incidence?.notes ?? '');

  const customerId = incidence?.customer_id ?? detail?.customer_id ?? null;
  form.value.customer_id = customerId ? Number(customerId) : null;

  open.value = true;

  const customerLabel = incidence?.customer?.company_name || incidence?.customer?.name || detail?.customer_label || null;
  if (form.value.customer_id) {
    if (customerLabel) {
      selectedCustomerLabel.value = String(customerLabel);
      customerQuery.value = String(customerLabel);
    } else {
      await lookupCustomerById(form.value.customer_id);
    }
  }
};

const submit = async () => {
  if (!form.value.id) return;

  saving.value = true;
  error.value = '';

  const payload = {
    title: form.value.title,
    date: form.value.date || null,
    priority: form.value.priority || 'media',
    notes: form.value.notes || null,
    customer_id: form.value.customer_id || null,
  };

  try {
    const res = await axios.put(`/incidencias/${form.value.id}`, payload);
    const updated = res?.data?.data;
    toastSuccess('Incidencia actualizada');
    hide();
    window.dispatchEvent(new CustomEvent('incidencias:updated', { detail: { incidence: updated } }));
  } catch (e) {
    error.value = firstValidationMessage(e) ?? e?.response?.data?.message ?? 'No se pudo actualizar la incidencia.';
    toastError(error.value);
  } finally {
    saving.value = false;
  }
};

const onExternalEdit = (e) => {
  show(e?.detail ?? {});
};

onMounted(() => {
  window.addEventListener('incidencias:edit', onExternalEdit);
});

onBeforeUnmount(() => {
  window.removeEventListener('incidencias:edit', onExternalEdit);
  if (customerTimer) window.clearTimeout(customerTimer);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.pop-enter-active,
.pop-leave-active {
  transition: transform 0.15s ease, opacity 0.15s ease;
}
.pop-enter-from,
.pop-leave-to {
  transform: translateY(8px) scale(0.98);
  opacity: 0;
}
</style>
