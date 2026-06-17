<template>
  <section class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="space-y-3">
          <span class="inline-flex items-center rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-violet-700 dark:border-violet-900/40 dark:bg-violet-950/40 dark:text-violet-200">
            Preview UX · no funcional
          </span>
          <div>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">
              Bandeja de envios unificada
            </h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
              Este mockup concentra plantilla, preparacion de archivo, seleccion masiva, diagnosticos y ficha del cliente
              en una sola superficie. La idea es eliminar el cambio constante entre pestañas y dejar cada envio en un flujo
              mas claro: revisar, preparar, confirmar canal y enviar.
            </p>
          </div>
          <div class="flex flex-wrap gap-2 text-xs">
            <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">Siempre visible la seleccion</span>
            <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">Plantilla + preview en contexto</span>
            <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">Canal recomendado por cliente</span>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:w-[360px]">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Redundancia eliminada</div>
            <div class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">4 pestañas a 1 flujo maestro</div>
            <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
              Plantillas, PDF, envio y cliente dejan de competir entre si.
            </p>
          </div>
          <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/30">
            <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Objetivo del preview</div>
            <div class="mt-2 text-sm font-medium text-emerald-900 dark:text-emerald-100">Validar jerarquia y experiencia</div>
            <p class="mt-1 text-xs leading-5 text-emerald-800/80 dark:text-emerald-200/80">
              Sin tocar procesos reales ni servicios externos.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="metric in metrics"
        :key="metric.label"
        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950"
      >
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ metric.label }}</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <div class="text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ metric.value }}</div>
          <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="metric.tone">
            {{ metric.note }}
          </span>
        </div>
      </article>
    </div>

    <div class="grid gap-6 xl:grid-cols-[320px,minmax(0,1fr),320px]">
      <aside class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Cola priorizada</h3>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
              {{ filteredItems.length }} visibles
            </span>
          </div>

          <div class="mt-3 grid gap-2">
            <input
              v-model="search"
              type="text"
              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
              placeholder="Buscar cliente, comercio o contacto"
            />

            <div class="flex flex-wrap gap-2">
              <button
                v-for="option in stageOptions"
                :key="option.value"
                type="button"
                class="rounded-full px-3 py-1.5 text-xs font-semibold transition"
                :class="stageFilter === option.value
                  ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'"
                @click="stageFilter = option.value"
              >
                {{ option.label }}
              </button>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <button
            v-for="item in filteredItems"
            :key="item.id"
            type="button"
            class="w-full rounded-2xl border p-4 text-left shadow-sm transition"
            :class="selectedId === item.id
              ? 'border-blue-500 bg-blue-50 dark:border-blue-500 dark:bg-blue-950/30'
              : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:hover:border-slate-700 dark:hover:bg-slate-900'"
            @click="selectedId = item.id"
          >
            <div class="flex items-start gap-3">
              <input
                :checked="selectedIds.includes(item.id)"
                type="checkbox"
                class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600"
                @click.stop
                @change="toggleSelected(item.id)"
              />
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                  <div class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ item.company }}</div>
                  <span class="rounded-full px-2 py-1 text-[11px] font-semibold" :class="stageTone(item.stage)">
                    {{ stageLabel(item.stage) }}
                  </span>
                </div>
                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ item.contact }} · {{ item.period }}</div>
                <div class="mt-3 flex flex-wrap gap-2">
                  <span
                    v-for="tag in item.tags"
                    :key="tag"
                    class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                  >
                    {{ tag }}
                  </span>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                  <span>{{ item.channel }}</span>
                  <span>{{ item.issues }} alertas</span>
                </div>
              </div>
            </div>
          </button>
        </div>
      </aside>

      <div class="space-y-4">
        <div v-if="selectedIds.length" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/30">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <div class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                {{ selectedIds.length }} cliente(s) listos para accion masiva
              </div>
              <p class="mt-1 text-xs leading-5 text-emerald-800/80 dark:text-emerald-200/80">
                La seleccion ya no requiere activar un modo aparte; queda integrada en la cola y habilita acciones de lote en contexto.
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <button type="button" class="rounded-xl bg-emerald-600 px-3 py-2 text-sm font-medium text-white">Enviar WhatsApp</button>
              <button type="button" class="rounded-xl border border-indigo-200 bg-white px-3 py-2 text-sm font-medium text-indigo-700 dark:border-indigo-900/40 dark:bg-slate-950 dark:text-indigo-200">Enviar email</button>
              <button type="button" class="rounded-xl border border-amber-200 bg-white px-3 py-2 text-sm font-medium text-amber-700 dark:border-amber-900/40 dark:bg-slate-950 dark:text-amber-200">Marcar manual</button>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-50">{{ selectedItem.company }}</h3>
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="stageTone(selectedItem.stage)">
                  {{ stageLabel(selectedItem.stage) }}
                </span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                  {{ selectedItem.channel }}
                </span>
              </div>
              <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                {{ selectedItem.summary }}
              </p>
            </div>

            <div class="grid gap-2 sm:grid-cols-3">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Periodo</div>
                <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ selectedItem.period }}</div>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Monto</div>
                <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ selectedItem.amount }}</div>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Servidor</div>
                <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ selectedItem.server }}</div>
              </div>
            </div>
          </div>

          <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1.2fr),minmax(0,0.8fr)]">
            <div class="space-y-4">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Flujo de trabajo unificado</h4>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Todo el caso en orden natural, sin cambiar de pestaña.</p>
                  </div>
                  <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-200">
                    Nuevo
                  </span>
                </div>

                <div class="mt-4 grid gap-3">
                  <div
                    v-for="step in selectedItem.steps"
                    :key="step.title"
                    class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-950"
                  >
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white dark:bg-slate-100 dark:text-slate-900">
                      {{ step.index }}
                    </div>
                    <div>
                      <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ step.title }}</div>
                      <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ step.description }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950">
                  <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Mensaje y preview</h4>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Plantilla en contexto</span>
                  </div>
                  <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    {{ selectedItem.template }}
                  </div>
                  <div class="mt-3 rounded-2xl bg-emerald-500/10 p-3 text-sm text-emerald-900 dark:text-emerald-100">
                    {{ selectedItem.preview }}
                  </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950">
                  <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Ficha del cliente</h4>
                    <button type="button" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">
                      Editar rapido
                    </button>
                  </div>
                  <dl class="mt-4 grid gap-3 text-sm">
                    <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-900">
                      <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Contacto</dt>
                      <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ selectedItem.contact }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-900">
                      <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">WhatsApp</dt>
                      <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ selectedItem.phone }}</dd>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-900">
                      <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Email</dt>
                      <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ selectedItem.email }}</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-center justify-between">
                  <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Canal sugerido</h4>
                  <span class="text-xs text-slate-500 dark:text-slate-400">Basado en disponibilidad y riesgo</span>
                </div>
                <div class="mt-4 space-y-3">
                  <div
                    v-for="channel in selectedItem.channels"
                    :key="channel.name"
                    class="rounded-2xl border p-3"
                    :class="channel.recommended
                      ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/20'
                      : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900'"
                  >
                    <div class="flex items-center justify-between gap-3">
                      <div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ channel.name }}</div>
                        <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ channel.reason }}</p>
                      </div>
                      <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="channel.badge">
                        {{ channel.state }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-center justify-between">
                  <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Adjunto y validaciones</h4>
                  <span class="text-xs text-slate-500 dark:text-slate-400">Previo al envio</span>
                </div>
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900">
                  <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ selectedItem.file.name }}</div>
                  <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ selectedItem.file.meta }}</div>
                </div>
                <ul class="mt-4 space-y-2">
                  <li
                    v-for="check in selectedItem.checks"
                    :key="check.label"
                    class="flex items-start gap-3 rounded-2xl bg-slate-50 p-3 text-sm dark:bg-slate-900"
                  >
                    <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold" :class="check.ok ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-200'">
                      {{ check.ok ? 'OK' : '!' }}
                    </span>
                    <div>
                      <div class="font-medium text-slate-900 dark:text-slate-100">{{ check.label }}</div>
                      <div class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ check.note }}</div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
          <article
            v-for="insight in insights"
            :key="insight.title"
            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950"
          >
            <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ insight.title }}</div>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ insight.description }}</p>
          </article>
        </div>
      </div>

      <aside class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Orquestacion</h3>
            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">
              En contexto
            </span>
          </div>
          <div class="mt-4 space-y-3">
            <div
              v-for="status in systemStatus"
              :key="status.name"
              class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900"
            >
              <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ status.name }}</div>
                <span class="rounded-full px-2 py-1 text-[11px] font-semibold" :class="status.badge">{{ status.state }}</span>
              </div>
              <p class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ status.note }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
          <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Componentes consolidados</h3>
          <ul class="mt-4 space-y-3 text-sm">
            <li class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-900">
              <div class="font-medium text-slate-900 dark:text-slate-100">Plantillas</div>
              <div class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">Ahora se ven junto al mensaje real, no en una pestaña aislada.</div>
            </li>
            <li class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-900">
              <div class="font-medium text-slate-900 dark:text-slate-100">Preparar archivo</div>
              <div class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">Se integra al detalle del cliente para evitar reprocesos y perdida de contexto.</div>
            </li>
            <li class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-900">
              <div class="font-medium text-slate-900 dark:text-slate-100">Clientes</div>
              <div class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">La ficha rapida reemplaza la tab completa para las ediciones mas comunes.</div>
            </li>
          </ul>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
          <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Alertas detectadas</h3>
          <div class="mt-4 space-y-3">
            <div
              v-for="issue in selectedItem.alerts"
              :key="issue.title"
              class="rounded-2xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/40 dark:bg-amber-950/20"
            >
              <div class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ issue.title }}</div>
              <p class="mt-1 text-xs leading-5 text-amber-800/80 dark:text-amber-200/80">{{ issue.note }}</p>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue';

