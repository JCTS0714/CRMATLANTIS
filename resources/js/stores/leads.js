import { defineStore } from 'pinia';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';

export const useLeadsStore = defineStore('leads', () => {
  // State
  const leads = ref([]);
  const stages = ref([]);
  const loading = ref(false);
  const error = ref(null);
  
  const filters = reactive({
    q: '',
    stage_id: null,
    created_by: null,
    date_from: null,
    date_to: null
  });

  const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0,
    from: null,
    to: null
  });

  // Getters
  const totalCount = computed(() => pagination.total || 0);
  const hasLeads = computed(() => leads.value.length > 0);
  const isEmpty = computed(() => !loading.value && leads.value.length === 0);
  
  const stageById = computed(() => {
    const map = new Map();
    stages.value.forEach(stage => {
      map.set(stage.id, stage);
    });
    return map;
  });

  const stageNameById = computed(() => {
    const map = new Map();
    stages.value.forEach(stage => {
      map.set(stage.id, stage.name);
    });
    return map;
  });

  const leadsByStage = computed(() => {
    const grouped = {};
    stages.value.forEach(stage => {
      grouped[stage.id] = [];
    });
    
    leads.value.forEach(lead => {
      if (grouped[lead.stage_id]) {
        grouped[lead.stage_id].push(lead);
      }
    });
    
    return grouped;
  });

  // Actions
  const fetchLeads = async (params = {}) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.get('/leads/data', { 
        params: { ...filters, ...params } 
      });
      
      const data = response.data?.data || response.data;
      leads.value = data?.items || [];
      
      if (data?.pagination) {
        Object.assign(pagination, data.pagination);
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar leads';
      console.error('Leads fetch error:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchStages = async () => {
    try {
      const response = await axios.get('/leads/stages');
      stages.value = response.data?.data || [];
    } catch (err) {
      console.error('Stages fetch error:', err);
    }
  };

  const createLead = async (leadData) => {
    try {
      const response = await axios.post('/leads', leadData);
      const newLead = response.data.data;
      leads.value.unshift(newLead);
      return newLead;
    } catch (err) {
      throw err;
    }
  };

  const updateLead = async (id, updates) => {
    try {
      const response = await axios.patch(`/leads/${id}`, updates);
      const updatedLead = response.data.data;
      
      const index = leads.value.findIndex(lead => lead.id === id);
      if (index > -1) {
        leads.value[index] = { ...leads.value[index], ...updatedLead };
      }
      
      return updatedLead;
    } catch (err) {
      throw err;
    }
  };

  const updateLeadStage = async (id, stageId) => {
    return await updateLead(id, { stage_id: stageId });
  };

  const archiveLead = async (id) => {
    try {
      await axios.patch(`/leads/${id}/archive`);
      
      const index = leads.value.findIndex(lead => lead.id === id);
      if (index > -1) {
        leads.value[index].archived_at = new Date().toISOString();
      }
    } catch (err) {
      throw err;
    }
  };

  const deleteLead = async (id) => {
    try {
      await axios.delete(`/leads/${id}`);
      
      const index = leads.value.findIndex(lead => lead.id === id);
      if (index > -1) {
        leads.value.splice(index, 1);
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
      stage_id: null,
      created_by: null,
      date_from: null,
      date_to: null
    });
  };

  const findLead = (id) => {
    return leads.value.find(lead => lead.id === id);
  };

  const refresh = async () => {
    await fetchLeads();
  };

  return {
    // State
    leads,
    stages,
    loading,
    error,
    filters,
    pagination,
    
    // Getters
    totalCount,
    hasLeads,
    isEmpty,
    stageById,
    stageNameById,
    leadsByStage,
    
    // Actions
    fetchLeads,
    fetchStages,
    createLead,
    updateLead,
    updateLeadStage,
    archiveLead,
    deleteLead,
    setFilters,
    clearFilters,
    findLead,
    refresh
  };
});