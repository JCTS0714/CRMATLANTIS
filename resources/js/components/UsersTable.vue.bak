<template>
  <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800">
    <div class="p-4 border-b border-gray-200 dark:border-slate-800">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-sm">
          <label for="users-search" class="sr-only">Buscar usuarios</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg
                class="w-4 h-4 text-gray-500 dark:text-slate-400"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 20 20"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
                />
              </svg>
            </div>
            <input
              id="users-search"
              v-model.trim="search"
              type="text"
              class="block w-full p-2.5 pl-10 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-600 focus:border-blue-600 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
              placeholder="Buscar por nombre o email"
            />
          </div>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-3">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600 dark:text-slate-300">Por página</span>
            <select
              v-model.number="perPage"
              class="text-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-600 focus:border-blue-600 block p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>

          <button
            v-if="search"
            type="button"
            class="text-sm text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 dark:text-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:hover:bg-slate-800"
            @click="clearSearch"
          >
            Limpiar
          </button>
        </div>
      </div>
    </div>

    <div class="relative overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-600 dark:text-slate-300">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-800 dark:text-slate-200">
          <tr>
            <th scope="col" class="px-6 py-3">ID</th>
            <th scope="col" class="px-6 py-3">Foto</th>
            <th scope="col" class="px-6 py-3">Nombre</th>
            <th scope="col" class="px-6 py-3">Email</th>
            <th scope="col" class="px-6 py-3">Creado</th>
            <th scope="col" class="px-6 py-3 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading" class="bg-white dark:bg-slate-900">
            <td colspan="6" class="px-6 py-6 text-gray-500 dark:text-slate-300">Cargando...</td>
          </tr>

          <tr v-else-if="rows.length === 0" class="bg-white dark:bg-slate-900">
            <td colspan="6" class="px-6 py-6 text-gray-500 dark:text-slate-300">No hay usuarios.</td>
          </tr>

          <tr
            v-else
            v-for="user in rows"
            :key="user.id"
            class="bg-white border-b border-gray-100 hover:bg-blue-50/40 transition-colors dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800/60"
          >
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-slate-100">{{ user.id }}</td>
            <td class="px-6 py-4">
              <div class="w-9 h-9 rounded-full overflow-hidden ring-1 ring-gray-200 dark:ring-slate-700 bg-gray-100 dark:bg-slate-800">
                <img
                  v-if="user.profile_photo_url"
                  :src="user.profile_photo_url"
                  :alt="user.name"
                  class="w-full h-full object-cover"
                  loading="lazy"
                />
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center text-[10px] font-semibold text-gray-700 dark:text-slate-100"
                >
                  {{ userInitials(user.name) }}
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-900 dark:text-slate-100">{{ user.name }}</td>
            <td class="px-6 py-4">{{ user.email }}</td>
            <td class="px-6 py-4">{{ formatDate(user.created_at) }}</td>
            <td class="px-6 py-4 text-right">
              <div class="inline-flex items-center gap-2">
                <button
                  type="button"
                  class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-gray-50 dark:text-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:hover:bg-slate-800"
                  @click="editUser(user)"
                >
                  Editar
                </button>
                <button
                  type="button"
                  class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                  :disabled="deletingId === user.id"
                  @click="deleteUser(user)"
                >
                  {{ deletingId === user.id ? 'Eliminando...' : 'Eliminar' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t border-gray-200 dark:border-slate-800">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-gray-600 dark:text-slate-300">
          <template v-if="meta.total">
            Mostrando {{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} de {{ meta.total }}
          </template>
          <template v-else>Sin resultados</template>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-3">
          <button
            type="button"
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:text-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:hover:bg-slate-800"
            :disabled="loading || meta.current_page <= 1"
            @click="goToPage(meta.current_page - 1)"
          >
            Anterior
          </button>

          <div class="text-sm text-gray-600 dark:text-slate-300">
            Página {{ meta.current_page }} de {{ meta.last_page }}
          </div>

          <button
            type="button"
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:text-slate-200 dark:bg-slate-900 dark:border-slate-700 dark:hover:bg-slate-800"
            :disabled="loading || meta.current_page >= meta.last_page"
            @click="goToPage(meta.current_page + 1)"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Create User Modal (Flowbite markup + Vue transitions) -->
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
        <div v-show="createOpen" class="relative w-full max-w-md">
          <div class="relative bg-white rounded-lg shadow dark:bg-slate-900">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-slate-800">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Crear usuario</h3>
              <button
                type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-slate-800 dark:hover:text-slate-100"
                @click="hideCreateModal"
              >
                <span class="sr-only">Cerrar</span>
                <svg
                  class="w-3 h-3"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 14 14"
                >
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
                    placeholder="Nombre"
                    required
                  />
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                  <input
                    v-model.trim="createForm.email"
                    type="email"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="correo@dominio.com"
                    required
                  />
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Rol</label>
                  <select
                    v-model="createForm.role"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    required
                  >
                    <option v-for="role in roleOptions" :key="role" :value="role">
                      {{ role }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Foto de perfil (opcional)</label>
                  <div class="flex items-center gap-3">
                    <div
                      class="w-9 h-9 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden dark:bg-slate-800 dark:border-slate-700"
                    >
                      <img
                        v-if="createPhotoPreview"
                        :src="createPhotoPreview"
                        alt="Preview"
                        class="w-full h-full object-cover"
                      />
                      <span v-else class="text-[9px] leading-none text-gray-500 dark:text-slate-300">Sin foto</span>
                    </div>

                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/webp"
                      class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-slate-100 focus:outline-none dark:bg-slate-800 dark:border-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-slate-700 dark:file:text-slate-100 dark:hover:file:bg-slate-600"
                      @change="onCreatePhotoChange"
                    />
                  </div>
                  <p class="mt-2 text-xs text-gray-500 dark:text-slate-300">JPG, PNG o WebP. Máximo 2MB.</p>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Contraseña</label>
                  <input
                    v-model="createForm.password"
                    type="password"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="********"
                    required
                    minlength="8"
                  />
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Confirmar contraseña</label>
                  <input
                    v-model="createForm.password_confirmation"
                    type="password"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="********"
                    required
                    minlength="8"
                  />
                </div>

                <p v-if="createError" class="text-sm text-red-600">{{ createError }}</p>
              </div>

              <button
                type="submit"
                class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                :disabled="creating"
              >
                {{ creating ? 'Creando...' : 'Crear' }}
              </button>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>

  <!-- Edit User Modal (Flowbite markup + Vue transitions) -->
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
        <div v-show="editOpen" class="relative w-full max-w-md">
          <div class="relative bg-white rounded-lg shadow dark:bg-slate-900">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-slate-800">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Editar usuario</h3>
              <button
                type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-slate-800 dark:hover:text-slate-100"
                @click="hideEditModal"
              >
                <span class="sr-only">Cerrar</span>
                <svg
                  class="w-3 h-3"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 14 14"
                >
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

            <form class="p-4 md:p-5" @submit.prevent="submitEdit">
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
                  <label class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                  <input
                    v-model.trim="editForm.email"
                    type="email"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    required
                  />
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Rol</label>
                  <select
                    v-model="editForm.role"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    required
                  >
                    <option v-for="role in roleOptions" :key="role" :value="role">
                      {{ role }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Foto de perfil (opcional)</label>
                  <div class="flex items-center gap-3">
                    <div
                      class="w-9 h-9 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden dark:bg-slate-800 dark:border-slate-700"
                    >
                      <img
                        v-if="editPhotoPreview"
                        :src="editPhotoPreview"
                        alt="Preview"
                        class="w-full h-full object-cover"
                      />
                      <span v-else class="text-[9px] leading-none text-gray-500 dark:text-slate-300">Sin foto</span>
                    </div>

                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/webp"
                      class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-slate-100 focus:outline-none dark:bg-slate-800 dark:border-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-slate-700 dark:file:text-slate-100 dark:hover:file:bg-slate-600"
                      @change="onEditPhotoChange"
                    />
                  </div>
                  <p class="mt-2 text-xs text-gray-500 dark:text-slate-300">JPG, PNG o WebP. Máximo 2MB.</p>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Nueva contraseña (opcional)</label>
                  <input
                    v-model="editForm.password"
                    type="password"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="********"
                    minlength="8"
                  />
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-900">Confirmar contraseña</label>
                  <input
                    v-model="editForm.password_confirmation"
                    type="password"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="********"
                    minlength="8"
                  />
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
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import axios from 'axios';

const rows = ref([]);
const loading = ref(true);
const deletingId = ref(null);

const search = ref('');
const perPage = ref(10);
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: null,
  to: null,
});

let searchDebounceTimer;

const createOpen = ref(false);
const editOpen = ref(false);

const creating = ref(false);
const createError = ref('');
const createForm = ref({
  name: '',
  email: '',
  role: 'employee',
  password: '',
  password_confirmation: '',
});

const createPhotoFile = ref(null);
const createPhotoPreview = ref('');

const updating = ref(false);
const editError = ref('');
const editingUserId = ref(null);
const editForm = ref({
  name: '',
  email: '',
  role: 'employee',
  password: '',
  password_confirmation: '',
});

const editPhotoFile = ref(null);
const editPhotoPreview = ref('');

const roleOptions = ref([]);

const loadRoleOptions = async () => {
  try {
    const response = await axios.get('/users/role-options');
    roleOptions.value = response?.data?.data ?? [];
  } catch {
    roleOptions.value = [];
  }
};

const ensureDefaultRole = (form) => {
  const options = roleOptions.value;
  if (!Array.isArray(options) || options.length === 0) return;
  if (!form.role || !options.includes(form.role)) {
    form.role = options.includes('employee') ? 'employee' : options[0];
  }
};

const load = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/users/data', {
      params: {
        search: search.value || undefined,
        page: meta.value.current_page,
        per_page: perPage.value,
      },
    });

    rows.value = response?.data?.data ?? [];
    meta.value = {
      ...meta.value,
      ...(response?.data?.meta ?? {}),
    };

    // If we deleted the last item on the last page, bounce back.
    if (meta.value.current_page > meta.value.last_page && meta.value.last_page > 0) {
      meta.value.current_page = meta.value.last_page;
      await load();
    }
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => {
  const next = Math.max(1, Math.min(meta.value.last_page || 1, page));
  if (next === meta.value.current_page) return;
  meta.value.current_page = next;
  load();
};

const clearSearch = () => {
  search.value = '';
};

const formatDate = (value) => {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleString();
};

const userInitials = (name) => {
  const raw = String(name ?? '').trim();
  if (!raw) return 'U';
  const parts = raw.split(/\s+/).map((p) => p.trim()).filter(Boolean);
  if (parts.length === 0) return 'U';
  const first = Array.from(parts[0])[0] ?? '';
  const last = parts.length > 1 ? Array.from(parts[parts.length - 1])[0] ?? '' : '';
  const initials = `${first}${last}`.trim();
  return (initials || 'U').toLocaleUpperCase('es-ES');
};

const editUser = async (user) => {
  await loadRoleOptions();
  editingUserId.value = user.id;
  editError.value = '';
  editForm.value = {
    name: user.name ?? '',
    email: user.email ?? '',
    role: user.role ?? 'employee',
    password: '',
    password_confirmation: '',
  };
  resetEditPhoto();
  ensureDefaultRole(editForm.value);
  editOpen.value = true;
};

const showCreateModal = async () => {
  await loadRoleOptions();
  createError.value = '';
  createForm.value = { name: '', email: '', role: 'employee', password: '', password_confirmation: '' };
  resetCreatePhoto();
  ensureDefaultRole(createForm.value);
  createOpen.value = true;
};

const hideCreateModal = () => {
  createOpen.value = false;
  resetCreatePhoto();
};

const hideEditModal = () => {
  editOpen.value = false;
  resetEditPhoto();
};

const resetCreatePhoto = () => {
  createPhotoFile.value = null;
  if (createPhotoPreview.value) URL.revokeObjectURL(createPhotoPreview.value);
  createPhotoPreview.value = '';
};

const resetEditPhoto = () => {
  editPhotoFile.value = null;
  if (editPhotoPreview.value) URL.revokeObjectURL(editPhotoPreview.value);
  editPhotoPreview.value = '';
};

const onCreatePhotoChange = (event) => {
  const file = event?.target?.files?.[0] ?? null;
  createPhotoFile.value = file;
  if (createPhotoPreview.value) URL.revokeObjectURL(createPhotoPreview.value);
  createPhotoPreview.value = file ? URL.createObjectURL(file) : '';
};

const onEditPhotoChange = (event) => {
  const file = event?.target?.files?.[0] ?? null;
  editPhotoFile.value = file;
  if (editPhotoPreview.value) URL.revokeObjectURL(editPhotoPreview.value);
  editPhotoPreview.value = file ? URL.createObjectURL(file) : '';
};

const firstValidationMessage = (error) => {
  const errors = error?.response?.data?.errors;
  if (!errors || typeof errors !== 'object') return null;
  const firstKey = Object.keys(errors)[0];
  const first = firstKey ? errors[firstKey]?.[0] : null;
  return typeof first === 'string' ? first : null;
};

const submitCreate = async () => {
  creating.value = true;
  createError.value = '';
  try {
    const fd = new FormData();
    fd.append('name', createForm.value.name ?? '');
    fd.append('email', createForm.value.email ?? '');
    fd.append('role', createForm.value.role ?? '');
    fd.append('password', createForm.value.password ?? '');
    fd.append('password_confirmation', createForm.value.password_confirmation ?? '');
    if (createPhotoFile.value) fd.append('photo', createPhotoFile.value);

    await axios.post('/users', fd);
    hideCreateModal();
    await load();
  } catch (error) {
    createError.value =
      firstValidationMessage(error) ?? error?.response?.data?.message ?? 'No se pudo crear el usuario.';
  } finally {
    creating.value = false;
  }
};

const submitEdit = async () => {
  if (!editingUserId.value) return;
  updating.value = true;
  editError.value = '';
  try {
    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('name', editForm.value.name ?? '');
    fd.append('email', editForm.value.email ?? '');
    fd.append('role', editForm.value.role ?? '');
    if (editForm.value.password) {
      fd.append('password', editForm.value.password);
      fd.append('password_confirmation', editForm.value.password_confirmation ?? '');
    }
    if (editPhotoFile.value) fd.append('photo', editPhotoFile.value);

    await axios.post(`/users/${editingUserId.value}`, fd);
    hideEditModal();
    await load();
  } catch (error) {
    editError.value =
      firstValidationMessage(error) ?? error?.response?.data?.message ?? 'No se pudo actualizar el usuario.';
  } finally {
    updating.value = false;
  }
};

const deleteUser = async (user) => {
  if (!confirm(`¿Eliminar el usuario "${user.name}"?`)) return;

  deletingId.value = user.id;
  try {
    await axios.delete(`/users/${user.id}`);
    await load();
  } catch (error) {
    const message = error?.response?.data?.message ?? 'No se pudo eliminar el usuario.';
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

watch(
  () => perPage.value,
  () => {
    meta.value.current_page = 1;
    load();
  }
);

watch(
  () => search.value,
  () => {
    meta.value.current_page = 1;
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
      load();
    }, 300);
  }
);

onMounted(() => {
  window.addEventListener('users:create', showCreateModal);
  window.addEventListener('keydown', onKeydown);
  loadRoleOptions();
  load();
});

onBeforeUnmount(() => {
  window.removeEventListener('users:create', showCreateModal);
  window.removeEventListener('keydown', onKeydown);
  document.body.classList.remove('overflow-hidden');
  clearTimeout(searchDebounceTimer);
  resetCreatePhoto();
  resetEditPhoto();
});
</script>
