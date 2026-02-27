<template>
  <div
    class="min-h-screen flex flex-col bg-gradient-to-br from-blue-50/60 via-gray-50 to-gray-50 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900"
  >
    <Header :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="toggleSidebar" />
    <Sidebar :collapsed="sidebarCollapsed" />

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
              v-if="isRoles"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createRole"
            >
              Crear rol
            </button>

            <button
              v-else-if="isLeadsBoard || isLeadsList"
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
              v-else-if="false"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
            >
              Nuevo registro
            </button>

            <button
              v-if="isPostventaIncidences || isPostventaCustomers"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createIncidence"
            >
              Nueva incidencia
            </button>

            <button
              v-else-if="isPostventaContadores"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createContador"
            >
              Nuevo contador
            </button>

            <button
              v-else-if="isPostventaCertificados"
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-300"
              @click="createCertificado"
            >
              Nuevo certificado
            </button>
          </div>

          <UsersTable v-if="isUsers" />

          <RolesTable v-else-if="isRoles" />

          <LeadsBoard v-else-if="isLeadsBoard" />

          <LeadsTable v-else-if="isLeadsList" />

          <LeadsWhatsAppCampaign v-else-if="isLeadsWhatsApp" />

          <LeadsEmailCampaign v-else-if="isLeadsEmail" />

          <IncidenciasTable v-else-if="isIncidencias && currentView === 'table'" />

          <IncidenciasBoard v-else-if="isIncidencias && currentView === 'board'" />

          <BacklogBoard v-else-if="isBacklog" />

          <PostventaCustomersTable v-else-if="isPostventaCustomers || isCustomers" />

          <ContadoresTable v-else-if="isPostventaContadores" />

          <CertificadosTable v-else-if="isPostventaCertificados" />

          <LostLeadsList v-else-if="isLostLeads" />

          <WaitingLeadsList v-else-if="isWaitingLeads" />

          <SettingsView v-else-if="isSettings" />

          <CalendarView v-else-if="isCalendar" />

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
import { computed, ref, defineAsyncComponent, onMounted } from 'vue';

import Header from './Header.vue';
import Sidebar from './Sidebar.vue';
import Footer from './Footer.vue';

// Lazy load heavy components for better performance
const UsersTable = defineAsyncComponent(() => import('./UsersTable.vue'));
const RolesTable = defineAsyncComponent(() => import('./RolesTable.vue'));
const LeadsBoard = defineAsyncComponent(() => import('./LeadsBoard.vue'));
const LeadsTable = defineAsyncComponent(() => import('./LeadsTable.vue'));
const LeadsWhatsAppCampaign = defineAsyncComponent(() => import('./LeadsWhatsAppCampaign.vue'));
const LeadsEmailCampaign = defineAsyncComponent(() => import('./LeadsEmailCampaign.vue'));
const CalendarView = defineAsyncComponent(() => import('./CalendarView.vue'));
const IncidenciasTable = defineAsyncComponent(() => import('./IncidenciasTable.vue'));
const IncidenciasBoard = defineAsyncComponent(() => import('./IncidenciasBoard.vue'));
const BacklogBoard = defineAsyncComponent(() => import('./BacklogBoard.vue'));
const PostventaCustomersTable = defineAsyncComponent(() => import('./PostventaCustomersTable.vue'));
const ContadoresTable = defineAsyncComponent(() => import('./ContadoresTable.vue'));
const CertificadosTable = defineAsyncComponent(() => import('./CertificadosTable.vue'));
const LostLeadsList = defineAsyncComponent(() => import('./LostLeadsList.vue'));
const WaitingLeadsList = defineAsyncComponent(() => import('./WaitingLeadsList.vue'));
const SettingsView = defineAsyncComponent(() => import('./SettingsView.vue'));

// Keep lighter components as regular imports
import DashboardView from './DashboardView.vue';
import IncidenceQuickModal from './IncidenceQuickModal.vue';
import IncidenceEditModal from './IncidenceEditModal.vue';

