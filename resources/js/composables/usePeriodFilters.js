import { reactive, ref } from 'vue';

export function usePeriodFilters() {
  const showAdvancedFilters = ref(false);
  const periodFilters = reactive({
    mode: 'all',
    month: '',
    fromMonth: '',
    toMonth: '',
    date: '',
    fromDate: '',
    toDate: '',
  });

  const formatDateOnly = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

  const monthBounds = (monthValue) => {
    if (!monthValue || !/^\d{4}-\d{2}$/.test(monthValue)) {
      return { from: null, to: null };
    }

    const [yearRaw, monthRaw] = monthValue.split('-');
    const year = Number(yearRaw);
    const month = Number(monthRaw);
    if (Number.isNaN(year) || Number.isNaN(month) || month < 1 || month > 12) {
      return { from: null, to: null };
    }

    const lastDay = new Date(year, month, 0).getDate();
    return {
      from: `${year}-${String(month).padStart(2, '0')}-01`,
      to: `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`,
    };
  };

  const normalizePeriodBounds = (from, to) => {
    if (!from || !to) {
      return { from, to };
    }
    return from > to ? { from: to, to: from } : { from, to };
  };

  const resolvePeriodRange = () => {
    if (periodFilters.mode === 'all') {
      return { from: null, to: null };
    }

    if (periodFilters.mode === 'last_week') {
      const now = new Date();
      const fromDate = new Date(now);
      fromDate.setDate(fromDate.getDate() - 6);
      return {
        from: formatDateOnly(fromDate),
        to: formatDateOnly(now),
      };
    }

    if (periodFilters.mode === 'month') {
      return monthBounds(periodFilters.month);
    }

    if (periodFilters.mode === 'between_months') {
      const start = monthBounds(periodFilters.fromMonth);
      const end = monthBounds(periodFilters.toMonth);
      return normalizePeriodBounds(start.from, end.to);
    }

    if (periodFilters.mode === 'date') {
      if (!periodFilters.date) {
        return { from: null, to: null };
      }
      return { from: periodFilters.date, to: periodFilters.date };
    }

    if (periodFilters.mode === 'between_dates') {
      return normalizePeriodBounds(periodFilters.fromDate || null, periodFilters.toDate || null);
    }

    return { from: null, to: null };
  };

  const resetPeriodFilters = () => {
    periodFilters.mode = 'all';
    periodFilters.month = '';
    periodFilters.fromMonth = '';
    periodFilters.toMonth = '';
    periodFilters.date = '';
    periodFilters.fromDate = '';
    periodFilters.toDate = '';
  };

  return {
    showAdvancedFilters,
    periodFilters,
    resolvePeriodRange,
    resetPeriodFilters,
    formatDateOnly,
  };
}
