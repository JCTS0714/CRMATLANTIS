import { ref, reactive, computed, watch } from 'vue';
import axios from 'axios';

/**
 * Enhanced composable for generic table functionality
 * @param {Object} config - Configuration object
 * @param {string} config.endpoint - API endpoint
 * @param {Array} config.columns - Table column definitions
 * @param {Object} config.defaultFilters - Default filter values
 * @param {Object} config.actions - Available actions (edit, delete, etc.)
 * @param {number} config.defaultPerPage - Items per page
 * @param {number} config.searchDebounce - Search debounce time
 */
export function useGenericTable(config = {}) {
  const {
    endpoint,
    columns = [],
    defaultFilters = {},
    actions = {},
    defaultPerPage = 25,
    searchDebounce = 300,
    transformData = null, // Function to transform API response
    onError = null // Custom error handler
  } = config;

  // State
  const loading = ref(false);
  const error = ref(null);
  const items = ref([]);
  const selectedItems = ref([]);
  
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
    sort_by: null,
    sort_direction: 'asc',
    ...defaultFilters
  });

  const tableState = reactive({
    sortColumn: null,
    sortDirection: 'asc',
    searchQuery: '',
    processing: new Set(), // IDs being processed
  });

  // Computed
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

  const hasSelectedItems = computed(() => selectedItems.value.length > 0);
  const allItemsSelected = computed(() => 
    hasData.value && items.value.every(item => selectedItems.value.includes(item.id))
  );

  // Core Methods
  const fetchData = async (params = {}) => {
    if (!endpoint) {
      console.error('useGenericTable: endpoint is required');
      return;
    }

    loading.value = true;
    error.value = null;

    try {
      const requestParams = { ...filters, ...params };
      const response = await axios.get(endpoint, { params: requestParams });
      
      let data = response.data?.data || response.data;
      
      // Apply data transformation if provided
      if (transformData && typeof transformData === 'function') {
        data = transformData(data);
      }

      items.value = data?.items || data || [];
      
      if (data?.pagination) {
        Object.assign(pagination, data.pagination);
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Error al cargar los datos';
      error.value = errorMessage;
      
      if (onError && typeof onError === 'function') {
        onError(err, errorMessage);
      } else {
        console.error('Table fetch error:', err);
      }
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

  const handleSearch = async (query) => {
    tableState.searchQuery = query;
    await applyFilters({ q: query, page: 1 });
  };

  const handleSort = async (column) => {
    let direction = 'asc';
    
    if (tableState.sortColumn === column) {
      direction = tableState.sortDirection === 'asc' ? 'desc' : 'asc';
    }
    
    tableState.sortColumn = column;
    tableState.sortDirection = direction;
    
    await applyFilters({
      sort_by: column,
      sort_direction: direction,
      page: 1
    });
  };

  const refresh = async () => {
    await fetchData();
  };

  const reset = async () => {
    Object.assign(filters, {
      q: '',
      page: 1,
      per_page: defaultPerPage,
      sort_by: null,
      sort_direction: 'asc',
      ...defaultFilters
    });
    
    tableState.sortColumn = null;
    tableState.sortDirection = 'asc';
    tableState.searchQuery = '';
    
    await fetchData();
  };

  // Selection Methods
  const selectItem = (id) => {
    if (!selectedItems.value.includes(id)) {
      selectedItems.value.push(id);
    }
  };

  const unselectItem = (id) => {
    const index = selectedItems.value.indexOf(id);
    if (index > -1) {
      selectedItems.value.splice(index, 1);
    }
  };

  const toggleSelection = (id) => {
    if (selectedItems.value.includes(id)) {
      unselectItem(id);
    } else {
      selectItem(id);
    }
  };

  const selectAll = () => {
    selectedItems.value = items.value.map(item => item.id);
  };

  const unselectAll = () => {
    selectedItems.value = [];
  };

  const toggleSelectAll = () => {
    if (allItemsSelected.value) {
      unselectAll();
    } else {
      selectAll();
    }
  };

  // CRUD Operations
  const performAction = async (actionName, id, data = null) => {
    if (!actions[actionName]) {
      console.error(`Action '${actionName}' is not defined`);
      return false;
    }

    tableState.processing.add(id);

    try {
      const result = await actions[actionName](id, data);
      
      // Update local state based on action
      if (actionName === 'delete') {
        const index = items.value.findIndex(item => item.id === id);
        if (index > -1) {
          items.value.splice(index, 1);
        }
        unselectItem(id);
      } else if (result?.data) {
        // Update item in local state
        const index = items.value.findIndex(item => item.id === id);
        if (index > -1) {
          items.value[index] = { ...items.value[index], ...result.data };
        }
      }
      
      return result;
    } catch (err) {
      const errorMessage = err.response?.data?.message || `Error en acción ${actionName}`;
      error.value = errorMessage;
      throw err;
    } finally {
      tableState.processing.delete(id);
    }
  };

  const bulkAction = async (actionName, ids, data = null) => {
    const results = [];
    const errors = [];

    for (const id of ids) {
      try {
        const result = await performAction(actionName, id, data);
        results.push({ id, result });
      } catch (err) {
        errors.push({ id, error: err });
      }
    }

    if (errors.length > 0) {
      console.error('Bulk action errors:', errors);
    }

    return { results, errors };
  };

  const isProcessing = (id) => {
    return tableState.processing.has(id);
  };

  // Search debouncing
  let searchTimeout;
  const debouncedSearch = (query) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      handleSearch(query);
    }, searchDebounce);
  };

  // Watchers for reactive updates
  watch(() => tableState.searchQuery, (newValue, oldValue) => {
    if (newValue !== oldValue) {
      debouncedSearch(newValue);
    }
  });

  return {
    // State
    loading,
    error,
    items,
    pagination,
    filters,
    tableState,
    selectedItems,
    
    // Computed
    hasData,
    isEmpty,
    totalCount,
    paginationInfo,
    hasSelectedItems,
    allItemsSelected,
    
    // Core Methods
    fetchData,
    applyFilters,
    changePage,
    changePerPage,
    handleSearch,
    handleSort,
    refresh,
    reset,
    
    // Selection
    selectItem,
    unselectItem,
    toggleSelection,
    selectAll,
    unselectAll,
    toggleSelectAll,
    
    // Actions
    performAction,
    bulkAction,
    isProcessing,
    
    // Utility
    debouncedSearch
  };
}