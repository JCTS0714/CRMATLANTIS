import { computed, nextTick, onBeforeUnmount, ref, unref, watch } from 'vue';

const STORAGE_PREFIX = 'crm:table-columns';

const safeParse = (value) => {
  try {
    return JSON.parse(value);
  } catch {
    return null;
  }
};

export const useColumnVisibility = ({ tableId, columns }) => {
  const visibleKeys = ref([]);
  const initialized = ref(false);
  const tableRef = ref(null);
  const observer = ref(null);

  const normalizedColumns = computed(() => {
    return (unref(columns) || []).filter((column) => column?.key && column?.label);
  });

  const allKeys = computed(() => normalizedColumns.value.map((column) => column.key));
  const storageKey = computed(() => `${STORAGE_PREFIX}:${tableId || 'default'}`);

  const readStoredKeys = () => {
    if (typeof window === 'undefined') return null;
    const raw = window.localStorage.getItem(storageKey.value);
    const parsed = safeParse(raw);
    return Array.isArray(parsed) ? parsed : null;
  };

  const persistKeys = (keys) => {
    if (typeof window === 'undefined') return;
    window.localStorage.setItem(storageKey.value, JSON.stringify(keys));
  };

  watch(allKeys, (keys) => {
    if (keys.length === 0) {
      visibleKeys.value = [];
      return;
    }

    if (!initialized.value) {
      const stored = readStoredKeys();
      const validStored = (stored || []).filter((key) => keys.includes(key));
      visibleKeys.value = validStored.length > 0 ? validStored : [...keys];
      initialized.value = true;
      return;
    }

    const currentValid = visibleKeys.value.filter((key) => keys.includes(key));
    visibleKeys.value = currentValid.length > 0 ? currentValid : [...keys];
  }, { immediate: true });

  watch([visibleKeys, storageKey], ([keys]) => {
    if (!initialized.value) return;
    persistKeys(keys);
  }, { deep: true });

  const visibleColumns = computed(() => {
    const keySet = new Set(visibleKeys.value);
    return normalizedColumns.value.filter((column) => keySet.has(column.key));
  });

  const applyVisibilityToTable = () => {
    const tableEl = tableRef.value;
    if (!tableEl) return;

    const keySet = new Set(visibleKeys.value);
    const keys = allKeys.value;
    const visibleCount = Math.max(1, keys.filter((key) => keySet.has(key)).length);

    tableEl.style.width = '100%';
    tableEl.style.tableLayout = 'auto';

    const colElements = tableEl.querySelectorAll('colgroup col');
    keys.forEach((key, index) => {
      const col = colElements[index];
      if (!col) return;
      const isVisible = keySet.has(key);
      col.style.display = isVisible ? '' : 'none';
      col.style.width = '';
      col.style.minWidth = '';
      col.style.maxWidth = '';
    });

    const rows = tableEl.querySelectorAll('tr');
    rows.forEach((row) => {
      const cells = Array.from(row.children).filter((cell) => {
        const tag = cell?.tagName;
        return tag === 'TH' || tag === 'TD';
      });

      keys.forEach((key, index) => {
        const cell = cells[index];
        if (!cell) return;
        const isVisible = keySet.has(key);
        cell.style.display = isVisible ? '' : 'none';
        cell.style.width = '';
        cell.style.minWidth = '';
        cell.style.maxWidth = '';
        cell.style.overflow = '';
      });

      if (cells.length === 1) {
        const onlyCell = cells[0];
        const hasColspan = onlyCell.hasAttribute('colspan') || Number(onlyCell.colSpan) > 1;
        if (hasColspan) {
          onlyCell.colSpan = visibleCount;
        }
      }
    });
  };

  watch([visibleKeys, allKeys, tableRef], async () => {
    await nextTick();
    applyVisibilityToTable();
  }, { deep: true, flush: 'post' });

  const stopObserver = () => {
    if (!observer.value) return;
    observer.value.disconnect();
    observer.value = null;
  };

  watch(tableRef, async (tableEl) => {
    stopObserver();
    if (!tableEl || typeof MutationObserver === 'undefined') return;

    observer.value = new MutationObserver(() => {
      applyVisibilityToTable();
    });

    observer.value.observe(tableEl, {
      childList: true,
      subtree: true,
    });

    await nextTick();
    applyVisibilityToTable();
  }, { flush: 'post' });

  onBeforeUnmount(() => {
    stopObserver();
  });

  const isColumnVisible = (key) => visibleKeys.value.includes(key);

  const toggleColumn = (key) => {
    if (!allKeys.value.includes(key)) return;

    if (visibleKeys.value.includes(key)) {
      if (visibleKeys.value.length === 1) return;
      visibleKeys.value = visibleKeys.value.filter((item) => item !== key);
      return;
    }

    visibleKeys.value = [...visibleKeys.value, key];
  };

  const resetColumns = () => {
    visibleKeys.value = [...allKeys.value];
  };

  return {
    tableRef,
    visibleKeys,
    visibleColumns,
    isColumnVisible,
    toggleColumn,
    resetColumns,
    applyVisibilityToTable,
  };
};
