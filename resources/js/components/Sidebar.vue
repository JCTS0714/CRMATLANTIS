<template>
  <aside
    id="logo-sidebar"
    class="fixed top-0 left-0 z-40 h-screen transition-all duration-200 -translate-x-full bg-slate-900 border-r border-slate-950 sm:translate-x-0"
    :class="collapsed ? 'w-20' : 'w-64'"
    aria-label="Sidebar"
  >
    <div class="h-full flex flex-col bg-slate-900">
      <div
        class="h-16 px-2 flex items-center bg-sky-600 border-b border-sky-700 overflow-hidden"
        :class="collapsed ? 'justify-center' : 'justify-start'"
      >
        <a href="/dashboard" class="flex items-center w-full">
          <img v-if="collapsed" :src="logoMark" alt="Atlantis" class="h-9 w-9 object-contain" />
          <img v-else :src="logoText" alt="Atlantis" class="h-9 w-44 object-cover object-left" />
        </a>
      </div>

      <div class="flex-1 px-3 pb-4 overflow-y-auto">
      <div
        class="px-2 pt-2 pb-3 text-xs font-semibold text-slate-300 uppercase tracking-wider"
        v-show="!collapsed"
      >
        Menú
      </div>

      <ul class="space-y-2 font-medium">
        <li>
          <a
            href="/dashboard"
            title="Inicio"
            aria-current="page"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              collapsed ? 'justify-center' : '',
              isActive('/dashboard')
                ? 'text-white bg-slate-800 border border-slate-700'
                : 'text-slate-200 hover:bg-slate-800'
            ]"
          >
            <svg
              class="w-5 h-5"
              :class="isActive('/dashboard') ? 'text-white/90' : 'text-slate-300 group-hover:text-white'"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M10 2a1 1 0 0 1 .7.3l7 7a1 1 0 0 1-1.4 1.4L16 10.4V17a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1v-3H9v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6.6l-.3.3A1 1 0 1 1 2.3 9.3l7-7A1 1 0 0 1 10 2Z"
              ></path>
            </svg>
            <span class="ms-3" v-show="!collapsed">Inicio</span>
          </a>
        </li>

        <li v-if="canSeeUsers">
          <a
            href="/users"
            title="Usuarios"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              collapsed ? 'justify-center' : '',
              isActive('/users')
                ? 'text-white bg-slate-800 border border-slate-700'
                : 'text-slate-200 hover:bg-slate-800'
            ]"
          >
            <svg
              class="w-5 h-5"
              :class="isActive('/users') ? 'text-white/90' : 'text-slate-300 group-hover:text-white'"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M10 10a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 8a7 7 0 0 1 14 0 1 1 0 0 1-2 0 5 5 0 0 0-10 0 1 1 0 0 1-2 0Z"
              ></path>
            </svg>
            <span class="flex-1 ms-3 whitespace-nowrap" v-show="!collapsed">Usuarios</span>
          </a>
        </li>

        <li v-if="canSeeRoles">
          <a
            href="/roles"
            title="Roles"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              collapsed ? 'justify-center' : '',
              isActive('/roles')
                ? 'text-white bg-slate-800 border border-slate-700'
                : 'text-slate-200 hover:bg-slate-800'
            ]"
          >
            <svg
              class="w-5 h-5"
              :class="isActive('/roles') ? 'text-white/90' : 'text-slate-300 group-hover:text-white'"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M10 2l7 3v5c0 5-3.58 9.74-7 10-3.42-.26-7-5-7-10V5l7-3z"></path>
            </svg>
            <span class="flex-1 ms-3 whitespace-nowrap" v-show="!collapsed">Roles</span>
          </a>
        </li>

        <li v-if="canSeeLeads">
          <div
            class="relative"
            ref="pipelineAnchor"
            @mouseenter="onPipelineMouseEnter"
            @mouseleave="onPipelineMouseLeave"
          >
            <button
              type="button"
              class="w-full flex items-center p-2 rounded-lg group"
              :class="[
                collapsed ? 'justify-center' : '',
                isActive('/leads') || isActive('/leads/whatsapp') || isActive('/leads/email') || isActive('/desistidos') || isActive('/espera')
                  ? 'text-white bg-slate-800 border border-slate-700'
                  : 'text-slate-200 hover:bg-slate-800'
              ]"
              @click="onTogglePipeline"
            >
              <svg
                class="w-5 h-5"
                :class="(isActive('/leads') || isActive('/leads/whatsapp') || isActive('/leads/email') || isActive('/desistidos') || isActive('/espera'))
                  ? 'text-white/90'
                  : 'text-slate-300 group-hover:text-white'"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M3 3h14v4H3V3zm0 6h6v8H3v-8zm8 0h6v8h-6v-8z"></path>
              </svg>

              <span class="flex-1 ms-3 whitespace-nowrap" v-show="!collapsed">Pipeline</span>

              <svg
                v-show="!collapsed"
                class="w-4 h-4"
                :class="pipelineOpen ? 'rotate-180' : ''"
                aria-hidden="true"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <ul v-show="!collapsed && pipelineOpen" class="mt-2 space-y-1 pl-2">
              <li>
                <a
                  href="/leads"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    path === '/leads' ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Leads</span>
                </a>
              </li>
              <li>
                <a
                  href="/leads/whatsapp"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    path === '/leads/whatsapp' ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">WhatsApp masivo</span>
                </a>
              </li>
              <li>
                <a
                  href="/leads/email"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    path === '/leads/email' ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Email masivo</span>
                </a>
              </li>
              <li>
                <a
                  href="/espera"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    isActive('/espera') ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Zona de espera</span>
                </a>
              </li>
              <li>
                <a
                  href="/desistidos"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    isActive('/desistidos') ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Desistidos</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li v-if="canSeeCustomers">
          <a
            href="/customers"
            title="Clientes"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              collapsed ? 'justify-center' : '',
              isActive('/customers')
                ? 'text-white bg-slate-800 border border-slate-700'
                : 'text-slate-200 hover:bg-slate-800'
            ]"
          >
            <svg
              class="w-5 h-5"
              :class="isActive('/customers') ? 'text-white/90' : 'text-slate-300 group-hover:text-white'"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M3 21h14v-8H3v8zM5 10h10v11H5V10zM7 3h6v4H7V3zM3 7h14v2H3V7z"></path>
            </svg>
            <span class="flex-1 ms-3 whitespace-nowrap" v-show="!collapsed">Clientes</span>
          </a>
        </li>

        <li v-if="canSeeCalendar">
          <a
            href="/calendar"
            title="Calendario"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              collapsed ? 'justify-center' : '',
              isActive('/calendar')
                ? 'text-white bg-slate-800 border border-slate-700'
                : 'text-slate-200 hover:bg-slate-800'
            ]"
          >
            <svg
              class="w-5 h-5"
              :class="isActive('/calendar') ? 'text-white/90' : 'text-slate-300 group-hover:text-white'"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M6 2a1 1 0 1 0 0 2h8a1 1 0 1 0 0-2H6Z"
              ></path>
              <path
                fill-rule="evenodd"
                d="M4 5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Zm3 4a1 1 0 0 0 0 2h6a1 1 0 1 0 0-2H7Zm0 4a1 1 0 1 0 0 2h4a1 1 0 1 0 0-2H7Z"
                clip-rule="evenodd"
              ></path>
            </svg>
            <span class="flex-1 ms-3 whitespace-nowrap" v-show="!collapsed">Calendario</span>
          </a>
        </li>

        <li v-if="canSeePostventa">
          <div
            class="relative"
            ref="postventaAnchor"
            @mouseenter="onPostventaMouseEnter"
            @mouseleave="onPostventaMouseLeave"
          >
            <button
              type="button"
              class="w-full flex items-center p-2 rounded-lg group"
              :class="[
                collapsed ? 'justify-center' : '',
                isActive('/incidencias') || isActive('/backlog') || isActive('/postventa')
                  ? 'text-white bg-slate-800 border border-slate-700'
                  : 'text-slate-200 hover:bg-slate-800'
              ]"
              @click="onTogglePostventa"
            >
              <svg
                class="w-5 h-5"
                :class="(isActive('/incidencias') || isActive('/backlog') || isActive('/postventa'))
                  ? 'text-white/90'
                  : 'text-slate-300 group-hover:text-white'"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M11.3 1.046a1 1 0 0 0-2.6 0l-.2.682a6.96 6.96 0 0 0-1.18.49l-.63-.35a1 1 0 0 0-1.24.22l-.9.9a1 1 0 0 0-.22 1.24l.35.63a6.96 6.96 0 0 0-.49 1.18l-.682.2a1 1 0 0 0 0 2.6l.682.2c.12.42.28.82.49 1.2l-.35.63a1 1 0 0 0 .22 1.24l.9.9a1 1 0 0 0 1.24.22l.63-.35c.38.2.78.37 1.2.49l.2.682a1 1 0 0 0 2.6 0l.2-.682c.42-.12.82-.28 1.2-.49l.63.35a1 1 0 0 0 1.24-.22l.9-.9a1 1 0 0 0 .22-1.24l-.35-.63c.2-.38.37-.78.49-1.2l.682-.2a1 1 0 0 0 0-2.6l-.682-.2a6.96 6.96 0 0 0-.49-1.18l.35-.63a1 1 0 0 0-.22-1.24l-.9-.9a1 1 0 0 0-1.24-.22l-.63.35c-.38-.2-.78-.37-1.2-.49l-.2-.682ZM10 13a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"
                ></path>
              </svg>

              <span class="flex-1 ms-3 whitespace-nowrap" v-show="!collapsed">Postventa</span>

              <svg
                v-show="!collapsed"
                class="w-4 h-4"
                :class="postventaOpen ? 'rotate-180' : ''"
                aria-hidden="true"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <ul v-show="!collapsed && postventaOpen" class="mt-2 space-y-1 pl-2">
              <li>
                <a
                  href="/backlog"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    isActive('/incidencias') || isActive('/backlog') ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Incidencias</span>
                </a>
              </li>
              <li>
                <a
                  href="/postventa/clientes"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    isActive('/postventa/clientes') ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Clientes</span>
                </a>
              </li>
              <li>
                <a
                  href="/postventa/contadores"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    isActive('/postventa/contadores') ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Contadores</span>
                </a>
              </li>
              <li>
                <a
                  href="/postventa/certificados"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    isActive('/postventa/certificados') ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Certificados</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
      </ul>
      </div>
    </div>

    <!-- Collapsed: hover flyout (teleported to body to avoid clipping/stacking issues) -->
    <teleport to="body">
      <div
        v-if="collapsed"
        v-show="postventaHoverOpen"
        class="fixed z-[9999]"
        :style="postventaFlyoutStyle"
        @mouseenter="onPostventaMouseEnter"
        @mouseleave="onPostventaMouseLeave"
      >
        <div class="min-w-56 bg-slate-900 border border-slate-800 rounded-lg shadow-sm overflow-hidden">
          <div class="px-4 py-3 flex items-center justify-between border-b border-slate-800">
            <div class="text-sm font-semibold text-slate-100">Postventa</div>
            <svg
              class="w-4 h-4 text-slate-300"
              aria-hidden="true"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>

          <ul class="py-2">
            <li>
              <a href="/backlog" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Incidencias</span>
              </a>
            </li>
            <li>
              <a href="/postventa/clientes" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Clientes</span>
              </a>
            </li>
            <li>
              <a href="/postventa/contadores" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Contadores</span>
              </a>
            </li>
            <li>
              <a href="/postventa/certificados" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Certificados</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </teleport>
    <teleport to="body">
      <div
        v-if="collapsed"
        v-show="pipelineHoverOpen"
        class="fixed z-[9999]"
        :style="pipelineFlyoutStyle"
        @mouseenter="onPipelineMouseEnter"
        @mouseleave="onPipelineMouseLeave"
      >
        <div class="min-w-56 bg-slate-900 border border-slate-800 rounded-lg shadow-sm overflow-hidden">
          <div class="px-4 py-3 flex items-center justify-between border-b border-slate-800">
            <div class="text-sm font-semibold text-slate-100">Pipeline</div>
            <svg
              class="w-4 h-4 text-slate-300"
              aria-hidden="true"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>

          <ul class="py-2">
            <li>
              <a href="/leads" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Leads</span>
              </a>
            </li>
            <li>
              <a href="/leads/whatsapp" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">WhatsApp masivo</span>
              </a>
            </li>
            <li>
              <a href="/espera" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Zona de espera</span>
              </a>
            </li>
            <li>
              <a href="/desistidos" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Desistidos</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </teleport>
  </aside>
