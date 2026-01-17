<template>
  <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800">
    <div class="p-4 border-b border-gray-200 dark:border-slate-800">
      <div class="text-sm text-gray-600 dark:text-slate-300">
        Crea roles y asigna permisos del sistema.
      </div>
    </div>

    <div class="relative overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-600 dark:text-slate-300">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-800 dark:text-slate-200">
          <tr>
            <th scope="col" class="px-6 py-3">Rol</th>
            <th scope="col" class="px-6 py-3">Módulos</th>
            <th scope="col" class="px-6 py-3">Permisos</th>
            <th scope="col" class="px-6 py-3 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading" class="bg-white dark:bg-slate-900">
            <td colspan="4" class="px-6 py-6 text-gray-500 dark:text-slate-300">Cargando...</td>
          </tr>

          <tr v-else-if="rows.length === 0" class="bg-white dark:bg-slate-900">
            <td colspan="4" class="px-6 py-6 text-gray-500 dark:text-slate-300">No hay roles.</td>
          </tr>

          <tr
            v-else
            v-for="role in rows"
            :key="role.id"
            class="bg-white border-b border-gray-100 hover:bg-blue-50/40 transition-colors dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800/60"
          >
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-slate-100">{{ role.name }}</td>
            <td class="px-6 py-4">
              <span class="text-gray-700 dark:text-slate-200">{{ menuCount(role) }}</span>
              <span class="text-gray-500 dark:text-slate-400">módulo(s)</span>
            </td>
            <td class="px-6 py-4">
              <span class="text-gray-700 dark:text-slate-200">{{ (role.permissions?.length ?? 0) }} </span>
              <span class="text-gray-500 dark:text-slate-400">permiso(s)</span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="inline-flex items-center gap-2">
                <button
                  type="button"
                  class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-gray-50 dark:text-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:hover:bg-slate-800"
                  @click="editRole(role)"
                >
                  Editar
                </button>
                <button
                  type="button"
                  class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                  :disabled="deletingId === role.id"
                  @click="deleteRole(role)"
                >
                  {{ deletingId === role.id ? 'Eliminando...' : 'Eliminar' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create Role Modal -->
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-show="createOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
      role="dialog"
      aria-modal="true"
      @click.self="hideCreateModal"
    >
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-show="createOpen" class="relative w-full max-w-2xl">
          <div class="relative bg-white rounded-lg shadow dark:bg-slate-900">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-slate-800">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Crear rol</h3>
              <button
                type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-slate-800 dark:hover:text-slate-100"
                @click="hideCreateModal"
              >
                <span class="sr-only">Cerrar</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                  <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m1 1 12 12M13 1 1 13"
                  />
                </svg>
              </button>
            </div>

            <form class="p-4 md:p-5" @submit.prevent="submitCreate">
              <div class="grid gap-4 mb-4 grid-cols-1">
                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Nombre</label>
                  <input
                    v-model.trim="createForm.name"
                    type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="Ej: supervisor"
                    required
                  />
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Módulos del menú</label>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label
                      v-for="m in menuModules"
                      :key="m.permission"
                      class="flex items-center gap-2 rounded-lg border border-gray-200 p-3 bg-white text-sm text-gray-700 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-200"
                    >
                      <input
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                        :checked="createForm.permissions.includes(m.permission)"
                        @change="toggleMenuPermission(createForm.permissions, m.permission, $event.target.checked)"
                      />
                      <span>{{ m.label }}</span>
                    </label>
                  </div>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Permisos</label>
                  <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 dark:bg-slate-800 dark:border-slate-700">
                    <div v-if="permissionsLoading" class="text-sm text-gray-500 dark:text-slate-300">Cargando permisos...</div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <div v-for="group in permissionGroups" :key="group.key" class="rounded-lg border border-gray-200 p-3 bg-white dark:bg-slate-900 dark:border-slate-700">
                        <div class="text-sm font-semibold text-gray-900 dark:text-slate-100 mb-2">{{ group.label }}</div>
                        <label v-for="perm in group.items" :key="perm" class="flex items-center gap-2 py-1 text-sm text-gray-700 dark:text-slate-200">
                          <input
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                            :value="perm"
                            v-model="createForm.permissions"
                          />
                          <span>{{ permissionLabel(perm) }}</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <p v-if="createError" class="text-sm text-red-600">{{ createError }}</p>
              </div>

              <button
                type="submit"
                class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                :disabled="creating"
              >
                {{ creating ? 'Creando...' : 'Crear rol' }}
              </button>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>

  <!-- Edit Role Modal -->
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-show="editOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
      role="dialog"
      aria-modal="true"
      @click.self="hideEditModal"
    >
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-show="editOpen" class="relative w-full max-w-2xl max-h-[90vh]">
          <div class="relative bg-white rounded-lg shadow dark:bg-slate-900 max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-slate-800">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Editar rol</h3>
              <button
                type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-slate-800 dark:hover:text-slate-100"
                @click="hideEditModal"
              >
                <span class="sr-only">Cerrar</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                  <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m1 1 12 12M13 1 1 13"
                  />
                </svg>
              </button>
            </div>

            <form class="min-h-0 p-4 md:p-5 overflow-y-auto" @submit.prevent="submitEdit">
              <div class="grid gap-4 mb-4 grid-cols-1">
                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Nombre</label>
                  <input
                    v-model.trim="editForm.name"
                    type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    required
                  />
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Módulos del menú</label>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label
                      v-for="m in menuModules"
                      :key="m.permission"
                      class="flex items-center gap-2 rounded-lg border border-gray-200 p-3 bg-white text-sm text-gray-700 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-200"
                    >
                      <input
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                        :checked="editForm.permissions.includes(m.permission)"
                        @change="toggleMenuPermission(editForm.permissions, m.permission, $event.target.checked)"
                      />
                      <span>{{ m.label }}</span>
                    </label>
                  </div>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Permisos</label>
                  <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 dark:bg-slate-800 dark:border-slate-700">
                    <div v-if="permissionsLoading" class="text-sm text-gray-500 dark:text-slate-300">Cargando permisos...</div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <div v-for="group in permissionGroups" :key="group.key" class="rounded-lg border border-gray-200 p-3 bg-white dark:bg-slate-900 dark:border-slate-700">
                        <div class="text-sm font-semibold text-gray-900 dark:text-slate-100 mb-2">{{ group.label }}</div>
                        <label v-for="perm in group.items" :key="perm" class="flex items-center gap-2 py-1 text-sm text-gray-700 dark:text-slate-200">
                          <input
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                            :value="perm"
                            v-model="editForm.permissions"
                          />
                          <span>{{ permissionLabel(perm) }}</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <p v-if="editError" class="text-sm text-red-600">{{ editError }}</p>
              </div>

              <button
                type="submit"
                class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                :disabled="updating"
              >
                {{ updating ? 'Guardando...' : 'Guardar cambios' }}
              </button>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';

const rows = ref([]);
const loading = ref(true);
const deletingId = ref(null);

const permissions = ref([]);
const permissionsLoading = ref(true);

const menuModules = [
  { permission: 'menu.users', label: 'Usuarios' },
  { permission: 'menu.roles', label: 'Roles' },
  { permission: 'menu.leads', label: 'Leads' },
  { permission: 'menu.email', label: 'Email masivo' },
  { permission: 'menu.customers', label: 'Clientes' },
  { permission: 'menu.calendar', label: 'Calendario' },
  { permission: 'menu.postventa', label: 'Postventa' },
];

const createOpen = ref(false);
const editOpen = ref(false);

const creating = ref(false);
const createError = ref('');
const createForm = ref({
  name: '',
  permissions: [],
});

const updating = ref(false);
const editError = ref('');
const editingRoleId = ref(null);
const editForm = ref({
  name: '',
  permissions: [],
});

const firstValidationMessage = (error) => {
  const errors = error?.response?.data?.errors;
  if (!errors || typeof errors !== 'object') return null;
  const firstKey = Object.keys(errors)[0];
  const first = firstKey ? errors[firstKey]?.[0] : null;
  return typeof first === 'string' ? first : null;
};

const permissionLabel = (permission) => {
  const [resource, action] = String(permission).split('.', 2);
  const actionLabel =
    action === 'view'
      ? 'Ver'
      : action === 'create'
        ? 'Crear'
        : action === 'update'
          ? 'Editar'
          : action === 'delete'
            ? 'Eliminar'
            : action ?? '';

  const resourceLabel =
    resource === 'users'
      ? 'Usuarios'
      : resource === 'roles'
        ? 'Roles'
        : resource === 'leads'
          ? 'Leads'
        : resource
          ? resource.charAt(0).toUpperCase() + resource.slice(1)
          : '';

  return actionLabel && resourceLabel ? `${actionLabel}` : permission;
};

const permissionGroups = computed(() => {
  const map = new Map();
  for (const perm of permissions.value) {
    if (String(perm).startsWith('menu.')) continue;
    const [resource] = String(perm).split('.', 1);
    const key = resource || 'otros';
    if (!map.has(key)) map.set(key, []);
    map.get(key).push(perm);
  }

  return Array.from(map.entries())
    .map(([key, items]) => ({
      key,
      label:
        key === 'users'
          ? 'Usuarios'
          : key === 'roles'
            ? 'Roles'
            : key === 'leads'
              ? 'Leads'
              : key.charAt(0).toUpperCase() + key.slice(1),
      items: items.slice().sort((a, b) => String(a).localeCompare(String(b))),
    }))
    .sort((a, b) => a.label.localeCompare(b.label));
});

const toggleMenuPermission = (permissionsArray, permission, checked) => {
  if (!Array.isArray(permissionsArray)) return;
  const idx = permissionsArray.indexOf(permission);
  if (checked && idx === -1) permissionsArray.push(permission);
  if (!checked && idx !== -1) permissionsArray.splice(idx, 1);
};

const menuCount = (role) => {
  const perms = role?.permissions;
  if (!Array.isArray(perms)) return 0;
  return perms.filter((p) => String(p).startsWith('menu.')).length;
};

const load = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/roles/data');
    rows.value = response?.data?.data ?? [];
  } finally {
    loading.value = false;
  }
};

