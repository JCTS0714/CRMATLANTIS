<template>
  <div
    class="min-h-screen flex flex-col bg-gradient-to-br from-blue-50/60 via-gray-50 to-gray-50 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900"
  >
    <Header
      :sidebar-collapsed="sidebarCollapsed"
      :mobile-sidebar-open="mobileSidebarOpen"
      @toggle-sidebar="toggleSidebar"
      @toggle-mobile-sidebar="toggleMobileSidebar"
    />
    <Sidebar :collapsed="sidebarCollapsed" :mobile-open="mobileSidebarOpen" />

    <button
      v-if="mobileSidebarOpen"
      type="button"
      class="fixed inset-0 z-30 bg-slate-950/50 backdrop-blur-[1px] sm:hidden"
      aria-label="Cerrar menú lateral"
      @click="closeMobileSidebar"
    ></button>

    <main
      class="transition-[margin] duration-200 flex-1 flex flex-col"
      :class="sidebarCollapsed ? 'sm:ml-20' : 'sm:ml-64'"
    >
      <div class="flex-1 p-4 pt-20">
        <div class="max-w-7xl mx-auto w-full">
          <div class="flex items-center justify-between mb-6">
            <div class="border-l-4 border-blue-600 pl-4">
              <h1 class="text-2xl font-semibold text-gray-900 dark:text-slate-100">{{ pageTitle }}</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-slate-300">{{ pageSubtitle }}</p>
            </div>

            <button
              v-if="isRoles && canCreateRoles"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createRole"
            >
              Crear rol
            </button>

            <button
              v-else-if="(isLeadsBoard || isLeadsList) && canCreateLeads"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createQuickLead"
            >
              Lead rápido
            </button>

            <button
              v-if="isLeadsBoard || isLeadsList"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:text-slate-200 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
              @click="toggleLeadsView"
            >
              {{ isLeadsBoard ? 'Vista lista' : 'Vista pipeline' }}
            </button>

            <button
              v-else-if="isPostventaIncidences"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:text-slate-200 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
              @click="togglePostventaView"
            >
              {{ isBacklog ? 'Vista lista' : 'Vista backlog' }}
            </button>

            <button
              v-else-if="isScrumTasks"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:text-slate-200 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
              @click="toggleScrumView"
            >
              {{ scrumViewMode === 'kanban' ? 'Vista lista' : 'Vista kanban' }}
            </button>

            <button
              v-else-if="false"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
            >
              Nuevo registro
            </button>

            <button
              v-if="(isPostventaIncidences || isPostventaCustomers) && canCreateIncidencias"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createIncidence"
            >
              Nueva incidencia
            </button>

            <button
              v-else-if="isScrumTasks"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createScrumTask"
            >
              Nueva tarea
            </button>

            <button
              v-else-if="isPostventaContadores && canCreateContadores"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createContador"
            >
              Nuevo contador
            </button>

            <button
              v-else-if="isPostventaCertificados && canCreateCertificados"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createCertificado"
            >
              Nuevo certificado
            </button>
          </div>

          <component
            v-if="currentStaticComponent"
            :is="currentStaticComponent"
            v-bind="currentStaticProps"
          />

          <DynamicModuleHost
            v-else-if="activeDynamicModule"
            :component-file="activeDynamicModule.component"
          />

          <template v-else>
            <DashboardView />
          </template>
        </div>
      </div>

      <Footer class="mt-auto w-full" />
    </main>

    <IncidenceQuickModal />
    <IncidenceEditModal />
  </div>
</template>

<script setup>
import { computed, ref, defineAsyncComponent, onBeforeUnmount, onMounted, watch } from 'vue';

import Header from './layout/Header.vue';
import Sidebar from './layout/Sidebar.vue';
import Footer from './layout/Footer.vue';
import DynamicModuleHost from './DynamicModuleHost.vue';

// Lazy load heavy components for better performance
const UsersTable = defineAsyncComponent(() => import('./UsersTable.vue'));
const RolesTable = defineAsyncComponent(() => import('./RolesTable.vue'));
const LeadsBoard = defineAsyncComponent(() => import('./leads/LeadsBoard.vue'));
const LeadsTable = defineAsyncComponent(() => import('./leads/LeadsTable.vue'));
const CalendarView = defineAsyncComponent(() => import('./CalendarView.vue'));
const IncidenciasTable = defineAsyncComponent(() => import('./incidencias/IncidenciasTable.vue'));
const IncidenciasBoard = defineAsyncComponent(() => import('./incidencias/IncidenciasBoard.vue'));
const BacklogBoard = defineAsyncComponent(() => import('./BacklogBoard.vue'));
const ScrumTasksView = defineAsyncComponent(() => import('./ScrumTasksView.vue'));
const PostventaCustomersTable = defineAsyncComponent(() => import('./PostventaCustomersTable.vue'));
const ContadoresTable = defineAsyncComponent(() => import('./ContadoresTable.vue'));
const CertificadosTable = defineAsyncComponent(() => import('./CertificadosTable.vue'));
const LostLeadsList = defineAsyncComponent(() => import('./leads/LostLeadsList.vue'));
const WaitingLeadsList = defineAsyncComponent(() => import('./leads/WaitingLeadsList.vue'));
const SettingsView = defineAsyncComponent(() => import('./settings/SettingsView.vue'));

