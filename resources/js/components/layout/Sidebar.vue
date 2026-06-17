<template>
  <aside
    id="logo-sidebar"
    class="fixed top-0 left-0 z-40 h-screen w-72 max-w-[85vw] border-r border-slate-950 bg-slate-900 transition-all duration-200 sm:max-w-none sm:translate-x-0"
    :class="[
      mobileOpen ? 'translate-x-0 shadow-2xl shadow-slate-950/35' : '-translate-x-full',
      compactMode ? 'sm:w-20' : 'sm:w-64'
    ]"
    aria-label="Sidebar"
    :aria-hidden="mobileOpen ? 'false' : undefined"
  >
    <div class="h-full flex flex-col bg-slate-900">
      <div
        class="h-16 px-2 flex items-center bg-sky-600 border-b border-sky-700 overflow-hidden"
        :class="compactMode ? 'justify-center' : 'justify-start'"
      >
        <a href="/dashboard" class="flex items-center w-full gap-2">
          <!-- Collapsed: fixed 40x40 box -->
          <img v-if="compactMode" :src="logoMarkSrc" alt="Atlantis" class="h-14 w-14 object-contain" />

          <!-- Expanded: fixed height, max width; contain to avoid cropping or stretching -->
          <img
            v-else
            :src="logoTextSrc"
            alt="Atlantis"
            class="h-14 w-full max-w-full flex-1 object-contain object-left"
          />
        </a>
      </div>

      <div class="flex-1 px-3 pb-4 overflow-y-auto">
      <div
        class="px-2 pt-2 pb-3 text-xs font-semibold text-slate-300 uppercase tracking-wider"
        v-show="showLabels"
      >
        Menú
      </div>

      <ul class="space-y-2 font-medium">
        <li>
          <a
            href="/dashboard"
            title="Inicio"
            :aria-current="isExact('/dashboard') ? 'page' : undefined"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              compactMode ? 'justify-center' : '',
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
            <span class="ms-3" v-show="showLabels">Inicio</span>
          </a>
        </li>

        <li v-if="canSeeUsers">
          <a
            href="/users"
            title="Usuarios"
            :aria-current="isActive('/users') ? 'page' : undefined"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              compactMode ? 'justify-center' : '',
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
            <span class="flex-1 ms-3 whitespace-nowrap" v-show="showLabels">Usuarios</span>
          </a>
        </li>

        <li v-if="canSeeRoles">
          <a
            href="/roles"
            title="Roles"
            :aria-current="isActive('/roles') ? 'page' : undefined"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              compactMode ? 'justify-center' : '',
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
            <span class="flex-1 ms-3 whitespace-nowrap" v-show="showLabels">Roles</span>
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
                compactMode ? 'justify-center' : '',
                isExact('/leads') || isActive('/desistidos') || isActive('/espera')
                  ? 'text-white bg-slate-800 border border-slate-700'
                  : 'text-slate-200 hover:bg-slate-800'
              ]"
              :aria-expanded="showLabels ? String(pipelineOpen) : undefined"
              @click="onTogglePipeline"
            >
              <svg
                class="w-5 h-5"
                :class="(isExact('/leads') || isActive('/desistidos') || isActive('/espera'))
                  ? 'text-white/90'
                  : 'text-slate-300 group-hover:text-white'"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M3 3h14v4H3V3zm0 6h6v8H3v-8zm8 0h6v8h-6v-8z"></path>
              </svg>

              <span class="flex-1 ms-3 whitespace-nowrap" v-show="showLabels">Pipeline</span>

              <svg
                v-show="showLabels"
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

            <ul v-show="showLabels && pipelineOpen" class="mt-2 space-y-1 pl-2">
              <li>
                <a
                  href="/leads"
                  :aria-current="path === '/leads' ? 'page' : undefined"
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
                  href="/espera"
                  :aria-current="isActive('/espera') ? 'page' : undefined"
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
                  :aria-current="isActive('/desistidos') ? 'page' : undefined"
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

        <li v-if="canSeeInbox">
          <div
            class="relative"
            ref="inboxAnchor"
            @mouseenter="onInboxMouseEnter"
            @mouseleave="onInboxMouseLeave"
          >
            <button
              type="button"
              class="w-full flex items-center p-2 rounded-lg group"
              :class="[
                compactMode ? 'justify-center' : '',
                isActive('/inbox')
                  ? 'text-white bg-slate-800 border border-slate-700'
                  : 'text-slate-200 hover:bg-slate-800'
              ]"
              :aria-expanded="showLabels ? String(inboxOpen) : undefined"
              @click="onToggleInbox"
            >
              <svg
                class="w-5 h-5"
                :class="isActive('/inbox')
                  ? 'text-white/90'
                  : 'text-slate-300 group-hover:text-white'"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M2 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4zm2 0 6 4 6-4H4zm14 2.2-8 5.3-8-5.3V16h16V6.2z"></path>
              </svg>

              <span class="flex-1 ms-3 whitespace-nowrap" v-show="showLabels">Bandeja de envios</span>

              <svg
                v-show="showLabels"
                class="w-4 h-4"
                :class="inboxOpen ? 'rotate-180' : ''"
                aria-hidden="true"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <ul v-show="showLabels && inboxOpen" class="mt-2 space-y-1 pl-2">
              <li v-if="canSeeInbox">
                <a
                  href="/inbox/facturas"
                  :aria-current="path === '/inbox/facturas' ? 'page' : undefined"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    path === '/inbox/facturas' ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Envios de facturas</span>
                </a>
              </li>
              <li v-if="canSeeInbox">
                <a
                  href="/inbox/facturas-preview"
                  :aria-current="path === '/inbox/facturas-preview' ? 'page' : undefined"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    path === '/inbox/facturas-preview' ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Preview rediseño</span>
                </a>
              </li>
              <li v-if="canSeeInbox">
                <a
                  href="/inbox/campanas"
                  :aria-current="path === '/inbox/campanas' ? 'page' : undefined"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    path === '/inbox/campanas' ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Campañas</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li v-if="canSeeCalendar">
          <a
            href="/calendar"
            title="Calendario"
            :aria-current="isActive('/calendar') ? 'page' : undefined"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              compactMode ? 'justify-center' : '',
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
            <span class="flex-1 ms-3 whitespace-nowrap" v-show="showLabels">Calendario</span>
          </a>
        </li>

        <li v-if="canSeeScrum">
          <div
            class="relative"
            ref="scrumAnchor"
            @mouseenter="onScrumMouseEnter"
            @mouseleave="onScrumMouseLeave"
          >
            <button
              type="button"
              class="w-full flex items-center p-2 rounded-lg group"
              :class="[
                compactMode ? 'justify-center' : '',
                isActive('/scrum')
                  ? 'text-white bg-slate-800 border border-slate-700'
                  : 'text-slate-200 hover:bg-slate-800'
              ]"
              :aria-expanded="showLabels ? String(scrumOpen) : undefined"
              @click="onToggleScrum"
            >
              <svg
                class="w-5 h-5"
                :class="isActive('/scrum') ? 'text-white/90' : 'text-slate-300 group-hover:text-white'"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M4 3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h4.5a1 1 0 0 0 0-2H5V5h10v2.5a1 1 0 1 0 2 0V4a1 1 0 0 0-1-1H4Zm7 6a1 1 0 0 0-1 1v2.586l-.293-.293a1 1 0 1 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l2-2a1 1 0 0 0-1.414-1.414L12 12.586V10a1 1 0 0 0-1-1Zm-6 2a1 1 0 0 0 0 2h2.5a1 1 0 1 0 0-2H5Z"
                ></path>
              </svg>

              <span class="flex-1 ms-3 whitespace-nowrap" v-show="showLabels">Scrum</span>

              <svg
                v-show="showLabels"
                class="w-4 h-4"
                :class="scrumOpen ? 'rotate-180' : ''"
                aria-hidden="true"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <ul v-show="showLabels && scrumOpen" class="mt-2 space-y-1 pl-2">
              <li>
                <a
                  href="/scrum/tareas"
                  :aria-current="isActive('/scrum/tareas') ? 'page' : undefined"
                  class="flex items-center gap-3 p-2 rounded-lg group"
                  :class="[
                    'text-slate-200 hover:bg-slate-800',
                    isActive('/scrum/tareas') ? 'bg-slate-800 border border-slate-700' : ''
                  ]"
                >
                  <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                  <span class="whitespace-nowrap">Tareas</span>
                </a>
              </li>
            </ul>
          </div>
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
                compactMode ? 'justify-center' : '',
                isActive('/incidencias') || isActive('/backlog') || isActive('/postventa')
                  ? 'text-white bg-slate-800 border border-slate-700'
                  : 'text-slate-200 hover:bg-slate-800'
              ]"
              :aria-expanded="showLabels ? String(postventaOpen) : undefined"
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

              <span class="flex-1 ms-3 whitespace-nowrap" v-show="showLabels">Postventa</span>

              <svg
                v-show="showLabels"
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

            <ul v-show="showLabels && postventaOpen" class="mt-2 space-y-1 pl-2">
              <li v-if="canSeeIncidencias">
                <a
                  href="/backlog"
                  :aria-current="isActive('/incidencias') || isActive('/backlog') ? 'page' : undefined"
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
              <li v-if="canSeeCustomers">
                <a
                  href="/postventa/clientes"
                  :aria-current="isActive('/postventa/clientes') ? 'page' : undefined"
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
              <li v-if="canSeeContadores">
                <a
                  href="/postventa/contadores"
                  :aria-current="isActive('/postventa/contadores') ? 'page' : undefined"
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
              <li v-if="canSeeCertificados">
                <a
                  href="/postventa/certificados"
                  :aria-current="isActive('/postventa/certificados') ? 'page' : undefined"
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

        <li v-for="module in dynamicMenuModules" :key="module.key || module.path">
          <a
            :href="module.path"
            :title="module.label"
            :aria-current="isActive(module.path) ? 'page' : undefined"
            class="flex items-center p-2 rounded-lg group"
            :class="[
              compactMode ? 'justify-center' : '',
              isActive(module.path)
                ? 'text-white bg-slate-800 border border-slate-700'
                : 'text-slate-200 hover:bg-slate-800'
            ]"
          >
            <svg
              class="w-5 h-5"
              :class="isActive(module.path) ? 'text-white/90' : 'text-slate-300 group-hover:text-white'"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M4 3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8.414a1 1 0 0 0-.293-.707l-4.414-4.414A1 1 0 0 0 11.586 3H4Zm7 1.414L15.586 9H12a1 1 0 0 1-1-1V4.414Z"></path>
            </svg>
            <span class="flex-1 ms-3 whitespace-nowrap" v-show="showLabels">{{ module.label }}</span>
          </a>
        </li>
      </ul>
      </div>
    </div>

    <!-- Collapsed: hover flyout (teleported to body to avoid clipping/stacking issues) -->
    <teleport to="body">
      <div
        v-if="compactMode"
        v-show="scrumHoverOpen"
        class="fixed z-[9999]"
        :style="scrumFlyoutStyle"
        @mouseenter="onScrumMouseEnter"
        @mouseleave="onScrumMouseLeave"
      >
        <div class="min-w-56 bg-slate-900 border border-slate-800 rounded-lg shadow-sm overflow-hidden">
          <div class="px-4 py-3 flex items-center justify-between border-b border-slate-800">
            <div class="text-sm font-semibold text-slate-100">Scrum</div>
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
              <a href="/scrum/tareas" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Tareas</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </teleport>
    <teleport to="body">
      <div
        v-if="compactMode"
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
            <li v-if="canSeeIncidencias">
              <a href="/backlog" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Incidencias</span>
              </a>
            </li>
            <li v-if="canSeeCustomers">
              <a href="/postventa/clientes" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Clientes</span>
              </a>
            </li>
            <li v-if="canSeeContadores">
              <a href="/postventa/contadores" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Contadores</span>
              </a>
            </li>
            <li v-if="canSeeCertificados">
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
        v-if="compactMode"
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

    <teleport to="body">
      <div
        v-if="compactMode"
        v-show="inboxHoverOpen"
        class="fixed z-[9999]"
        :style="inboxFlyoutStyle"
        @mouseenter="onInboxMouseEnter"
        @mouseleave="onInboxMouseLeave"
      >
        <div class="min-w-56 bg-slate-900 border border-slate-800 rounded-lg shadow-sm overflow-hidden">
          <div class="px-4 py-3 flex items-center justify-between border-b border-slate-800">
            <div class="text-sm font-semibold text-slate-100">Bandeja de envios</div>
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
            <li v-if="canSeeInbox">
              <a href="/inbox/facturas" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Envios de facturas</span>
              </a>
            </li>
            <li v-if="canSeeInbox">
              <a href="/inbox/campanas" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span class="w-3 h-3 rounded-full border border-slate-400"></span>
                <span class="whitespace-nowrap">Campañas</span>
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

