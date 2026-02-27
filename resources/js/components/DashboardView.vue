<template>
  <div>
    <div
      v-if="loading"
      class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
    >
      Cargando dashboard…
    </div>

    <div
      v-else-if="error"
      class="rounded-lg border border-red-200 bg-white p-6 text-sm text-red-700 dark:border-red-900/40 dark:bg-slate-900 dark:text-red-300"
    >
      {{ error }}
    </div>

    <div v-else class="space-y-6">
      <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
        <div class="flex items-center gap-2">
          <label class="text-sm text-slate-600 dark:text-slate-300">Mes</label>
          <input
                ref="monthInput"
            v-model="selectedMonth"
            type="month"
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                @click="openMonthPicker"
          />
          <button
            type="button"
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
            :disabled="loading"
            @click="applyMonth"
          >
            Ver
          </button>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a
          v-if="cards.leads"
          href="/leads"
          class="block rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500 dark:text-slate-300">Leads</span>
            <span class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-slate-800 dark:text-slate-200"
              >Total</span
            >
          </div>
          <div class="mt-3 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ cards.leads.total }}</div>
          <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Abiertos: {{ cards.leads.open }} · Archivados: {{ cards.leads.archived }}
          </div>
        </a>

        <a
          v-if="cards.customers"
          href="/postventa/clientes"
          class="block rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500 dark:text-slate-300">Clientes</span>
            <span class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-slate-800 dark:text-slate-200"
              >Total</span
            >
          </div>
          <div class="mt-3 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ cards.customers.total }}</div>
          <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Clientes registrados</div>
        </a>

        <a
          v-if="cards.incidencias"
          href="/backlog"
          class="block rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500 dark:text-slate-300">Incidencias</span>
            <span class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-slate-800 dark:text-slate-200"
              >Postventa</span
            >
          </div>
          <div class="mt-3 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ cards.incidencias.open }}</div>
          <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Abiertas · Archivadas: {{ cards.incidencias.archived }}</div>
        </a>

        <a
          v-if="cards.certificados"
          href="/postventa/certificados"
          class="block rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500 dark:text-slate-300">Certificados</span>
            <span
              class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-slate-800 dark:text-slate-200"
              >Activos</span
            >
          </div>
          <div class="mt-3 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ cards.certificados.activos }}</div>
          <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Por vencer (30d): {{ cards.certificados.por_vencer_30d }} · Vencidos: {{ cards.certificados.vencidos }}
          </div>
        </a>

        <a
          v-if="cards.contadores"
          href="/postventa/contadores"
          class="block rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500 dark:text-slate-300">Contadores</span>
            <span class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-slate-800 dark:text-slate-200"
              >Total</span
            >
          </div>
          <div class="mt-3 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ cards.contadores.total }}</div>
          <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Asignados: {{ cards.contadores.assigned }} · Sin asignar: {{ cards.contadores.unassigned }}
          </div>
        </a>

        <a
          v-if="cards.calendar"
          href="/calendar"
          class="block rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-500 dark:text-slate-300">Calendario</span>
            <span class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-slate-800 dark:text-slate-200"
              >7 días</span
            >
          </div>
          <div class="mt-3 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ cards.calendar.upcoming_7d }}</div>
          <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Eventos asignados próximos</div>
        </a>
      </section>

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
          <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 p-6 dark:border-slate-800">
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Certificados por vencer</h2>
                <div class="flex items-center gap-2">
                  <TableColumnsDropdown
                    :columns="certificadosColumns"
                    :visible-keys="visibleKeys"
                    @toggle="toggleColumn"
                    @reset="resetColumns"
                  />
                  <a
                    v-if="cards.certificados"
                    href="/postventa/certificados"
                    class="text-sm font-medium text-blue-600 hover:underline"
                  >
                    Ver certificados
                  </a>
                </div>
              </div>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Próximos 30 días (máx. 5)</p>
            </div>

            <div ref="tableScrollRef" class="overflow-x-auto">
              <table ref="tableRef" class="min-w-full text-left text-sm text-slate-700 dark:text-slate-200">
                <colgroup>
                  <col
                    v-for="column in certificadosColumns"
                    :key="column.key"
                    :style="{ display: isColumnVisible(column.key) ? '' : 'none' }"
                  />
                </colgroup>
                <thead class="bg-slate-50 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                  <tr>
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3">RUC</th>
                    <th class="px-6 py-3">Tipo</th>
                    <th class="px-6 py-3">Vence</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="c in lists.certificados_por_vencer"
                    :key="c.id"
                    class="border-t border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
                  >
                    <td class="px-6 py-3 font-medium text-slate-900 dark:text-slate-100">{{ c.nombre }}</td>
                    <td class="px-6 py-3">{{ c.ruc || '—' }}</td>
                    <td class="px-6 py-3">{{ c.tipo || '—' }}</td>
                    <td class="px-6 py-3">{{ c.fecha_vencimiento || '—' }}</td>
                  </tr>

                  <tr v-if="!lists.certificados_por_vencer?.length">
                    <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-300" colspan="4">Sin vencimientos próximos.</td>
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
          </div>
        </div>

        <div>
          <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 p-6 dark:border-slate-800">
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Próximos eventos</h2>
                <a v-if="cards.calendar" href="/calendar" class="text-sm font-medium text-blue-600 hover:underline">Ver calendario</a>
              </div>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Asignados a ti (máx. 5)</p>
            </div>

            <div class="p-6">
              <div v-if="!lists.upcoming_events?.length" class="text-sm text-slate-500 dark:text-slate-300">Sin eventos próximos.</div>

              <ul v-else class="space-y-3">
                <li v-for="e in lists.upcoming_events" :key="e.id" class="rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                  <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ e.title }}</div>
                  <div class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                    {{ formatDateTime(e.start_at) }}
                    <span v-if="e.location">· {{ e.location }}</span>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div v-if="lists.recent_leads" class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="border-b border-slate-200 p-6 dark:border-slate-800">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Últimos leads</h2>
              <a href="/leads" class="text-sm font-medium text-blue-600 hover:underline">Ver leads</a>
            </div>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Últimos 5 registros</p>
          </div>
          <div class="divide-y divide-slate-200 dark:divide-slate-800">
            <div v-for="l in lists.recent_leads" :key="l.id" class="p-6">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ l.name }}</div>
                  <div class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ l.company_name || '—' }}</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ formatMoney(l.amount, l.currency) }}</div>
                  <div class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ formatDateTime(l.created_at) }}</div>
                </div>
              </div>
            </div>

            <div v-if="!lists.recent_leads?.length" class="p-6 text-sm text-slate-500 dark:text-slate-300">Sin leads.</div>
          </div>
        </div>

        <div
          v-if="lists.recent_incidences"
          class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
          <div class="border-b border-slate-200 p-6 dark:border-slate-800">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Últimas incidencias</h2>
              <a href="/backlog" class="text-sm font-medium text-blue-600 hover:underline">Ver incidencias</a>
            </div>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Últimos 5 registros</p>
          </div>
          <div class="divide-y divide-slate-200 dark:divide-slate-800">
            <div v-for="i in lists.recent_incidences" :key="i.id" class="p-6">
              <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ i.correlative }} · {{ i.title }}</div>
              <div class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                Prioridad: {{ i.priority || '—' }} · Fecha: {{ i.date || '—' }} · {{ formatDateTime(i.created_at) }}
              </div>
            </div>

            <div v-if="!lists.recent_incidences?.length" class="p-6 text-sm text-slate-500 dark:text-slate-300">Sin incidencias.</div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import TableColumnsDropdown from './base/TableColumnsDropdown.vue';