const loadPermissions = async () => {
  permissionsLoading.value = true;
  try {
    const response = await axios.get('/roles/permissions');
    permissions.value = response?.data?.data ?? [];
  } finally {
    permissionsLoading.value = false;
  }
};

const showCreateModal = () => {
  createError.value = '';
  createForm.value = { name: '', permissions: [] };
  createOpen.value = true;
};

const hideCreateModal = () => {
  createOpen.value = false;
};

const hideEditModal = () => {
  editOpen.value = false;
};

const editRole = (role) => {
  editingRoleId.value = role.id;
  editError.value = '';
  editForm.value = {
    name: role.name ?? '',
    permissions: Array.isArray(role.permissions) ? role.permissions.slice() : [],
  };
  editOpen.value = true;
};

const submitCreate = async () => {
  creating.value = true;
  createError.value = '';
  try {
    await axios.post('/roles', createForm.value);
    hideCreateModal();
    await load();
  } catch (error) {
    createError.value =
      firstValidationMessage(error) ?? error?.response?.data?.message ?? 'No se pudo crear el rol.';
  } finally {
    creating.value = false;
  }
};

const submitEdit = async () => {
  if (!editingRoleId.value) return;
  updating.value = true;
  editError.value = '';
  try {
    await axios.put(`/roles/${editingRoleId.value}`, editForm.value);
    hideEditModal();
    await load();
  } catch (error) {
    editError.value = firstValidationMessage(error) ?? error?.response?.data?.message ?? 'No se pudo actualizar el rol.';
  } finally {
    updating.value = false;
  }
};

const deleteRole = async (role) => {
  if (!confirm(`¿Eliminar el rol "${role.name}"?`)) return;

  deletingId.value = role.id;
  try {
    await axios.delete(`/roles/${role.id}`);
    await load();
  } catch (error) {
    const message = error?.response?.data?.message ?? 'No se pudo eliminar el rol.';
    alert(message);
  } finally {
    deletingId.value = null;
  }
};

const onKeydown = (event) => {
  if (event.key !== 'Escape') return;
  if (createOpen.value) hideCreateModal();
  if (editOpen.value) hideEditModal();
};

watch(
  () => createOpen.value || editOpen.value,
  (open) => {
    document.body.classList.toggle('overflow-hidden', open);
  }
);

onMounted(async () => {
  window.addEventListener('roles:create', showCreateModal);
  window.addEventListener('keydown', onKeydown);
  await Promise.all([loadPermissions(), load()]);
});

onBeforeUnmount(() => {
  window.removeEventListener('roles:create', showCreateModal);
  window.removeEventListener('keydown', onKeydown);
  document.body.classList.remove('overflow-hidden');
});
</script>
