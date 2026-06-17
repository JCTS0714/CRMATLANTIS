<template>
  <div class="space-y-5 p-4">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
        <div class="space-y-3">
          <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-200">
            Postventa · Clientes
          </span>
          <div>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Cartera operativa de clientes</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
              Centraliza seguimiento comercial, incidencias y mantenimiento de datos sin cambiar de contexto.
              Esta vista prioriza búsqueda rápida, filtros útiles y acciones de operación diaria.
            </p>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[420px]">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Clientes</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ pagination.total || 0 }}</div>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Base registrada</div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Filtros</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ activeFiltersCount }}</div>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Reglas activas</div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Columnas</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ visibleColumnCount }}</div>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Visibles en tabla</div>
          </div>
        </div>
      </div>

      <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
        <div class="space-y-2">
          <label for="postventa-customer-search" class="text-sm font-medium text-slate-700 dark:text-slate-200">Buscar cliente</label>
          <input
            id="postventa-customer-search"
            v-model="searchInput"
            type="text"
            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            placeholder="Buscar por nombre, empresa, documento, correo o teléfono"
          />
          <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 dark:bg-slate-800">{{ pagination.total ? `Mostrando ${pagination.from}–${pagination.to} de ${pagination.total}` : 'Sin resultados cargados' }}</span>
            <span v-if="searchInput" class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-blue-700 dark:bg-blue-950/30 dark:text-blue-200">Búsqueda activa</span>
            <span v-if="hasActiveAdvancedFilters" class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">{{ activeFiltersCount }} filtros aplicados</span>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 xl:justify-end">
          <input
            v-if="canCreateCustomers"
            ref="importInput"
            type="file"
            accept=".csv,text/csv"
            class="hidden"
            @change="onImportFileSelected"
          />

          <button
            type="button"
            class="inline-flex items-center rounded-2xl border px-3 py-2 text-sm font-medium shadow-sm transition"
            :class="showAdvancedFilters || hasActiveAdvancedFilters
              ? 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-200 dark:hover:bg-blue-950/50'
              : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800'"
            :aria-expanded="showAdvancedFilters"
            @click="showAdvancedFilters = !showAdvancedFilters"
          >
            {{ showAdvancedFilters ? 'Ocultar filtros' : hasActiveAdvancedFilters ? `Filtros activos (${activeFiltersCount})` : 'Filtros avanzados' }}
          </button>

          <TableColumnsDropdown
            :columns="columns"
            :visible-keys="visibleKeys"
            @toggle="toggleColumn"
            @reset="resetColumns"
          />

          <button
            v-if="canCreateCustomers"
            type="button"
            class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
            :disabled="importing"
            @click="triggerImport"
          >
            {{ importing ? 'Importando…' : 'Importar CSV' }}
          </button>

          <button
            v-if="canCreateCustomers"
            type="button"
            class="inline-flex items-center rounded-2xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-60"
            :disabled="creating"
            @click="createCustomer"
          >
            {{ creating ? 'Creando…' : 'Crear cliente' }}
          </button>

          <button
            v-if="canDeleteCustomers"
            type="button"
            class="inline-flex items-center rounded-2xl border border-red-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 disabled:opacity-60 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-900/40"
            :disabled="clearingTable"
            @click="clearCustomersTableLocal"
          >
            {{ clearingTable ? 'Limpiando…' : 'Borrar tabla' }}
          </button>
        </div>
      </div>
    </section>

    <div v-if="showAdvancedFilters" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
      <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Filtros avanzados</h3>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Afina la cartera por servidor, membresía, estado y fecha de contacto.</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-sm dark:bg-slate-950 dark:text-slate-300">
          {{ activeFiltersCount }} activos
        </span>
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Servidor</label>
          <select v-model="advancedFilters.servidor" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in serverOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Menbresia</label>
          <select v-model="advancedFilters.menbresia" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Todas</option>
            <option v-for="option in menbresiaOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Estado</label>
          <select v-model="advancedFilters.estado" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in estadoOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Tipo documento</label>
          <select v-model="advancedFilters.document_type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="option in documentTypeOptions" :key="option" :value="option">{{ option.toUpperCase() }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Rubro</label>
          <input v-model="advancedFilters.rubro" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Ej. restaurante" />
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Mes contacto</label>
          <select v-model="advancedFilters.fecha_contacto_mes" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">Todos</option>
            <option v-for="month in monthOptions" :key="month.value" :value="month.value">{{ month.label }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Año contacto</label>
          <input v-model="advancedFilters.fecha_contacto_anio" type="number" min="2000" max="2100" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="2026" />
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-2">
        <button type="button" class="inline-flex items-center rounded-2xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700" @click="applyAdvancedFilters">
          Aplicar filtros
        </button>
        <button type="button" class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-800" :disabled="!hasActiveAdvancedFilters" @click="clearAdvancedFilters">
          Limpiar
        </button>
      </div>
    </div>

    <div
      ref="tableScrollRef"
      class="relative overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950"
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
            <th class="px-4 py-3">Contacto</th>
            <th class="px-4 py-3">Empresa</th>
            <th class="px-4 py-3">Ciudad</th>
            <th class="px-4 py-3">Precio</th>
            <th class="px-4 py-3">Rubro</th>
            <th class="px-4 py-3">Documento</th>
            <th class="px-4 py-3">Teléfono</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Link</th>
            <th class="px-4 py-3">Usuario</th>
            <th class="px-4 py-3">Contraseña</th>
            <th class="px-4 py-3">Servidor</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3">Menbresia</th>
            <th class="px-4 py-3">Mes contacto</th>
            <th class="px-4 py-3">Año contacto</th>
            <th class="px-4 py-3">F. creación</th>
            <th class="px-4 py-3">Observación</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody class="transition-opacity duration-200" :class="{ 'opacity-55': loading }">
          <tr v-if="!customers.length">
            <td :colspan="visibleColumnSpan" class="px-6 py-12 text-center">
              <div class="mx-auto max-w-md space-y-2">
                <div class="text-base font-semibold text-slate-900 dark:text-slate-100">No hay clientes para mostrar</div>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">
                  Ajusta la búsqueda, limpia filtros o importa una nueva base para volver a poblar esta vista.
                </p>
              </div>
            </td>
          </tr>
          <tr
            v-for="(c, index) in customers"
            :key="c.id"
            class="border-t border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
          >
            <td class="px-4 py-3">{{ getRowNumber(index) }}</td>
            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
              <div class="flex items-center gap-2">
                <span>{{ c.contact_name || c.name || '—' }}</span>
                <span v-if="c.multiple_businesses" class="inline-flex items-center rounded px-1 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                  Varios negocios
                </span>
              </div>
            </td>
            <td class="px-4 py-3">{{ c.company_name || '—' }}</td>
            <td class="px-4 py-3">{{ c.company_address || '—' }}</td>
            <td class="px-4 py-3">{{ c.precio ?? '—' }}</td>
            <td class="px-4 py-3">{{ c.rubro || '—' }}</td>
            <td class="px-4 py-3">
              <span v-if="c.document_type || c.document_number">{{ c.document_type || '' }} {{ c.document_number || '' }}</span>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3">{{ c.contact_phone || '—' }}</td>
            <td class="px-4 py-3">{{ c.contact_email || '—' }}</td>
            <td class="px-4 py-3">{{ c.link || '—' }}</td>
            <td class="px-4 py-3">{{ c.usuario || '—' }}</td>
            <td class="px-4 py-3">{{ c.contrasena || '—' }}</td>
            <td class="px-4 py-3">{{ c.servidor || '—' }}</td>
            <td class="px-4 py-3 whitespace-nowrap">
              <select
                v-if="canUpdateCustomers"
                class="min-w-30 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                :value="c.estado || 'activo'"
                :disabled="changingStatusIds.has(c.id)"
                @change="updateCustomerStatus(c, $event.target.value)"
              >
                <option value="activo">activo</option>
                <option value="retirado">retirado</option>
                <option value="eliminado">eliminado</option>
              </select>
              <span v-else>{{ c.estado || 'activo' }}</span>
            </td>
            <td class="px-4 py-3">{{ c.menbresia || '—' }}</td>
            <td class="px-4 py-3">{{ c.fecha_contacto_mes || '—' }}</td>
            <td class="px-4 py-3">{{ c.fecha_contacto_anio || '—' }}</td>
            <td class="px-4 py-3">{{ c.fecha_creacion || '—' }}</td>
            <td class="px-4 py-3">{{ c.observacion || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  v-if="canCreateIncidencias"
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  @click="openIncidence(c)"
                >
                  Abrir incidencia
                </button>

                <button
                  v-if="canUpdateCustomers"
                  type="button"
                  class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="savingIds.has(c.id)"
                  @click="editCustomer(c)"
                >
                  Editar
                </button>

                <button
                  v-if="canDeleteCustomers"
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
      class="sticky bottom-0 z-20 mt-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white/95 shadow-sm dark:border-slate-700 dark:bg-slate-950/95"
    >
      <div :style="{ width: `${stickyScrollWidth}px`, height: '1px' }"></div>
    </div>

    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-950">
      <div class="text-sm text-slate-600 dark:text-slate-300">
        Página {{ pagination.current_page }} de {{ pagination.last_page }}
      </div>

      <div class="flex items-center justify-between gap-3 sm:justify-end">
        <button
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="pagination.current_page <= 1 || loading"
        @click="goToPage(pagination.current_page - 1)"
        >
          Anterior
        </button>

        <button
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="pagination.current_page >= pagination.last_page || loading"
          @click="goToPage(pagination.current_page + 1)"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { confirmDialog, promptCustomerCreate, promptCustomerEdit, toastError, toastSuccess } from '../ui/alerts';
import TableColumnsDropdown from './base/TableColumnsDropdown.vue';
import { useColumnVisibility } from '../composables/useColumnVisibility';
import { useStickyHorizontalScroll } from '../composables/useStickyHorizontalScroll';

const customers = ref([]);
const loading = ref(false);
const creating = ref(false);
const tableJustUpdated = ref(false);

const importInput = ref(null);
const importing = ref(false);
const clearingTable = ref(false);

const authUser = computed(() => window.__AUTH_USER__ ?? null);
const hasPermission = (permission) => {
  const perms = authUser.value?.permissions;
  return Array.isArray(perms) && perms.includes(permission);
};

const canCreateCustomers = computed(() => hasPermission('customers.create'));
const canUpdateCustomers = computed(() => hasPermission('customers.update'));
const canDeleteCustomers = computed(() => hasPermission('customers.delete'));
const canCreateIncidencias = computed(() => hasPermission('incidencias.create'));

const savingIds = ref(new Set());
const deletingIds = ref(new Set());
const changingStatusIds = ref(new Set());

const searchInput = ref('');
let searchTimeout = null;
const showAdvancedFilters = ref(false);

const advancedFilters = ref({
  servidor: '',
  menbresia: '',
  estado: '',
  rubro: '',
  document_type: '',
  fecha_contacto_mes: '',
  fecha_contacto_anio: '',
});

const serverOptions = ['ATLANTIS ONLINE', 'ATLANTIS VIP', 'ATLANTIS POS', 'ATLANTIS FAST', 'LORITO'];
const menbresiaOptions = ['Mensual', 'Trimestral', 'Semestral', 'Anual'];
const estadoOptions = ['activo', 'retirado', 'eliminado'];
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

const activeFiltersCount = computed(() => {
  return Object.values(advancedFilters.value).filter((value) => String(value).trim() !== '').length;
});
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
  { key: 'customer', label: 'Contacto' },
  { key: 'company', label: 'Empresa' },
  { key: 'city', label: 'Ciudad' },
  { key: 'precio', label: 'Precio' },
  { key: 'rubro', label: 'Rubro' },
  { key: 'document', label: 'Documento' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'email', label: 'Email' },
  { key: 'link', label: 'Link' },
  { key: 'usuario', label: 'Usuario' },
  { key: 'contrasena', label: 'Contraseña' },
  { key: 'servidor', label: 'Servidor' },
  { key: 'estado', label: 'Estado' },
  { key: 'menbresia', label: 'Menbresia' },
  { key: 'fecha_contacto_mes', label: 'Mes contacto' },
  { key: 'fecha_contacto_anio', label: 'Año contacto' },
  { key: 'fecha_creacion', label: 'F. creación' },
  { key: 'observacion', label: 'Observación' },
  { key: 'actions', label: 'Acciones' },
];

const {
  tableRef,
  visibleKeys,
  isColumnVisible,
  toggleColumn,
  resetColumns,
} = useColumnVisibility({
  tableId: 'postventa-customers-table',
  columns,
});

const visibleColumnCount = computed(() => visibleKeys.value.length || columns.length);
const visibleColumnSpan = computed(() => Math.max(visibleKeys.value.length || columns.length, 1));

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
  if (advancedFilters.value.estado) params.estado = advancedFilters.value.estado;
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
    pagination.value = { ...pagination.value, ...(data.pagination || {}) };

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
    estado: '',
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

const syncFacturaInboxAfterImport = async () => {
  try {
    const { data } = await axios.post('/api/facturas/pagos/sync-mes-actual');
    const created = Number(data?.data?.created || 0);
    toastSuccess(created > 0
      ? `Facturas sincronizadas: ${created} pendiente(s) creadas.`
      : 'Facturas sincronizadas: no hubo nuevos pendientes.');
  } catch (e) {
    // La importacion de clientes ya fue correcta; si falla sync avisamos sin bloquear.
    const msg = e?.response?.data?.message ?? 'Clientes importados, pero no se pudo sincronizar facturas automaticamente.';
    toastError(msg);
  }
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
    await syncFacturaInboxAfterImport();
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
    title: 'Borrar tabla de clientes',
    text: 'Se eliminarán todos los clientes de la tabla. Esta acción no se puede deshacer.',
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

const updateCustomerStatus = async (c, estado) => {
  if (!c?.id) return;
  const previous = c.estado || 'activo';
  if (estado === previous) return;

  c.estado = estado;
  changingStatusIds.value.add(c.id);
  try {
    const { data } = await axios.patch(`/customers/${c.id}/status`, { estado });
    c.estado = data?.data?.estado || estado;
    toastSuccess('Estado actualizado');
  } catch (e) {
    c.estado = previous;
    const msg = e?.response?.data?.message ?? 'No se pudo actualizar el estado.';
    toastError(msg);
  } finally {
    changingStatusIds.value.delete(c.id);
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

const consumeAutoEditCustomerId = () => {
  const url = new URL(window.location.href);
  const rawId = url.searchParams.get('edit_customer_id');
  if (!rawId) return null;

  const id = Number(rawId);
  url.searchParams.delete('edit_customer_id');
  url.searchParams.delete('source');

  const next = `${url.pathname}${url.search ? url.search : ''}${url.hash || ''}`;
  window.history.replaceState({}, '', next || '/postventa/clientes');

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
