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
    :subtitle="editingUser ? `Modificar datos de ${editingUser.name}` : 'Agregar nuevo usuario al sistema'"
    max-width="xl"
    close-on-backdrop
  >
    <div class="py-2">
      <form @submit.prevent="saveUser">
        <div class="grid grid-cols-1 gap-5">
          <!-- Información Personal -->
          <div class="space-y-5">
            <div class="border-b border-gray-200 dark:border-slate-700 pb-3">
              <h4 class="text-base font-semibold text-gray-900 dark:text-slate-100">Información Personal</h4>
              <p class="text-sm text-gray-600 dark:text-slate-400">Datos básicos del usuario</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                  Nombre completo *
                </label>
                <input
                  v-model="userForm.name"
                  type="text"
                  required
                  placeholder="Ej: Juan Carlos Pérez"
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-blue-500 dark:focus:ring-blue-500/20"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                  Correo electrónico *
                </label>
                <input
                  v-model="userForm.email"
                  type="email"
                  required
                  placeholder="usuario@empresa.com"
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-blue-500 dark:focus:ring-blue-500/20"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                  Rol *
                </label>
                <select
                  v-model="userForm.role"
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:focus:border-blue-500 dark:focus:ring-blue-500/20"
                >
                  <option
                    v-for="roleName in roleOptions"
                    :key="roleName"
                    :value="roleName"
                  >
                    {{ roleName }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Foto de perfil -->
            <div class="space-y-3">
              <div class="border-b border-gray-200 dark:border-slate-700 pb-3">
                <h4 class="text-base font-semibold text-gray-900 dark:text-slate-100">Foto de perfil</h4>
                <p class="text-sm text-gray-600 dark:text-slate-400">Sube una foto para el usuario (opcional)</p>
              </div>

              <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 dark:bg-slate-800 flex items-center justify-center">
                  <img v-if="photoPreview" :src="photoPreview" alt="Preview" class="w-full h-full object-cover" />
                  <div v-else class="text-sm text-gray-500 dark:text-slate-400">Sin foto</div>
                </div>

                <div class="flex-1">
                  <input
                    type="file"
                    accept="image/*"
                    @change="handlePhotoChange"
                    class="block w-full text-sm text-gray-500 file:bg-white file:border file:rounded file:px-3 file:py-1 file:mr-3 file:border-gray-300 file:text-sm file:cursor-pointer dark:file:bg-slate-800"
                  />
                  <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">Formatos: JPG, PNG. Tamaño recomendado: 300x300px</p>
                </div>
              </div>
            </div>

            <div v-if="!editingUser" class="space-y-3">
              <div class="border-b border-gray-200 dark:border-slate-700 pb-3">
                <h4 class="text-base font-semibold text-gray-900 dark:text-slate-100">Configuración de Acceso</h4>
                <p class="text-sm text-gray-600 dark:text-slate-400">Contraseña para el nuevo usuario</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                  Contraseña *
                </label>
                <input
                  v-model="userForm.password"
                  type="password"
                  required
                  placeholder="Mínimo 8 caracteres"
                  class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-blue-500 dark:focus:ring-blue-500/20"
                />
                <p class="mt-2 text-xs text-gray-500 dark:text-slate-400">
                  La contraseña debe tener al menos 8 caracteres
                </p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <template #footer>
      <div class="flex items-center justify-between pt-4">
        <div class="text-xs text-gray-500 dark:text-slate-400">
          * Campos obligatorios
        </div>
        <div class="flex items-center gap-3">
          <BaseButton
            v-if="!editingUser && isLocalAutofillEnabled"
            variant="secondary"
            size="sm"
            @click="fillUserCreateForTest"
          >
            Rellenar test
          </BaseButton>
          <BaseButton 
            variant="secondary" 
            size="sm"
            @click="closeUserModal"
          >
            Cancelar
          </BaseButton>
          <BaseButton 
            variant="primary" 
            size="sm"
            :loading="saving"
            :disabled="!userForm.name || !userForm.email || (!editingUser && !userForm.password)"
            @click="saveUser"
          >
            <div class="flex items-center justify-center">
              <svg v-if="!saving" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span>{{ editingUser ? 'Actualizar' : 'Crear' }}</span>
            </div>
          </BaseButton>
        </div>
      </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
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
const roleOptions = ref(['employee']);

const isLocalAutofillEnabled = (() => {
  if (typeof window === 'undefined') return false;
  const host = window.location.hostname;
  return import.meta.env.DEV || host === 'localhost' || host === '127.0.0.1' || host === '::1';
})();

let localAutofillModulePromise = null;
const getLocalAutofillModule = async () => {
  if (!isLocalAutofillEnabled) return null;
  if (!localAutofillModulePromise) {
    localAutofillModulePromise = import('/resources/js/local/customerModalAutofill.local.js').catch(() => null);
  }
  return localAutofillModulePromise;
};

const userForm = ref({
  name: '',
  email: '',
  role: 'employee',
  password: ''
});

const photoFile = ref(null);
const photoPreview = ref(null);

// Methods
const formatDateTime = (datetime) => {
  if (!datetime) return '';
  return new Date(datetime).toLocaleString('es-ES');
};

const editUser = (user) => {
  const firstRole = Array.isArray(user?.roles) ? user.roles[0]?.name : null;
  const selectedRole = firstRole || user?.role || 'employee';

  editingUser.value = user;
  userForm.value = {
    name: user.name,
    email: user.email,
    role: selectedRole,
    password: ''
  };
  // set photo preview if available
  photoPreview.value = user.profile_photo_url || user.profile_photo_path || null;
  photoFile.value = null;
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
    const selectedRole = userForm.value.role || 'employee';

    // If a photo file was selected, use FormData for multipart upload
    let payload = null;
    if (photoFile.value) {
      const fd = new FormData();
      fd.append('name', userForm.value.name);
      fd.append('email', userForm.value.email);
      fd.append('role', selectedRole);
      if (!editingUser.value) {
        fd.append('password', userForm.value.password);
        fd.append('password_confirmation', userForm.value.password);
      }
      fd.append('photo', photoFile.value);
      payload = fd;
    } else {
      payload = { ...userForm.value };
      payload.role = selectedRole;
      if (!editingUser.value && userForm.value.password) {
        payload.password_confirmation = userForm.value.password;
      }
    }

    if (editingUser.value) {
      await performTableAction('update', editingUser.value.id, payload);
    } else {
      await performTableAction('create', null, payload);
    }

    closeUserModal();
    refresh();
  } catch (err) {
    console.error('Error saving user:', err);

    const message = err?.response?.data?.message || 'No se pudo guardar el usuario.';
    const validation = err?.response?.data?.errors;

    if (validation && typeof validation === 'object') {
      const details = Object.values(validation)
        .flat()
        .filter(Boolean)
        .join('<br>');

      Swal.fire({
        title: 'Validación fallida',
        html: details || message,
        icon: 'error',
      });
    } else {
      Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
      });
    }
  } finally {
    saving.value = false;
  }
};