// Keep lighter components as regular imports
import DashboardView from './DashboardView.vue';
import IncidenceQuickModal from './incidencias/IncidenceQuickModal.vue';
import IncidenceEditModal from './incidencias/IncidenceEditModal.vue';

const normalizedPath = window.location.pathname.replace(/\/+$/, '') || '/';
const normalizePath = (value) => {
  const str = String(value || '').trim();
  if (!str) return '/';
  return str.replace(/\/+$/, '') || '/';
};

const appModules = computed(() => window.__APP_MODULES__ ?? { dynamic: [] });
const dynamicModules = computed(() =>
  (appModules.value.dynamic ?? []).filter((module) => {
    return module
      && typeof module === 'object'
      && typeof module.path === 'string'
      && typeof module.component === 'string'
      && module.path.length > 0
      && module.component.length > 0;
  })
);

const activeDynamicModule = computed(() =>
  dynamicModules.value.find((module) => normalizePath(module.path) === normalizedPath) ?? null
);

const isUsers = computed(() => normalizedPath.startsWith('/users'));
const isRoles = computed(() => normalizedPath.startsWith('/roles'));
const isLeadsBoard = computed(() => normalizedPath === '/leads');
const isLeadsList = computed(() => normalizedPath === '/leads/list');
const isLeads = computed(() => normalizedPath.startsWith('/leads'));
const isCustomers = computed(() => normalizedPath.startsWith('/customers'));
const isCalendar = computed(() => normalizedPath.startsWith('/calendar'));
const isIncidencias = computed(() => normalizedPath.startsWith('/incidencias'));
const currentView = computed(() => {
  if (isIncidencias.value) {
    return normalizedPath === '/incidencias/board' ? 'board' : 'table';
  }
  return 'table';
});
const isBacklog = computed(() => normalizedPath.startsWith('/backlog'));
const isScrumTasks = computed(() => normalizedPath.startsWith('/scrum/tareas'));
const isPostventaCustomers = computed(() => normalizedPath === '/postventa/clientes');
const isPostventaContadores = computed(() => normalizedPath === '/postventa/contadores');
const isPostventaCertificados = computed(() => normalizedPath === '/postventa/certificados');
const isSettings = computed(() => normalizedPath.startsWith('/configuracion'));
const isLostLeads = computed(() => normalizedPath === '/desistidos');
const isWaitingLeads = computed(() => normalizedPath === '/espera');

const scrumQuery = new URLSearchParams(window.location.search);
const scrumViewMode = computed(() => (scrumQuery.get('view') === 'list' ? 'list' : 'kanban'));

const isPostventaIncidences = computed(() => isIncidencias.value || isBacklog.value);

const authUser = computed(() => window.__AUTH_USER__ ?? null);
const hasPermission = (permission) => {
  const perms = authUser.value?.permissions;
  return Array.isArray(perms) && perms.includes(permission);
};

const canCreateRoles = computed(() => hasPermission('roles.create'));
const canCreateLeads = computed(() => hasPermission('leads.create'));
const canCreateIncidencias = computed(() => hasPermission('incidencias.create'));
const canCreateContadores = computed(() => hasPermission('contadores.create'));
const canCreateCertificados = computed(() => hasPermission('certificados.create'));