const normalizedPath = window.location.pathname.replace(/\/+$/, '') || '/';
const isUsers = computed(() => normalizedPath.startsWith('/users'));
const isRoles = computed(() => normalizedPath.startsWith('/roles'));
const isLeadsBoard = computed(() => normalizedPath === '/leads');
const isLeadsList = computed(() => normalizedPath === '/leads/list');
const isLeadsWhatsApp = computed(() => normalizedPath === '/leads/whatsapp');
const isLeadsEmail = computed(() => normalizedPath === '/leads/email');
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
const isPostventaCustomers = computed(() => normalizedPath === '/postventa/clientes');
const isPostventaContadores = computed(() => normalizedPath === '/postventa/contadores');
const isPostventaCertificados = computed(() => normalizedPath === '/postventa/certificados');
const isSettings = computed(() => normalizedPath.startsWith('/configuracion'));

const isPostventaIncidences = computed(() => isIncidencias.value || isBacklog.value);

const pageTitle = computed(() =>
  isUsers.value
    ? 'Usuarios'
    : isRoles.value
      ? 'Roles'
      : isSettings.value
        ? 'Configuración'
      : isLeadsWhatsApp.value
        ? 'WhatsApp'
      : isLeadsEmail.value
        ? 'Email'
      : isLeads.value
        ? 'Leads'
        : normalizedPath === '/desistidos'
          ? 'Desistidos'
          : normalizedPath === '/espera'
            ? 'Zona de espera'
          : isCustomers.value
            ? 'Clientes'
            : isCalendar.value
              ? 'Calendario'
              : isPostventaIncidences.value
                ? 'Incidencias'
                  : isPostventaCustomers.value
                    ? 'Clientes'
                    : isPostventaContadores.value
                      ? 'Contadores'
                      : isPostventaCertificados.value
                        ? 'Certificados'
            : 'Dashboard'
);
const pageSubtitle = computed(() =>
  isUsers.value
    ? 'Gestión de usuarios del sistema'
    : isRoles.value
      ? 'Crea roles y asigna permisos'
      : isSettings.value
        ? 'Personalización del sistema'
      : isLeadsWhatsApp.value
        ? 'Campañas manuales asistidas (sin API)'
      : isLeadsEmail.value
        ? 'Campañas informativas por correo (con baja automática)'
      : isLeadsBoard.value
        ? 'Pipeline de oportunidades'
        : isLeadsList.value
          ? 'Gestión de leads por etapas'
          : normalizedPath === '/desistidos'
            ? 'Leads marcados como desistidos'
            : normalizedPath === '/espera'
              ? 'Leads enviados a zona de espera'
            : isCustomers.value
              ? 'Listado de clientes'
              : isCalendar.value
                ? 'Agenda y recordatorios'
                  : isIncidencias.value
                    ? `Registro y seguimiento de incidencias (vista ${currentView.value === 'board' ? 'kanban' : 'lista'})`
                    : isBacklog.value
                      ? 'Registro y seguimiento de incidencias (vista backlog)'
                    : isPostventaCustomers.value
                      ? 'Clientes (postventa)'
                      : isPostventaContadores.value
                        ? 'Gestión de contadores'
                        : isPostventaCertificados.value
                          ? 'Gestión de certificados'
      : 'Resumen general del sistema'
);

const isPostventa = computed(
  () => isPostventaIncidences.value || isPostventaCustomers.value || isPostventaContadores.value || isPostventaCertificados.value
);

const isLostLeads = computed(() => normalizedPath === '/desistidos');

const isWaitingLeads = computed(() => normalizedPath === '/espera');

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

const createContador = () => {
  window.dispatchEvent(new CustomEvent('contadores:create'));
};

const createCertificado = () => {
  window.dispatchEvent(new CustomEvent('certificados:create'));
};

const sidebarCollapsed = ref(true);
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};

onMounted(() => {
  // Theme initialization and other setup can go here if needed
  // No longer using Flowbite
});
</script>