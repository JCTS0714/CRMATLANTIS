<template>
  <div>
    <div class="mb-4 text-sm text-gray-600 dark:text-slate-300">
      Arrastra un lead entre columnas para cambiar su etapa.
    </div>

      <div class="mb-4 flex items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Limite:</label>
          <select
            v-model.number="limit"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
          >
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Periodo:</label>
          <select
            v-model="periodType"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
          >
            <option value="all">Todos</option>
            <option value="last_week">Última semana</option>
            <option value="month">Por mes</option>
            <option value="between_months">Entre meses</option>
            <option value="date">Por fecha</option>
            <option value="between_dates">Entre fechas</option>
          </select>
        </div>

        <div v-if="periodType === 'month'" class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Mes:</label>
          <input
            v-model="periodMonth"
            type="month"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
          />
        </div>

        <div v-if="periodType === 'between_months'" class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Desde mes:</label>
          <input
            v-model="periodMonthFrom"
            type="month"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
          />
        </div>

        <div v-if="periodType === 'between_months'" class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Hasta mes:</label>
          <input
            v-model="periodMonthTo"
            type="month"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
          />
        </div>

        <div v-if="periodType === 'date'" class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Fecha:</label>
          <input
            v-model="periodDate"
            type="date"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
          />
        </div>

        <div v-if="periodType === 'between_dates'" class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Desde:</label>
          <input
            v-model="periodDateFrom"
            type="date"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
          />
        </div>

        <div v-if="periodType === 'between_dates'" class="flex items-center gap-2">
          <label class="text-xs text-gray-600 dark:text-slate-300">Hasta:</label>
          <input
            v-model="periodDateTo"
            type="date"
            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30 dark:[color-scheme:dark]"
          />
        </div>

      </div>

    <div v-if="loading" class="text-sm text-gray-600 dark:text-slate-300">Cargando...</div>
    <div v-else-if="moveError" class="mb-3 text-sm text-red-600">{{ moveError }}</div>

    <div v-else class="grid gap-4" :class="gridColsClass">
      <section
        v-for="stage in stages"
        :key="stage.id"
        :data-stage-id="stage.id"
        class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800"
        @dragover.prevent='onDragOver'
        @drop.prevent='onDropOnStage(stage, $event)'
      >
        <div class="p-4 border-b border-gray-200 dark:border-slate-800">
          <div class="flex items-center justify-between gap-3">
            <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ stage.name }}</div>
            <div class="text-xs text-gray-600 dark:text-slate-300">{{ stage.count }}</div>
          </div>
        </div>

        <div class="p-3 space-y-3 min-h-[140px]">
          <div v-if="(stage.leads?.length ?? 0) === 0" class="text-sm text-gray-500 dark:text-slate-400 px-1">
            Sin leads.
          </div>

          <div v-for="(lead, index) in stage.leads" :key="lead.id" :data-lead-id="lead.id">
            <!-- Preview line when dragging over -->
            <div 
              v-if="dragOverStageId === stage.id && dropPreviewPosition === index && draggedLead?.id !== lead.id"
              class="h-1 bg-blue-500 rounded-full mx-3 mb-2 animate-pulse transition-all duration-200"
            ></div>

            <article
              :data-lead-id="lead.id"
              class="select-none bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3 dark:bg-slate-800 dark:border-slate-700 will-change-transform"
              :class="{
                'opacity-60 scale-95 shadow-lg': draggedLead?.id === lead.id,
                'cursor-not-allowed opacity-80': isLeadLocked(lead, stage),
                'cursor-grab hover:bg-blue-50/30 dark:hover:bg-slate-800/50 hover:shadow-md': !isLeadLocked(lead, stage)
              }"
              :style="draggedLead?.id === lead.id ? 'transition: none !important; transform: scale(1.02);' : 'transition: transform 0.1s ease, opacity 0.1s ease;'"
              :draggable="!isLeadLocked(lead, stage)"
              @dragstart="onDragStart(lead, stage, $event)"
              @dragend="onDragEnd"
              @click="openEditModal(lead)"
            >
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ lead.name }}</div>
                <div v-if="lead.company_name" class="text-xs text-gray-600 dark:text-slate-300 mt-1">
                  {{ lead.company_name }}
                </div>
                <div v-else-if="lead.contact_name" class="text-xs text-gray-600 dark:text-slate-300 mt-1">
                  {{ lead.contact_name }}
                </div>
              </div>

              <div v-if="lead.amount !== null && lead.amount !== undefined" class="text-xs text-gray-700 dark:text-slate-200">
                {{ formatMoney(lead.amount, lead.currency) }}
              </div>
            </div>

            <div v-if="lead.contact_phone || lead.contact_email" class="mt-2 text-xs text-gray-600 dark:text-slate-300">
              <div v-if="lead.contact_phone">{{ lead.contact_phone }}</div>
              <div v-if="lead.contact_email">{{ lead.contact_email }}</div>
            </div>

            <div class="mt-3 flex items-center justify-between gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-300 dark:hover:bg-blue-900"
                @click.stop.prevent="goToCalendarWithLead(lead)"
              >
                Agendar
              </button>

              <button
                v-if="stage.is_won"
                type="button"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="archivingIds.has(lead.id)"
                @click.stop.prevent="archiveLead(lead, stage)"
              >
                {{ archivingIds.has(lead.id) ? 'Archivando…' : 'Archivar' }}
              </button>
            </div>
            </article>
          </div>
          
          <!-- Preview line at the end when dragging -->
          <div 
            v-if="dragOverStageId === stage.id && dropPreviewPosition >= (stage.leads?.length || 0)"
            class="h-1 bg-blue-500 rounded-full mx-3 mt-2 animate-pulse transition-all duration-200"
          ></div>
        </div>
      </section>
    </div>
  </div>

  <!-- Quick Lead Modal (Kommo-like) -->
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-show="quickOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
      role="dialog"
      aria-modal="true"
      @click.self="hideQuickModal"
    >
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-show="quickOpen" class="relative w-full max-w-lg">
          <div class="relative max-h-[90vh] overflow-hidden bg-white rounded-lg shadow dark:bg-slate-900">
            <div class="p-4 md:p-5 border-b rounded-t dark:border-slate-800">
              <div class="text-xs font-semibold text-gray-500 dark:text-slate-300 uppercase tracking-wider">Contacto inicial</div>
              <div class="mt-1 text-sm text-gray-600 dark:text-slate-300">
                0 Clientes potenciales: 0 S/
              </div>
              <div class="mt-3 text-lg font-semibold text-gray-900 dark:text-slate-100 text-center">Lead rápido</div>
            </div>

            <form class="max-h-[70vh] overflow-y-auto p-4 md:p-5" @submit.prevent="submitQuick">
              <div class="grid gap-3">
                <div class="text-xs text-gray-500 dark:text-slate-400">Los campos con <span class="font-semibold text-red-500">*</span> son obligatorios.</div>

                <label class="text-xs font-medium text-gray-600 dark:text-slate-300">Nombre <span class="text-red-500">*</span></label>
                <input
                  v-model.trim="quickForm.name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Nombre"
                  required
                />

                <div class="flex gap-3">
                  <input
                    v-model.number="quickForm.amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="0"
                  />
                  <select
                    v-model="quickForm.currency"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  >
                    <option value="PEN">S/</option>
                    <option value="USD">USD</option>
                  </select>
                </div>

                <input
                  v-model.trim="quickForm.contact_name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Nombre"
                />

                <input
                  v-model.trim="quickForm.contact_phone"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Teléfono"
                />

                <input
                  v-model.trim="quickForm.contact_email"
                  type="email"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Correo"
                />

                <input
                  v-model.trim="quickForm.company_name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Compañía: Nombre"
                />

                <input
                  v-model.trim="quickForm.company_address"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Compañía: Dirección"
                />

                <input
                  v-model.trim="quickForm.migracion"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Migración (opcional)"
                />

                <select
                  v-model="quickForm.referencia"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                >
                  <option value="">Referencia (opcional)</option>
                  <option value="TIK TOK">TIK TOK</option>
                  <option value="FACEBOOK">FACEBOOK</option>
                  <option value="INSTAGRAM">INSTAGRAM</option>
                  <option value="whatsapp">whatsapp</option>
                  <option value="otros">otros</option>
                </select>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <select
                    v-model="quickForm.document_type"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  >
                    <option value="dni">DNI</option>
                    <option value="ruc">RUC</option>
                    <option value="otro">OTRO</option>
                  </select>

                  <input
                    v-model.trim="quickForm.document_number"
                    type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    :placeholder="documentPlaceholder"
                  />
                </div>

                <textarea
                  v-model.trim="quickForm.observacion"
                  rows="3"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Observación (opcional)"
                ></textarea>

                <p v-if="quickError" class="text-sm text-red-600">{{ quickError }}</p>
              </div>

              <div class="mt-5 flex items-center justify-between">
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
                  :disabled="quickSaving"
                >
                  {{ quickSaving ? 'Creando...' : 'Crear' }}
                </button>

                <div class="flex items-center gap-3">
                  <button
                    v-if="isLocalAutofillEnabled"
                    type="button"
                    class="inline-flex items-center rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-800 hover:bg-amber-100 dark:border-amber-800/40 dark:bg-amber-950/20 dark:text-amber-300 dark:hover:bg-amber-900/30"
                    @click="fillQuickLeadForTest"
                  >
                    Rellenar test
                  </button>
                  <button
                    type="button"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-slate-100"
                    @click="hideQuickModal"
                  >
                    Cancelar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>

  <!-- Edit Lead Modal -->
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
      @click.self="closeEditModal"
    >
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-show="editOpen" class="relative w-full max-w-lg">
          <div class="relative max-h-[90vh] overflow-hidden bg-white rounded-lg shadow dark:bg-slate-900">
            <div class="p-4 md:p-5 border-b rounded-t dark:border-slate-800">
              <div class="text-lg font-semibold text-gray-900 dark:text-slate-100 text-center">Editar lead</div>
            </div>

            <form class="max-h-[70vh] overflow-y-auto p-4 md:p-5" @submit.prevent="submitEdit">
              <div class="grid gap-3">
                <div class="text-xs text-gray-500 dark:text-slate-400">Los campos con <span class="font-semibold text-red-500">*</span> son obligatorios.</div>

                <label class="text-xs font-medium text-gray-600 dark:text-slate-300">Nombre <span class="text-red-500">*</span></label>
                <input
                  v-model.trim="editForm.name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Nombre"
                  required
                />

                <div class="flex gap-3">
                  <input
                    v-model.number="editForm.amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="0"
                  />
                  <select
                    v-model="editForm.currency"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  >
                    <option value="PEN">S/</option>
                    <option value="USD">USD</option>
                  </select>
                </div>

                <input
                  v-model.trim="editForm.contact_name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Nombre"
                />

                <input
                  v-model.trim="editForm.contact_phone"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Teléfono"
                />

                <input
                  v-model.trim="editForm.contact_email"
                  type="email"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Contacto: Correo"
                />

                <input
                  v-model.trim="editForm.company_name"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Compañía: Nombre"
                />

                <input
                  v-model.trim="editForm.company_address"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Compañía: Dirección"
                />

                <input
                  v-model.trim="editForm.migracion"
                  type="text"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  placeholder="Migración (opcional)"
                />

                <select
                  v-model="editForm.referencia"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                >
                  <option value="">Referencia (opcional)</option>
                  <option value="TIK TOK">TIK TOK</option>
                  <option value="FACEBOOK">FACEBOOK</option>
                  <option value="INSTAGRAM">INSTAGRAM</option>
                  <option value="whatsapp">whatsapp</option>
                  <option value="otros">otros</option>
                </select>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <select
                    v-model="editForm.document_type"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                  >
                    <option value="dni">DNI</option>
                    <option value="ruc">RUC</option>
                    <option value="otro">OTRO</option>
                  </select>

                  <input
                    v-model.trim="editForm.document_number"
                    type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                    placeholder="Documento: Número (opcional)"
                  />
                
                <textarea
                  ref="observacionInput"
                  v-model.trim="editForm.observacion"
                  rows="3"
                  placeholder="Observación (motivo de desistimiento o notas)"
                  class="mt-2 sm:col-span-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                ></textarea>
                </div>

                <p v-if="editError" class="text-sm text-red-600">{{ editError }}</p>
              </div>

              <div class="mt-5 flex items-center justify-between">
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
                  :disabled="editSaving"
                >
                  <svg v-if="!editSaving" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  {{ editSaving ? 'Guardando...' : 'Guardar' }}
                </button>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-300 dark:hover:bg-blue-900"
                    :disabled="editSaving"
                    @click.prevent="sendToEspera"
                  >
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    En Espera
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 dark:border-red-800 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900"
                    :disabled="editSaving"
                    @click.prevent="markDesistido"
                  >
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Desistir
                  </button>

                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                    @click="closeEditModal"
                  >
                    Cancelar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup>