const staticViewDefinitions = [
  {
    matches: () => isUsers.value,
    component: UsersTable,
    title: () => 'Usuarios',
    subtitle: () => 'Gestión de usuarios del sistema',
  },
  {
    matches: () => isRoles.value,
    component: RolesTable,
    title: () => 'Roles',
    subtitle: () => 'Crea roles y asigna permisos',
  },
  {
    matches: () => isLeadsBoard.value,
    component: LeadsBoard,
    title: () => 'Leads',
    subtitle: () => 'Pipeline de oportunidades',
  },
  {
    matches: () => isLeadsList.value,
    component: LeadsTable,
    title: () => 'Leads',
    subtitle: () => 'Gestión de leads por etapas',
  },
  {
    matches: () => isIncidencias.value && currentView.value === 'table',
    component: IncidenciasTable,
    title: () => 'Incidencias',
    subtitle: () => 'Registro y seguimiento de incidencias (vista lista)',
  },
  {
    matches: () => isIncidencias.value && currentView.value === 'board',
    component: IncidenciasBoard,
    title: () => 'Incidencias',
    subtitle: () => 'Registro y seguimiento de incidencias (vista kanban)',
  },
  {
    matches: () => isBacklog.value,
    component: BacklogBoard,
    title: () => 'Incidencias',
    subtitle: () => 'Registro y seguimiento de incidencias (vista backlog)',
  },
  {
    matches: () => isScrumTasks.value,
    component: ScrumTasksView,
    props: () => ({ viewMode: scrumViewMode.value }),
    title: () => 'Scrum',
    subtitle: () => `Planificacion y seguimiento de tareas (vista ${scrumViewMode.value === 'kanban' ? 'kanban' : 'lista'})`,
  },
  {
    matches: () => isPostventaCustomers.value || isCustomers.value,
    component: PostventaCustomersTable,
    title: () => 'Clientes',
    subtitle: () => (isPostventaCustomers.value ? 'Clientes (postventa)' : 'Listado de clientes'),
  },
  {
    matches: () => isPostventaContadores.value,
    component: ContadoresTable,
    title: () => 'Contadores',
    subtitle: () => 'Gestión de contadores',
  },
  {
    matches: () => isPostventaCertificados.value,
    component: CertificadosTable,
    title: () => 'Certificados',
    subtitle: () => 'Gestión de certificados',
  },
  {
    matches: () => isLostLeads.value,
    component: LostLeadsList,
    title: () => 'Desistidos',
    subtitle: () => 'Leads marcados como desistidos',
  },
  {
    matches: () => isWaitingLeads.value,
    component: WaitingLeadsList,
    title: () => 'Zona de espera',
    subtitle: () => 'Leads enviados a zona de espera',
  },
  {
    matches: () => isSettings.value,
    component: SettingsView,
    title: () => 'Configuración',
    subtitle: () => 'Personalización del sistema',
  },
  {
    matches: () => isCalendar.value,
    component: CalendarView,
    title: () => 'Calendario',
    subtitle: () => 'Agenda y recordatorios',
  },
];

const currentStaticView = computed(() => {
  return staticViewDefinitions.find((view) => view.matches()) ?? null;
});

const currentStaticComponent = computed(() => currentStaticView.value?.component ?? null);
const currentStaticProps = computed(() => currentStaticView.value?.props?.() ?? {});

const pageTitle = computed(() =>
  activeDynamicModule.value?.label
    ? activeDynamicModule.value.label
    : currentStaticView.value?.title?.()
      ?? (isPostventaIncidences.value ? 'Incidencias' : 'Dashboard')
);
const pageSubtitle = computed(() =>
  activeDynamicModule.value?.subtitle
    ? activeDynamicModule.value.subtitle
    : currentStaticView.value?.subtitle?.()
      ?? (isPostventaIncidences.value
        ? `Registro y seguimiento de incidencias (vista ${currentView.value === 'board' ? 'kanban' : isBacklog.value ? 'backlog' : 'lista'})`
        : 'Resumen general del sistema')
);

const isPostventa = computed(
  () => isPostventaIncidences.value || isPostventaCustomers.value || isPostventaContadores.value || isPostventaCertificados.value
);

const toggleLeadsView = () => {
  if (!isLeads.value) return;
  window.location.assign(isLeadsBoard.value ? '/leads/list' : '/leads');
};

const togglePostventaView = () => {
  if (isBacklog.value) {
    window.location.assign('/incidencias');
  } else if (isIncidencias.value) {
    if (currentView.value === 'table') {
      window.location.assign('/incidencias/board');
    } else {
      window.location.assign('/backlog');
    }
  }
};

const createRole = () => {
  window.dispatchEvent(new CustomEvent('roles:create'));
};

const createQuickLead = () => {
  window.dispatchEvent(new CustomEvent('leads:create-quick'));
};

const createIncidence = () => {
  window.dispatchEvent(new CustomEvent('incidencias:create'));
};

const createScrumTask = () => {
  window.dispatchEvent(new CustomEvent('scrum-tasks:create'));
};

const toggleScrumView = () => {
  if (!isScrumTasks.value) return;
  const nextView = scrumViewMode.value === 'kanban' ? 'list' : 'kanban';
  window.location.assign(`/scrum/tareas?view=${nextView}`);
};

const createContador = () => {
  window.dispatchEvent(new CustomEvent('contadores:create'));
};

const createCertificado = () => {
  window.dispatchEvent(new CustomEvent('certificados:create'));
};

const sidebarCollapsed = ref(true);
const mobileSidebarOpen = ref(false);

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};

const syncBodyScrollLock = (locked) => {
  document.body.style.overflow = locked ? 'hidden' : '';
};

const closeMobileSidebar = () => {
  mobileSidebarOpen.value = false;
};

const toggleMobileSidebar = () => {
  mobileSidebarOpen.value = !mobileSidebarOpen.value;
};

watch(mobileSidebarOpen, (isOpen) => {
  syncBodyScrollLock(isOpen);
});

onMounted(() => {
  // Theme initialization and other setup can go here if needed
  // No longer using Flowbite
});

onBeforeUnmount(() => {
  syncBodyScrollLock(false);
});
</script>