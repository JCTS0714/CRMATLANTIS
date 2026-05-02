<template>
  <div class="p-4">
    <div class="mb-4 flex items-center justify-between">
      <div>
        <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">Calendario</div>
        <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">Tu agenda personal (solo tú la ves).</div>
      </div>

      <button
        type="button"
        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"
        :disabled="saving"
        @click="createEventQuick"
      >
        Nuevo evento
      </button>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="px-3 py-2 text-center text-base font-semibold text-gray-800 dark:text-slate-100">
        {{ currentTitle }}
      </div>
      <FullCalendar ref="calendarRef" :options="calendarOptions" />
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';

import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

import { confirmDialog, toastError, toastSuccess } from '../ui/alerts';
import { useCalendarEventPrompt } from '../composables/useCalendarEventPrompt';
import { useCalendarRuntime } from '../composables/useCalendarRuntime';

// Importar sistema de notificaciones
import '../notifications.js';
import '../calendar-notifications.js';

const authUser = window.__AUTH_USER__ ?? null;
const canDeleteEvents = computed(() => {
  const perms = authUser?.permissions ?? [];
  return Array.isArray(perms) && perms.includes('calendar.delete');
});

const saving = ref(false);
const calendarRef = ref(null);
const currentTitle = ref('');

const refetchEvents = () => {
  try {
    calendarRef.value?.getApi?.().refetchEvents();
  } catch {
    // ignore
  }
};

const {
  start: startCalendarRuntime,
  stop: stopCalendarRuntime,
  scheduleEventNotifications,
  cancelEventNotifications,
} = useCalendarRuntime({ refetchEvents });

const { promptEvent } = useCalendarEventPrompt();

const moveCalendar = (direction) => {
  const api = calendarRef.value?.getApi?.();
  if (!api) return;
  const viewType = api.view?.type;

  if (viewType === 'dayGridMonth') {
    api.incrementDate({ months: direction });
    return;
  }

  if (viewType === 'timeGridWeek') {
    api.incrementDate({ weeks: direction });
    return;
  }

  api.incrementDate({ days: direction });
};

const updateTitle = (view = null) => {
  const api = calendarRef.value?.getApi?.();
  const sourceView = view ?? api?.view;
  const start = sourceView?.currentStart;
  if (!start) return;

  const mid = new Date(start);
  mid.setDate(mid.getDate() + 15);

  currentTitle.value = new Intl.DateTimeFormat('es-PE', {
    year: 'numeric',
    month: 'long',
  }).format(mid);
};

