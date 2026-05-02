import { ref } from 'vue';

export function useLeadsBoardPeriodFilters() {
  const periodType = ref('last_week');
  const periodMonth = ref('');
  const periodMonthFrom = ref('');
  const periodMonthTo = ref('');
  const periodDate = ref('');
  const periodDateFrom = ref('');
  const periodDateTo = ref('');

  const pad = (value) => String(value).padStart(2, '0');

  const toDateString = (date) => {
    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1);
    const day = pad(date.getDate());
    return `${year}-${month}-${day}`;
  };

  const clearPeriodInputs = () => {
    periodMonth.value = '';
    periodMonthFrom.value = '';
    periodMonthTo.value = '';
    periodDate.value = '';
    periodDateFrom.value = '';
    periodDateTo.value = '';
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

  return {
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
  };
}