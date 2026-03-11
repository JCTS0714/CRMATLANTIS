<template>
  <div class="p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="w-full sm:max-w-md">
        <input
          v-model="searchInput"
          type="text"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
          placeholder="Buscar contador (contador, empresa, contacto, teléfono, usuario, servidor…)"
        />
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <div class="text-sm text-slate-600 dark:text-slate-300">
          <span v-if="pagination.total">Mostrando {{ pagination.from }}–{{ pagination.to }} de {{ pagination.total }}</span>
          <span v-else>Sin resultados</span>
        </div>

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="showAdvancedFilters = !showAdvancedFilters"
        >
          {{ showAdvancedFilters ? 'Ocultar filtros' : 'Filtros avanzados' }}
          <span
            v-if="activeAdvancedFiltersCount"
            class="ml-2 inline-flex min-w-5 items-center justify-center rounded-full bg-sky-100 px-1.5 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-950/60 dark:text-sky-300"
          >
            {{ activeAdvancedFiltersCount }}
          </span>
        </button>

        <label
          class="inline-flex cursor-pointer items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :class="importing ? 'pointer-events-none opacity-50' : ''"
        >
          <input type="file" class="hidden" accept=".csv,.txt,text/csv" @change="onImportFileChange" />
          Importar CSV
        </label>

        <button
          v-if="isLocalOnlyActionEnabled"
          type="button"
          class="inline-flex items-center rounded-lg border border-red-300 bg-white px-3 py-2 text-sm text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-800 dark:bg-slate-900 dark:text-red-300 dark:hover:bg-red-950/30"
          :disabled="clearingLocal || loading"
          @click="clearTableLocal"
        >
          Limpiar tabla (local)
        </button>

        <TableColumnsDropdown
          :columns="columns"
          :visible-keys="visibleKeys"
          @toggle="toggleColumn"
          @reset="resetColumns"
        />
      </div>
    </div>

    <div v-if="showAdvancedFilters" class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50">
      <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Servidor</label>
          <select v-model="advancedFilters.servidor" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in serverOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Estado empresa</label>
          <select v-model="advancedFilters.estado_empresa" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in estadoEmpresaOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Usuario</label>
          <select v-model="advancedFilters.has_usuario" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in availabilityOptions" :key="`usuario-${option.value}`" :value="option.value">{{ option.label }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Teléfono</label>
          <select v-model="advancedFilters.has_telefono" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in availabilityOptions" :key="`telefono-${option.value}`" :value="option.value">{{ option.label }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Link</label>
          <select v-model="advancedFilters.has_link" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in availabilityOptions" :key="`link-${option.value}`" :value="option.value">{{ option.label }}</option>
          </select>
        </div>
      </div>

      <div class="mt-3 flex flex-wrap items-center gap-2">
        <button type="button" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="applyAdvancedFilters">
          Aplicar filtros
        </button>
        <button type="button" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" :disabled="!hasActiveAdvancedFilters" @click="clearAdvancedFilters">
          Limpiar
        </button>
      </div>
    </div>

    <div v-if="importStatus" class="mt-2 text-sm text-slate-600 dark:text-slate-300">
      {{ importStatus }}
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
            <th class="px-4 py-3">Contador</th>
            <th class="px-4 py-3">Empresa</th>
            <th class="px-4 py-3">Contacto</th>
            <th class="px-4 py-3">Teléfono</th>
            <th class="px-4 py-3">Usuario</th>
            <th class="px-4 py-3">Contraseña</th>
            <th class="px-4 py-3">Servidor</th>
            <th class="px-4 py-3">Estado empresa</th>
            <th class="px-4 py-3">Link</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="c in contadores"
            :key="c.id"
            class="border-t border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
          >
            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ c.nom_contador || '—' }}</td>
            <td class="px-4 py-3">{{ c.comercio || '—' }}</td>
            <td class="px-4 py-3">{{ c.titular_tlf || '—' }}</td>
            <td class="px-4 py-3">{{ c.telefono || '—' }}</td>
            <td class="px-4 py-3">{{ c.usuario || '—' }}</td>
            <td class="px-4 py-3">{{ c.contrasena || '—' }}</td>
            <td class="px-4 py-3">{{ c.servidor || '—' }}</td>
            <td class="px-4 py-3">{{ c.estado_empresa || c.customer?.estado || 'activo' }}</td>
            <td class="px-4 py-3">
              <a
                v-if="c.link"
                :href="normalizeLink(c.link)"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sky-600 hover:underline dark:text-sky-400"
              >
                {{ c.link }}
              </a>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="savingIds.has(c.id)"
                  @click="editContador(c)"
                >
                  Editar
                </button>
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-60 dark:border-red-900/40 dark:bg-slate-900 dark:text-red-300 dark:hover:bg-red-950/30"
                  :disabled="deletingIds.has(c.id)"
                  @click="deleteContador(c)"
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
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { confirmDialog, promptContadorCreate, promptContadorEdit, toastError, toastSuccess } from '../ui/alerts';
import TableColumnsDropdown from './base/TableColumnsDropdown.vue';
import { useColumnVisibility } from '../composables/useColumnVisibility';
import { useStickyHorizontalScroll } from '../composables/useStickyHorizontalScroll';