import logoMark from '../../../images/LOGO.png';
import logoText from '../../../images/LOGO_TEXTO.png';

const appLogoMark = window.__APP_LOGO_MARK__ ?? '';
const appLogoFull = window.__APP_LOGO_FULL__ ?? '';
const logoMarkSrc = computed(() => appLogoMark || logoMark);
const logoTextSrc = computed(() => appLogoFull || logoText);

const path = window.location.pathname;

const isActive = (prefix) => {
  if (prefix === '/dashboard') return path === '/dashboard';
  return path.startsWith(prefix);
};

const isExact = (route) => path === route;

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
  mobileOpen: {
    type: Boolean,
    default: false,
  },
});

const { collapsed, mobileOpen } = toRefs(props);
const compactMode = computed(() => collapsed.value && !mobileOpen.value);
const showLabels = computed(() => !compactMode.value);

const authUser = computed(() => {
  return window.__AUTH_USER__ ?? null;
});

const hasPermission = (permission) => {
  const perms = authUser.value?.permissions;
  return Array.isArray(perms) && perms.includes(permission);
};

const canSeeUsers = computed(() => hasPermission('menu.users') && hasPermission('users.view'));
const canSeeRoles = computed(() => hasPermission('menu.roles') && hasPermission('roles.view'));
const canSeeLeads = computed(() => hasPermission('menu.leads') && hasPermission('leads.view'));
const canSeeInbox = computed(() => hasPermission('menu.inbox'));
const canSeeCalendar = computed(() => hasPermission('calendar.view'));
const canSeeIncidencias = computed(() => hasPermission('incidencias.view'));
const canSeeCustomers = computed(() => hasPermission('customers.view'));
const canSeeContadores = computed(() => hasPermission('contadores.view'));
const canSeeCertificados = computed(() => hasPermission('certificados.view'));
const canSeePostventa = computed(() => {
  if (!hasPermission('menu.postventa')) return false;
  return canSeeIncidencias.value || canSeeCustomers.value || canSeeContadores.value || canSeeCertificados.value;
});
const canSeeScrum = computed(() => Boolean(authUser.value));