const stageOptions = [
  { value: 'all', label: 'Todos' },
  { value: 'pending', label: 'Pendientes' },
  { value: 'ready', label: 'Listos' },
  { value: 'sent', label: 'Enviados' },
];

const items = [
  {
    id: 101,
    company: 'Atlantis VIP Downtown',
    contact: 'Maria Perez',
    phone: '+57 300 123 9988',
    email: 'facturas@atlantisvip.co',
    period: 'Abril 2026',
    amount: '$ 240.000',
    server: 'ATLANTIS VIP',
    stage: 'ready',
    channel: 'WhatsApp API',
    issues: 1,
    tags: ['PDF listo', 'Plantilla aplicada', 'Seguimiento'],
    summary: 'Caso listo para salida. El archivo ya fue validado y el canal recomendado sigue siendo WhatsApp API, con email como respaldo.',
    template: 'Hola Maria, te compartimos la factura del periodo Abril 2026. Si necesitas soporte, responde a este mensaje.',
    preview: 'Hola Maria, te compartimos la factura del periodo Abril 2026. Quedo atenta a tu confirmacion.',
    file: {
      name: 'factura-atlantis-vip-abril-2026.pdf',
      meta: 'PDF · 248 KB · generado hace 12 min',
    },
    steps: [
      { index: '1', title: 'Validar contacto y deuda', description: 'La cabecera consolida contacto, periodo, monto y servidor para evitar ir a la pestaña de clientes.' },
      { index: '2', title: 'Ajustar mensaje en contexto', description: 'La plantilla se edita viendo al mismo tiempo el preview final y las alertas del cliente.' },
      { index: '3', title: 'Confirmar archivo y diagnosticos', description: 'El archivo preparado, las validaciones y los errores quedan visibles en un unico bloque lateral.' },
      { index: '4', title: 'Enviar por canal sugerido', description: 'La interfaz destaca un canal principal y deja los respaldos sin repetir acciones en varias pantallas.' },
    ],
    channels: [
      { name: 'WhatsApp API', state: 'Recomendado', reason: 'Numero valido y Kapso disponible. Mejor tiempo de respuesta.', recommended: true, badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200' },
      { name: 'Email', state: 'Respaldo', reason: 'Disponible si el WhatsApp falla o si el cliente pide copia por correo.', recommended: false, badge: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-200' },
      { name: 'Manual', state: 'Solo contingencia', reason: 'Se usa si el envio API se bloquea o requiere seguimiento humano.', recommended: false, badge: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-200' },
    ],
    checks: [
      { label: 'Archivo publico generado', note: 'El PDF ya tiene URL visible para compartir y respaldo manual.', ok: true },
      { label: 'Mensaje con variables resueltas', note: 'Se detecto cliente, mes y monto correctamente en el preview.', ok: true },
      { label: 'Numero listo para API', note: 'Se recomienda revisar el prefijo de pais antes de automatizar en lote.', ok: false },
    ],
    alerts: [
      { title: 'Prefijo por confirmar', note: 'El numero luce correcto pero el flujo sugiere confirmar pais antes de envio masivo.' },
    ],
  },
  {
    id: 102,
    company: 'Atlantis Fast Norte',
    contact: 'Luis Rojas',
    phone: '+57 301 555 1111',
    email: 'admin@atlantisfast.com',
    period: 'Abril 2026',
    amount: '$ 180.000',
    server: 'ATLANTIS FAST',
    stage: 'pending',
    channel: 'Archivo faltante',
    issues: 3,
    tags: ['Sin PDF', 'Pendiente de revision'],
    summary: 'Cliente con deuda activa pero aun sin archivo preparado. El rediseño pone esta ausencia como bloqueo principal.',
    template: 'Hola Luis, te compartimos tu factura del periodo Abril 2026.',
    preview: 'Hola Luis, aun falta adjuntar el archivo final antes del envio.',
    file: { name: 'Sin archivo', meta: 'Se requiere cargar o regenerar el PDF' },
    steps: [
      { index: '1', title: 'Revisar deuda', description: 'El operador identifica primero si el caso amerita salida inmediata.' },
      { index: '2', title: 'Completar adjunto', description: 'La falta de PDF bloquea el resto del flujo y se muestra como prioridad.' },
      { index: '3', title: 'Definir mensaje', description: 'La plantilla puede quedar lista aunque el archivo aun no exista.' },
      { index: '4', title: 'Liberar canal', description: 'Solo se habilita el envio cuando todo el caso esta completo.' },
    ],
    channels: [
      { name: 'WhatsApp API', state: 'Bloqueado', reason: 'No hay archivo asociado todavia.', recommended: false, badge: 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-200' },
      { name: 'Email', state: 'Bloqueado', reason: 'El correo no debe salir sin soporte adjunto.', recommended: false, badge: 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-200' },
      { name: 'Manual', state: 'Pendiente', reason: 'Disponible solo cuando se complete el PDF.', recommended: false, badge: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-200' },
    ],
    checks: [
      { label: 'Archivo publico generado', note: 'No existe un adjunto listo para compartir.', ok: false },
      { label: 'Mensaje con variables resueltas', note: 'La plantilla base ya puede usarse cuando exista el documento.', ok: true },
      { label: 'Canal sugerido', note: 'El sistema debe esconder acciones irrelevantes hasta desbloquear el archivo.', ok: false },
    ],
    alerts: [
      { title: 'Sin PDF preparado', note: 'El rediseño trata este hallazgo como bloqueo visible, no como mensaje perdido en otra pestaña.' },
      { title: 'Demasiados pasos manuales', note: 'Se sugiere agrupar carga y validacion en un solo bloque.' },
    ],
  },
  {
    id: 103,
    company: 'Atlantis Online Centro',
    contact: 'Paola Gomez',
    phone: '+57 302 777 4433',
    email: 'pagos@atlantisonline.co',
    period: 'Marzo 2026',
    amount: '$ 320.000',
    server: 'ATLANTIS ONLINE',
    stage: 'sent',
    channel: 'Email',
    issues: 0,
    tags: ['Enviado', 'Seguimiento cerrado'],
    summary: 'Caso ya despachado. El rediseño lo mantiene visible como historial corto sin mezclarlo con pendientes urgentes.',
    template: 'Hola Paola, te enviamos la factura del periodo Marzo 2026.',
    preview: 'Factura enviada correctamente y en espera de confirmacion de pago.',
    file: { name: 'factura-online-marzo-2026.pdf', meta: 'PDF · 198 KB · enviado ayer' },
    steps: [
      { index: '1', title: 'Historial claro', description: 'Los enviados siguen consultables sin ensuciar la cola activa.' },
      { index: '2', title: 'Seguimiento simple', description: 'Solo se muestran datos clave y accion de reenvio.' },
      { index: '3', title: 'Auditoria compacta', description: 'Fecha, canal y resultado se concentran en la misma ficha.' },
      { index: '4', title: 'Cierre visible', description: 'Evita volver a preparar o editar por error un caso ya despachado.' },
    ],
    channels: [
      { name: 'Email', state: 'Enviado', reason: 'Canal utilizado en el ultimo despacho.', recommended: true, badge: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-200' },
      { name: 'WhatsApp API', state: 'Disponible', reason: 'Puede quedar como segundo envio si el cliente lo solicita.', recommended: false, badge: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' },
      { name: 'Manual', state: 'No necesario', reason: 'No hay alertas activas en este cliente.', recommended: false, badge: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' },
    ],
    checks: [
      { label: 'Archivo publico generado', note: 'El archivo quedo adjunto al historial.', ok: true },
      { label: 'Mensaje con variables resueltas', note: 'No se detectaron inconsistencias en la salida.', ok: true },
      { label: 'Estado de envio sincronizado', note: 'La linea de tiempo muestra fecha y canal final.', ok: true },
    ],
    alerts: [],
  },
];

const insights = [
  {
    title: '1. Menos cambio de contexto',
    description: 'La bandeja actual separa plantillas, PDF, envios y clientes en tabs independientes. El rediseño junta decisiones relacionadas en la ficha activa del caso.',
  },
  {
    title: '2. La seleccion deja de ser un modo',
    description: 'En lugar de activar un estado extra para seleccionar, los checkboxes viven en la lista. Asi las acciones masivas aparecen justo cuando se necesitan.',
  },
  {
    title: '3. Diagnosticos donde importan',
    description: 'Los errores de Kapso, telefono o archivo pasan al lateral del caso y del canal recomendado, no como mensajes sueltos repartidos por la pantalla.',
  },
];

const systemStatus = [
  { name: 'Kapso', state: 'Configurado', note: 'Visible como semaforo lateral, no como metrica aislada. Se relaciona con el canal recomendado.', badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200' },
  { name: 'Plantilla activa', state: 'Lista', note: 'La edicion se hace junto al preview del mensaje y no en una vista paralela.', badge: 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-200' },
  { name: 'Fallback manual', state: 'Disponible', note: 'Sigue existiendo pero queda subordinado a la recomendacion del sistema.', badge: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-200' },
];

const search = ref('');
const stageFilter = ref('all');
const selectedId = ref(items[0].id);
const selectedIds = ref([items[0].id, items[1].id]);

const filteredItems = computed(() => {
  const term = search.value.trim().toLowerCase();

  return items.filter((item) => {
    if (stageFilter.value !== 'all' && item.stage !== stageFilter.value) {
      return false;
    }

    if (!term) {
      return true;
    }

    return [
      item.company,
      item.contact,
      item.email,
      item.phone,
      item.server,
    ].some((value) => String(value).toLowerCase().includes(term));
  });
});

const selectedItem = computed(() => filteredItems.value.find((item) => item.id === selectedId.value)
  || items.find((item) => item.id === selectedId.value)
  || items[0]);

const metrics = computed(() => [
  {
    label: 'Pendientes',
    value: items.filter((item) => item.stage === 'pending').length,
    note: 'Requieren preparacion',
    tone: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-200',
  },
  {
    label: 'Listos para salir',
    value: items.filter((item) => item.stage === 'ready').length,
    note: 'Con canal sugerido',
    tone: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200',
  },
  {
    label: 'Seleccion actual',
    value: selectedIds.value.length,
    note: 'Sin modo adicional',
    tone: 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-200',
  },
  {
    label: 'Alertas visibles',
    value: items.reduce((total, item) => total + item.issues, 0),
    note: 'Priorizadas por caso',
    tone: 'bg-violet-100 text-violet-700 dark:bg-violet-950/40 dark:text-violet-200',
  },
]);

function toggleSelected(id) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((itemId) => itemId !== id);
    return;
  }

  selectedIds.value = [...selectedIds.value, id];
}

function stageLabel(stage) {
  if (stage === 'pending') return 'Pendiente';
  if (stage === 'ready') return 'Listo';
  if (stage === 'sent') return 'Enviado';
  return stage;
}

function stageTone(stage) {
  if (stage === 'ready') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200';
  if (stage === 'pending') return 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-200';
  if (stage === 'sent') return 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
  return 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
}
</script>