const contadores = ref([]);
const loading = ref(false);
const savingIds = ref(new Set());
const deletingIds = ref(new Set());

const searchInput = ref('');
let searchTimeout = null;
const showAdvancedFilters = ref(false);

const advancedFilters = ref({
  servidor: '',
  estado_empresa: '',
  has_usuario: '',
  has_telefono: '',
  has_link: '',
});

const importing = ref(false);
const importStatus = ref('');
const clearingLocal = ref(false);

const serverOptions = ['ATLANTIS ONLINE', 'ATLANTIS VIP', 'ATLANTIS POS', 'ATLANTIS FAST', 'LORITO'];
const estadoEmpresaOptions = ['activo', 'retirado', 'eliminado'];
const availabilityOptions = [
  { value: '1', label: 'Con dato' },
  { value: '0', label: 'Sin dato' },
];

const hasActiveAdvancedFilters = computed(() => Object.values(advancedFilters.value).some((value) => String(value).trim() !== ''));
const activeAdvancedFiltersCount = computed(() => Object.values(advancedFilters.value).filter((value) => String(value).trim() !== '').length);

const isLocalOnlyActionEnabled =
  import.meta.env.DEV || ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const columns = [
  { key: 'nom_contador', label: 'Contador' },
  { key: 'comercio', label: 'Empresa' },
  { key: 'titular_tlf', label: 'Contacto' },
  { key: 'telefono', label: 'Teléfono' },
  { key: 'usuario', label: 'Usuario' },
  { key: 'contrasena', label: 'Contraseña' },
  { key: 'servidor', label: 'Servidor' },
  { key: 'estado', label: 'Estado empresa' },
  { key: 'link', label: 'Link' },
  { key: 'actions', label: 'Acciones' },
];

const {
  tableRef,
  visibleKeys,
  isColumnVisible,
  toggleColumn,
  resetColumns,
} = useColumnVisibility({
  tableId: 'contadores-table',
  columns,
});

const {
  tableScrollRef,
  stickyScrollRef,
  stickyScrollWidth,
  showStickyXScroll,
} = useStickyHorizontalScroll({ tableRef });

const buildAdvancedParams = () => {
  const params = {};

  if (advancedFilters.value.servidor) params.servidor = advancedFilters.value.servidor;
  if (advancedFilters.value.estado_empresa) params.estado_empresa = advancedFilters.value.estado_empresa;
  if (advancedFilters.value.has_usuario) params.has_usuario = advancedFilters.value.has_usuario;
  if (advancedFilters.value.has_telefono) params.has_telefono = advancedFilters.value.has_telefono;
  if (advancedFilters.value.has_link) params.has_link = advancedFilters.value.has_link;

  return params;
};

const fetchRows = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await axios.get('/postventa/contadores/data', {
      params: {
        q: searchInput.value || '',
        per_page: pagination.value.per_page,
        page,
        ...buildAdvancedParams(),
      },
    });

    contadores.value = data.contadores || [];
    pagination.value = {
      ...pagination.value,
      ...(data.pagination || {}),
    };
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => {
  const p = Math.max(1, Math.min(pagination.value.last_page || 1, page));
  fetchRows(p);
};

