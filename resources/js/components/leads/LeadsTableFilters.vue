<template>
  <section>
    <!-- Search and Controls Row -->
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex-1">
        <label class="sr-only" for="leads-search">Buscar</label>
        <input
          id="leads-search"
          :value="modelValue"
          type="search"
          placeholder="Buscar por nombre, empresa, contacto, email, teléfono, documento…"
          class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500"
          @input="$emit('update:modelValue', $event.target.value)"
        />
      </div>

      <div class="flex items-center gap-2">
        <input
          ref="importInput"
          type="file"
          accept=".csv,text/csv"
          class="hidden"
          @change="$emit('import-file', $event)"
        />

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="importing"
          @click="$refs.importInput.click()"
        >
          {{ importing ? 'Importando…' : 'Importar CSV' }}
        </button>

        <select
          :value="perPage"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
          @change="$emit('update:per-page', Number($event.target.value))"
        >
          <option :value="10">10</option>
          <option :value="15">15</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
      </div>
    </div>

    <!-- Period Filter Row -->
    <div class="mb-4 flex flex-wrap items-center gap-2">
      <div class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Periodo:</label>
        <select
          v-model="periodType"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
        >
          <option value="all">Todos</option>
          <option value="last_week">Última semana</option>
          <option value="month">Por mes</option>
          <option value="between_months">Entre meses</option>
          <option value="date">Por fecha</option>
          <option value="between_dates">Entre fechas</option>
        </select>
      </div>

      <div v-if="periodType === 'month'" class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Mes:</label>
        <input
          v-model="periodMonth"
          type="month"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:scheme-dark"
        />
      </div>

      <div v-if="periodType === 'between_months'" class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Desde mes:</label>
        <input
          v-model="periodMonthFrom"
          type="month"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:scheme-dark"
        />
      </div>

      <div v-if="periodType === 'between_months'" class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Hasta mes:</label>
        <input
          v-model="periodMonthTo"
          type="month"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:scheme-dark"
        />
      </div>

      <div v-if="periodType === 'date'" class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Fecha:</label>
        <input
          v-model="periodDate"
          type="date"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:scheme-dark"
        />
      </div>

      <div v-if="periodType === 'between_dates'" class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Desde:</label>
        <input
          v-model="periodDateFrom"
          type="date"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:scheme-dark"
        />
      </div>

      <div v-if="periodType === 'between_dates'" class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Hasta:</label>
        <input
          v-model="periodDateTo"
          type="date"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:scheme-dark"
        />
      </div>

    </div>

    <!-- Stage Filter Tabs -->
    <div class="mb-4 overflow-x-auto">
      <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <button
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition"
          :class="activeStageId === null ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-800'"
          @click="$emit('stage-filter', null)"
        >
          Todos
          <span class="ml-2 rounded bg-black/5 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-slate-200">{{ totalCount }}</span>
        </button>

        <button
          v-for="stage in stages"
          :key="stage.id"
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition"
          :class="activeStageId === stage.id ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-800'"
          @click="$emit('stage-filter', stage.id)"
        >
          {{ stage.name }}
          <span class="ml-2 rounded bg-black/5 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-slate-200">{{ stage.count ?? 0 }}</span>
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, watch } from 'vue';

defineProps({
  modelValue: {
    type: String,
    required: true
  },
  stages: {
    type: Array,
    required: true
  },
  activeStageId: {
    type: [Number, null],
    default: null
  },
  totalCount: {
    type: Number,
    default: 0
  },
  perPage: {
    type: Number,
    default: 25
  },
  importing: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits([
  'update:modelValue',
  'update:per-page',
  'stage-filter',
  'import-file',
  'period-filter'
]);

const periodType = ref('last_week');
const periodMonth = ref('');
const periodMonthFrom = ref('');
const periodMonthTo = ref('');
const periodDate = ref('');
const periodDateFrom = ref('');
const periodDateTo = ref('');

const clearPeriodInputs = () => {
  periodMonth.value = '';
  periodMonthFrom.value = '';
  periodMonthTo.value = '';
  periodDate.value = '';
  periodDateFrom.value = '';
  periodDateTo.value = '';
};

const pad = (value) => String(value).padStart(2, '0');

const toDateString = (date) => {
  const year = date.getFullYear();
  const month = pad(date.getMonth() + 1);
  const day = pad(date.getDate());
  return `${year}-${month}-${day}`;
};

const getMonthRange = (value) => {
  if (!value || !/^\d{4}-\d{2}$/.test(value)) {
    return { from: null, to: null };
  }

  const [yearText, monthText] = value.split('-');
  const year = Number(yearText);
  const month = Number(monthText);
  if (Number.isNaN(year) || Number.isNaN(month) || month < 1 || month > 12) {
    return { from: null, to: null };
  }

  const lastDay = new Date(year, month, 0).getDate();
  return {
    from: `${year}-${pad(month)}-01`,
    to: `${year}-${pad(month)}-${pad(lastDay)}`,
  };
};

const normalizeRange = (from, to) => {
  if (from && to && from > to) {
    return { from: to, to: from };
  }
  return { from, to };
};

const resolvePeriodRange = () => {
  switch (periodType.value) {
    case 'last_week': {
      const end = new Date();
      const start = new Date(end);
      start.setDate(start.getDate() - 6);
      return {
        from: toDateString(start),
        to: toDateString(end),
      };
    }

    case 'month': {
      const { from, to } = getMonthRange(periodMonth.value);
      return { from: from ?? undefined, to: to ?? undefined };
    }

    case 'between_months': {
      const startRange = getMonthRange(periodMonthFrom.value);
      const endRange = getMonthRange(periodMonthTo.value);
      return normalizeRange(startRange.from ?? undefined, endRange.to ?? undefined);
    }

    case 'date': {
      if (!periodDate.value) return { from: undefined, to: undefined };
      return { from: periodDate.value, to: periodDate.value };
    }

    case 'between_dates': {
      return normalizeRange(
        periodDateFrom.value || undefined,
        periodDateTo.value || undefined,
      );
    }

    default:
      return { from: undefined, to: undefined };
  }
};

const isPeriodReady = () => {
  if (periodType.value === 'all' || periodType.value === 'last_week') return true;
  if (periodType.value === 'month') return !!periodMonth.value;
  if (periodType.value === 'between_months') return !!periodMonthFrom.value && !!periodMonthTo.value;
  if (periodType.value === 'date') return !!periodDate.value;
  if (periodType.value === 'between_dates') return !!periodDateFrom.value && !!periodDateTo.value;
  return false;
};

watch(periodType, (newType, oldType) => {
  if (newType !== oldType) {
    clearPeriodInputs();
  }

  if (newType === 'all' || newType === 'last_week') {
    const range = resolvePeriodRange();
    emit('period-filter', {
      date_from: range.from,
      date_to: range.to,
    });
  }
});

const initialRange = resolvePeriodRange();
emit('period-filter', {
  date_from: initialRange.from,
  date_to: initialRange.to,
});

watch(
  [periodMonth, periodMonthFrom, periodMonthTo, periodDate, periodDateFrom, periodDateTo],
  () => {
    if (!isPeriodReady()) return;
    const range = resolvePeriodRange();
    emit('period-filter', {
      date_from: range.from,
      date_to: range.to,
    });
  }
);
</script>