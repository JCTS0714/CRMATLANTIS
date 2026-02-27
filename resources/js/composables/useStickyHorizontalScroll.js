import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

export function useStickyHorizontalScroll({ tableRef }) {
  const tableScrollRef = ref(null);
  const stickyScrollRef = ref(null);
  const stickyScrollWidth = ref(0);
  const showStickyXScroll = ref(false);

  let syncingScroll = false;
  let tableResizeObserver = null;

  const syncStickyFromTable = () => {
    const tableEl = tableScrollRef.value;
    const stickyEl = stickyScrollRef.value;
    if (!tableEl || !stickyEl || syncingScroll) return;

    syncingScroll = true;
    stickyEl.scrollLeft = tableEl.scrollLeft;
    syncingScroll = false;
  };

  const syncTableFromSticky = () => {
    const tableEl = tableScrollRef.value;
    const stickyEl = stickyScrollRef.value;
    if (!tableEl || !stickyEl || syncingScroll) return;

    syncingScroll = true;
    tableEl.scrollLeft = stickyEl.scrollLeft;
    syncingScroll = false;
  };

  const refreshStickyScroll = () => {
    const tableEl = tableScrollRef.value;
    if (!tableEl) return;

    const hasHorizontalOverflow = tableEl.scrollWidth > tableEl.clientWidth + 1;
    const rect = tableEl.getBoundingClientRect();
    const nativeScrollbarVisible = rect.bottom <= window.innerHeight && rect.bottom >= 0;

    stickyScrollWidth.value = tableEl.scrollWidth;
    showStickyXScroll.value = hasHorizontalOverflow && !nativeScrollbarVisible;

    if (hasHorizontalOverflow && stickyScrollRef.value) {
      stickyScrollRef.value.scrollLeft = tableEl.scrollLeft;
    }
  };

  const setupStickyScroll = () => {
    const tableEl = tableScrollRef.value;
    const stickyEl = stickyScrollRef.value;
    if (!tableEl || !stickyEl) return;

    tableEl.addEventListener('scroll', syncStickyFromTable, { passive: true });
    stickyEl.addEventListener('scroll', syncTableFromSticky, { passive: true });

    if (typeof ResizeObserver !== 'undefined') {
      tableResizeObserver = new ResizeObserver(() => {
        refreshStickyScroll();
      });

      tableResizeObserver.observe(tableEl);
      if (tableRef?.value) {
        tableResizeObserver.observe(tableRef.value);
      }
    }

    window.addEventListener('resize', refreshStickyScroll);
    window.addEventListener('scroll', refreshStickyScroll, { passive: true });

    refreshStickyScroll();
  };

  const teardownStickyScroll = () => {
    const tableEl = tableScrollRef.value;
    const stickyEl = stickyScrollRef.value;

    if (tableEl) {
      tableEl.removeEventListener('scroll', syncStickyFromTable);
    }

    if (stickyEl) {
      stickyEl.removeEventListener('scroll', syncTableFromSticky);
    }

    if (tableResizeObserver) {
      tableResizeObserver.disconnect();
      tableResizeObserver = null;
    }

    window.removeEventListener('resize', refreshStickyScroll);
    window.removeEventListener('scroll', refreshStickyScroll);
  };

  onMounted(async () => {
    await nextTick();
    setupStickyScroll();
  });

  onBeforeUnmount(() => {
    teardownStickyScroll();
  });

  watch(tableRef, async () => {
    await nextTick();
    refreshStickyScroll();
  });

  return {
    tableScrollRef,
    stickyScrollRef,
    stickyScrollWidth,
    showStickyXScroll,
    refreshStickyScroll,
  };
}
