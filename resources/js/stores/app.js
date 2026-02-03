import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAppStore = defineStore('app', () => {
  // State
  const user = ref(window.__AUTH_USER__ || null);
  const sidebarCollapsed = ref(false);
  const theme = ref('light');
  const loading = ref(false);
  const notifications = ref([]);

  // Getters
  const isAuthenticated = computed(() => !!user.value);
  const userName = computed(() => user.value?.name || '');
  const userEmail = computed(() => user.value?.email || '');
  const userPermissions = computed(() => user.value?.permissions || []);
  
  const hasPermission = computed(() => (permission) => {
    return userPermissions.value.includes(permission);
  });

  const isDarkTheme = computed(() => theme.value === 'dark');
  
  // Actions
  const setUser = (userData) => {
    user.value = userData;
  };

  const clearUser = () => {
    user.value = null;
  };

  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  };

  const setSidebarCollapsed = (collapsed) => {
    sidebarCollapsed.value = collapsed;
  };

  const setTheme = (newTheme) => {
    theme.value = newTheme;
    // Apply theme to document
    if (newTheme === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
    
    // Save to localStorage
    try {
      localStorage.setItem('crmatlantis-theme', newTheme);
    } catch (error) {
      console.warn('Could not save theme to localStorage:', error);
    }
  };

  const toggleTheme = () => {
    setTheme(isDarkTheme.value ? 'light' : 'dark');
  };

  const setLoading = (isLoading) => {
    loading.value = isLoading;
  };

  const addNotification = (notification) => {
    const id = Date.now();
    notifications.value.push({
      id,
      type: 'info',
      title: '',
      message: '',
      duration: 5000,
      ...notification
    });

    // Auto remove after duration
    if (notification.duration && notification.duration > 0) {
      setTimeout(() => {
        removeNotification(id);
      }, notification.duration);
    }

    return id;
  };

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  };

  const clearNotifications = () => {
    notifications.value = [];
  };

  // Initialize theme from localStorage or system preference
  const initializeTheme = () => {
    try {
      const savedTheme = localStorage.getItem('crmatlantis-theme');
      if (savedTheme) {
        setTheme(savedTheme);
      } else {
        // Use system preference
        const systemTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches 
          ? 'dark' 
          : 'light';
        setTheme(systemTheme);
      }
    } catch (error) {
      console.warn('Could not initialize theme:', error);
      setTheme('light');
    }
  };

  return {
    // State
    user,
    sidebarCollapsed,
    theme,
    loading,
    notifications,
    
    // Getters
    isAuthenticated,
    userName,
    userEmail,
    userPermissions,
    hasPermission,
    isDarkTheme,
    
    // Actions
    setUser,
    clearUser,
    toggleSidebar,
    setSidebarCollapsed,
    setTheme,
    toggleTheme,
    setLoading,
    addNotification,
    removeNotification,
    clearNotifications,
    initializeTheme
  };
});