import { computed, ref, watch } from 'vue';
import axios from 'axios';

export function useLeadsBoardData({
  periodType,
  periodMonth,
  periodMonthFrom,
  periodMonthTo,
  periodDate,
  periodDateFrom,
  periodDateTo,
  clearPeriodInputs,
  resolvePeriodRange,
  isPeriodReady,
  toastError,
}) {
  const loading = ref(true);
  const stages = ref([]);
  const limit = ref(20);
  const suppressLimitReload = ref(false);

  const gridColsClass = computed(() => {
    const n = stages.value.length || 1;
    if (n <= 1) return 'grid-cols-1';
    if (n === 2) return 'grid-cols-1 md:grid-cols-2';
    if (n === 3) return 'grid-cols-1 md:grid-cols-3';
    return 'grid-cols-1 md:grid-cols-2 xl:grid-cols-4';
  });

  const load = async ({ showLoading = true } = {}) => {
    if (showLoading) loading.value = true;
    try {
      const range = resolvePeriodRange();
      const params = {
        limit: limit.value || undefined,
        date_from: range.from,
        date_to: range.to,
      };

      const response = await axios.get('/leads/board-data', { params });
      stages.value = response?.data?.data?.stages ?? [];
    } catch (e) {
      stages.value = [];
      const msg = e?.response?.data?.message ?? 'No se pudo cargar el pipeline.';
      toastError(msg);
      // eslint-disable-next-line no-console
      console.error('Leads board load error', e);
    } finally {
      if (showLoading) loading.value = false;
    }
  };

  watch(limit, async (newValue, oldValue) => {
    if (suppressLimitReload.value) return;
    if (newValue === oldValue) return;
    await load({ showLoading: false });
  });

  watch(periodType, async (newType, oldType) => {
    if (newType !== oldType) {
      clearPeriodInputs();
    }

    if (newType === 'all' || newType === 'last_week') {
      await load({ showLoading: false });
    }
  });

  watch(
    [periodMonth, periodMonthFrom, periodMonthTo, periodDate, periodDateFrom, periodDateTo],
    async () => {
      if (!isPeriodReady()) return;
      await load({ showLoading: false });
    }
  );

  return {
    loading,
    stages,
    limit,
    gridColsClass,
    load,
  };
}