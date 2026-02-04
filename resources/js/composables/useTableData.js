import { ref, reactive, computed, watch } from 'vue';
import axios from 'axios';

/**
 * Composable for managing table data with pagination, filtering and search
 * @param {Object} config - Configuration object
 * @param {string} config.endpoint - API endpoint for fetching data
 * @param {number} config.defaultPerPage - Default items per page (default: 25)
 * @param {Object} config.defaultFilters - Default filter values
 * @param {number} config.searchDebounce - Debounce time for search (default: 300ms)
 */
export function useTableData(config = {}) {
  const {
    endpoint,
    defaultPerPage = 25,
    defaultFilters = {},
    searchDebounce = 300
  } = config;

  // Reactive state
  const loading = ref(false);
  const error = ref(null);
  const items = ref([]);
  const stagesData = ref([]);
  
  const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: defaultPerPage,
    total: 0,
    from: null,
    to: null
  });

  const filters = reactive({
    q: '',
    page: 1,
    per_page: defaultPerPage,
    ...defaultFilters
  });

  // Computed values
  const hasData = computed(() => items.value.length > 0);
  const isEmpty = computed(() => !loading.value && items.value.length === 0);
  const totalCount = computed(() => pagination.total || 0);
  
  const paginationInfo = computed(() => {
    if (!pagination.total) return '';
    const start = pagination.from || 0;
    const end = pagination.to || 0;
    const total = pagination.total || 0;
    return `${start}-${end} de ${total}`;
  });

  // Methods
  const fetchData = async (params = {}) => {
    if (!endpoint) {
      console.error('useTableData: endpoint is required');
      return;
    }

    loading.value = true;
    error.value = null;

    try {
      const requestParams = { ...filters, ...params };
      const response = await axios.get(endpoint, { params: requestParams });
      
      const data = response.data?.data || response.data;
      // Support both 'items' (generic) and 'leads' (LeadDataController response)
      items.value = data?.items || data?.leads || [];
      
      // Also extract stages if available
      if (data?.stages) {
        // Emit stages for consumers that need them
        stagesData.value = data.stages;
      }
      
      if (data?.pagination) {
        Object.assign(pagination, data.pagination);
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar los datos';
      console.error('useTableData fetch error:', err);
    } finally {
      loading.value = false;
    }
  };

  const applyFilters = async (newFilters = {}) => {
    Object.assign(filters, newFilters);
    await fetchData();
  };

  const changePage = async (page) => {
    await applyFilters({ page });
  };

  const changePerPage = async (perPage) => {
    await applyFilters({ per_page: perPage, page: 1 });
  };

  const search = async (query) => {
    await applyFilters({ q: query, page: 1 });
  };

  const refresh = async () => {
    await fetchData();
  };

  const reset = async () => {
    Object.assign(filters, {
      q: '',
      page: 1,
      per_page: defaultPerPage,
      ...defaultFilters
    });
    await fetchData();
  };

  // Search debouncing
  let searchTimeout;
  const debouncedSearch = (query) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      search(query);
    }, searchDebounce);
  };

  return {
    // State
    loading,
    error,
    items,
    stagesData,
    pagination,
    filters,
    
    // Computed
    hasData,
    isEmpty,
    totalCount,
    paginationInfo,
    
    // Methods
    fetchData,
    applyFilters,
    changePage,
    changePerPage,
    search,
    debouncedSearch,
    refresh,
    reset
  };
}