import { confirmDialog, toastSuccess, toastError } from '../ui/alerts';
import { useLeadsBoardPeriodFilters } from '../composables/useLeadsBoardPeriodFilters';
import { useLeadsBoardLeadModals } from '../composables/useLeadsBoardLeadModals';
import { useLeadsBoardDragDrop } from '../composables/useLeadsBoardDragDrop';
import { useLeadsBoardLeadActions } from '../composables/useLeadsBoardLeadActions';
import { useLeadsBoardData } from '../composables/useLeadsBoardData';
import { useLeadsBoardLifecycle } from '../composables/useLeadsBoardLifecycle';

const {
  periodType,
  periodMonth,
  periodMonthFrom,
  periodMonthTo,
  periodDate,
  periodDateFrom,
  periodDateTo,
  clearPeriodInputs,
  resolvePeriodRange,
  isPeriodReady,
} = useLeadsBoardPeriodFilters();

const {
  loading,
  stages,
  limit,
  gridColsClass,
  load,
} = useLeadsBoardData({
  periodType,
  periodMonth,
  periodMonthFrom,
  periodMonthTo,
  periodDate,
  periodDateFrom,
  periodDateTo,
  clearPeriodInputs,
  resolvePeriodRange,
  isPeriodReady,
  toastError,
});