const toLocalInput = (dt) => {
  if (!dt) return '';
  const d = new Date(dt);
  if (Number.isNaN(d.getTime())) return '';
  const pad = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const applyLeadStageChangeFromEventPayload = async (payload) => {
  const eventType = payload?.event_type ?? '';
  if (eventType !== 'lead_followup') return;

  const relatedType = payload?.related_type ?? '';
  const relatedId = Number(payload?.related_id ?? 0);
  const leadStageId = Number(payload?.meta?.lead_stage_id ?? 0);

  if (relatedType !== 'lead' || !Number.isFinite(relatedId) || relatedId < 1) return;
  if (!Number.isFinite(leadStageId) || leadStageId < 1) return;

  try {
    await axios.patch(`/leads/${relatedId}/move-stage`, { stage_id: leadStageId });
    window.dispatchEvent(new CustomEvent('leads:stage-changed-from-calendar', {
      detail: { leadId: relatedId, stageId: leadStageId },
    }));
    toastSuccess('Estado del lead actualizado');
  } catch (e) {
    toastError(e?.response?.data?.message ?? 'Evento guardado, pero no se pudo cambiar el estado del lead.');
  }
};

const normalizeRelatedType = (value) => {
  const text = String(value || '').toLowerCase();
  if (!text) return null;
  if (text.endsWith('lead') || text === 'lead') return 'lead';
  if (text.endsWith('customer') || text === 'customer') return 'customer';
  if (text.endsWith('certificado') || text === 'certificate') return 'certificate';
  return null;
};

const eventTypeClassNames = (eventType) => {
  if (eventType === 'customer_payment') {
    return ['bg-emerald-600', 'border-emerald-700/30', 'dark:bg-emerald-500/70', 'dark:border-emerald-300/20'];
  }
  if (eventType === 'certificate_expiry') {
    return ['bg-amber-600', 'border-amber-700/30', 'dark:bg-amber-500/70', 'dark:border-amber-300/20'];
  }
  if (eventType === 'lead_followup') {
    return ['bg-indigo-600', 'border-indigo-700/30', 'dark:bg-indigo-500/70', 'dark:border-indigo-300/20'];
  }
  if (eventType === 'meeting') {
    return ['bg-violet-600', 'border-violet-700/30', 'dark:bg-violet-500/70', 'dark:border-violet-300/20'];
  }

  return ['bg-blue-600', 'border-blue-700/30', 'dark:bg-blue-500/70', 'dark:border-blue-300/20'];
};

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  customButtons: {
    prevCustom: {
      text: '‹',
      click: () => moveCalendar(-1),
    },
    nextCustom: {
      text: '›',
      click: () => moveCalendar(1),
    },
  },
  headerToolbar: {
    left: 'prevCustom,nextCustom today',
    center: '',
    right: 'dayGridMonth,timeGridWeek,timeGridDay',
  },
  eventDisplay: 'block',
  eventClassNames: (arg) => [
    'rounded-md',
    'px-2',
    'py-1',
    'text-xs',
    'font-semibold',
    'text-white',
    'shadow-sm',
    'border',
    ...eventTypeClassNames(arg?.event?.extendedProps?.event_type || 'general'),
  ],
  selectable: true,
  editable: true,
  eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
  datesSet: (arg) => updateTitle(arg.view),
  events: async (info, successCallback, failureCallback) => {
    try {
      const res = await axios.get('/calendar/events', {
        params: { start: info.startStr, end: info.endStr },
      });
      successCallback(res?.data?.data ?? []);
    } catch (e) {
      toastError('No se pudo cargar el calendario');
      failureCallback(e);
    }
  },
  dateClick: async (arg) => {
    const start = new Date(arg.date);
    const end = new Date(arg.date);
    end.setHours(end.getHours() + 1);

    const result = await promptEvent({
      start_at: toLocalInput(start),
      end_at: toLocalInput(end),
    });

    if (!result || result.action !== 'save') return;
    const payload = result.payload;

    saving.value = true;
    try {
      const response = await axios.post('/calendar/events', payload);
      const newEvent = response.data?.data || response.data;
      await applyLeadStageChangeFromEventPayload(payload);
      
      toastSuccess('Evento creado');
      arg.view.calendar.refetchEvents();
      
      // Programar notificaciones para el nuevo evento
      if (newEvent && newEvent.id) {
        scheduleEventNotifications({
          id: newEvent.id,
          title: payload.title,
          description: payload.description,
          start_at: payload.start_at,
          end_at: payload.end_at,
          reminder_minutes: payload.reminder_minutes
        });
      }
    } catch (e) {
      toastError(e?.response?.data?.message ?? 'No se pudo crear el evento');
    } finally {
      saving.value = false;
    }
  },
  eventClick: async (clickInfo) => {
    const ev = clickInfo.event;
    const ext = ev.extendedProps || {};

    const result = await promptEvent({
      event_type: ext.event_type ?? 'general',
      is_existing: true,
      title: ev.title,
      start_at: toLocalInput(ev.start),
      end_at: toLocalInput(ev.end),
      reminder_minutes: ext.reminder_minutes ?? '',
      location: ext.location ?? '',
      description: ext.description ?? '',
      related_type: normalizeRelatedType(ext.related_type) || '',
      related_id: ext.related_id ?? '',
      lead_stage_id: ext?.meta?.lead_stage_id ?? '',
      meta: ext?.meta ?? null,
      allowDelete: canDeleteEvents.value,
    });

    if (!result) return;

    if (result.action === 'delete') {
      const ok = await confirmDialog({
        title: 'Eliminar evento',
        text: 'Esta acción no se puede deshacer.',
        confirmText: 'Eliminar',
      });
      if (!ok) return;

      saving.value = true;
      try {
        await axios.delete(`/calendar/events/${ev.id}`);
        toastSuccess('Evento eliminado');
        clickInfo.view.calendar.refetchEvents();
        
        // Cancelar notificaciones del evento eliminado
        cancelEventNotifications(ev.id);
      } catch (e) {
        toastError(e?.response?.data?.message ?? 'No se pudo eliminar el evento');
      } finally {
        saving.value = false;
      }
      return;
    }

    if (result.action !== 'save') return;
    const payload = result.payload;

    saving.value = true;
    try {
      await axios.put(`/calendar/events/${ev.id}`, payload);
      await applyLeadStageChangeFromEventPayload(payload);
      toastSuccess('Evento actualizado');
      clickInfo.view.calendar.refetchEvents();
      
      // Reprogramar notificaciones para el evento actualizado
      cancelEventNotifications(ev.id);
      scheduleEventNotifications({
        id: ev.id,
        title: payload.title,
        description: payload.description,
        start_at: payload.start_at,
        end_at: payload.end_at,
        reminder_minutes: payload.reminder_minutes
      });
    } catch (e) {
      toastError(e?.response?.data?.message ?? 'No se pudo actualizar el evento');
    } finally {
      saving.value = false;
    }
  },
  eventDrop: async (info) => {
    const ev = info.event;
    const ext = ev.extendedProps || {};
    saving.value = true;
    try {
      await axios.put(`/calendar/events/${ev.id}`, {
        title: ev.title,
        start_at: ev.start?.toISOString(),
        end_at: ev.end?.toISOString() ?? null,
        reminder_minutes: ext.reminder_minutes ?? null,
        location: ext.location ?? null,
        description: ext.description ?? null,
        event_type: ext.event_type ?? 'general',
        related_type: normalizeRelatedType(ext.related_type),
        related_id: ext.related_id ?? null,
      });
      toastSuccess('Evento movido');
    } catch (e) {
      info.revert();
      toastError(e?.response?.data?.message ?? 'No se pudo mover el evento');
    } finally {
      saving.value = false;
    }
  },
  eventResize: async (info) => {
    const ev = info.event;
    const ext = ev.extendedProps || {};
    saving.value = true;
    try {
      await axios.put(`/calendar/events/${ev.id}`, {
        title: ev.title,
        start_at: ev.start?.toISOString(),
        end_at: ev.end?.toISOString() ?? null,
        reminder_minutes: ext.reminder_minutes ?? null,
        location: ext.location ?? null,
        description: ext.description ?? null,
        event_type: ext.event_type ?? 'general',
        related_type: normalizeRelatedType(ext.related_type),
        related_id: ext.related_id ?? null,
      });
      toastSuccess('Evento actualizado');
    } catch (e) {
      info.revert();
      toastError(e?.response?.data?.message ?? 'No se pudo actualizar el evento');
    } finally {
      saving.value = false;
    }
  },
}));