</template>

<script setup>
import { computed, ref, toRefs } from 'vue';

import logoMark from '../../images/LOGO.png';
import logoText from '../../images/LOGO_TEXTO.png';

const path = window.location.pathname;

const isActive = (prefix) => {
  if (prefix === '/dashboard') return path === '/dashboard';
  return path.startsWith(prefix);
};

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
});

const { collapsed } = toRefs(props);

const authUser = computed(() => {
  return window.__AUTH_USER__ ?? null;
});

const hasPermission = (permission) => {
  const perms = authUser.value?.permissions;
  return Array.isArray(perms) && perms.includes(permission);
};

const canSeeUsers = computed(() => hasPermission('menu.users'));
const canSeeRoles = computed(() => hasPermission('menu.roles'));
const canSeeLeads = computed(() => hasPermission('menu.leads'));
const canSeeEmail = computed(() => hasPermission('menu.email') || hasPermission('menu.leads'));
const canSeeCustomers = computed(() => hasPermission('menu.customers'));
const canSeeCalendar = computed(() => hasPermission('menu.calendar') || hasPermission('calendar.view'));
const canSeePostventa = computed(() => hasPermission('menu.postventa'));
              <li v-if="canSeeEmail">
                <a
                  href="/leads/email"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    path === '/leads/email' ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Email masivo</span>
                </a>
              </li>

  const rect = postventaAnchor.value?.getBoundingClientRect?.();
  if (rect) {
    postventaFlyoutStyle.value = {
      left: `${Math.round(rect.right + 8)}px`,
      top: `${Math.round(rect.top)}px`,
    };
  }

  postventaHoverOpen.value = true;
};
const onPostventaMouseLeave = () => {
  if (!collapsed.value) return;
  if (postventaHoverCloseTimer) clearTimeout(postventaHoverCloseTimer);
  postventaHoverCloseTimer = setTimeout(() => {
    postventaHoverOpen.value = false;
  }, 120);
};

