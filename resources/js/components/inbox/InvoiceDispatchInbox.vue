<template>
  <section class="space-y-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
        <div class="space-y-3">
          <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-200">
            Operaciones de envio
          </span>
          <div>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Bandeja de facturacion</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
              Centraliza plantillas, preparacion de archivos, diagnosticos y envios en una sola cola operativa.
              La vista activa ahora es <span class="font-semibold text-slate-900 dark:text-slate-100">{{ currentTabLabel }}</span>.
            </p>
          </div>
          <div class="flex flex-wrap gap-2 text-xs">
            <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ filteredRows.length }} casos visibles</span>
            <span class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">{{ readyRows.length }} listos para enviar</span>
            <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ selectedRowCount }} seleccionados</span>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:w-[360px]">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Canal Kapso</div>
            <div class="mt-2 text-sm font-semibold" :class="kapsoCardClass">
              {{ kapsoStatus.configured ? 'Configurado y operativo' : 'Pendiente de configurar' }}
            </div>
            <p v-if="kapsoMissing.length" class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
              Faltan: {{ kapsoMissing.join(', ') }}
            </p>
          </div>
          <a
            href="/inbox/facturas-preview"
            class="rounded-2xl border border-violet-200 bg-violet-50 p-4 text-left transition hover:bg-violet-100 dark:border-violet-900/40 dark:bg-violet-950/30"
          >
            <div class="text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-200">Preview UX</div>
            <div class="mt-2 text-sm font-semibold text-violet-900 dark:text-violet-100">Ver direccion del rediseño</div>
            <p class="mt-1 text-xs leading-5 text-violet-800/80 dark:text-violet-200/80">Compara la vista actual con el flujo unificado propuesto.</p>
          </a>
        </div>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pendientes</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <div class="text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ stats.pendientes }}</div>
          <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950/30 dark:text-amber-200">Por preparar</span>
        </div>
      </article>
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Facturas preparadas</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <div class="text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ stats.preparadas }}</div>
          <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/30 dark:text-blue-200">Archivo listo</span>
        </div>
      </article>
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Listos para API</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <div class="text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ stats.listasApi }}</div>
          <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">Sin bloqueos</span>
        </div>
      </article>
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Seleccion actual</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <div class="text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ selectionMode ? selectedRowCount : 0 }}</div>
          <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="selectionMode ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'">
            {{ selectionMode ? 'Modo activo' : 'Modo inactivo' }}
          </span>
        </div>
      </article>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
      <div class="grid gap-5 border-b border-slate-200 p-5 dark:border-slate-800 xl:grid-cols-[minmax(0,1fr),minmax(280px,0.95fr)]">
        <div>
          <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Acciones principales</div>
          <div class="mt-3 flex flex-wrap gap-2">
            <button
              type="button"
              class="inline-flex items-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 disabled:opacity-50"
              :disabled="loading"
              @click="loadRows"
            >
              {{ loading ? 'Cargando...' : 'Refrescar bandeja' }}
            </button>

            <button
              type="button"
              class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 disabled:opacity-50"
              :disabled="syncing"
              @click="syncCurrentMonth"
            >
              {{ syncing ? 'Sincronizando...' : 'Generar pendientes del mes a cobrar' }}
            </button>

            <button
              type="button"
              class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:opacity-50"
              :disabled="bulkSendModal.processing || readyRows.length === 0"
              @click="openBulkSendModal('whatsapp')"
            >
              {{ bulkSendModal.processing ? 'Procesando...' : `Enviar PDF masivo (${readyRows.length})` }}
            </button>

            <button
              type="button"
              class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="askKapsoTest"
            >
              Probar Kapso
            </button>

            <button
              type="button"
              class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="toggleSelectionMode"
            >
              {{ selectionMode ? 'Desactivar seleccion' : 'Activar seleccion' }}
            </button>

            <button
              v-if="selectionMode"
              type="button"
              class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="selectAllVisibleRows"
            >
              Seleccionar visibles
            </button>

            <button
              v-if="selectionMode"
              type="button"
              class="inline-flex items-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 shadow-sm hover:bg-rose-100 focus:outline-none focus:ring-4 focus:ring-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-200"
              @click="clearSelectedRows"
            >
              Limpiar seleccion
            </button>
          </div>
        </div>

        <div>
          <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Filtros operativos</div>
          <div class="mt-3 flex flex-wrap gap-2">
            <select
              v-model="filters.pagoEstado"
              class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            >
              <option value="">Estado pago: todos</option>
              <option value="pendiente">Pendiente</option>
              <option value="factura_enviada">Factura enviada</option>
              <option value="pagado">Pagado</option>
              <option value="inactivo">Inactivo</option>
            </select>

            <select
              v-model.number="filters.mes"
              class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            >
              <option :value="0">Mes: todos</option>
              <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>

            <select
              v-model="filters.servidor"
              class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            >
              <option value="">Servidor: todos</option>
              <option v-for="server in serverOptions" :key="`filter-server-${server}`" :value="server">{{ server }}</option>
            </select>

            <button
              type="button"
              class="inline-flex items-center rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-200"
              @click="loadRows"
            >
              Aplicar filtros
            </button>
          </div>
        </div>
      </div>

      <div v-if="selectionMode" class="border-b border-slate-200 bg-slate-50/80 px-5 py-3 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="flex flex-wrap items-center gap-2 text-xs">
          <span class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 font-medium text-white dark:bg-slate-100 dark:text-slate-900">
            Seleccionados: {{ selectedRowCount }}
          </span>
          <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">
            Listos para enviar: {{ selectedReadyRows.length }}
          </span>
        </div>
      </div>

      <div v-if="globalError" class="border-b border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
        {{ globalError }}
      </div>

      <div v-if="globalInfo" class="border-b border-emerald-200 bg-emerald-50 px-5 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ globalInfo }}
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
      <div class="border-b border-slate-200 p-4 dark:border-slate-800">
        <nav class="flex flex-wrap gap-2" aria-label="Pestañas bandeja de envios">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-medium transition"
            :class="activeTab === tab.key
              ? 'bg-slate-900 text-white shadow-sm dark:bg-slate-100 dark:text-slate-900'
              : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'"
            @click="activeTab = tab.key"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <div class="p-5">
        <section v-if="activeTab === 'plantilla'" class="space-y-4">
          <div class="grid gap-4 xl:grid-cols-[minmax(0,1.1fr),minmax(0,0.9fr)]">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Gestor de plantillas</h3>
                  <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Selecciona, edita o crea mensajes base sin perder el contexto de la bandeja.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                  <span class="rounded-full bg-white px-3 py-1 font-medium text-slate-700 shadow-sm dark:bg-slate-900 dark:text-slate-200">{{ templates.length }} plantillas</span>
                  <span class="rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-700 dark:bg-blue-950/30 dark:text-blue-200">{{ filteredRows.length }} casos filtrados</span>
                </div>
              </div>

              <div class="mt-4 grid gap-3 lg:grid-cols-6">
                <label class="text-sm text-gray-700 dark:text-slate-200 lg:col-span-3">
                  Plantilla activa
                  <select
                    v-model.number="selectedTemplateId"
                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                    @change="onTemplateSelected"
                  >
                    <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">
                      {{ tpl.nombre }}{{ tpl.is_default ? ' (predeterminada)' : '' }}
                    </option>
                  </select>
                </label>

                <div class="flex flex-wrap items-end gap-2 lg:col-span-3">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200"
                    @click="openTemplateEditor('create')"
                  >
                    Nueva plantilla
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    :disabled="!activeTemplate"
                    @click="openTemplateEditor('edit')"
                  >
                    Editar plantilla
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 shadow-sm hover:bg-rose-100 focus:outline-none focus:ring-4 focus:ring-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-200"
                    :disabled="!activeTemplate"
                    @click="deleteActiveTemplate"
                  >
                    Eliminar plantilla
                  </button>
                </div>
              </div>

              <div v-if="templateEditor.open" class="mt-4 grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <label class="text-sm text-gray-700 dark:text-slate-200">
                  Nombre
                  <input
                    v-model="templateEditor.form.nombre"
                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                    placeholder="Ej: Recordatorio estándar"
                  />
                </label>

                <label class="text-sm text-gray-700 dark:text-slate-200">
                  Contenido
                  <textarea
                    v-model="templateEditor.form.contenido"
                    rows="5"
                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                  />
                </label>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-slate-200">
                  <input v-model="templateEditor.form.is_default" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600" />
                  Marcar como predeterminada
                </label>

                <div class="flex flex-wrap gap-2">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200"
                    :disabled="templateEditor.saving"
                    @click="saveTemplate"
                  >
                    {{ templateEditor.saving ? 'Guardando...' : 'Guardar plantilla' }}
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    @click="closeTemplateEditor"
                  >
                    Cancelar
                  </button>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Preview y alcance</h3>
                  <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Revisa el mensaje final antes de aplicarlo a toda la cola o a una selección puntual.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                  <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">Seleccionados: {{ selectedRowCount }}</span>
                  <span class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">Listos: {{ readyRows.length }}</span>
                </div>
              </div>

              <div v-if="activeTemplate" class="mt-4 space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                  <div class="font-medium text-slate-900 dark:text-slate-100">Contenido de la plantilla activa</div>
                  <div class="mt-3 whitespace-pre-wrap leading-6">{{ activeTemplate.contenido }}</div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-950">
                  <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Variables disponibles</div>
                  <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200" v-pre>{{cliente}}</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200" v-pre>{{comercio}}</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200" v-pre>{{mes}}</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200" v-pre>{{anio}}</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200" v-pre>{{precio}}</span>
                  </div>
                </div>

                <div v-if="templatePreviewRow" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-100">
                  <div class="text-xs font-semibold uppercase tracking-wide">Preview ejemplo</div>
                  <div class="mt-3 leading-6">{{ renderTemplateWithContent(activeTemplate.contenido, templatePreviewRow) }}</div>
                </div>

                <div class="flex flex-wrap gap-2">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-200"
                    :disabled="!activeTemplate"
                    @click="applyActiveTemplate(false)"
                  >
                    Aplicar a todos los filtrados ({{ filteredRows.length }})
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 shadow-sm hover:bg-indigo-100 focus:outline-none focus:ring-4 focus:ring-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/30 dark:text-indigo-200"
                    :disabled="!activeTemplate || selectedRowCount === 0"
                    @click="applyActiveTemplate(true)"
                  >
                    Aplicar a seleccionados ({{ selectedRowCount }})
                  </button>
                </div>
              </div>

              <div v-else class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-400">
                Selecciona o crea una plantilla para habilitar el preview y la aplicación masiva.
              </div>
            </div>
          </div>
        </section>

        <section v-else-if="activeTab === 'archivos'" class="space-y-3">
          <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/40 dark:bg-blue-950/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
              <div>
                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100">Carga masiva de comprobantes</h3>
                <p class="mt-1 text-xs leading-5 text-blue-800/80 dark:text-blue-200/80">
                  Selecciona todos los PDFs de una sola vez. Se emparejan por nombre de comercio (sin extension) y se preparan automaticamente.
                </p>
                <p v-if="filters.servidor" class="mt-2 text-xs font-medium text-blue-900 dark:text-blue-100">
                  Filtro activo: {{ filters.servidor }}
                </p>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <input
                  ref="bulkFileInput"
                  type="file"
                  multiple
                  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                  class="hidden"
                  @change="onBulkFilesSelected"
                />
                <button
                  type="button"
                  class="inline-flex items-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 disabled:opacity-50"
                  :disabled="bulkPreparing"
                  @click="triggerBulkUpload"
                >
                  {{ bulkPreparing ? `Preparando ${bulkProgress.done}/${bulkProgress.total}...` : 'Seleccionar PDFs masivos' }}
                </button>
              </div>
            </div>

            <div v-if="bulkSummary.visible" class="mt-4 space-y-3 rounded-xl border border-blue-200 bg-white p-4 text-sm dark:border-blue-900/40 dark:bg-slate-950">
              <div class="flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">
                  Preparadas: {{ bulkSummary.prepared }}
                </span>
                <span class="rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700 dark:bg-amber-950/30 dark:text-amber-200">
                  Sin coincidencia: {{ bulkSummary.unmatched }}
                </span>
                <span v-if="bulkSummary.failed > 0" class="rounded-full bg-rose-50 px-3 py-1 font-medium text-rose-700 dark:bg-rose-950/30 dark:text-rose-200">
                  Con error: {{ bulkSummary.failed }}
                </span>
              </div>

              <ul v-if="bulkSummary.unmatchedFiles.length" class="list-disc space-y-1 pl-5 text-xs text-slate-600 dark:text-slate-300">
                <li v-for="item in bulkSummary.unmatchedFiles" :key="`unmatched-${item.fileName}`">
                  {{ item.fileName }} — {{ item.reason }}
                </li>
              </ul>

              <ul v-if="bulkSummary.errors.length" class="list-disc space-y-1 pl-5 text-xs text-rose-700 dark:text-rose-200">
                <li v-for="item in bulkSummary.errors" :key="`bulk-error-${item.fileName}`">
                  {{ item.fileName }} — {{ item.message }}
                </li>
              </ul>
            </div>
          </div>

          <article
            v-for="row in filteredRows"
            :key="`pdf-${row.id}`"
            class="rounded-lg border border-gray-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"
          >
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <div class="flex items-center gap-2">
                  <input
                    v-if="selectionMode"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600"
                    :checked="isRowSelected(row.id)"
                    @change="setRowSelected(row.id, $event.target.checked)"
                  />
                  <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ clientName(row) }}</div>
                </div>
                <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
                  {{ row.cliente?.contact_name || 'Sin contacto' }} · {{ row.cliente?.contact_phone || 'Sin celular' }} · {{ row.cliente?.contact_email || 'Sin email' }}
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">
                  Pago #{{ row.id }} · {{ monthLabel(row.mes) }} {{ row.anio }}
                </div>
              </div>
              <span class="rounded-full px-2 py-1 text-xs font-medium" :class="badgeClass(row.envio?.estado)">
                {{ row.envio?.estado || 'sin preparar' }}
              </span>
            </div>

            <div class="mt-3 grid gap-3 lg:grid-cols-3">
              <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-slate-200">Archivo PDF</label>
                <input
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                  class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:text-slate-200 dark:file:bg-slate-800 dark:file:text-slate-100"
                  @change="onPickFile(row.id, $event)"
                />
              </div>
              <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-slate-200">Mensaje para este cliente</label>
                <textarea
                  v-model="drafts[row.id].mensajeTemplate"
                  rows="3"
                  class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
                />
              </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 disabled:opacity-50"
                :disabled="busy[row.id]"
                @click="preparar(row)"
              >
                Preparar factura
              </button>
              <a
                v-if="preparedData[row.id]?.facturaUrl"
                :href="preparedData[row.id].facturaUrl"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              >
                Ver archivo público
              </a>
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="openEditClientModal(row)"
              >
                Editar cliente
              </button>
            </div>

            <div v-if="rowMessages[row.id]" class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
              {{ rowMessages[row.id] }}
            </div>
            <div v-if="rowDiagnostics[row.id]?.length" class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
              <div class="font-medium">Diagnostico:</div>
              <ul class="mt-1 list-disc pl-5">
                <li v-for="code in rowDiagnostics[row.id]" :key="code">{{ diagnosticLabel(code) }}</li>
              </ul>
            </div>
          </article>
        </section>

        <section v-else-if="activeTab === 'envios'" class="space-y-3">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">Acciones masivas</div>
                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                  Envía los PDFs masivos desde esta cola con seguimiento de progreso y errores en un modal.
                </p>
              </div>

              <div class="flex flex-wrap gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:opacity-50"
                  :disabled="bulkSendModal.processing || readyRows.length === 0"
                  @click="openBulkSendModal('whatsapp')"
                >
                  {{ bulkSendModal.processing ? 'Procesando...' : `Enviar PDF masivo (${readyRows.length})` }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="selectionMode" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <div class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">Acción masiva en contexto</div>
                <p class="mt-1 text-xs leading-5 text-emerald-800/80 dark:text-emerald-200/80">
                  Usa la selección actual para disparar WhatsApp, email o cierre manual sin salir del flujo de envíos.
                </p>
              </div>

              <div class="flex flex-wrap gap-2">
                <button
                  type="button"
                  class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:opacity-50"
                  :disabled="bulkSending || selectedReadyRows.length === 0"
                  @click="runBulkSend('whatsapp')"
                >
                  {{ bulkSending ? 'Procesando...' : `Enviar WA a seleccionados (${selectedReadyRows.length})` }}
                </button>

                <button
                  type="button"
                  class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:opacity-50"
                  :disabled="bulkSending || bulkSendModal.processing || selectedReadyRows.length === 0"
                  @click="openBulkSendModal('whatsapp')"
                >
                  {{ bulkSending || bulkSendModal.processing ? 'Procesando...' : `Enviar PDF masivo (${selectedReadyRows.length})` }}
                </button>

                <button
                  type="button"
                  class="inline-flex items-center rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 shadow-sm hover:bg-indigo-100 focus:outline-none focus:ring-4 focus:ring-indigo-100 disabled:opacity-50 dark:border-indigo-900/40 dark:bg-indigo-950/30 dark:text-indigo-200"
                  :disabled="bulkSending || bulkSendModal.processing || selectedReadyRows.length === 0"
                  @click="runBulkSend('email')"
                >
                  {{ bulkSending || bulkSendModal.processing ? 'Procesando...' : `Enviar Email a seleccionados (${selectedReadyRows.length})` }}
                </button>

                <button
                  type="button"
                  class="inline-flex items-center rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 shadow-sm hover:bg-amber-100 focus:outline-none focus:ring-4 focus:ring-amber-100 disabled:opacity-50 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200"
                  :disabled="bulkSending || selectedReadyRows.length === 0"
                  @click="runBulkSend('manual')"
                >
                  {{ bulkSending ? 'Procesando...' : `Marcar seleccionados (${selectedReadyRows.length})` }}
                </button>
              </div>
            </div>
          </div>

          <article
            v-for="row in readyRows"
            :key="`send-${row.id}`"
            class="rounded-lg border border-gray-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"
          >
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <div class="flex items-center gap-2">
                  <input
                    v-if="selectionMode"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600"
                    :checked="isRowSelected(row.id)"
                    @change="setRowSelected(row.id, $event.target.checked)"
                  />
                  <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ clientName(row) }}</div>
                </div>
                <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
                  {{ row.cliente?.contact_phone || 'Sin celular' }} · {{ row.cliente?.contact_email || 'Sin email' }}
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-slate-400">
                  Mensaje: {{ preparedData[row.id]?.mensaje || drafts[row.id]?.mensajeTemplate || '-' }}
                </div>
              </div>
              <div class="flex flex-wrap gap-2">
                <span class="rounded-full px-2 py-1 text-xs font-medium" :class="badgeClass(row.envio?.estado)">
                  {{ row.envio?.estado || 'sin preparar' }}
                </span>
                <span v-if="row.envio?.fechaEnviado" class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-slate-800 dark:text-slate-200">
                  Enviado: {{ formatDate(row.envio.fechaEnviado) }}
                </span>
              </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:opacity-50"
                :disabled="busy[row.id]"
                @click="enviarWhatsapp(row)"
              >
                Enviar archivo WA
              </button>
              <a
                :href="manualLink(row)"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              >
                Abrir WA manual
              </a>
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 shadow-sm hover:bg-indigo-100 focus:outline-none focus:ring-4 focus:ring-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/30 dark:text-indigo-200"
                :disabled="busy[row.id]"
                @click="enviarEmail(row)"
              >
                Enviar email
              </button>
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 shadow-sm hover:bg-amber-100 focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200"
                :disabled="busy[row.id]"
                @click="marcarManual(row)"
              >
                Marcar enviada
              </button>
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="openEditClientModal(row)"
              >
                Editar cliente
              </button>
            </div>

            <div v-if="rowMessages[row.id]" class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
              {{ rowMessages[row.id] }}
            </div>
            <div v-if="rowDiagnostics[row.id]?.length" class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
              <div class="font-medium">Diagnostico:</div>
              <ul class="mt-1 list-disc pl-5">
                <li v-for="code in rowDiagnostics[row.id]" :key="code">{{ diagnosticLabel(code) }}</li>
              </ul>
            </div>
          </article>

          <div v-if="readyRows.length === 0" class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-gray-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
            Aún no hay clientes listos para enviar. Ve a la pestaña "Preparar PDF" y prepara al menos una factura para habilitar esta cola.
          </div>
        </section>

        <div
          v-if="bulkSendModal.open"
          class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
          @click.self="closeBulkSendModal"
        >
          <div class="w-full max-w-2xl rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-xl">
            <div class="flex items-center justify-between gap-3">
              <div>
                <h3 class="text-lg font-semibold text-white">Envio masivo de PDF</h3>
                <p class="mt-2 text-sm text-slate-300">
                  {{ bulkSendModal.processing ? 'Enviando facturas...' : 'Envio masivo completado.' }}
                </p>
              </div>
              <button
                type="button"
                class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-200 hover:bg-slate-700 disabled:opacity-50"
                :disabled="bulkSendModal.processing"
                @click="closeBulkSendModal"
              >
                Cerrar
              </button>
            </div>

            <div class="mt-4 rounded-2xl border border-slate-700 bg-slate-950 p-4">
              <div class="mb-3 flex items-center justify-between gap-3 text-sm text-slate-300">
                <span>Modo:</span>
                <span class="font-medium text-white">{{ bulkSendModal.mode === 'whatsapp' ? 'WhatsApp API' : bulkSendModal.mode === 'email' ? 'Email' : 'Manual' }}</span>
              </div>
              <div class="h-3 overflow-hidden rounded-full bg-slate-800">
                <div
                  class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                  :style="{ width: bulkSendModal.total ? `${Math.round((bulkSendModal.progress / bulkSendModal.total) * 100)}%` : '0%' }"
                ></div>
              </div>
              <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-300">
                <span>{{ bulkSendModal.progress }} / {{ bulkSendModal.total }} procesados</span>
                <span>{{ bulkSendModal.completed }} exitosos · {{ bulkSendModal.failed }} fallidos</span>
              </div>
              <div v-if="bulkSendModal.currentClient" class="mt-2 text-sm text-slate-300">
                Enviando a: <span class="font-medium text-white">{{ bulkSendModal.currentClient }}</span>
              </div>
            </div>

            <div v-if="bulkSendModal.errors.length" class="mt-4 rounded-2xl border border-rose-700 bg-rose-950/30 p-4 text-sm text-rose-200">
              <div class="font-semibold text-white">Errores durante el envio</div>
              <ul class="mt-3 space-y-2">
                <li v-for="error in bulkSendModal.errors" :key="`bulk-error-${error.id}`" class="rounded-xl border border-rose-700 bg-rose-950/50 p-3">
                  <div class="font-medium text-white">{{ error.clientName }}</div>
                  <div class="mt-1 text-sm text-rose-200">{{ error.message }}</div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <section v-else-if="activeTab === 'clientes'" class="space-y-3">
          <div class="grid gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-slate-700 dark:bg-slate-800/40 lg:grid-cols-6">
            <input
              v-model="clientFilters.search"
              class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
              placeholder="Buscar cliente, comercio, contacto..."
            />

            <select
              v-model="clientFilters.servidor"
              class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            >
              <option value="">Servidor: todos</option>
              <option v-for="server in clientServerOptions" :key="`client-server-${server}`" :value="server">{{ server }}</option>
            </select>

            <select
              v-model="clientFilters.pagoEstado"
              class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            >
              <option value="">Estado pago: todos</option>
              <option value="pendiente">Pendiente</option>
              <option value="factura_enviada">Factura enviada</option>
              <option value="pagado">Pagado</option>
              <option value="inactivo">Inactivo</option>
            </select>

            <select
              v-model.number="clientFilters.mes"
              class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            >
              <option :value="0">Mes: todos</option>
              <option v-for="m in monthOptions" :key="`client-filter-${m.value}`" :value="m.value">{{ m.label }}</option>
            </select>

            <input
              v-model="clientFilters.anio"
              type="number"
              min="2000"
              max="2100"
              class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
              placeholder="Año"
            />

            <select
              v-model="clientFilters.estadoFactura"
              class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            >
              <option value="">Estado factura: todos</option>
              <option value="factura_pendiente">Factura pendiente</option>
              <option value="factura_enviada">Factura enviada</option>
            </select>
          </div>

          <article
            v-for="clientRow in filteredClientRows"
            :key="`client-${clientRow.key}`"
            class="rounded-lg border border-gray-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"
          >
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div>
                <div class="flex items-center gap-2">
                  <input
                    v-if="selectionMode"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600"
                    :checked="isRowSelected(clientRow.row?.id)"
                    @change="setRowSelected(clientRow.row?.id, $event.target.checked)"
                  />
                  <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ clientRow.cliente?.company_name || clientRow.cliente?.name || 'Cliente' }}</div>
                </div>
                <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
                  {{ clientRow.cliente?.contact_name || 'Sin contacto' }} · {{ clientRow.cliente?.contact_phone || 'Sin celular' }} · {{ clientRow.cliente?.contact_email || 'Sin email' }}
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">
                  Último período: {{ monthLabel(clientRow.row?.mes) }} {{ clientRow.row?.anio }} · Estado factura: {{ clientRow.row?.estado || 'sin dato' }}
                </div>
              </div>

              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="openEditClientModal(clientRow.row)"
              >
                Editar
              </button>
            </div>

            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
              <label class="text-xs text-gray-600 dark:text-slate-300">
                Estado de pago (rápido)
                <select
                  :value="clientRow.cliente?.pago_estado || 'pendiente'"
                  :disabled="!!quickStateSaving[clientRow.key]"
                  class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                  @change="updateClientPaymentState(clientRow, $event.target.value)"
                >
                  <option value="pendiente">Pendiente</option>
                  <option value="factura_enviada">Factura enviada</option>
                  <option value="pagado">Pagado</option>
                  <option value="inactivo">Inactivo</option>
                </select>
              </label>

              <div class="text-xs text-gray-600 dark:text-slate-300">
                Mes pagado
                <div class="mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-100">
                  {{ clientRow.cliente?.mes_pagado ? monthLabel(clientRow.cliente.mes_pagado) : '-' }}
                </div>
              </div>

              <div class="text-xs text-gray-600 dark:text-slate-300">
                Mes por pagar
                <div class="mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-100">
                  {{ clientRow.cliente?.mes_por_pagar ? monthLabel(clientRow.cliente.mes_por_pagar) : '-' }}
                </div>
              </div>
            </div>
          </article>
        </section>

        <div v-if="!loading && rows.length === 0" class="mt-4 rounded-lg border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
          No hay pagos mensuales cargados. Usa "Generar pendientes del mes a cobrar" para crear la lista de clientes.
        </div>

        <div v-else-if="activeTab === 'clientes' && !loading && filteredClientRows.length === 0" class="mt-4 rounded-lg border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
          No hay clientes para los filtros seleccionados.
        </div>

        <div v-else-if="activeTab !== 'clientes' && !loading && filteredRows.length === 0" class="mt-4 rounded-lg border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
          No hay resultados para los filtros seleccionados.
        </div>
      </div>
    </div>

    <div
      v-if="editClientModal.open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
      @click.self="closeEditClientModal"
    >
      <div class="w-full max-w-2xl rounded-lg border border-slate-700 bg-slate-900 p-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-700 pb-3">
          <h3 class="text-base font-semibold text-slate-100">Editar cliente</h3>
          <button type="button" class="rounded px-2 py-1 text-slate-300 hover:bg-slate-800" @click="closeEditClientModal">X</button>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          <label class="text-sm text-slate-200">
            Nombre
            <input v-model="editClientModal.form.name" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
          </label>
          <label class="text-sm text-slate-200">
            Comercio
            <input v-model="editClientModal.form.company_name" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
          </label>
          <label class="text-sm text-slate-200">
            Contacto
            <input v-model="editClientModal.form.contact_name" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
          </label>
          <label class="text-sm text-slate-200">
            Teléfono
            <input v-model="editClientModal.form.contact_phone" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
          </label>
          <label class="text-sm text-slate-200">
            Email
            <input v-model="editClientModal.form.contact_email" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
          </label>
          <label class="text-sm text-slate-200">
            Precio
            <input v-model.number="editClientModal.form.precio" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
          </label>
          <label class="text-sm text-slate-200">
            Estado pago
            <select v-model="editClientModal.form.pago_estado" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100">
              <option value="pendiente">Pendiente</option>
              <option value="factura_enviada">Factura enviada</option>
              <option value="pagado">Pagado</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </label>
          <label class="text-sm text-slate-200">
            Mes pagado
            <select v-model.number="editClientModal.form.mes_pagado" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100">
              <option :value="null">(vacío)</option>
              <option v-for="m in monthOptions" :key="`paid-${m.value}`" :value="m.value">{{ m.label }}</option>
            </select>
          </label>
          <label class="text-sm text-slate-200">
            Mes por pagar
            <select v-model.number="editClientModal.form.mes_por_pagar" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100">
              <option :value="null">(vacío)</option>
              <option v-for="m in monthOptions" :key="`due-${m.value}`" :value="m.value">{{ m.label }}</option>
            </select>
          </label>
        </div>

        <div v-if="editClientModal.error" class="mt-3 rounded-lg border border-red-900/40 bg-red-950/30 p-3 text-sm text-red-200">
          {{ editClientModal.error }}
        </div>

        <div class="mt-4 flex justify-end gap-2">
          <button type="button" class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200" @click="closeEditClientModal">Cancelar</button>
          <button
            type="button"
            class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            :disabled="editClientModal.saving"
            @click="saveClientChanges"
          >
            {{ editClientModal.saving ? 'Guardando...' : 'Guardar cliente' }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const rows = ref([]);
const loading = ref(false);
const syncing = ref(false);
const activeTab = ref('plantilla');
const globalError = ref('');
const globalInfo = ref('');
const autoSyncedOnEmpty = ref(false);
const filters = reactive({
  pagoEstado: '',
  mes: 0,
  servidor: '',
});

const kapsoStatus = ref({ enabled: false, configured: false, missing: [] });

const drafts = reactive({});
const busy = reactive({});
const rowMessages = reactive({});
const rowDiagnostics = reactive({});
const preparedData = reactive({});

const kapsoMissing = computed(() => kapsoStatus.value?.missing || []);
const kapsoCardClass = computed(() => (kapsoStatus.value?.configured ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300'));
const tabs = [
  { key: 'plantilla', label: 'Plantilla mensaje' },
  { key: 'archivos', label: 'Preparar PDF' },
  { key: 'envios', label: 'Envios y preview' },
  { key: 'clientes', label: 'Clientes' },
];
const currentTabLabel = computed(() => tabs.find((tab) => tab.key === activeTab.value)?.label || 'Bandeja');

const defaultTemplate = ref('Hola {{cliente}}, te compartimos tu factura de {{mes}}/{{anio}}.');
const monthOptions = [
  { value: 1, label: 'Enero' },
  { value: 2, label: 'Febrero' },
  { value: 3, label: 'Marzo' },
  { value: 4, label: 'Abril' },
  { value: 5, label: 'Mayo' },
  { value: 6, label: 'Junio' },
  { value: 7, label: 'Julio' },
  { value: 8, label: 'Agosto' },
  { value: 9, label: 'Septiembre' },
  { value: 10, label: 'Octubre' },
  { value: 11, label: 'Noviembre' },
  { value: 12, label: 'Diciembre' },
];
const KNOWN_SERVER_OPTIONS = ['ATLANTIS ONLINE', 'ATLANTIS VIP', 'ATLANTIS POS', 'ATLANTIS FAST', 'LORITO'];
const editClientModal = reactive({
  open: false,
  clienteId: null,
  rowId: null,
  saving: false,
  error: '',
  form: {
    name: '',
    company_name: '',
    contact_name: '',
    contact_phone: '',
    contact_email: '',
    precio: null,
    pago_estado: 'pendiente',
    mes_pagado: null,
    mes_por_pagar: null,
  },
});
const clientFilters = reactive({
  search: '',
  servidor: '',
  pagoEstado: '',
  mes: 0,
  anio: '',
  estadoFactura: '',
});
const quickStateSaving = reactive({});
const selectionMode = ref(false);
const selectedRowIds = ref([]);
const bulkSending = ref(false);
const bulkFileInput = ref(null);
const bulkPreparing = ref(false);
const bulkProgress = reactive({ done: 0, total: 0 });
const bulkSummary = reactive({
  visible: false,
  prepared: 0,
  unmatched: 0,
  failed: 0,
  unmatchedFiles: [],
  errors: [],
});

const bulkSendModal = reactive({
  open: false,
  processing: false,
  mode: 'whatsapp',
  progress: 0,
  total: 0,
  completed: 0,
  failed: 0,
  errors: [],
  currentClient: '',
  rows: [],
});

const templates = ref([]);
const selectedTemplateId = ref(null);
const templateEditor = reactive({
  open: false,
  mode: 'create',
  saving: false,
  form: {
    id: null,
    nombre: '',
    contenido: 'Hola {{cliente}}, te compartimos tu factura de {{mes}}/{{anio}}.',
    is_default: false,
  },
});

const filteredRows = computed(() => rows.value.filter((row) => {
  const customer = row?.cliente || {};
  if (filters.pagoEstado && (customer.pago_estado || 'pendiente') !== filters.pagoEstado) {
    return false;
  }

  if (filters.servidor && String(customer.servidor || '').trim() !== filters.servidor) {
    return false;
  }

  if (Number(filters.mes) > 0 && Number(row?.mes) !== Number(filters.mes)) {
    return false;
  }

  return true;
}));

const serverOptions = computed(() => {
  const dynamicValues = rows.value
    .map((row) => String(row?.cliente?.servidor || '').trim())
    .filter((value) => value !== '');

  return [...new Set([...KNOWN_SERVER_OPTIONS, ...dynamicValues])];
});

const stats = computed(() => {
  const pendientes = filteredRows.value.filter((r) => r.estado === 'factura_pendiente').length;
  const preparadas = filteredRows.value.filter((r) => r.envio?.estado === 'preparado').length;
  const listasApi = filteredRows.value.filter((r) => (rowDiagnostics[r.id] || []).length === 0 && !!preparedData[r.id]?.facturaUrl).length;
  return { pendientes, preparadas, listasApi };
});

function extractRows(payload) {
  const source = payload?.data;
  if (Array.isArray(source)) {
    return source;
  }

  if (Array.isArray(source?.data)) {
    return source.data;
  }

  return [];
}

const readyRows = computed(() => filteredRows.value.filter((row) => {
  const hasPreparedFile = !!preparedData[row.id]?.facturaUrl || !!row.envio?.archivoUrl;
  const diagnostics = rowDiagnostics[row.id] || [];
  return hasPreparedFile && diagnostics.length === 0;
}));

const uniqueClientRows = computed(() => {
  const seen = new Set();
  const result = [];

  for (const row of rows.value) {
    const customerId = Number(row?.cliente?.id || 0);
    if (!customerId || seen.has(customerId)) {
      continue;
    }

    seen.add(customerId);
    result.push({
      key: customerId,
      row,
      cliente: row.cliente,
    });
  }

  return result;
});

const clientServerOptions = computed(() => {
  const dynamicValues = uniqueClientRows.value
    .map((entry) => String(entry?.cliente?.servidor || '').trim())
    .filter((value) => value !== '');

  return [...new Set([...KNOWN_SERVER_OPTIONS, ...dynamicValues])];
});

const filteredClientRows = computed(() => {
  const search = (clientFilters.search || '').trim().toLowerCase();
  const filterYear = Number(clientFilters.anio || 0);

  return uniqueClientRows.value.filter((entry) => {
    const row = entry.row || {};
    const customer = entry.cliente || {};

    if (search) {
      const haystack = [
        customer.company_name,
        customer.name,
        customer.contact_name,
        customer.contact_phone,
        customer.contact_email,
      ].map((value) => String(value || '').toLowerCase());

      if (!haystack.some((value) => value.includes(search))) {
        return false;
      }
    }

    if (clientFilters.pagoEstado && (customer.pago_estado || 'pendiente') !== clientFilters.pagoEstado) {
      return false;
    }

    if (clientFilters.servidor && String(customer.servidor || '').trim() !== clientFilters.servidor) {
      return false;
    }

    if (Number(clientFilters.mes) > 0 && Number(row.mes) !== Number(clientFilters.mes)) {
      return false;
    }

    if (filterYear > 0 && Number(row.anio) !== filterYear) {
      return false;
    }

    if (clientFilters.estadoFactura && row.estado !== clientFilters.estadoFactura) {
      return false;
    }

    return true;
  });
});

const activeTemplate = computed(() => templates.value.find((tpl) => tpl.id === Number(selectedTemplateId.value || 0)) || null);
const selectedRowCount = computed(() => selectedRowIds.value.length);
const selectedReadyRows = computed(() => readyRows.value.filter((row) => selectedRowIds.value.includes(row.id)));
const visibleRowsForSelection = computed(() => {
  if (activeTab.value === 'clientes') {
    return filteredClientRows.value.map((entry) => entry.row).filter(Boolean);
  }

  if (activeTab.value === 'envios') {
    return readyRows.value;
  }

  return filteredRows.value;
});
const templatePreviewRow = computed(() => {
  if (selectedRowIds.value.length > 0) {
    const target = rows.value.find((row) => selectedRowIds.value.includes(row.id));
    if (target) {
      return target;
    }
  }

  return filteredRows.value[0] || null;
});

function ensureDraft(pagoId) {
  if (!drafts[pagoId]) {
    drafts[pagoId] = {
      mensajeTemplate: 'Hola {{cliente}}, te compartimos tu factura de {{mes}}/{{anio}}.',
      archivo: null,
    };
  }
}

function monthLabel(monthNumber) {
  const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
  return months[(Number(monthNumber) || 1) - 1] || String(monthNumber);
}

function badgeClass(status) {
  if (status === 'enviado') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200';
  if (status === 'preparado') return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200';
  return 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-200';
}

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString();
}

function diagnosticLabel(code) {
  const map = {
    celular_invalido: 'El celular no es valido para WhatsApp API.',
    kapso_no_configurado: 'Kapso no esta configurado en variables de entorno.',
    public_base_url_no_publica: 'PUBLIC_BASE_URL no es publica (usa dominio HTTPS real).',
  };
  return map[code] || code;
}

function buildApiErrorMessage(error, fallbackMessage) {
  const data = error?.response?.data || {};
  const parts = [];

  if (data.error_code) {
    parts.push(`[${data.error_code}]`);
  }

  const baseMessage = String(data.message || '').trim();
  if (baseMessage) {
    parts.push(baseMessage);
  }

  const validationErrors = Object.values(data.errors || {})
    .flatMap((messages) => (Array.isArray(messages) ? messages : []))
    .map((message) => String(message || '').trim())
    .filter((message) => message !== '');

  if (validationErrors.length > 0) {
    parts.push(validationErrors.join(' '));
  }

  if (Array.isArray(data.diagnostic_details) && data.diagnostic_details.length > 0) {
    const diagnosticDetails = data.diagnostic_details
      .map((item) => String(item?.message || item?.code || '').trim())
      .filter((item) => item !== '');

    if (diagnosticDetails.length > 0) {
      parts.push(`Diagnostico: ${diagnosticDetails.join(' | ')}`);
    }
  }

  const detailError = String(data?.details?.error || '').trim();
  if (detailError) {
    parts.push(`Detalle: ${detailError}`);
  }

  if (parts.length > 0) {
    return parts.join(' ');
  }

  return fallbackMessage;
}

function normalizeComparableName(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '');
}

function getExpectedNameForRow(row) {
  return normalizeComparableName(row?.cliente?.company_name || row?.cliente?.name || '');
}

function getFileBaseName(file) {
  const rawName = String(file?.name || '');
  const dotIndex = rawName.lastIndexOf('.');
  const baseName = dotIndex > 0 ? rawName.slice(0, dotIndex) : rawName;
  return normalizeComparableName(baseName);
}

function resetBulkSummary() {
  bulkSummary.visible = false;
  bulkSummary.prepared = 0;
  bulkSummary.unmatched = 0;
  bulkSummary.failed = 0;
  bulkSummary.unmatchedFiles = [];
  bulkSummary.errors = [];
}

function triggerBulkUpload() {
  bulkFileInput.value?.click();
}

function matchBulkFiles(files) {
  const targetRows = filteredRows.value.filter((row) => ['factura_pendiente', 'factura_enviada'].includes(row.estado));
  const rowsByName = new Map();

  for (const row of targetRows) {
    const key = getExpectedNameForRow(row);
    if (!key) {
      continue;
    }

    if (!rowsByName.has(key)) {
      rowsByName.set(key, []);
    }

    rowsByName.get(key).push(row);
  }

  const matches = [];
  const unmatched = [];
  const usedRowIds = new Set();

  for (const file of files) {
    const key = getFileBaseName(file);
    const candidates = rowsByName.get(key) || [];

    if (candidates.length === 0) {
      unmatched.push({
        fileName: file.name,
        reason: 'Sin cliente coincidente en la bandeja filtrada',
      });
      continue;
    }

    const row = candidates.find((candidate) => !usedRowIds.has(candidate.id)) || null;
    if (!row) {
      unmatched.push({
        fileName: file.name,
        reason: 'Ya existe otro archivo asignado para ese comercio',
      });
      continue;
    }

    usedRowIds.add(row.id);
    matches.push({ row, file });
  }

  return { matches, unmatched };
}

async function onBulkFilesSelected(event) {
  const files = Array.from(event?.target?.files || []);
  if (event?.target) {
    event.target.value = '';
  }

  if (files.length === 0) {
    return;
  }

  resetBulkSummary();
  bulkSummary.visible = true;

  const { matches, unmatched } = matchBulkFiles(files);
  bulkSummary.unmatched = unmatched.length;
  bulkSummary.unmatchedFiles = unmatched;

  if (matches.length === 0) {
    globalInfo.value = 'No se encontraron coincidencias para preparar facturas.';
    return;
  }

  if (activeTemplate.value) {
    for (const { row } of matches) {
      ensureDraft(row.id);
      drafts[row.id].mensajeTemplate = activeTemplate.value.contenido;
    }
  }

  bulkPreparing.value = true;
  bulkProgress.done = 0;
  bulkProgress.total = matches.length;
  globalError.value = '';
  globalInfo.value = `Preparando ${matches.length} factura(s)...`;

  for (const { row, file } of matches) {
    ensureDraft(row.id);
    drafts[row.id].archivo = file;

    const result = await preparar(row, { reload: false, silent: true });
    bulkProgress.done += 1;

    if (result.ok) {
      bulkSummary.prepared += 1;
      continue;
    }

    bulkSummary.failed += 1;
    bulkSummary.errors.push({
      fileName: file.name,
      message: result.message,
    });
  }

  await loadRows();

  bulkPreparing.value = false;
  globalInfo.value = `Carga masiva completada: ${bulkSummary.prepared} preparada(s), ${bulkSummary.unmatched} sin coincidencia, ${bulkSummary.failed} con error.`;
}

function onPickFile(pagoId, event) {
  ensureDraft(pagoId);
  const files = event?.target?.files;
  drafts[pagoId].archivo = files && files[0] ? files[0] : null;
}

function clientName(row) {
  return row?.cliente?.company_name || row?.cliente?.name || 'Cliente';
}

function renderTemplateWithContent(template, row) {
  return String(template || '')
    .replaceAll('{{cliente}}', row?.cliente?.contact_name || row?.cliente?.name || 'cliente')
    .replaceAll('{{comercio}}', row?.cliente?.company_name || row?.cliente?.name || 'comercio')
    .replaceAll('{{mes}}', String(row?.mes || ''))
    .replaceAll('{{anio}}', String(row?.anio || ''))
    .replaceAll('{{precio}}', String(row?.cliente?.precio || ''));
}

function renderTemplatePreview(row) {
  const template = drafts[row.id]?.mensajeTemplate || defaultTemplate.value;
  return renderTemplateWithContent(template, row);
}

function manualLink(row) {
  return preparedData[row.id]?.whatsappUrl || (row?.cliente?.contact_phone
    ? `https://wa.me/${String(row.cliente.contact_phone).replace(/\D+/g, '')}`
    : '#');
}

function isRowSelected(rowId) {
  return selectedRowIds.value.includes(Number(rowId || 0));
}

function setRowSelected(rowId, selected) {
  const id = Number(rowId || 0);
  if (!id) {
    return;
  }

  if (selected && !selectedRowIds.value.includes(id)) {
    selectedRowIds.value = [...selectedRowIds.value, id];
    return;
  }

  if (!selected) {
    selectedRowIds.value = selectedRowIds.value.filter((value) => value !== id);
  }
}

function clearSelectedRows() {
  selectedRowIds.value = [];
}

function toggleSelectionMode() {
  selectionMode.value = !selectionMode.value;
  if (!selectionMode.value) {
    clearSelectedRows();
  }
}

function selectAllVisibleRows() {
  const ids = visibleRowsForSelection.value
    .map((row) => Number(row?.id || 0))
    .filter((id) => id > 0);

  selectedRowIds.value = [...new Set(ids)];
}

async function loadTemplates() {
  const response = await axios.get('/api/facturas/plantillas');
  templates.value = Array.isArray(response?.data?.data) ? response.data.data : [];

  const preferred = templates.value.find((tpl) => tpl.is_default) || templates.value[0] || null;
  selectedTemplateId.value = preferred ? preferred.id : null;
  defaultTemplate.value = preferred?.contenido || defaultTemplate.value;
}

function onTemplateSelected() {
  const selected = activeTemplate.value;
  if (selected) {
    defaultTemplate.value = selected.contenido;
  }
}

function openTemplateEditor(mode) {
  if (mode === 'edit' && !activeTemplate.value) {
    globalError.value = 'Selecciona una plantilla para editar.';
    return;
  }

  templateEditor.open = true;
  templateEditor.mode = mode;
  templateEditor.saving = false;

  if (mode === 'edit') {
    templateEditor.form = {
      id: activeTemplate.value.id,
      nombre: activeTemplate.value.nombre,
      contenido: activeTemplate.value.contenido,
      is_default: !!activeTemplate.value.is_default,
    };
    return;
  }

  templateEditor.form = {
    id: null,
    nombre: '',
    contenido: defaultTemplate.value || 'Hola {{cliente}}, te compartimos tu factura de {{mes}}/{{anio}}.',
    is_default: templates.value.length === 0,
  };
}

function closeTemplateEditor() {
  templateEditor.open = false;
}

async function saveTemplate() {
  const nombre = String(templateEditor.form.nombre || '').trim();
  const contenido = String(templateEditor.form.contenido || '').trim();

  if (!nombre || !contenido) {
    globalError.value = 'Nombre y contenido de plantilla son obligatorios.';
    return;
  }

  templateEditor.saving = true;
  globalError.value = '';

  try {
    const payload = {
      nombre,
      contenido,
      is_default: !!templateEditor.form.is_default,
    };

    if (templateEditor.mode === 'edit' && templateEditor.form.id) {
      await axios.put(`/api/facturas/plantillas/${templateEditor.form.id}`, payload);
      globalInfo.value = 'Plantilla actualizada correctamente.';
    } else {
      await axios.post('/api/facturas/plantillas', payload);
      globalInfo.value = 'Plantilla creada correctamente.';
    }

    await loadTemplates();
    closeTemplateEditor();
  } catch (error) {
    globalError.value = error?.response?.data?.message || 'No se pudo guardar la plantilla.';
  } finally {
    templateEditor.saving = false;
  }
}

async function deleteActiveTemplate() {
  if (!activeTemplate.value) {
    return;
  }

  const confirmed = window.confirm(`¿Eliminar la plantilla "${activeTemplate.value.nombre}"?`);
  if (!confirmed) {
    return;
  }

  globalError.value = '';
  try {
    await axios.delete(`/api/facturas/plantillas/${activeTemplate.value.id}`);
    globalInfo.value = 'Plantilla eliminada correctamente.';
    await loadTemplates();
  } catch (error) {
    globalError.value = error?.response?.data?.message || 'No se pudo eliminar la plantilla.';
  }
}

function applyActiveTemplate(onlySelected) {
  if (!activeTemplate.value) {
    globalError.value = 'No hay plantilla activa para aplicar.';
    return;
  }

  const targetRows = onlySelected
    ? rows.value.filter((row) => selectedRowIds.value.includes(row.id))
    : filteredRows.value;

  if (targetRows.length === 0) {
    globalError.value = onlySelected
      ? 'No hay filas seleccionadas para aplicar la plantilla.'
      : 'No hay filas filtradas para aplicar la plantilla.';
    return;
  }

  for (const row of targetRows) {
    ensureDraft(row.id);
    drafts[row.id].mensajeTemplate = activeTemplate.value.contenido;
  }

  defaultTemplate.value = activeTemplate.value.contenido;
  globalInfo.value = `Plantilla aplicada a ${targetRows.length} cliente(s).`;
}

function applyCustomerPatchToRows(customerId, patch = {}) {
  for (const row of rows.value) {
    if (Number(row?.cliente?.id || 0) === Number(customerId || 0) && row.cliente) {
      row.cliente = {
        ...row.cliente,
        ...patch,
      };
    }
  }
}

async function loadKapsoStatus() {
  const response = await axios.get('/api/integraciones/kapso/status');
  kapsoStatus.value = response.data || { enabled: false, configured: false, missing: [] };
}

async function loadRows() {
  loading.value = true;
  globalError.value = '';
  try {
    const mes = Number(filters.mes) > 0 ? Number(filters.mes) : undefined;
    const response = await axios.get('/api/facturas/pendientes', {
      params: {
        per_page: 100,
        pago_estado: filters.pagoEstado || undefined,
        mes,
        servidor: filters.servidor || undefined,
      },
    });

    rows.value = extractRows(response.data);
    for (const row of rows.value) {
      ensureDraft(row.id);
      if (row.envio?.mensaje) {
        drafts[row.id].mensajeTemplate = row.envio.mensaje;
      } else {
        drafts[row.id].mensajeTemplate = defaultTemplate.value;
      }

      if (row.envio?.archivoUrl) {
        preparedData[row.id] = {
          ...(preparedData[row.id] || {}),
          facturaUrl: row.envio.archivoUrl,
          mensaje: drafts[row.id].mensajeTemplate,
        };
      }
    }

    if (rows.value.length === 0 && !autoSyncedOnEmpty.value) {
      autoSyncedOnEmpty.value = true;
      await syncCurrentMonth(true);
    }
  } catch (error) {
    globalError.value = error?.response?.data?.message || 'No se pudo cargar la bandeja de facturas.';
  } finally {
    loading.value = false;
  }
}

async function syncCurrentMonth(silent = false) {
  syncing.value = true;
  globalError.value = '';
  if (!silent) {
    globalInfo.value = '';
  }
  try {
    const response = await axios.post('/api/facturas/pagos/sync-mes-actual');
    if (!silent) {
      globalInfo.value = response.data?.message || 'Sincronizado.';
    }
    await loadRows();
  } catch (error) {
    if (!silent) {
      globalError.value = error?.response?.data?.message || 'No se pudo sincronizar el mes actual.';
    }
  } finally {
    syncing.value = false;
  }
}

async function preparar(row, options = {}) {
  const { reload = true, silent = false } = options;
  ensureDraft(row.id);
  if (!silent) {
    rowMessages[row.id] = '';
    rowDiagnostics[row.id] = [];
  }

  if (!drafts[row.id].archivo) {
    const message = 'Selecciona un archivo antes de preparar.';
    if (!silent) {
      rowMessages[row.id] = message;
    }
    return { ok: false, message };
  }

  busy[row.id] = true;
  try {
    const formData = new FormData();
    formData.append('pagoId', String(row.id));
    formData.append('mensajeTemplate', drafts[row.id].mensajeTemplate || '');
    formData.append('archivo', drafts[row.id].archivo);

    const response = await axios.post('/api/facturas/preparar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    preparedData[row.id] = response.data?.data || {};
    rowDiagnostics[row.id] = response.data?.data?.whatsappDiagnostics || [];
    if (!silent) {
      rowMessages[row.id] = response.data?.message || 'Factura preparada.';
    }

    if (reload) {
      await loadRows();
    }

    return { ok: true, message: response.data?.message || 'Factura preparada.' };
  } catch (error) {
    const message = buildApiErrorMessage(error, 'No se pudo preparar la factura.');
    rowMessages[row.id] = message;
    rowDiagnostics[row.id] = error?.response?.data?.diagnostics || [];
    return { ok: false, message };
  } finally {
    busy[row.id] = false;
  }
}

async function enviarWhatsapp(row) {
  rowMessages[row.id] = '';
  rowDiagnostics[row.id] = [];
  busy[row.id] = true;

  try {
    const response = await axios.post(`/api/facturas/${row.id}/enviar-whatsapp`);
    rowMessages[row.id] = response.data?.message || 'Enviado por WhatsApp API.';
    await loadRows();
  } catch (error) {
    rowMessages[row.id] = buildApiErrorMessage(error, 'No se pudo enviar por WhatsApp API.');
    rowDiagnostics[row.id] = error?.response?.data?.diagnostics || [];

    const fallback = error?.response?.data?.whatsappUrl;
    if (fallback) {
      preparedData[row.id] = {
        ...(preparedData[row.id] || {}),
        whatsappUrl: fallback,
      };
    }
  } finally {
    busy[row.id] = false;
  }
}

async function enviarEmail(row) {
  rowMessages[row.id] = '';
  busy[row.id] = true;
  try {
    const response = await axios.post(`/api/facturas/${row.id}/enviar-email`);
    rowMessages[row.id] = response.data?.message || 'Email enviado.';
    await loadRows();
  } catch (error) {
    rowMessages[row.id] = buildApiErrorMessage(error, 'No se pudo enviar por email.');
  } finally {
    busy[row.id] = false;
  }
}

async function marcarManual(row) {
  rowMessages[row.id] = '';
  busy[row.id] = true;
  try {
    const response = await axios.post(`/api/facturas/${row.id}/marcar-enviada`);
    rowMessages[row.id] = response.data?.message || 'Marcada como enviada.';
    await loadRows();
  } catch (error) {
    rowMessages[row.id] = buildApiErrorMessage(error, 'No se pudo marcar como enviada.');
  } finally {
    busy[row.id] = false;
  }
}

async function runBulkSend(mode) {
  const targetRows = [...selectedReadyRows.value];
  if (targetRows.length === 0) {
    globalError.value = 'No hay clientes seleccionados listos para envio.';
    return;
  }

  bulkSending.value = true;
  globalError.value = '';

  try {
    for (const row of targetRows) {
      if (mode === 'whatsapp') {
        await enviarWhatsapp(row);
        continue;
      }

      if (mode === 'email') {
        await enviarEmail(row);
        continue;
      }

      await marcarManual(row);
    }

    globalInfo.value = `Operacion masiva completada para ${targetRows.length} cliente(s).`;
  } catch (error) {
    globalError.value = error?.response?.data?.message || 'Fallo el procesamiento masivo.';
  } finally {
    bulkSending.value = false;
  }
}

function openBulkSendModal(mode) {
  const targetRows = selectionMode.value && selectedReadyRows.value.length > 0
    ? [...selectedReadyRows.value]
    : [...readyRows.value];

  if (targetRows.length === 0) {
    globalError.value = 'No hay clientes listos para envio masivo.';
    return;
  }

  bulkSendModal.open = true;
  bulkSendModal.processing = true;
  bulkSendModal.mode = mode;
  bulkSendModal.progress = 0;
  bulkSendModal.total = targetRows.length;
  bulkSendModal.completed = 0;
  bulkSendModal.failed = 0;
  bulkSendModal.errors = [];
  bulkSendModal.currentClient = '';
  bulkSendModal.rows = targetRows;

  sendBulkRows(targetRows, mode);
}

function closeBulkSendModal() {
  if (bulkSendModal.processing) {
    return;
  }
  bulkSendModal.open = false;
}

async function sendBulkRows(rows, mode) {
  globalError.value = '';
  globalInfo.value = '';

  for (const row of rows) {
    bulkSendModal.currentClient = clientName(row);

    const result = await sendRow(row, mode);
    bulkSendModal.progress += 1;

    if (result.ok) {
      bulkSendModal.completed += 1;
    } else {
      bulkSendModal.failed += 1;
      bulkSendModal.errors.push({
        id: row.id,
        clientName: clientName(row),
        message: result.message,
      });
    }
  }

  bulkSendModal.currentClient = '';
  bulkSendModal.processing = false;
  globalInfo.value = `Envio masivo completado: ${bulkSendModal.completed} exitosos, ${bulkSendModal.failed} con error.`;
  await loadRows();
}

async function sendRow(row, mode) {
  try {
    let response;
    if (mode === 'whatsapp') {
      response = await axios.post(`/api/facturas/${row.id}/enviar-whatsapp`);
      return { ok: true, message: response.data?.message || 'Enviado por WhatsApp API.' };
    }

    if (mode === 'email') {
      response = await axios.post(`/api/facturas/${row.id}/enviar-email`);
      return { ok: true, message: response.data?.message || 'Email enviado.' };
    }

    response = await axios.post(`/api/facturas/${row.id}/marcar-enviada`);
    return { ok: true, message: response.data?.message || 'Marcada como enviada.' };
  } catch (error) {
    return {
      ok: false,
      message: buildApiErrorMessage(error, mode === 'email' ? 'No se pudo enviar por email.' : mode === 'manual' ? 'No se pudo marcar como enviada.' : 'No se pudo enviar por WhatsApp API.'),
    };
  }
}

async function askKapsoTest() {
  const celularDestino = window.prompt('Numero destino (con o sin prefijo pais):');
  if (!celularDestino) return;

  globalError.value = '';
  globalInfo.value = '';

  try {
    const response = await axios.post('/api/integraciones/kapso/test', { celularDestino });
    globalInfo.value = response.data?.message || 'Prueba Kapso enviada.';
  } catch (error) {
    globalError.value = buildApiErrorMessage(error, 'Error al probar Kapso.');
  }
}

function normalizeMonthValue(value) {
  const month = Number(value);
  return month >= 1 && month <= 12 ? month : null;
}

function openEditClientModal(row) {
  const customer = row?.cliente || {};
  editClientModal.open = true;
  editClientModal.clienteId = customer.id || null;
  editClientModal.rowId = row.id;
  editClientModal.saving = false;
  editClientModal.error = '';
  editClientModal.form = {
    name: customer.name || '',
    company_name: customer.company_name || '',
    contact_name: customer.contact_name || '',
    contact_phone: customer.contact_phone || '',
    contact_email: customer.contact_email || '',
    precio: customer.precio != null ? Number(customer.precio) : null,
    pago_estado: customer.pago_estado || 'pendiente',
    mes_pagado: normalizeMonthValue(customer.mes_pagado),
    mes_por_pagar: normalizeMonthValue(customer.mes_por_pagar),
  };
}

function closeEditClientModal() {
  editClientModal.open = false;
  editClientModal.error = '';
}

async function saveClientChanges() {
  if (!editClientModal.clienteId) {
    editClientModal.error = 'No se encontro el cliente para editar.';
    return;
  }

  editClientModal.saving = true;
  editClientModal.error = '';

  try {
    const payload = {
      ...editClientModal.form,
      mes_pagado: normalizeMonthValue(editClientModal.form.mes_pagado),
      mes_por_pagar: normalizeMonthValue(editClientModal.form.mes_por_pagar),
    };

    const response = await axios.patch(`/api/facturas/clientes/${editClientModal.clienteId}`, payload);
    const updated = response?.data?.data || {};

    applyCustomerPatchToRows(editClientModal.clienteId, updated);

    globalInfo.value = response?.data?.message || 'Cliente actualizado.';
    closeEditClientModal();
    await loadRows();
  } catch (error) {
    editClientModal.error = error?.response?.data?.message || 'No se pudo actualizar el cliente.';
  } finally {
    editClientModal.saving = false;
  }
}

async function updateClientPaymentState(clientRow, pagoEstado) {
  const customerId = Number(clientRow?.cliente?.id || 0);
  if (!customerId) {
    return;
  }

  quickStateSaving[customerId] = true;
  globalError.value = '';

  try {
    const response = await axios.patch(`/api/facturas/clientes/${customerId}`, {
      pago_estado: pagoEstado,
    });

    const updated = response?.data?.data || { pago_estado: pagoEstado };
    applyCustomerPatchToRows(customerId, updated);
    globalInfo.value = response?.data?.message || 'Estado de pago actualizado.';
  } catch (error) {
    globalError.value = error?.response?.data?.message || 'No se pudo actualizar el estado de pago.';
  } finally {
    quickStateSaving[customerId] = false;
  }
}

onMounted(async () => {
  await loadKapsoStatus();

  try {
    await loadTemplates();
  } catch (error) {
    globalError.value = error?.response?.data?.message || 'No se pudieron cargar las plantillas.';
  }

  await loadRows();
});
</script>