const createEventQuick = async () => {
  const start = new Date();
  const end = new Date(start);
  end.setHours(end.getHours() + 1);

  const result = await promptEvent({
    start_at: toLocalInput(start),
    end_at: toLocalInput(end),
  });

  if (!result || result.action !== 'save') return;
  const payload = result.payload;

  saving.value = true;
  try {
    const response = await axios.post('/calendar/events', payload);
    const newEvent = response.data?.data || response.data;
    await applyLeadStageChangeFromEventPayload(payload);
    
    toastSuccess('Evento creado');
    refetchEvents();
    
    // Programar notificaciones para el nuevo evento
    if (newEvent && newEvent.id) {
      scheduleEventNotifications({
        id: newEvent.id,
        title: payload.title,
        description: payload.description,
        start_at: payload.start_at,
        end_at: payload.end_at,
        reminder_minutes: payload.reminder_minutes
      });
    }
  } catch (e) {
    toastError(e?.response?.data?.message ?? 'No se pudo crear el evento');
  } finally {
    saving.value = false;
  }
};

const parseDateParam = (v) => {
  if (!v) return null;
  const d = new Date(v);
  if (!Number.isNaN(d.getTime())) return d;
  const d2 = new Date(String(v).replace(' ', 'T'));
  if (!Number.isNaN(d2.getTime())) return d2;
  return null;
};