const appModules = computed(() => window.__APP_MODULES__ ?? { dynamic: [] });
const canAccessModule = (module) => {
  const menuPermission = String(module?.menu_permission || '').trim();
  const viewPermission = String(module?.view_permission || '').trim();

  if (menuPermission && !hasPermission(menuPermission)) {
    return false;
  }

  if (viewPermission && !hasPermission(viewPermission)) {
    return false;
  }

  return true;
};

const dynamicMenuModules = computed(() =>
  (appModules.value.dynamic ?? []).filter((module) => {
    return module
      && typeof module === 'object'
      && module.auto_menu !== false
      && typeof module.path === 'string'
      && module.path.length > 0
      && typeof module.label === 'string'
      && module.label.length > 0
      && canAccessModule(module);
  })
);

const scrumOpen = ref(isActive('/scrum'));
const scrumHoverOpen = ref(false);
const scrumAnchor = ref(null);
const scrumFlyoutStyle = ref({ left: '0px', top: '0px' });

let scrumHoverCloseTimer = null;
const onScrumMouseEnter = () => {
  if (!collapsed.value) return;
  if (scrumHoverCloseTimer) {
    clearTimeout(scrumHoverCloseTimer);
    scrumHoverCloseTimer = null;
  }

  const rect = scrumAnchor.value?.getBoundingClientRect?.();
  if (rect) {
    scrumFlyoutStyle.value = {
      left: `${Math.round(rect.right + 8)}px`,
      top: `${Math.round(rect.top)}px`,
    };
  }

  scrumHoverOpen.value = true;
};
const onScrumMouseLeave = () => {
  if (!collapsed.value) return;
  if (scrumHoverCloseTimer) clearTimeout(scrumHoverCloseTimer);
  scrumHoverCloseTimer = setTimeout(() => {
    scrumHoverOpen.value = false;
  }, 120);
};