const isLocalAutofillEnabled = (() => {
  if (typeof window === 'undefined') return false;
  const host = window.location.hostname;
  return import.meta.env.DEV || host === 'localhost' || host === '127.0.0.1' || host === '::1';
})();

let localAutofillModulePromise = null;
const getLocalAutofillModule = async () => {
  if (!isLocalAutofillEnabled) return null;
  if (!localAutofillModulePromise) {
    localAutofillModulePromise = import('/resources/js/local/customerModalAutofill.local.js').catch(() => null);
  }
  return localAutofillModulePromise;
};

const {
  moveError,
  draggedLead,
  dragOverStageId,
  dropPreviewPosition,
  isLeadLocked,
  onDragStart,
  onDragEnd,
  onDragOver,
  onDropOnStage,
  removeLeadFromStage,
} = useLeadsBoardDragDrop({
  stages,
  load,
  confirmDialog,
  toastSuccess,
});

const {
  archivingIds,
  formatMoney,
  archiveLead,
  goToCalendarWithLead,
} = useLeadsBoardLeadActions({
  confirmDialog,
  toastSuccess,
  toastError,
  moveError,
  removeLeadFromStage,
});

const {
  quickOpen,
  quickSaving,
  quickError,
  quickForm,
  documentPlaceholder,
  showQuickModal,
  hideQuickModal,
  fillQuickLeadForTest,
  submitQuick,
  editOpen,
  editSaving,
  editError,
  observacionInput,
  editForm,
  openEditModal,
  closeEditModal,
  submitEdit,
  markDesistido,
  sendToEspera,
} = useLeadsBoardLeadModals({
  stages,
  getLocalAutofillModule,
  confirmDialog,
  toastSuccess,
  toastError,
});

useLeadsBoardLifecycle({
  showQuickModal,
  load,
});
</script>
