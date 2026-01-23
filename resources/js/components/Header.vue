<template>
  <nav
    class="fixed top-0 right-0 z-50 h-16 bg-sky-600 border-b border-sky-700 shadow-sm"
    :class="sidebarCollapsed ? 'left-0 sm:left-20' : 'left-0 sm:left-64'"
  >
    <div class="h-full px-3 lg:px-5 lg:pl-3">
      <div class="h-full flex items-center justify-between">
        <div class="flex items-center justify-start rtl:justify-end">
          <button
            data-drawer-target="logo-sidebar"
            data-drawer-toggle="logo-sidebar"
            aria-controls="logo-sidebar"
            type="button"
            class="inline-flex items-center p-2 text-sm text-white/90 rounded-lg sm:hidden hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-white/40"
          >
            <span class="sr-only">Abrir sidebar</span>
            <svg
              class="w-6 h-6"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                clip-rule="evenodd"
                fill-rule="evenodd"
                d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75Zm0 10.5a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Z"
              ></path>
            </svg>
          </button>

          <button
            type="button"
            class="hidden sm:inline-flex items-center p-2 text-sm text-white/90 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-white/40"
            @click="$emit('toggle-sidebar')"
          >
            <span class="sr-only">Contraer/expandir sidebar</span>
            <svg
              v-if="!sidebarCollapsed"
              class="w-6 h-6"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M3 4a1 1 0 0 1 1-1h8a1 1 0 1 1 0 2H5v10h7a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1V4Zm12.293 2.293a1 1 0 0 1 1.414 0l2 2a1 1 0 0 1 0 1.414l-2 2a1 1 0 1 1-1.414-1.414L15.586 10l-.293-.293a1 1 0 0 1 0-1.414ZM9 10a1 1 0 0 1 1-1h7a1 1 0 1 1 0 2h-7a1 1 0 0 1-1-1Z"
                clip-rule="evenodd"
              ></path>
            </svg>
            <svg
              v-else
              class="w-6 h-6"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M3 4a1 1 0 0 1 1-1h8a1 1 0 1 1 0 2H5v10h7a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1V4Zm12.707 2.293a1 1 0 0 1 0 1.414L15.414 8H9a1 1 0 1 0 0 2h6.414l.293.293a1 1 0 0 1 0 1.414l-2 2a1 1 0 1 0 1.414 1.414l2-2a1 1 0 0 0 0-1.414L16.414 10l.707-.707a1 1 0 0 0 0-1.414l-2-2a1 1 0 0 0-1.414 1.414l2 2Z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </button>

        </div>

        <div class="flex items-center gap-2">
          <div class="relative">
            <button
              type="button"
              class="inline-flex items-center justify-center w-9 h-9 text-sm text-white/90 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-white/40"
              aria-expanded="false"
              data-dropdown-toggle="notifications-dropdown"
              data-dropdown-placement="bottom-end"
              aria-label="Notificaciones"
            >
              <svg
                class="w-5 h-5"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M10 2a6 6 0 0 0-6 6v2.586l-.707.707A1 1 0 0 0 3 13h14a1 1 0 0 0 .707-1.707L17 10.586V8a6 6 0 0 0-6-6Zm0 16a2.5 2.5 0 0 0 2.45-2H7.55A2.5 2.5 0 0 0 10 18Z"
                />
              </svg>

              <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-white text-sky-700 text-[10px] font-semibold flex items-center justify-center ring-1 ring-sky-700"
              >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
              </span>
            </button>

            <div
              id="notifications-dropdown"
              class="z-50 hidden my-4 w-80 max-w-[90vw] text-sm bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-slate-900 dark:divide-slate-800"
            >
              <div class="px-4 py-3">
                <p class="text-sm font-semibold text-gray-900 dark:text-slate-100">Notificaciones</p>
                <p class="text-xs text-gray-500 dark:text-slate-300">No leídas: {{ unreadCount }}</p>
              </div>

              <ul class="py-2 max-h-72 overflow-auto">
                <li v-if="notifications.length === 0" class="px-4 py-2 text-gray-500 dark:text-slate-300">
                  No hay notificaciones nuevas.
                </li>
                <li v-for="n in notifications" :key="n.id">
                  <button
                    type="button"
                    class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-slate-800"
                    @click="openNotification(n)"
                  >
                    <p class="text-sm text-gray-900 dark:text-slate-100">
                      {{ n?.data?.title ?? 'Notificación' }}
                    </p>
                    <p v-if="n?.data?.message" class="text-xs text-gray-500 dark:text-slate-300">
                      {{ n.data.message }}
                    </p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-400">
                      {{ formatNotifTime(n?.created_at) }}
                    </p>
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <button
            type="button"
            class="inline-flex items-center justify-center w-9 h-9 text-sm text-white/90 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-white/40"
            :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
            @click="onToggleTheme"
          >
            <svg
              v-if="isDark"
              class="w-5 h-5"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M10 2a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm4.95 3.05a1 1 0 0 1 0 1.41l-.7.7a1 1 0 1 1-1.41-1.41l.7-.7a1 1 0 0 1 1.41 0ZM17 9a1 1 0 1 1 0 2h-1a1 1 0 1 1 0-2h1ZM10 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM4 9a1 1 0 1 0 0 2H3a1 1 0 1 0 0-2h1Zm2.16-3.54a1 1 0 0 1 1.41 0l.7.7a1 1 0 0 1-1.41 1.41l-.7-.7a1 1 0 0 1 0-1.41ZM10 16a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1Zm5.54.46a1 1 0 0 1 0 1.41l-.7.7a1 1 0 1 1-1.41-1.41l.7-.7a1 1 0 0 1 1.41 0ZM6.57 15.76a1 1 0 0 1 0 1.41l-.7.7a1 1 0 1 1-1.41-1.41l.7-.7a1 1 0 0 1 1.41 0Z"
              />
            </svg>

            <svg
              v-else
              class="w-5 h-5"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M17.293 13.293A8 8 0 0 1 6.707 2.707a1 1 0 0 1 1.11-1.66A10 10 0 1 0 18.953 12.183a1 1 0 0 1-1.66 1.11Z"
              />
            </svg>
          </button>

          <div class="flex items-center ms-3">
            <button
              type="button"
              class="flex text-sm rounded-full ring-1 ring-sky-700 hover:ring-white/60 focus:ring-4 focus:ring-white/30"
              aria-expanded="false"
              data-dropdown-toggle="user-dropdown"
              data-dropdown-placement="bottom"
            >
              <span class="sr-only">Abrir menú de usuario</span>
              <img
                v-if="userPhotoUrl"
                :src="userPhotoUrl"
                :alt="userName"
                class="w-8 h-8 rounded-full object-cover bg-white ring-1 ring-sky-700"
                loading="lazy"
              />
              <div
                v-else
                class="w-8 h-8 rounded-full bg-white text-sky-700 flex items-center justify-center text-xs font-semibold"
              >
                {{ userInitials }}
              </div>
            </button>

            <div
              id="user-dropdown"
              class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-slate-900 dark:divide-slate-800"
            >
              <div class="px-4 py-3">
                <p class="text-sm text-gray-900 dark:text-slate-100">{{ userName }}</p>
                <p class="text-sm font-medium text-gray-500 truncate dark:text-slate-300">{{ userEmail }}</p>
              </div>
              <ul class="py-2">
                <li>
                  <a
                    href="/profile"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-slate-200 dark:hover:bg-slate-800"
                  >
                    Perfil
                  </a>
                </li>
                <li>
                  <a
                    href="/configuracion"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-slate-200 dark:hover:bg-slate-800"
                  >
                    Configuración
                  </a>
                </li>
                <li>
                  <form method="POST" action="/logout" class="block">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <button
                      type="submit"
                      class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                      Salir
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { toggleTheme } from '../theme';
import axios from 'axios';
import { toastInfo } from '../ui/alerts';

defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['toggle-sidebar']);

const csrfToken =
  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const appLogoMark = window.__APP_LOGO_MARK__ ?? '';

const authUser = computed(() => {
  // Injected by the Blade wrapper (dashboard)
  return window.__AUTH_USER__ ?? null;
});

const userName = computed(() => {
  return authUser.value?.name ?? 'Usuario';
});

const userEmail = computed(() => {
  return authUser.value?.email ?? '';
});

const userPhotoUrl = computed(() => {
  return authUser.value?.profile_photo_url ?? '';
});

const userInitials = computed(() => {
  const name = String(authUser.value?.name ?? '').trim();
  if (!name) return 'U';

  const parts = name
    .split(/\s+/)
    .map((p) => p.trim())
    .filter(Boolean);

  if (parts.length === 0) return 'U';

  const first = Array.from(parts[0])[0] ?? '';
  const last = parts.length > 1 ? Array.from(parts[parts.length - 1])[0] ?? '' : '';
  const initials = `${first}${last}`.trim();

  return (initials || 'U').toLocaleUpperCase('es-ES');
});

const isDark = ref(false);

const notifications = ref([]);
const unreadCount = ref(0);
let notifTimer = null;
let lastSeenIds = new Set();

const loadNotifications = async ({ toastNew = false } = {}) => {
  try {
    const res = await axios.get('/notifications');
    const data = res?.data?.data;
    const list = data?.notifications ?? [];
    const count = data?.count ?? 0;

    if (toastNew) {
      const newOnes = list.filter((n) => !lastSeenIds.has(n.id));
      for (const n of newOnes.slice(0, 2)) {
        const title = n?.data?.title ? `Recordatorio: ${n.data.title}` : 'Nuevo recordatorio';
        toastInfo(title);
      }
    }

    notifications.value = list;
    unreadCount.value = Number(count) || 0;
    lastSeenIds = new Set(list.map((n) => n.id));
  } catch {
    // silent
  }
};

const openNotification = async (n) => {
  if (!n?.id) return;
  try {
    await axios.post(`/notifications/${n.id}/read`);
  } catch {
    // ignore
  }
  const url = n?.data?.url;
  if (url) window.location.assign(url);
  else loadNotifications({ toastNew: false });
};

const formatNotifTime = (createdAt) => {
  if (!createdAt) return '';
  const d = new Date(createdAt);
  if (Number.isNaN(d.getTime())) return String(createdAt);
  return d.toLocaleString();
};

const syncTheme = () => {
  isDark.value = document.documentElement.classList.contains('dark');
};

const onToggleTheme = () => {
  const newTheme = toggleTheme();
  isDark.value = newTheme === 'dark';
};

onMounted(() => {
  syncTheme();
  window.addEventListener('theme:changed', syncTheme);

  loadNotifications({ toastNew: false });
  notifTimer = setInterval(() => loadNotifications({ toastNew: true }), 30000);
});

onBeforeUnmount(() => {
  window.removeEventListener('theme:changed', syncTheme);
  if (notifTimer) clearInterval(notifTimer);
});
</script>