const onTogglePostventa = () => {
  if (collapsed.value) {
    window.location.assign('/backlog');
    return;
  }
  postventaOpen.value = !postventaOpen.value;
};

// Pipeline submenu state & handlers
const pipelineOpen = ref(isActive('/leads') || isActive('/leads/whatsapp') || isActive('/leads/email') || isActive('/desistidos') || isActive('/espera'));
const pipelineHoverOpen = ref(false);
const pipelineAnchor = ref(null);
const pipelineFlyoutStyle = ref({ left: '0px', top: '0px' });

let pipelineHoverCloseTimer = null;
const onPipelineMouseEnter = () => {
  if (!collapsed.value) return;
  if (pipelineHoverCloseTimer) {
    clearTimeout(pipelineHoverCloseTimer);
    pipelineHoverCloseTimer = null;
  }

  const rect = pipelineAnchor.value?.getBoundingClientRect?.();
  if (rect) {
    pipelineFlyoutStyle.value = {
      left: `${Math.round(rect.right + 8)}px`,
      top: `${Math.round(rect.top)}px`,
    };
  }

  pipelineHoverOpen.value = true;
};
const onPipelineMouseLeave = () => {
  if (!collapsed.value) return;
  if (pipelineHoverCloseTimer) clearTimeout(pipelineHoverCloseTimer);
  pipelineHoverCloseTimer = setTimeout(() => {
    pipelineHoverOpen.value = false;
  }, 120);
};

const onTogglePipeline = () => {
  if (collapsed.value) {
    window.location.assign('/leads');
    return;
  }
  pipelineOpen.value = !pipelineOpen.value;
};
</script>