const closeUserModal = () => {
  editingUser.value = null;
  userForm.value = {
    name: '',
    email: '',
    role: 'employee',
    password: ''
  };
  photoFile.value = null;
  photoPreview.value = null;
  userModal.value?.close();
  // Reset create modal state after a brief delay
  nextTick(() => {
    showCreateModal.value = false;
  });
};

// Watch for create modal
watch(() => showCreateModal.value, (newValue) => {
  if (newValue) {
    // Reset form when opening modal
    userForm.value = {
      name: '',
      email: '',
      role: 'employee',
      password: ''
    };
    editingUser.value = null;
    photoFile.value = null;
    photoPreview.value = null;
    // Use nextTick to ensure DOM is ready
    nextTick(() => {
      userModal.value?.open();
    });
  }
});

// Handle photo selection and preview
const handlePhotoChange = (e) => {
  const file = e.target.files && e.target.files[0];
  if (!file) return;
  photoFile.value = file;
  const reader = new FileReader();
  reader.onload = (ev) => {
    photoPreview.value = ev.target.result;
  };
  reader.readAsDataURL(file);
};

const fillUserCreateForTest = async () => {
  if (editingUser.value) return;
  const module = await getLocalAutofillModule();
  module?.autofillUserCreateForm?.(userForm, roleOptions.value);
};

const loadRoleOptions = async () => {
  try {
    const { data } = await axios.get('/users/role-options');
    const roles = Array.isArray(data?.data) ? data.data.filter(Boolean) : [];
    roleOptions.value = roles.length > 0 ? roles : ['employee'];

    if (!roleOptions.value.includes(userForm.value.role)) {
      userForm.value.role = roleOptions.value[0];
    }
  } catch (err) {
    roleOptions.value = ['employee'];
  }
};

// Initialize
onMounted(async () => {
  await loadRoleOptions();
  await fetchData();
});
</script>