const maybePrefillFromUrl = async () => {
  const url = new URL(window.location.href);
  const relatedType = url.searchParams.get('related_type') || '';
  const relatedId = url.searchParams.get('related_id') || '';
  const title = url.searchParams.get('title') || '';
  const description = url.searchParams.get('description') || '';

  if (!relatedType && !relatedId && !title && !description) return;

  const startParam = parseDateParam(url.searchParams.get('start'));
  const endParam = parseDateParam(url.searchParams.get('end'));

  const start = startParam ?? new Date();
  const end = endParam ?? new Date(start.getTime() + 60 * 60 * 1000);

  const result = await promptEvent({
    event_type: relatedType === 'lead' ? 'lead_followup' : 'general',
    title: title || '',
    start_at: toLocalInput(start),
    end_at: toLocalInput(end),
    related_type: relatedType,
    related_id: relatedId,
    description,
  });

  // Clear params even if user cancels, to prevent re-opening.
  url.searchParams.delete('related_type');
  url.searchParams.delete('related_id');
  url.searchParams.delete('title');
  url.searchParams.delete('description');
  url.searchParams.delete('start');
  url.searchParams.delete('end');
  window.history.replaceState({}, '', url.pathname + url.search);

  if (!result || result.action !== 'save') return;
  const payload = result.payload;

  saving.value = true;
  try {
    const response = await axios.post('/calendar/events', payload);
    const newEvent = response.data?.data || response.data;
    await applyLeadStageChangeFromEventPayload(payload);
    
    toastSuccess('Evento creado');
    refetchEvents();
    
    // Programar notificaciones para el nuevo evento (desde URL params)
    if (newEvent && newEvent.id) {
      scheduleEventNotifications({
        id: newEvent.id,
        title: payload.title,
        description: payload.description,
        start_at: payload.start_at,
        end_at: payload.end_at,
        reminder_minutes: payload.reminder_minutes
      });
    }
  } catch (e) {
    toastError(e?.response?.data?.message ?? 'No se pudo crear el evento');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  startCalendarRuntime({
    onReady: () => {
      const api = calendarRef.value?.getApi?.();
      if (api) {
        api.gotoDate(new Date());
      }
      updateTitle();
    },
    onAfterReady: () => {
      maybePrefillFromUrl();
    },
  });
});

onBeforeUnmount(() => {
  stopCalendarRuntime();
});
</script>

<style>
/* Keep FullCalendar readable in dark mode without adding new theme colors */
.fc {
  --fc-border-color: rgba(148, 163, 184, 0.25);
}
html.dark .fc {
  --fc-page-bg-color: transparent;
  --fc-neutral-bg-color: rgba(15, 23, 42, 0.6);
  --fc-neutral-text-color: rgba(226, 232, 240, 0.9);
  --fc-border-color: rgba(148, 163, 184, 0.2);
  --fc-today-bg-color: rgba(59, 130, 246, 0.10);

  /* Buttons */
  --fc-button-text-color: rgba(226, 232, 240, 0.95);
  --fc-button-bg-color: rgba(30, 41, 59, 0.8);
  --fc-button-border-color: rgba(148, 163, 184, 0.25);
  --fc-button-hover-bg-color: rgba(51, 65, 85, 0.9);
  --fc-button-hover-border-color: rgba(148, 163, 184, 0.35);
  --fc-button-active-bg-color: rgba(59, 130, 246, 0.35);
  --fc-button-active-border-color: rgba(59, 130, 246, 0.45);
}

html.dark .fc,
html.dark .fc a {
  color: rgba(226, 232, 240, 0.92);
}

html.dark .fc .fc-toolbar-title {
  color: rgba(226, 232, 240, 0.95);
}

.fc .fc-toolbar-title {
  display: none;
}

html.dark .fc .fc-col-header-cell-cushion,
html.dark .fc .fc-daygrid-day-number {
  color: rgba(226, 232, 240, 0.92);
}

/* Make month-view events look like readable pills */
.fc .fc-daygrid-event {
  margin: 2px 2px;
}

.fc .fc-event,
.fc .fc-event .fc-event-main {
  color: inherit;
}

.fc .fc-event-title,
.fc .fc-event-time {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
