import { onBeforeUnmount, ref } from 'vue';
import axios from 'axios';

export function useResponsibleLookup() {
  const responsibleQuery = ref('');
  const responsibleLoading = ref(false);
  const responsibleDropdownOpen = ref(false);
  const responsibleOptions = ref([]);

  let responsibleSearchTimer = null;

  const loadResponsibleOptions = async (query = '') => {
    responsibleLoading.value = true;
    try {
      const { data } = await axios.get('/scrum/tareas/responsables', {
        params: {
          search: query,
          per_page: 100,
        },
      });

      const rows = Array.isArray(data?.data) ? data.data : [];
      responsibleOptions.value = rows
        .filter((user) => user?.id)
        .map((user) => ({
          id: user.id,
          name: user.name,
          email: user.email,
        }));
    } catch {
      responsibleOptions.value = [];
    } finally {
      responsibleLoading.value = false;
    }
  };

  const clearSearchTimer = () => {
    if (responsibleSearchTimer) {
      clearTimeout(responsibleSearchTimer);
      responsibleSearchTimer = null;
    }
  };

  const scheduleResponsibleSearch = (query = '') => {
    clearSearchTimer();
    responsibleSearchTimer = setTimeout(() => {
      loadResponsibleOptions(query.trim());
    }, 250);
  };

  const openResponsibleDropdown = () => {
    responsibleDropdownOpen.value = true;
  };

  const closeResponsibleDropdown = () => {
    setTimeout(() => {
      responsibleDropdownOpen.value = false;
    }, 120);
  };

  const selectResponsible = (user) => {
    responsibleQuery.value = user.name;
    responsibleDropdownOpen.value = false;
    return user;
  };

  onBeforeUnmount(() => {
    clearSearchTimer();
  });

  return {
    responsibleQuery,
    responsibleLoading,
    responsibleDropdownOpen,
    responsibleOptions,
    loadResponsibleOptions,
    scheduleResponsibleSearch,
    openResponsibleDropdown,
    closeResponsibleDropdown,
    selectResponsible,
    clearSearchTimer,
  };
}
