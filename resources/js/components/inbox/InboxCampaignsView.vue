<template>
  <section class="space-y-4">
    <div class="grid gap-4 lg:grid-cols-4">
      <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-slate-400">Disponibles</div>
        <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-slate-100">{{ stats.available }}</div>
        <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Con teléfono y listos para entrar en campaña</div>
      </article>

      <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-slate-400">Seleccionados</div>
        <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-slate-100">{{ stats.selected }}</div>
        <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Destinatarios que entrarán en el envío actual</div>
      </article>

      <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-slate-400">Campañas recientes</div>
        <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-slate-100">{{ stats.history }}</div>
        <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Últimos envíos guardados en el sistema</div>
      </article>

      <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-slate-400">Kapso</div>
        <div class="mt-2 text-sm font-semibold" :class="kapsoCardClass">
          {{ kapsoStatus.configured ? 'Configurado' : 'No configurado' }}
        </div>
        <div v-if="kapsoMissing.length" class="mt-1 text-xs text-gray-500 dark:text-slate-400">
          Faltan: {{ kapsoMissing.join(', ') }}
        </div>
      </article>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 disabled:opacity-50"
          :disabled="loadingRecipients"
          @click="loadRecipients"
        >
          {{ loadingRecipients ? 'Cargando...' : 'Refrescar bandeja' }}
        </button>

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="askKapsoTest"
        >
          Probar Kapso
        </button>

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 disabled:opacity-50"
          :disabled="loadingHistory"
          @click="loadHistory"
        >
          {{ loadingHistory ? 'Cargando historial...' : 'Cargar campañas recientes' }}
        </button>

        <select
          v-model="filters.source"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
          @change="onSourceChanged"
        >
          <option value="leads">Origen: leads</option>
          <option value="customers">Origen: clientes</option>
        </select>

        <select
          v-if="filters.source === 'leads'"
          v-model.number="filters.stage_id"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
        >
          <option :value="null">Etapa: todas</option>
          <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
        </select>

        <input
          v-model="filters.q"
          type="search"
          class="min-w-64 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
          placeholder="Buscar nombre, empresa o teléfono"
        />

        <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
          <input v-model="filters.only_with_phone" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600" />
          Solo con teléfono
        </label>

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-200"
          @click="loadRecipients"
        >
          Aplicar filtros
        </button>

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="toggleSelectAllVisible"
        >
          {{ allSelected ? 'Quitar visibles' : 'Seleccionar visibles' }}
        </button>

        <button
          type="button"
          class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 shadow-sm hover:bg-rose-100 focus:outline-none focus:ring-4 focus:ring-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-200"
          @click="clearSelectedRows"
        >
          Limpiar selección
        </button>

        <span class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
          Seleccionados: {{ selectedRowCount }}
        </span>
      </div>

      <div v-if="globalError" class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
        {{ globalError }}
      </div>

      <div v-if="globalInfo" class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ globalInfo }}
      </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="border-b border-gray-200 p-3 dark:border-slate-800">
        <nav class="flex flex-wrap gap-2" aria-label="Pestañas campañas">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium"
            :class="activeTab === tab.key
              ? 'bg-blue-600 text-white shadow-sm'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'"
            @click="activeTab = tab.key"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <div class="p-4">
        <section v-if="activeTab === 'plantilla'" class="space-y-4">
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200">
              <div class="font-medium">Plantillas Meta aprobadas</div>
              <div class="mt-1">
                Selecciona una plantilla aprobada y revisa cómo quedará con el contacto actualmente seleccionado. Los parámetros se completan automáticamente con los datos del destinatario.
              </div>
            </div>

            <div class="mt-3">
              <label class="text-sm text-gray-700 dark:text-slate-200">
                Nombre exacto de plantilla Meta
                <div class="mt-1 flex gap-2">
                  <select
                    v-model="form.meta_template_name"
                    :disabled="loadingMetaTemplates"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                  >
                    <option value="">Selecciona una plantilla aprobada</option>
                    <option v-for="template in metaTemplates" :key="template.id || template.name" :value="template.name">
                      {{ template.name }} · {{ template.language }} · {{ template.category }}
                    </option>
                  </select>

                  <button
                    type="button"
                    class="inline-flex shrink-0 items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    :disabled="loadingMetaTemplates"
                    @click="loadMetaTemplates"
                  >
                    {{ loadingMetaTemplates ? 'Cargando...' : 'Traer plantillas' }}
                  </button>
                </div>
                <div v-if="metaTemplatesError" class="mt-2 text-xs text-red-600 dark:text-red-300">
                  {{ metaTemplatesError }}
                </div>
              </label>
            </div>

            <div class="mt-3 rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
              <div class="flex items-center justify-between gap-3">
                <div class="font-medium text-gray-900 dark:text-slate-100">Previsualización</div>
                <div v-if="templatePreviewContact" class="text-xs text-gray-500 dark:text-slate-400">
                  Vista con: {{ templatePreviewContact.display_name || 'Contacto seleccionado' }}
                </div>
              </div>
              <div class="mt-3 whitespace-pre-wrap">{{ metaTemplatePreview || 'Selecciona una plantilla aprobada para ver su contenido.' }}</div>
              <div v-if="selectedMetaTemplate" class="mt-3 text-xs text-gray-500 dark:text-slate-400">
                Parámetros detectados: {{ selectedMetaTemplate.parameter_count }}
              </div>
              <div v-if="selectedMetaTemplate && selectedMetaTemplate.parameter_count > 0" class="mt-1 text-xs text-gray-500 dark:text-slate-400">
                Los parámetros se completan automáticamente con nombre, empresa y teléfono del destinatario.
              </div>
            </div>
          </div>
        </section>

        <section v-else-if="activeTab === 'envios'" class="space-y-3">
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-slate-700 dark:bg-slate-800/40">
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:opacity-50"
                :disabled="primaryActionDisabled"
                @click="handlePrimaryAction"
              >
                {{ primaryActionLabel }}
              </button>

              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="loadingHistory"
                @click="loadHistory"
              >
                Ver historial
              </button>

              <span v-if="createdCampaignId" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-200">
                Campaña creada: #{{ createdCampaignId }}
              </span>
            </div>
          </div>

          <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr),320px]">
            <div>
              <article
                v-for="recipient in activeRecipients"
                :key="recipient.id"
                class="mb-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"
              >
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">
                      {{ recipient.display_name || 'Sin nombre' }}
                    </div>
                    <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
                      {{ recipient.phone || 'Sin teléfono' }}
                    </div>
                    <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                      {{ recipient.rendered_message }}
                    </div>
                  </div>

                  <div class="flex flex-wrap gap-2">
                    <span class="rounded-full px-2 py-1 text-xs font-medium" :class="recipientBadgeClass(recipient.status)">
                      {{ recipient.status }}
                    </span>
                  </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                  <a
                    class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                    :href="waLink(recipient.phone, recipient.rendered_message)"
                    target="_blank"
                    rel="noopener"
                    @click="markOpened(recipient)"
                  >
                    Abrir WhatsApp
                  </a>

                  <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 shadow-sm hover:bg-emerald-100 focus:outline-none focus:ring-4 focus:ring-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-200"
                    @click="markSent(recipient)"
                  >
                    Marcar enviado
                  </button>
                </div>
              </article>

              <div v-if="activeRecipients.length === 0" class="rounded-lg border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Aún no hay una campaña abierta. Crea una campaña desde la selección actual o abre una del historial.
              </div>
            </div>

            <aside class="space-y-3">
              <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">Campaña activa</div>
                <div v-if="activeCampaign" class="mt-3 space-y-2 text-sm text-gray-700 dark:text-slate-200">
                  <div>#{{ activeCampaign.id }} {{ activeCampaign.name || 'Sin nombre' }}</div>
                  <div class="text-xs text-gray-500 dark:text-slate-400">{{ activeCampaign.recipients?.length || 0 }} destinatarios</div>
                </div>
                <div v-else class="mt-3 text-sm text-gray-600 dark:text-slate-300">Sin campaña seleccionada.</div>
              </div>

              <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-2">
                  <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">Últimas campañas</div>
                  <button
                    type="button"
                    class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    @click="loadHistory"
                  >
                    Recargar
                  </button>
                </div>

                <div v-if="historyCampaigns.length > 0" class="mt-3 space-y-2">
                  <button
                    v-for="campaign in historyCampaigns"
                    :key="campaign.id"
                    type="button"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-sm text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    @click="openCampaign(campaign.id)"
                  >
                    <span>#{{ campaign.id }} {{ campaign.name || 'Sin nombre' }}</span>
                    <span class="text-xs text-gray-500 dark:text-slate-400">{{ formatDate(campaign.created_at) }}</span>
                  </button>
                </div>

                <div v-else class="mt-3 text-sm text-gray-600 dark:text-slate-300">
                  No hay historial cargado todavía.
                </div>
              </div>
            </aside>
          </div>
        </section>

        <section v-else-if="activeTab === 'clientes'" class="space-y-3">
          <div v-if="isCustomerSource" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <article
              v-for="metric in customerStateMetrics"
              :key="metric.key"
              class="rounded-xl border p-4 shadow-sm"
              :class="metric.cardClass"
            >
              <div class="text-xs font-medium uppercase tracking-wide opacity-80">{{ metric.label }}</div>
              <div class="mt-2 text-2xl font-semibold">{{ metric.count }}</div>
              <div class="mt-1 text-xs opacity-80">{{ metric.help }}</div>
            </article>
          </div>

          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-slate-700 dark:bg-slate-800/40">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
              <div class="space-y-3">
                <div v-if="isCustomerSource" class="flex flex-wrap gap-2">
                  <button
                    v-for="option in customerStateOptions"
                    :key="option.value"
                    type="button"
                    class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-4 focus:ring-blue-100"
                    :class="clientListState.paymentStatus === option.value
                      ? 'bg-blue-600 text-white shadow-sm'
                      : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800'"
                    @click="clientListState.paymentStatus = option.value"
                  >
                    {{ option.label }}
                    <span
                      v-if="option.value !== 'all'"
                      class="ml-2 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                      :class="clientListState.paymentStatus === option.value ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
                    >
                      {{ customerStateCount(option.value) }}
                    </span>
                  </button>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-slate-300">
                  <span class="rounded-lg border border-gray-200 bg-white px-3 py-2 font-medium text-gray-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    Mostrando {{ paginationRangeLabel }}
                  </span>
                  <span class="rounded-lg border border-gray-200 bg-white px-3 py-2 font-medium text-gray-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    Seleccionados: {{ selectedRowCount }}
                  </span>
                </div>
              </div>

              <div class="flex flex-wrap gap-2">
                <label v-if="isCustomerSource" class="text-sm text-gray-600 dark:text-slate-300">
                  Mes objetivo
                  <select
                    v-model.number="clientListState.targetMonth"
                    class="mt-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                  >
                    <option v-for="month in monthOptions" :key="month.value" :value="month.value">{{ month.label }}</option>
                  </select>
                </label>

                <label class="text-sm text-gray-600 dark:text-slate-300">
                  Por página
                  <select
                    v-model.number="clientListState.perPage"
                    class="mt-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                  >
                    <option :value="12">12</option>
                    <option :value="24">24</option>
                    <option :value="48">48</option>
                  </select>
                </label>

                <div class="flex flex-wrap gap-2 self-end">
                  <button
                    v-if="isCustomerSource"
                    type="button"
                    class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800 shadow-sm hover:bg-amber-100 focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200"
                    @click="applySuggestedSelection"
                  >
                    Preseleccionar deuda {{ monthLabel(clientListState.targetMonth) }}
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    @click="toggleSelectAllVisible"
                  >
                    {{ allSelected ? 'Quitar página' : 'Seleccionar página' }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div v-for="group in groupedContacts" :key="group.key" class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="paymentStatusBadgeClass(group.key)">
                  {{ group.label }}
                </span>
                <span class="text-sm font-medium text-gray-700 dark:text-slate-200">{{ group.contacts.length }} registros en esta página</span>
              </div>
              <div v-if="group.help" class="text-xs text-gray-500 dark:text-slate-400">{{ group.help }}</div>
            </div>

            <article
              v-for="contact in group.contacts"
              :key="contact.id"
              class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-500"
            >
              <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 gap-3">
                  <input
                    type="checkbox"
                    class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-4 focus:ring-blue-100"
                    :checked="isRowSelected(contact.id)"
                    @change="setRowSelected(contact.id, $event.target.checked)"
                  />

                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                      <div class="text-base font-semibold text-gray-900 dark:text-slate-100">{{ contact.display_name || 'Sin nombre' }}</div>
                      <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        {{ contact.type === 'customer' ? 'Cliente' : 'Lead' }}
                      </span>
                    </div>

                    <div class="mt-1 text-sm text-gray-600 dark:text-slate-300">
                      {{ contact.secondary || 'Sin empresa' }}
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-500 dark:text-slate-400">
                      <span>{{ contact.phone || 'Sin teléfono' }}</span>
                      <span v-if="contact.stage_name">Etapa: {{ contact.stage_name }}</span>
                      <span v-if="contact.updated_at">Actualizado: {{ formatShortDate(contact.updated_at) }}</span>
                    </div>
                  </div>
                </div>

                <div class="grid min-w-0 gap-2 sm:grid-cols-2 xl:min-w-90 xl:grid-cols-1">
                  <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200">
                    <div class="font-semibold uppercase tracking-wide text-[11px] text-slate-500 dark:text-slate-400">Estado de pago</div>
                    <div class="mt-1 flex items-center gap-2">
                      <span class="rounded-full px-2 py-1 text-[11px] font-semibold" :class="paymentStatusBadgeClass(normalizePaymentStatus(contact.pago_estado))">
                        {{ paymentStatusLabel(normalizePaymentStatus(contact.pago_estado)) }}
                      </span>
                      <span v-if="isCustomerDueForTargetMonth(contact)" class="rounded-full bg-amber-100 px-2 py-1 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                        Debe {{ monthLabel(clientListState.targetMonth) }}
                      </span>
                    </div>
                  </div>

                  <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200">
                    <div class="font-semibold uppercase tracking-wide text-[11px] text-slate-500 dark:text-slate-400">Seguimiento</div>
                    <div class="mt-1">{{ paymentTrackingSummary(contact) }}</div>
                  </div>
                </div>
              </div>
            </article>
          </div>

          <div v-if="!loadingRecipients && filteredContacts.length === 0" class="rounded-lg border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
            No hay destinatarios para los filtros seleccionados.
          </div>

          <div v-if="filteredContacts.length > 0" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="text-sm text-gray-600 dark:text-slate-300">
              Página {{ clientListState.page }} de {{ totalPages }}
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="clientListState.page <= 1"
                @click="clientListState.page -= 1"
              >
                Anterior
              </button>

              <button
                v-for="page in visiblePageNumbers"
                :key="page"
                type="button"
                class="inline-flex min-w-10 items-center justify-center rounded-lg px-3 py-2 text-sm font-medium"
                :class="clientListState.page === page
                  ? 'bg-blue-600 text-white shadow-sm'
                  : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800'"
                @click="clientListState.page = page"
              >
                {{ page }}
              </button>

              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="clientListState.page >= totalPages"
                @click="clientListState.page += 1"
              >
                Siguiente
              </button>
            </div>
          </div>
        </section>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';

const tabs = [
  { key: 'plantilla', label: 'Plantilla mensaje' },
  { key: 'envios', label: 'Envios y preview' },
  { key: 'clientes', label: 'Clientes' },
];

const activeTab = ref('plantilla');
const globalError = ref('');
const globalInfo = ref('');
const loadingRecipients = ref(false);
const loadingHistory = ref(false);
const creatingCampaign = ref(false);
const createdCampaignId = ref(null);

const kapsoStatus = ref({ enabled: false, configured: false, missing: [] });
const stages = ref([]);
const availableContacts = ref([]);
const historyCampaigns = ref([]);
const activeCampaign = ref(null);
const selectedRowIds = ref([]);
const metaTemplates = ref([]);
const loadingMetaTemplates = ref(false);
const metaTemplatesError = ref('');
const sendingMetaTemplate = ref(false);
const customerStateOrder = ['pendiente', 'factura_enviada', 'pagado', 'inactivo', 'sin_estado'];
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
const customerStateOptions = [
  { value: 'all', label: 'Todos' },
  { value: 'due_target', label: 'Pendientes del mes' },
  { value: 'pendiente', label: 'Pendiente' },
  { value: 'factura_enviada', label: 'Factura enviada' },
  { value: 'pagado', label: 'Pagado' },
  { value: 'inactivo', label: 'Inactivo' },
];

const clientListState = reactive({
  paymentStatus: 'all',
  targetMonth: 4,
  perPage: 12,
  page: 1,
});

const filters = reactive({
  source: 'customers',
  stage_id: null,
  q: '',
  limit: 500,
  only_with_phone: true,
});

const counts = ref({
  total: 0,
  with_phone: 0,
  without_phone: 0,
  returned: 0,
});

const form = reactive({
  meta_template_name: '',
});

const kapsoMissing = computed(() => kapsoStatus.value?.missing || []);
const kapsoCardClass = computed(() => (kapsoStatus.value?.configured ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300'));
const isCustomerSource = computed(() => filters.source === 'customers');
const selectedRowCount = computed(() => selectedRowIds.value.length);
const activeRecipients = computed(() => activeCampaign.value?.recipients || []);
const selectedMetaTemplate = computed(() => metaTemplates.value.find((template) => template.name === form.meta_template_name) || null);
const primaryActionLabel = computed(() => {
  return sendingMetaTemplate.value
    ? 'Enviando plantilla Meta...'
    : `Enviar plantilla Meta a seleccionados (${selectedRowCount.value})`;
});
const primaryActionDisabled = computed(() => {
  if (selectedRowCount.value === 0) {
    return true;
  }

  return sendingMetaTemplate.value || !form.meta_template_name;
});

const stats = computed(() => ({
  available: counts.value.with_phone || availableContacts.value.length,
  selected: selectedRowIds.value.length,
  history: historyCampaigns.value.length,
}));

const filteredContacts = computed(() => {
  const contacts = [...availableContacts.value];

  if (isCustomerSource.value) {
    const filtered = contacts.filter((contact) => {
      if (clientListState.paymentStatus === 'all') {
        return true;
      }

      if (clientListState.paymentStatus === 'due_target') {
        return isCustomerDueForTargetMonth(contact);
      }

      return normalizePaymentStatus(contact?.pago_estado) === clientListState.paymentStatus;
    });

    filtered.sort(compareContactsForList);

    return filtered;
  }

  return contacts;
});

const totalPages = computed(() => {
  const pages = Math.ceil(filteredContacts.value.length / clientListState.perPage);

  return Math.max(1, pages || 1);
});

const paginatedContacts = computed(() => {
  const start = (clientListState.page - 1) * clientListState.perPage;

  return filteredContacts.value.slice(start, start + clientListState.perPage);
});

const selectionScopeContacts = computed(() => (activeTab.value === 'clientes' ? paginatedContacts.value : availableContacts.value));

const allSelected = computed(() => {
  if (selectionScopeContacts.value.length === 0) {
    return false;
  }

  return selectionScopeContacts.value.every((contact) => selectedRowIds.value.includes(Number(contact?.id || 0)));
});

const visiblePageNumbers = computed(() => {
  const maxVisible = 5;
  let start = Math.max(1, clientListState.page - 2);
  let end = Math.min(totalPages.value, start + maxVisible - 1);

  if ((end - start + 1) < maxVisible) {
    start = Math.max(1, end - maxVisible + 1);
  }

  return Array.from({ length: Math.max(0, end - start + 1) }, (_, index) => start + index);
});

const paginationRangeLabel = computed(() => {
  if (filteredContacts.value.length === 0) {
    return '0 registros';
  }

  const start = (clientListState.page - 1) * clientListState.perPage + 1;
  const end = Math.min(filteredContacts.value.length, start + clientListState.perPage - 1);

  return `${start}-${end} de ${filteredContacts.value.length}`;
});

const groupedContacts = computed(() => {
  if (!isCustomerSource.value) {
    return paginatedContacts.value.length > 0
      ? [{ key: 'sin_estado', label: 'Resultados', help: '', contacts: paginatedContacts.value }]
      : [];
  }

  return customerStateOrder
    .map((status) => {
      const contacts = paginatedContacts.value.filter((contact) => normalizePaymentStatus(contact?.pago_estado) === status);

      return {
        key: status,
        label: paymentStatusLabel(status),
        help: status === 'pendiente' || status === 'factura_enviada'
          ? `Pendientes por revisar contra ${monthLabel(clientListState.targetMonth)}.`
          : '',
        contacts,
      };
    })
    .filter((group) => group.contacts.length > 0);
});

const customerStateMetrics = computed(() => {
  if (!isCustomerSource.value) {
    return [];
  }

  return [
    {
      key: 'due_target',
      label: `Pendientes ${monthLabel(clientListState.targetMonth)}`,
      count: customerStateCount('due_target'),
      help: 'Se preseleccionan por defecto al cargar clientes.',
      cardClass: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100',
    },
    {
      key: 'pendiente',
      label: 'Pendiente',
      count: customerStateCount('pendiente'),
      help: 'Clientes sin pago registrado del ciclo activo.',
      cardClass: 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-100',
    },
    {
      key: 'factura_enviada',
      label: 'Factura enviada',
      count: customerStateCount('factura_enviada'),
      help: 'Se envió factura pero aún no se confirma el pago.',
      cardClass: 'border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-100',
    },
    {
      key: 'pagado',
      label: 'Pagado',
      count: customerStateCount('pagado'),
      help: 'Clientes al día según el tracking actual.',
      cardClass: 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-100',
    },
  ];
});

const templatePreviewContact = computed(() => {
  if (selectedRowIds.value.length > 0) {
    const selected = availableContacts.value.find((contact) => selectedRowIds.value.includes(Number(contact.id)));
    if (selected) {
      return selected;
    }
  }

  return availableContacts.value[0] || null;
});

const metaTemplatePreview = computed(() => {
  const template = selectedMetaTemplate.value;
  if (!template?.body_text) {
    return '';
  }

  const parameters = buildTemplateParameters(templatePreviewContact.value, Number(template.parameter_count || 0));

  return String(template.body_text).replace(/\{\{(\d+)\}\}/g, (_, rawIndex) => {
    const index = Number(rawIndex) - 1;
    return String(parameters[index] || `{{${rawIndex}}}`);
  });
});

function onSourceChanged() {
  filters.stage_id = null;
  clearSelectedRows();
  clientListState.page = 1;
  loadRecipients();
}

function monthLabel(monthNumber) {
  return monthOptions.find((month) => month.value === Number(monthNumber))?.label || 'Sin mes';
}

function formatShortDate(value) {
  if (!value) return '-';
  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return String(value);
  }

  return new Intl.DateTimeFormat('es-PE', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(date);
}

function normalizePaymentStatus(status) {
  const normalized = String(status || '').trim().toLowerCase();

  if (['pendiente'].includes(normalized)) return 'pendiente';
  if (['factura_enviada'].includes(normalized)) return 'factura_enviada';
  if (['pagado'].includes(normalized)) return 'pagado';
  if (['inactivo'].includes(normalized)) return 'inactivo';

  return 'sin_estado';
}

function paymentStatusLabel(status) {
  if (status === 'pendiente') return 'Pendiente';
  if (status === 'factura_enviada') return 'Factura enviada';
  if (status === 'pagado') return 'Pagado';
  if (status === 'inactivo') return 'Inactivo';
  return 'Sin estado';
}

function paymentStatusBadgeClass(status) {
  if (status === 'pendiente') return 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-200';
  if (status === 'factura_enviada') return 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-200';
  if (status === 'pagado') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200';
  if (status === 'inactivo') return 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
  return 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-200';
}

function isCustomerDueForTargetMonth(contact) {
  if (!isCustomerSource.value) {
    return false;
  }

  const status = normalizePaymentStatus(contact?.pago_estado);
  if (status === 'inactivo') {
    return false;
  }

  const targetMonth = Number(clientListState.targetMonth || 0);
  const dueMonth = Number(contact?.mes_por_pagar || 0);
  const paidMonth = Number(contact?.mes_pagado || 0);

  if (dueMonth > 0) {
    return dueMonth <= targetMonth;
  }

  if (paidMonth > 0) {
    return paidMonth < targetMonth;
  }

  return status === 'pendiente' || status === 'factura_enviada';
}

function paymentTrackingSummary(contact) {
  const parts = [];

  if (contact?.mes_pagado) {
    parts.push(`Ultimo pagado: ${monthLabel(contact.mes_pagado)}`);
  }

  if (contact?.mes_por_pagar) {
    parts.push(`Mes por pagar: ${monthLabel(contact.mes_por_pagar)}`);
  }

  return parts.length > 0 ? parts.join(' · ') : 'Sin tracking de pagos cargado';
}

function compareContactsForList(a, b) {
  const aRank = customerStateOrder.indexOf(normalizePaymentStatus(a?.pago_estado));
  const bRank = customerStateOrder.indexOf(normalizePaymentStatus(b?.pago_estado));

  if (aRank !== bRank) {
    return aRank - bRank;
  }

  const aDue = Number(a?.mes_por_pagar || 99);
  const bDue = Number(b?.mes_por_pagar || 99);

  if (aDue !== bDue) {
    return aDue - bDue;
  }

  const aName = String(a?.secondary || a?.display_name || '').trim();
  const bName = String(b?.secondary || b?.display_name || '').trim();

  return aName.localeCompare(bName, 'es', { sensitivity: 'base' });
}

function customerStateCount(state) {
  if (!isCustomerSource.value) {
    return 0;
  }

  if (state === 'due_target') {
    return availableContacts.value.filter((contact) => isCustomerDueForTargetMonth(contact)).length;
  }

  return availableContacts.value.filter((contact) => normalizePaymentStatus(contact?.pago_estado) === state).length;
}

function defaultSelectedContactIds(contacts) {
  const ids = contacts
    .filter((contact) => Number(contact?.id || 0) > 0)
    .map((contact) => Number(contact.id));

  if (!isCustomerSource.value) {
    return ids;
  }

  const suggested = contacts
    .filter((contact) => Number(contact?.id || 0) > 0 && isCustomerDueForTargetMonth(contact))
    .map((contact) => Number(contact.id));

  return suggested.length > 0 ? suggested : ids;
}

function applySuggestedSelection() {
  selectedRowIds.value = defaultSelectedContactIds(availableContacts.value);
}

function buildTemplateParameters(contact, parameterCount) {
  const displayName = String(contact?.display_name || '').trim();
  const company = String(contact?.secondary || '').trim();
  const phone = String(contact?.phone || '').trim();
  const defaults = [
    displayName || company || phone || 'Cliente',
    company || displayName || 'CRM Atlantis',
    phone || displayName || 'CRM Atlantis',
    'CRM Atlantis',
  ];

  return Array.from({ length: Math.max(0, Number(parameterCount || 0)) }, (_, index) => defaults[index] || defaults[0] || 'CRM Atlantis');
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

function toggleSelectAllVisible() {
  const scopeIds = selectionScopeContacts.value
    .map((contact) => Number(contact?.id || 0))
    .filter((id) => id > 0);

  if (scopeIds.length === 0) {
    return;
  }

  if (allSelected.value) {
    selectedRowIds.value = selectedRowIds.value.filter((id) => !scopeIds.includes(id));
    return;
  }

  selectedRowIds.value = Array.from(new Set([...selectedRowIds.value, ...scopeIds]));
}

function normalizePhoneDigits(phone) {
  const digits = String(phone || '').replace(/\D+/g, '');
  if (!digits || digits.length < 8) return '';
  if (digits.length === 9 && digits.startsWith('9')) return `51${digits}`;
  return digits;
}

function waLink(phone, text) {
  const digits = normalizePhoneDigits(phone);
  if (!digits) {
    return '#';
  }

  return `https://wa.me/${digits}?text=${encodeURIComponent(text || '')}`;
}

function recipientBadgeClass(status) {
  if (status === 'sent') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200';
  if (status === 'opened') return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200';
  if (status === 'failed') return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200';
  return 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-200';
}

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString();
}

function buildApiErrorMessage(error, fallbackMessage) {
  const data = error?.response?.data || {};
  return data?.message || fallbackMessage;
}

async function loadKapsoStatus() {
  const response = await axios.get('/api/integraciones/kapso/status');
  kapsoStatus.value = response.data || { enabled: false, configured: false, missing: [] };
}

async function loadRecipients() {
  loadingRecipients.value = true;
  globalError.value = '';

  try {
    const params = {
      source: filters.source,
      q: filters.q || undefined,
      limit: filters.limit,
      only_with_phone: filters.only_with_phone ? 1 : 0,
    };

    if (filters.source === 'leads' && filters.stage_id) {
      params.stage_id = filters.stage_id;
    }

    const response = await axios.get('/leads/whatsapp/recipients', { params });
    const data = response?.data?.data || {};

    stages.value = Array.isArray(data.stages) ? data.stages : [];
    availableContacts.value = Array.isArray(data.contacts) ? data.contacts : [];
    counts.value = data.counts || { total: 0, with_phone: 0, without_phone: 0, returned: 0 };
    clientListState.page = 1;
    selectedRowIds.value = defaultSelectedContactIds(availableContacts.value);
  } catch (error) {
    globalError.value = buildApiErrorMessage(error, 'No se pudieron cargar los destinatarios.');
  } finally {
    loadingRecipients.value = false;
  }
}

async function loadHistory() {
  loadingHistory.value = true;
  globalError.value = '';

  try {
    const response = await axios.get('/leads/whatsapp-campaigns');
    historyCampaigns.value = response?.data?.data?.campaigns || [];
  } catch (error) {
    globalError.value = buildApiErrorMessage(error, 'No se pudo cargar el historial de campañas.');
  } finally {
    loadingHistory.value = false;
  }
}

async function loadMetaTemplates() {
  loadingMetaTemplates.value = true;
  metaTemplatesError.value = '';

  try {
    const response = await axios.get('/api/integraciones/kapso/templates');
    metaTemplates.value = response?.data?.data?.templates || response?.data?.templates || [];

    if (!form.meta_template_name && metaTemplates.value.length > 0) {
      form.meta_template_name = metaTemplates.value[0].name;
    }
  } catch (error) {
    metaTemplatesError.value = buildApiErrorMessage(error, 'No se pudieron traer las plantillas aprobadas.');
  } finally {
    loadingMetaTemplates.value = false;
  }
}

async function sendMetaTemplateToSelected() {
  if (!selectedMetaTemplate.value) {
    globalError.value = 'Selecciona una plantilla Meta aprobada.';
    return;
  }

  sendingMetaTemplate.value = true;
  globalError.value = '';
  globalInfo.value = '';

  const BATCH_SIZE = 50;
  const templateName = selectedMetaTemplate.value.name;
  const languageCode = String(selectedMetaTemplate.value.language || '').trim() || 'es';
  const parameterCount = Number(selectedMetaTemplate.value?.parameter_count || 0);

  const recipients = availableContacts.value
    .filter((contact) => selectedRowIds.value.includes(Number(contact.id)))
    .map((contact) => ({
      id: contact.id,
      display_name: contact.display_name,
      secondary: contact.secondary,
      phone: contact.phone,
      parameters: buildTemplateParameters(contact, parameterCount),
    }));

  if (recipients.length === 0) {
    globalError.value = 'Selecciona al menos un destinatario con celular.';
    sendingMetaTemplate.value = false;
    return;
  }

  let totalSent = 0;
  let totalFailed = 0;

  try {
    for (let offset = 0; offset < recipients.length; offset += BATCH_SIZE) {
      const chunk = recipients.slice(offset, offset + BATCH_SIZE);
      const response = await axios.post('/api/integraciones/kapso/templates/send', {
        template_name: templateName,
        language_code: languageCode,
        recipients: chunk,
      });

      totalSent += Number(response?.data?.data?.sent || 0);
      totalFailed += Number(response?.data?.data?.failed || 0);
    }

    globalInfo.value = `Solicitud enviada a Kapso (${recipients.length} destinatarios). Aceptados por API: ${totalSent}. Fallidos: ${totalFailed}. La entrega final depende de WhatsApp y del estado real del número.`;
    activeTab.value = 'envios';
  } catch (error) {
    const partial = totalSent > 0 || totalFailed > 0;
    const prefix = partial
      ? `Envio parcial antes del error. Aceptados: ${totalSent}. Fallidos: ${totalFailed}. `
      : '';
    globalError.value = prefix + buildApiErrorMessage(error, 'No se pudo enviar la plantilla Meta.');
  } finally {
    sendingMetaTemplate.value = false;
  }
}

async function handlePrimaryAction() {
  await sendMetaTemplateToSelected();
}

async function openCampaign(campaignId) {
  globalError.value = '';

  try {
    const response = await axios.get(`/leads/whatsapp-campaigns/${campaignId}`);
    activeCampaign.value = response?.data?.data?.campaign || null;
    activeTab.value = 'envios';
  } catch (error) {
    globalError.value = buildApiErrorMessage(error, 'No se pudo abrir la campaña.');
  }
}

async function patchRecipient(recipientId, payload) {
  await axios.patch(`/leads/whatsapp-campaign-recipients/${recipientId}`, payload);
}

async function markOpened(recipient) {
  if (!recipient?.id || recipient.status !== 'pending') {
    return;
  }

  try {
    await patchRecipient(recipient.id, { status: 'opened' });
    recipient.status = 'opened';
  } catch {
    // no-op
  }
}

async function markSent(recipient) {
  if (!recipient?.id) {
    return;
  }

  try {
    await patchRecipient(recipient.id, { status: 'sent' });
    recipient.status = 'sent';
  } catch {
    // no-op
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

watch(
  () => [clientListState.paymentStatus, clientListState.perPage],
  () => {
    clientListState.page = 1;
  }
);

watch(
  () => filteredContacts.value.length,
  () => {
    if (clientListState.page > totalPages.value) {
      clientListState.page = totalPages.value;
    }
  }
);

onMounted(async () => {
  await loadKapsoStatus();
  await Promise.allSettled([loadRecipients(), loadHistory(), loadMetaTemplates()]);
});
</script>