import { useColumnVisibility } from '../composables/useColumnVisibility';
import { useStickyHorizontalScroll } from '../composables/useStickyHorizontalScroll';

const certificadosColumns = [
  { key: 'nombre', label: 'Nombre' },
  { key: 'ruc', label: 'RUC' },
  { key: 'tipo', label: 'Tipo' },
  { key: 'vence', label: 'Vence' },
];

const {
  tableRef,
  visibleKeys,
  isColumnVisible,
  toggleColumn,
  resetColumns,
} = useColumnVisibility({
  tableId: 'dashboard-certificados-table',
  columns: certificadosColumns,
});

const {
  tableScrollRef,
  stickyScrollRef,
  stickyScrollWidth,
  showStickyXScroll,
} = useStickyHorizontalScroll({ tableRef });

const loading = ref(true);
const error = ref('');

const cards = ref({});
const lists = ref({
  certificados_por_vencer: [],
  upcoming_events: [],
  recent_leads: null,
  recent_incidences: null,
});

const monthInput = ref(null);
const selectedMonth = ref('');

const openMonthPicker = () => {
  const el = monthInput.value;
  if (!el) return;
  el.focus();
  if (typeof el.showPicker === 'function') el.showPicker();
};

const formatDateTime = (value) => {
  if (!value) return '—';
  try {
    const date = new Date(value);
    return new Intl.DateTimeFormat('es-PE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(date);
  } catch {
    return String(value);
  }
};

const formatMoney = (amount, currency) => {
  if (amount === null || amount === undefined || amount === '') return '—';
  const n = Number(amount);
  if (!Number.isFinite(n)) return String(amount);

  const cur = currency || 'PEN';
  try {
    return new Intl.NumberFormat('es-PE', {
      style: 'currency',
      currency: cur,
      maximumFractionDigits: 2,
    }).format(n);
  } catch {
    return `${n.toFixed(2)} ${cur}`;
  }
};

const load = async () => {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await axios.get('/dashboard/summary', {
      params: {
        month: selectedMonth.value || undefined,
      },
    });
    cards.value = data?.cards || {};
    lists.value = {
      ...lists.value,
      ...(data?.lists || {}),
    };
  } catch (e) {
    error.value = e?.response?.data?.message ?? 'No se pudo cargar el dashboard.';
  } finally {
    loading.value = false;
  }
};

const applyMonth = () => {
  load();
};

onMounted(() => {
  load();
});
</script>
