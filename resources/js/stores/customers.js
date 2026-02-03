import { defineStore } from 'pinia';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';

export const useCustomersStore = defineStore('customers', () => {
  // State
  const customers = ref([]);
  const loading = ref(false);
  const error = ref(null);
  
  const filters = reactive({
    q: '',
    status: null,
    date_from: null,
    date_to: null
  });

  const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0
  });

  // Getters
  const totalCount = computed(() => pagination.total || 0);
  const hasCustomers = computed(() => customers.value.length > 0);
  const isEmpty = computed(() => !loading.value && customers.value.length === 0);

  // Actions
  const fetchCustomers = async (params = {}) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.get('/customers/data', { 
        params: { ...filters, ...params } 
      });
      
      const data = response.data?.data || response.data;
      customers.value = data?.items || [];
      
      if (data?.pagination) {
        Object.assign(pagination, data.pagination);
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar clientes';
      console.error('Customers fetch error:', err);
    } finally {
      loading.value = false;
    }
  };

  const createCustomer = async (customerData) => {
    try {
      const response = await axios.post('/customers', customerData);
      const newCustomer = response.data.data;
      customers.value.unshift(newCustomer);
      return newCustomer;
    } catch (err) {
      throw err;
    }
  };

  const updateCustomer = async (id, updates) => {
    try {
      const response = await axios.patch(`/customers/${id}`, updates);
      const updatedCustomer = response.data.data;
      
      const index = customers.value.findIndex(customer => customer.id === id);
      if (index > -1) {
        customers.value[index] = { ...customers.value[index], ...updatedCustomer };
      }
      
      return updatedCustomer;
    } catch (err) {
      throw err;
    }
  };

  const deleteCustomer = async (id) => {
    try {
      await axios.delete(`/customers/${id}`);
      
      const index = customers.value.findIndex(customer => customer.id === id);
      if (index > -1) {
        customers.value.splice(index, 1);
      }
    } catch (err) {
      throw err;
    }
  };

  const setFilters = (newFilters) => {
    Object.assign(filters, newFilters);
  };

  const clearFilters = () => {
    Object.assign(filters, {
      q: '',
      status: null,
      date_from: null,
      date_to: null
    });
  };

  const findCustomer = (id) => {
    return customers.value.find(customer => customer.id === id);
  };

  const refresh = async () => {
    await fetchCustomers();
  };

  return {
    // State
    customers,
    loading,
    error,
    filters,
    pagination,
    
    // Getters
    totalCount,
    hasCustomers,
    isEmpty,
    
    // Actions
    fetchCustomers,
    createCustomer,
    updateCustomer,
    deleteCustomer,
    setFilters,
    clearFilters,
    findCustomer,
    refresh
  };
});