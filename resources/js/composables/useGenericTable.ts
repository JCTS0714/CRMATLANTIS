import { ref, reactive, computed, type Ref, type ComputedRef } from 'vue';
import axios, { AxiosError } from 'axios';
import type { PaginationData, TableFilters, ApiResponse } from '@/types';

export interface GenericTableConfig {
  endpoint: string;
  defaultFilters?: Record<string, any>;
  defaultPerPage?: number;
  actions?: Record<string, Function>;
}

export interface TableState {
  searchQuery: string;
  sortBy: string | null;
  sortDirection: 'asc' | 'desc';
  processing: Set<string | number>;
}

export interface UseGenericTableReturn<T = any> {
  // Data
  data: Ref<T[]>;
  loading: Ref<boolean>;
  error: Ref<string | null>;
  
  // Pagination
  pagination: Ref<PaginationData<T>>;
  paginationInfo: ComputedRef<string>;
  
  // Filters and state
  filters: Ref<TableFilters>;
  tableState: Ref<TableState>;
  searchQuery: Ref<string>;
  perPage: Ref<number>;
  
  // Methods
  fetchData: (params?: Record<string, any>) => Promise<void>;
  refetchData: () => Promise<void>;
  changePage: (page: number) => Promise<void>;
  changePerPage: (newPerPage: number) => Promise<void>;
  handleSearch: (query: string) => Promise<void>;
  handleSort: (field: string) => Promise<void>;
  performAction: (action: string, id: string | number, data?: any) => Promise<any>;
  isProcessing: (id: string | number) => boolean;
  refresh: () => Promise<void>;
}

export function useGenericTable<T = any>(config: GenericTableConfig): UseGenericTableReturn<T> {
  // Reactive data
  const data = ref<T[]>([]) as Ref<T[]>;
  const loading = ref<boolean>(false);
  const error = ref<string | null>(null);
  
  const pagination = ref<PaginationData<T>>({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: config.defaultPerPage || 25,
    total: 0,
    from: null,
    to: null,
    next_page_url: null,
    prev_page_url: null
  });

  const filters = ref<TableFilters>({
    search: '',
    per_page: config.defaultPerPage || 25,
    page: 1,
    sort_by: null as string | null,
    sort_direction: 'asc',
    ...config.defaultFilters
  });

  const tableState = ref<TableState>({
    searchQuery: '',
    sortBy: null,
    sortDirection: 'asc',
    processing: new Set()
  });

  // Computed properties
  const searchQuery = computed({
    get: () => tableState.value.searchQuery,
    set: (value: string) => {
      tableState.value.searchQuery = value;
      filters.value.search = value;
    }
  });

  const perPage = computed({
    get: () => filters.value.per_page || 25,
    set: (value: number) => {
      filters.value.per_page = value;
    }
  });

  const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    if (!from || !to || !total) return 'No hay datos';
    return `Mostrando ${from} a ${to} de ${total} resultados`;
  });

  // Methods
  const fetchData = async (additionalParams: Record<string, any> = {}): Promise<void> => {
    loading.value = true;
    error.value = null;

    try {
      const params = { ...filters.value, ...additionalParams };
      
      // Clean up empty params
      Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === null || params[key] === undefined) {
          delete params[key];
        }
      });

      const response = await axios.get<PaginationData<T>>(config.endpoint, { params });
      
      data.value = response.data.data;
      pagination.value = response.data;
      
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al cargar los datos';
      console.error('Error fetching data:', err);
    } finally {
      loading.value = false;
    }
  };

  const refetchData = async (): Promise<void> => {
    await fetchData();
  };

  const changePage = async (page: number): Promise<void> => {
    filters.value.page = page;
    await fetchData();
  };

  const changePerPage = async (newPerPage: number): Promise<void> => {
    filters.value.per_page = newPerPage;
    filters.value.page = 1; // Reset to first page
    await fetchData();
  };

  const handleSearch = async (query: string): Promise<void> => {
    searchQuery.value = query;
    filters.value.page = 1; // Reset to first page on search
    await fetchData();
  };

  const handleSort = async (field: string): Promise<void> => {
    if (tableState.value.sortBy === field) {
      tableState.value.sortDirection = tableState.value.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      tableState.value.sortBy = field;
      tableState.value.sortDirection = 'asc';
    }

    filters.value.sort_by = field;
    filters.value.sort_direction = tableState.value.sortDirection;
    
    await fetchData();
  };

  const performAction = async (
    action: string, 
    id: string | number, 
    data?: any
  ): Promise<any> => {
    if (!config.actions || !config.actions[action]) {
      throw new Error(`Action '${action}' is not defined`);
    }

    // Mark as processing
    tableState.value.processing.add(id);

    try {
      const result = await config.actions[action](id, data);
      return result;
    } catch (err: any) {
      throw err;
    } finally {
      // Unmark as processing
      tableState.value.processing.delete(id);
    }
  };

  const isProcessing = (id: string | number): boolean => {
    return tableState.value.processing.has(id);
  };

  const refresh = async (): Promise<void> => {
    await fetchData();
  };

  return {
    // Data
    data,
    loading,
    error,
    
    // Pagination
    pagination,
    paginationInfo,
    
    // Filters and state
    filters,
    tableState,
    searchQuery,
    perPage,
    
    // Methods
    fetchData,
    refetchData,
    changePage,
    changePerPage,
    handleSearch,
    handleSort,
    performAction,
    isProcessing,
    refresh
  };
}