const applyAdvancedFilters = async () => {
  await fetchRows(1);
};

const clearAdvancedFilters = async () => {
  advancedFilters.value = {
    servidor: '',
    estado_empresa: '',
    has_usuario: '',
    has_telefono: '',
    has_link: '',
  };
  await fetchRows(1);
};

const normalizeLink = (value) => {
  const raw = String(value ?? '').trim();
  if (!raw) return '#';
  if (/^https?:\/\//i.test(raw)) return raw;
  return `https://${raw}`;
};

const onImportFileChange = async (event) => {
  const file = event?.target?.files?.[0];
  if (event?.target) event.target.value = '';
  if (!file) return;

  importing.value = true;
  importStatus.value = 'Importando...';
  try {
    const form = new FormData();
    form.append('csv', file);

    const { data } = await axios.post('/postventa/contadores/import', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    const output = (data?.output ?? '').trim();
    importStatus.value = output !== '' ? output : 'Importación finalizada.';
    toastSuccess('Importación finalizada');
    fetchRows(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo importar el CSV.';
    importStatus.value = msg;
    toastError(msg);
  } finally {
    importing.value = false;
  }
};

const clearTableLocal = async () => {
  const ok = await confirmDialog({
    title: 'Limpiar tabla de contadores',
    text: 'Se eliminarán todos los registros de contadores (solo local).',
    confirmText: 'Limpiar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  clearingLocal.value = true;
  try {
    const { data } = await axios.post('/postventa/contadores/clear-local');
    const deleted = Number(data?.deleted ?? 0);
    importStatus.value = `Tabla limpiada en local. Registros eliminados: ${deleted}.`;
    toastSuccess(`Tabla limpiada (${deleted} registros)`);
    fetchRows(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo limpiar la tabla en local.';
    toastError(msg);
  } finally {
    clearingLocal.value = false;
  }
};

const createContador = async () => {
  const payload = await promptContadorCreate();
  if (!payload) return;

  try {
    await axios.post('/postventa/contadores', payload);
    toastSuccess('Contador creado');
    fetchRows(1);
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo crear el contador.';
    toastError(msg);
  }
};

const editContador = async (c) => {
  if (!c?.id) return;
  const payload = await promptContadorEdit(c);
  if (!payload) return;

  savingIds.value.add(c.id);
  try {
    const res = await axios.put(`/postventa/contadores/${c.id}`, payload);
    const updated = res?.data?.data;
    const customer = Array.isArray(updated?.customers) && updated.customers.length > 0
      ? updated.customers[0]
      : (updated?.customer ?? null);

    Object.assign(c, {
      ...payload,
      comercio: updated?.comercio ?? c.comercio,
      customer,
      customers: customer ? [customer] : [],
    });

    toastSuccess('Contador actualizado');
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo actualizar el contador.';
    toastError(msg);
  } finally {
    savingIds.value.delete(c.id);
  }
};

const deleteContador = async (c) => {
  if (!c?.id) return;
  const ok = await confirmDialog({
    title: 'Eliminar contador',
    text: 'Se eliminará el contador y su asignación a cliente (si existe).',
    confirmText: 'Eliminar',
    cancelText: 'Cancelar',
    icon: 'warning',
  });
  if (!ok) return;

  deletingIds.value.add(c.id);
  try {
    await axios.delete(`/postventa/contadores/${c.id}`);
    contadores.value = contadores.value.filter((x) => x.id !== c.id);
    toastSuccess('Contador eliminado');
    fetchRows(Math.min(pagination.value.current_page, pagination.value.last_page || 1));
  } catch (e) {
    const msg = e?.response?.data?.message ?? 'No se pudo eliminar el contador.';
    toastError(msg);
  } finally {
    deletingIds.value.delete(c.id);
  }
};

const onCreateEvent = () => createContador();

onMounted(() => {
  window.addEventListener('contadores:create', onCreateEvent);
});

onBeforeUnmount(() => {
  window.removeEventListener('contadores:create', onCreateEvent);
});

watch(
  searchInput,
  () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      fetchRows(1);
    }, 300);
  },
  { flush: 'post' }
);

fetchRows(1);
</script>
