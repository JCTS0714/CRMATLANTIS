<template>
  <div>
    <div class="mb-4 text-sm text-gray-600 dark:text-slate-300">
      Gestiona tareas internas con una vista kanban de tres estados o cambia a vista lista para revisar detalles.
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
      <div class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Buscar:</label>
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="Nombre o descripcion"
          class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30"
        />
      </div>

      <div class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Responsable:</label>
        <select
          v-model="filters.responsible"
          class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
        >
          <option value="">Todos</option>
          <option v-for="name in responsibleFilterOptions" :key="name" :value="name">{{ name }}</option>
        </select>
      </div>

      <div class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-slate-300">Prioridad:</label>
        <select
          v-model="filters.priority"
          class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
        >
          <option value="">Todas</option>
          <option value="alta">Alta</option>
          <option value="media">Media</option>
          <option value="baja">Baja</option>
        </select>
      </div>

      <button
        type="button"
        class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        @click="resetFilters"
      >
        Limpiar filtros
      </button>
    </div>

    <div v-if="loadingTasks" class="mb-4 text-sm text-gray-600 dark:text-slate-300">Cargando tareas...</div>
    <div v-if="loadError" class="mb-4 text-sm text-red-600">{{ loadError }}</div>

    <div v-if="isKanbanView" class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <section
        v-for="column in columns"
        :key="column.id"
        class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900"
        :class="dragOverColumnId === column.id ? 'ring-2 ring-blue-300 dark:ring-blue-700' : ''"
        @dragover.prevent="onDragOverColumn(column.id, $event)"
        @drop.prevent="onDropOnColumn(column.id, $event)"
      >
        <div class="border-b border-gray-200 p-4 dark:border-slate-800">
          <div class="flex items-center justify-between gap-3">
            <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ column.title }}</div>
            <div class="text-xs text-gray-600 dark:text-slate-300">{{ tasksByColumn[column.id].length }}</div>
          </div>
        </div>

        <div class="min-h-[220px] space-y-3 p-3">
          <div
            v-if="tasksByColumn[column.id].length === 0"
            class="rounded-lg border border-dashed border-gray-200 px-3 py-6 text-center text-sm text-gray-500 dark:border-slate-700 dark:text-slate-400"
          >
            Sin tareas.
          </div>

          <article
            v-for="task in tasksByColumn[column.id]"
            :key="task.id"
            class="cursor-pointer rounded-lg border border-gray-200 bg-gray-50 p-3 transition hover:shadow-sm dark:border-slate-700 dark:bg-slate-800"
            :class="draggedTaskId === task.id ? 'opacity-70' : ''"
            draggable="true"
            @click="openForm(task)"
            @dragstart="onDragStart(task.id, $event)"
            @dragend="onDragEnd"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ task.nombre }}</div>
                <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">Responsable: {{ task.responsable }}</div>
              </div>
              <span class="rounded px-2 py-0.5 text-xs font-medium" :class="priorityClass(task.prioridad)">
                {{ task.prioridad }}
              </span>
            </div>

            <p class="mt-2 line-clamp-2 text-xs text-gray-600 dark:text-slate-300">{{ task.descripcion }}</p>

            <div class="mt-3 grid grid-cols-1 gap-1 text-xs text-gray-600 dark:text-slate-300">
              <span>Asignador: {{ task.asignador }}</span>
              <span>Ejecucion: {{ formatDate(task.tiempoEjecucion) }}</span>
            </div>

            <div class="mt-3 flex items-center justify-end gap-2">
              <span
                class="inline-flex items-center rounded-md px-2 py-1 text-[11px] font-semibold"
                :class="timeStatusClass(task)"
              >
                {{ timeStatusLabel(task) }}
              </span>
            </div>
          </article>
        </div>
      </section>
    </div>

    <div v-else class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-slate-800">
          <thead class="bg-gray-50 dark:bg-slate-900/40">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-slate-200">Nombre</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-slate-200">Asignador</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-slate-200">Responsable</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-slate-200">Prioridad</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-slate-200">Ejecucion</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-slate-200">Estado tiempo</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-slate-200">Estado</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-slate-200">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
            <tr v-if="filteredTasks.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">Sin tareas para mostrar.</td>
            </tr>
            <tr v-for="task in filteredTasks" :key="task.id">
              <td class="px-4 py-3 text-gray-800 dark:text-slate-100">
                <div class="font-medium">{{ task.nombre }}</div>
                <div class="mt-1 max-w-xs truncate text-xs text-gray-500 dark:text-slate-400">{{ task.descripcion }}</div>
              </td>
              <td class="px-4 py-3 text-gray-700 dark:text-slate-200">{{ task.asignador }}</td>
              <td class="px-4 py-3 text-gray-700 dark:text-slate-200">{{ task.responsable }}</td>
              <td class="px-4 py-3">
                <span class="rounded px-2 py-0.5 text-xs font-medium" :class="priorityClass(task.prioridad)">
                  {{ task.prioridad }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-700 dark:text-slate-200">{{ formatDate(task.tiempoEjecucion) }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center rounded-md px-2 py-1 text-[11px] font-semibold"
                  :class="timeStatusClass(task)"
                >
                  {{ timeStatusLabel(task) }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-700 dark:text-slate-200">{{ statusLabel(task.estado) }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  @click="openForm(task)"
                >
                  Ver/Editar
                </button>
                <button
                  type="button"
                  class="inline-flex items-center rounded-lg border border-red-200 bg-white px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-900/40 dark:bg-slate-900 dark:text-red-300 dark:hover:bg-red-950/30"
                  :disabled="deletingTask"
                  @click="deleteTask(task.id)"
                >
                  Eliminar
                </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="formOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40" @click="closeForm"></div>

      <section class="relative z-10 w-full max-w-3xl rounded-xl border border-gray-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <header class="border-b border-gray-200 px-6 py-4 dark:border-slate-800">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">{{ editingTaskId ? 'Editar tarea' : 'Nueva tarea' }}</h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-slate-300">Formulario visual sin integracion backend por ahora.</p>
        </header>

        <form class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2" @submit.prevent="saveTask">
          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Nombre</label>
            <input
              v-model.trim="form.nombre"
              type="text"
              required
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-blue-900/30"
            />
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Descripcion</label>
            <textarea
              v-model.trim="form.descripcion"
              rows="3"
              required
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-blue-900/30"
            ></textarea>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Asignador</label>
            <input
              v-model.trim="form.asignador"
              type="text"
              readonly
              class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
            />
          </div>

          <div class="relative">
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Responsable</label>
            <input
              v-model.trim="responsibleQuery"
              type="text"
              required
              autocomplete="off"
              placeholder="Buscar usuario..."
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-blue-900/30"
              @focus="onResponsibleFocus"
              @input="onResponsibleInput"
              @blur="onResponsibleBlur"
            />

            <div
              v-if="responsibleDropdownOpen"
              class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-900"
            >
              <div v-if="responsibleLoading" class="px-3 py-2 text-xs text-gray-500 dark:text-slate-400">
                Buscando usuarios...
              </div>

              <button
                v-for="user in responsibleOptions"
                :key="user.id"
                type="button"
                class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-slate-800"
                @mousedown.prevent="selectResponsible(user)"
              >
                <span class="text-sm text-gray-800 dark:text-slate-100">{{ user.name }}</span>
                <span class="text-xs text-gray-500 dark:text-slate-400">{{ user.email }}</span>
              </button>

              <div
                v-if="!responsibleLoading && responsibleOptions.length === 0"
                class="px-3 py-2 text-xs text-gray-500 dark:text-slate-400"
              >
                Sin usuarios disponibles.
              </div>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Prioridad</label>
            <select
              v-model="form.prioridad"
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-blue-900/30"
              @change="onPriorityChange"
            >
              <option value="alta">Alta</option>
              <option value="media">Media</option>
              <option value="baja">Baja</option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Tiempo de ejecucion</label>
            <input
              v-model="form.tiempoEjecucion"
              type="datetime-local"
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
            />
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Observacion</label>
            <textarea
              v-model.trim="form.observacion"
              rows="2"
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-blue-900/30"
            ></textarea>
          </div>

          <div class="flex items-end justify-end gap-2 md:col-span-2">
            <button
              v-if="editingTaskId"
              type="button"
              class="inline-flex items-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-900/40 dark:bg-slate-900 dark:text-red-300 dark:hover:bg-red-950/30 disabled:opacity-60"
              :disabled="deletingTask || savingTask"
              @click="deleteTask(editingTaskId)"
            >
              {{ deletingTask ? 'Eliminando...' : 'Eliminar' }}
            </button>
            <button
              type="button"
              class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="closeForm"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
              :disabled="savingTask"
            >
              {{ savingTask ? 'Guardando...' : (editingTaskId ? 'Guardar cambios' : 'Crear tarea') }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { toastError, toastSuccess } from '../ui/alerts';

const props = defineProps({
  viewMode: {
    type: String,
    default: 'kanban',
  },
});

const columns = [
  { id: 'pendiente', title: 'Pendiente' },
  { id: 'en_progreso', title: 'En progreso' },
  { id: 'completada', title: 'Completada' },
];

const tasks = ref([]);
const loadingTasks = ref(false);
const savingTask = ref(false);
const deletingTask = ref(false);
const loadError = ref('');

const filters = reactive({
  search: '',
  responsible: '',
  priority: '',
});

const formOpen = ref(false);
const editingTaskId = ref(null);
const draggedTaskId = ref(null);
const dragOverColumnId = ref('');
const responsibleQuery = ref('');
const responsibleLoading = ref(false);
const responsibleDropdownOpen = ref(false);
const responsibleOptions = ref([]);
let responsibleSearchTimer = null;
const form = reactive({
  nombre: '',
  descripcion: '',
  asignador: '',
  responsableId: null,
  responsable: '',
  prioridad: 'media',
  tiempoEjecucion: '',
  observacion: '',
  estado: 'pendiente',
});

const isKanbanView = computed(() => props.viewMode !== 'list');

const authUser = computed(() => window.__AUTH_USER__ ?? null);
const loggedUserId = computed(() => authUser.value?.id ?? null);
const loggedUserName = computed(() => authUser.value?.name ?? 'Usuario actual');

const responsibleFilterOptions = computed(() => {
  const names = tasks.value.map((task) => task.responsable).filter(Boolean);
  return Array.from(new Set(names)).sort((a, b) => a.localeCompare(b));
});

const filteredTasks = computed(() => {
  const term = filters.search.trim().toLowerCase();

  return tasks.value.filter((task) => {
    const matchesSearch =
      !term ||
      task.nombre.toLowerCase().includes(term) ||
      task.descripcion.toLowerCase().includes(term);
    const matchesResponsible = !filters.responsible || task.responsable === filters.responsible;
    const matchesPriority = !filters.priority || task.prioridad === filters.priority;

    return matchesSearch && matchesResponsible && matchesPriority;
  });
});

const tasksByColumn = computed(() => {
  return columns.reduce((acc, column) => {
    acc[column.id] = filteredTasks.value.filter((task) => task.estado === column.id);
    return acc;
  }, {});
});

const statusLabel = (status) => columns.find((column) => column.id === status)?.title ?? status;

const priorityClass = (priority) => {
  if (priority === 'alta') return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200';
  if (priority === 'media') return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200';
  return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200';
};

const formatDate = (value) => {
  if (!value) return 'Sin fecha';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString();
};

const toDateTimeLocal = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const defaultDaysByPriority = (priority) => {
  if (priority === 'alta') return 1;
  if (priority === 'media') return 2;
  return 3;
};

const setDefaultExecutionByPriority = (priority) => {
  const days = defaultDaysByPriority(priority);
  const target = new Date();
  target.setDate(target.getDate() + days);
  form.tiempoEjecucion = toDateTimeLocal(target);
};

const onPriorityChange = () => {
  setDefaultExecutionByPriority(form.prioridad);
};

const timeStatusValue = (task) => {
  if (task?.estado === 'completada') return 0;

  if (task?.estadoTiempo === 0 || task?.estadoTiempo === 1) {
    return task.estadoTiempo;
  }

  if (!task?.tiempoEjecucion) return 0;
  const dueDate = new Date(task.tiempoEjecucion);
  if (Number.isNaN(dueDate.getTime())) return 0;

  return new Date() > dueDate ? 1 : 0;
};

const timeStatusLabel = (task) => (timeStatusValue(task) === 1 ? 'Retrasado' : 'A tiempo');

const timeStatusClass = (task) => {
  return timeStatusValue(task) === 1
    ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'
    : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200';
};

const resetForm = () => {
  form.nombre = '';
  form.descripcion = '';
  form.asignador = loggedUserName.value;
  form.responsableId = null;
  form.responsable = '';
  form.prioridad = 'media';
  setDefaultExecutionByPriority(form.prioridad);
  form.observacion = '';
  form.estado = 'pendiente';
  responsibleQuery.value = '';
};

const resetFilters = () => {
  filters.search = '';
  filters.responsible = '';
  filters.priority = '';
};

const openForm = (task = null) => {
  if (!task) {
    editingTaskId.value = null;
    resetForm();
    formOpen.value = true;
    loadResponsibleOptions('');
    return;
  }

  editingTaskId.value = task.id;
  form.nombre = task.nombre;
  form.descripcion = task.descripcion;
  form.asignador = task.asignador;
  form.responsableId = task.responsable_id ?? null;
  form.responsable = task.responsable;
  form.prioridad = task.prioridad;
  form.tiempoEjecucion = task.tiempoEjecucion;
  form.observacion = task.observacion;
  form.estado = task.estado;
  responsibleQuery.value = task.responsable || '';
  formOpen.value = true;
  loadResponsibleOptions(task.responsable || '');
};

const closeForm = () => {
  formOpen.value = false;
  responsibleDropdownOpen.value = false;
};

const normalizeTask = (task) => ({
  id: task.id,
  nombre: task.nombre,
  descripcion: task.descripcion,
  asignador_id: task.asignador_id,
  asignador: task.asignador || '',
  responsable_id: task.responsable_id,
  responsable: task.responsable || '',
  prioridad: task.prioridad,
  tiempoEjecucion: task.tiempo_ejecucion,
  observacion: task.observacion,
  estado: task.estado,
  estadoTiempo: Number(task.estado_tiempo ?? 0),
});

const loadTasks = async () => {
  loadingTasks.value = true;
  loadError.value = '';
  try {
    const { data } = await axios.get('/scrum/tareas/data', {
      params: {
        per_page: 200,
      },
    });

    const rows = Array.isArray(data?.data) ? data.data : [];
    tasks.value = rows.map(normalizeTask);
  } catch (error) {
    loadError.value = error?.response?.data?.message || 'No se pudieron cargar las tareas.';
    tasks.value = [];
  } finally {
    loadingTasks.value = false;
  }
};

const saveTask = async () => {
  if (!form.responsableId) {
    toastError('Selecciona un responsable valido.');
    return;
  }

  savingTask.value = true;
  try {
    const payload = {
      nombre: form.nombre,
      descripcion: form.descripcion,
      responsable_id: form.responsableId,
      prioridad: form.prioridad,
      tiempo_ejecucion: form.tiempoEjecucion || null,
      observacion: form.observacion || null,
    };

    if (editingTaskId.value) {
      await axios.put(`/scrum/tareas/${editingTaskId.value}`, payload);
      toastSuccess('Tarea actualizada.');
    } else {
      await axios.post('/scrum/tareas', payload);
      toastSuccess('Tarea creada.');
    }

    await loadTasks();
    closeForm();
  } catch (error) {
    toastError(error?.response?.data?.message || 'No se pudo guardar la tarea.');
  } finally {
    savingTask.value = false;
  }
};

const deleteTask = async (taskId) => {
  if (!taskId) return;

  if (!window.confirm('¿Deseas eliminar esta tarea?')) {
    return;
  }

  deletingTask.value = true;
  try {
    await axios.delete(`/scrum/tareas/${taskId}`);
    toastSuccess('Tarea eliminada.');
    await loadTasks();

    if (editingTaskId.value === taskId) {
      closeForm();
    }
  } catch (error) {
    toastError(error?.response?.data?.message || 'No se pudo eliminar la tarea.');
  } finally {
    deletingTask.value = false;
  }
};

const updateStatus = async (id, status) => {
  const target = tasks.value.find((task) => task.id === id);
  if (!target) return;
  const previous = target.estado;
  target.estado = status;

  try {
    await axios.patch(`/scrum/tareas/${id}/status`, { estado: status });
  } catch {
    target.estado = previous;
    toastError('No se pudo actualizar el estado.');
  }
};

const onDragStart = (taskId, event) => {
  draggedTaskId.value = taskId;
  if (event?.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(taskId));
  }
};

const onDragOverColumn = (columnId, event) => {
  dragOverColumnId.value = columnId;
  if (event?.dataTransfer) event.dataTransfer.dropEffect = 'move';
};

const onDropOnColumn = (columnId, event) => {
  const idFromTransfer = Number(event?.dataTransfer?.getData('text/plain'));
  const taskId = Number.isFinite(idFromTransfer) && idFromTransfer > 0
    ? idFromTransfer
    : draggedTaskId.value;

  if (!taskId) {
    onDragEnd();
    return;
  }

  updateStatus(taskId, columnId);
  onDragEnd();
};

const onDragEnd = () => {
  draggedTaskId.value = null;
  dragOverColumnId.value = '';
};

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

const onResponsibleInput = () => {
  form.responsable = responsibleQuery.value;
  form.responsableId = null;
  responsibleDropdownOpen.value = true;
  if (responsibleSearchTimer) clearTimeout(responsibleSearchTimer);
  responsibleSearchTimer = setTimeout(() => {
    loadResponsibleOptions(responsibleQuery.value.trim());
  }, 250);
};

const onResponsibleFocus = () => {
  responsibleDropdownOpen.value = true;
  loadResponsibleOptions(responsibleQuery.value.trim());
};

const onResponsibleBlur = () => {
  setTimeout(() => {
    responsibleDropdownOpen.value = false;
  }, 120);
};

const selectResponsible = (user) => {
  form.responsableId = user.id;
  form.responsable = user.name;
  responsibleQuery.value = user.name;
  responsibleDropdownOpen.value = false;
};

const onCreateFromHeader = () => {
  openForm();
};

onMounted(() => {
  resetForm();
  loadTasks();
  window.addEventListener('scrum-tasks:create', onCreateFromHeader);
});

onBeforeUnmount(() => {
  if (responsibleSearchTimer) clearTimeout(responsibleSearchTimer);
  window.removeEventListener('scrum-tasks:create', onCreateFromHeader);
});
</script>