const onToggleScrum = () => {
  if (collapsed.value) {
    window.location.assign('/scrum/tareas');
    return;
  }
  scrumOpen.value = !scrumOpen.value;
};

const postventaOpen = ref(isActive('/incidencias') || isActive('/backlog') || isActive('/postventa'));
const postventaHoverOpen = ref(false);
const postventaAnchor = ref(null);
const postventaFlyoutStyle = ref({ left: '0px', top: '0px' });

let postventaHoverCloseTimer = null;
const onPostventaMouseEnter = () => {
  if (!collapsed.value) return;
  if (postventaHoverCloseTimer) {
    clearTimeout(postventaHoverCloseTimer);
    postventaHoverCloseTimer = null;
  }

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
const pipelineOpen = ref(isExact('/leads') || isActive('/desistidos') || isActive('/espera'));
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

// Inbox submenu state & handlers
const inboxOpen = ref(isActive('/inbox'));
const inboxHoverOpen = ref(false);
const inboxAnchor = ref(null);
const inboxFlyoutStyle = ref({ left: '0px', top: '0px' });

let inboxHoverCloseTimer = null;
const onInboxMouseEnter = () => {
  if (!collapsed.value) return;
  if (inboxHoverCloseTimer) {
    clearTimeout(inboxHoverCloseTimer);
    inboxHoverCloseTimer = null;
  }

  const rect = inboxAnchor.value?.getBoundingClientRect?.();
  if (rect) {
    inboxFlyoutStyle.value = {
      left: `${Math.round(rect.right + 8)}px`,
      top: `${Math.round(rect.top)}px`,
    };
  }

  inboxHoverOpen.value = true;
};
const onInboxMouseLeave = () => {
  if (!collapsed.value) return;
  if (inboxHoverCloseTimer) clearTimeout(inboxHoverCloseTimer);
  inboxHoverCloseTimer = setTimeout(() => {
    inboxHoverOpen.value = false;
  }, 120);
};

const onToggleInbox = () => {
  if (collapsed.value) {
    window.location.assign('/inbox/facturas');
    return;
  }
  inboxOpen.value = !inboxOpen.value;
};
</script>