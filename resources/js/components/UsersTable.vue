<template>
  <GenericTable
    table-id="users-table"
    title="Usuarios"
    :columns="columns"
    :items="items"
    :loading="loading"
    :error="error"
    :pagination="pagination"
    :pagination-info="paginationInfo"
    :search-query="tableState.searchQuery"
    search-placeholder="Buscar por nombre o email"
    :per-page="filters.per_page"
    :per-page-options="[10, 25, 50, 100]"
    sortable
    @search="handleSearch"
    @sort="handleSort"
    @page-change="changePage"
    @per-page-change="changePerPage"
  >
    <!-- Header Actions -->
    <template #headerActions>
      <BaseButton 
        variant="primary"
        @click="showCreateModal = true"
      >
        Crear Usuario
      </BaseButton>
    </template>

    <!-- Row Template -->
    <template #row="{ item }">
      <tr class="border-b border-gray-100 bg-white hover:bg-blue-50/40 transition-colors dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60">
        <td class="px-4 py-3 font-medium text-gray-900 dark:text-slate-100">{{ item.id }}</td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center dark:bg-blue-900">
              <span class="text-sm font-medium text-blue-600 dark:text-blue-300">
                {{ item.name?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <div class="font-medium text-gray-900 dark:text-slate-100">{{ item.name }}</div>
              <div class="text-sm text-gray-500 dark:text-slate-400">{{ item.email }}</div>
            </div>
          </div>
        </td>
        <td class="px-4 py-3">
          <div class="flex flex-wrap gap-1">
            <BaseBadge 
              v-for="role in item.roles" 
              :key="role.id"
              variant="primary"
              size="xs"
              rounded
            >
              {{ role.name }}
            </BaseBadge>
          </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-500 dark:text-slate-400">
          {{ formatDateTime(item.created_at) }}
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-2">
            <BaseButton 
              variant="ghost" 
              size="sm"
              :loading="isProcessing(item.id)"
              @click="editUser(item)"
            >
              Editar
            </BaseButton>
            <BaseButton 
              variant="ghost" 
              size="sm"
              :loading="isProcessing(item.id)"
              @click="confirmDeleteUser(item)"
            >
              Eliminar
            </BaseButton>
          </div>
        </td>
      </tr>
    </template>
  </GenericTable>

  <!-- Create/Edit User Modal -->
  <BaseModal 
    ref="userModal"
    :title="editingUser ? 'Editar Usuario' : 'Crear Usuario'"
    max-width="lg"
  >
    <form @submit.prevent="saveUser">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">
            Nombre
          </label>
          <input
            v-model="userForm.name"
            type="text"
            required
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">
            Email
          </label>
          <input
            v-model="userForm.email"
            type="email"
            required
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          />
        </div>

        <div v-if="!editingUser">
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">
            Contraseña
          </label>
          <input
            v-model="userForm.password"
            type="password"
            required
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          />
        </div>
      </div>
    </form>

    <template #footer>
      <div class="flex justify-end gap-3">
        <BaseButton variant="secondary" @click="closeUserModal">
          Cancelar
        </BaseButton>
        <BaseButton 
          variant="primary" 
          :loading="saving"
          @click="saveUser"
        >
          {{ editingUser ? 'Actualizar' : 'Crear' }}
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Components
import GenericTable from './base/GenericTable.vue';
import BaseButton from './base/BaseButton.vue';
import BaseModal from './base/BaseModal.vue';
import BaseBadge from './base/BaseBadge.vue';

// Composables
import { useGenericTable } from '../composables/useGenericTable.js';
import { useTableActions } from '../composables/useTableActions.js';

// Table configuration
const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Usuario', sortable: true },
  { key: 'roles', label: 'Roles' },
  { key: 'created_at', label: 'Creado', sortable: true },
  { key: 'actions', label: 'Acciones' }
];

// Use generic table
const {
  loading,
  error,
  items,
  pagination,
  filters,
  tableState,
  paginationInfo,
  fetchData,
  changePage,
  changePerPage,
  handleSearch,
  handleSort,
  performAction,
  isProcessing,
  refresh
} = useGenericTable({
  endpoint: '/users/data',
  defaultFilters: {},
  actions: {},
  defaultPerPage: 25
});

// Use table actions
const { performAction: performTableAction, performBulkAction } = useTableActions('/users');

// Component state
const editingUser = ref(null);
const saving = ref(false);
const showCreateModal = ref(false);
const userModal = ref(null);

const userForm = ref({
  name: '',
  email: '',
  password: ''
});

// Methods
const formatDateTime = (datetime) => {
  if (!datetime) return '';
  return new Date(datetime).toLocaleString('es-ES');
};

const editUser = (user) => {
  editingUser.value = user;
  userForm.value = {
    name: user.name,
    email: user.email,
    password: ''
  };
  userModal.value?.open();
};

const confirmDeleteUser = async (user) => {
  const success = await performTableAction('delete', user.id, null, user.name);
  if (success) {
    refresh();
  }
};
const saveUser = async () => {
  saving.value = true;

  try {
    if (editingUser.value) {
      await performTableAction('update', editingUser.value.id, userForm.value);
    } else {
      await performTableAction('create', null, userForm.value);
    }

    closeUserModal();
    refresh();
  } catch (err) {
    console.error('Error saving user:', err);
  } finally {
    saving.value = false;
  }
};

const closeUserModal = () => {
  editingUser.value = null;
  showCreateModal.value = false;
  userForm.value = {
    name: '',
    email: '',
    password: ''
  };
  userModal.value?.close();
};

// Watch for create modal
watch(() => showCreateModal.value, (newValue) => {
  if (newValue) {
    userModal.value?.open();
  }
});

// Initialize
onMounted(async () => {
  await fetchData();
});
</script>