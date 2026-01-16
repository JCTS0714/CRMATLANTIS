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

const authUser = window.__AUTH_USER__ ?? null;
const canDeleteEvents = computed(() => {
  const perms = authUser?.permissions ?? [];
  return Array.isArray(perms) && perms.includes('calendar.delete');
});

const saving = ref(false);
const calendarRef = ref(null);

const refetchEvents = () => {
  try {
    calendarRef.value?.getApi?.().refetchEvents();
  } catch {
    // ignore
  }
};

const toLocalInput = (dt) => {
  if (!dt) return '';
  const d = new Date(dt);
  if (Number.isNaN(d.getTime())) return '';
  const pad = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const promptEvent = async ({
  title = '',
  start_at = '',
  end_at = '',
  all_day = false,
  reminder_minutes = '',
  related_type = '',
  related_id = '',
  description = '',
  location = '',
  allowDelete = false,
} = {}) => {
  const escapeHtml = (v) =>
    String(v ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

  const inputClass =
    'mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100';

  const html = `
    <div class="grid gap-3 text-left">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Título</label>
        <input id="sw-ev-title" class="${inputClass}" value="${escapeHtml(title)}" />
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Inicio</label>
          <input id="sw-ev-start" type="datetime-local" class="${inputClass}" value="${escapeHtml(start_at)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Fin</label>
          <input id="sw-ev-end" type="datetime-local" class="${inputClass}" value="${escapeHtml(end_at)}" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Recordatorio (minutos antes)</label>
          <input id="sw-ev-reminder" type="number" min="1" max="10080" class="${inputClass}" value="${escapeHtml(reminder_minutes)}" placeholder="(opcional)" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Ubicación</label>
          <input id="sw-ev-location" class="${inputClass}" value="${escapeHtml(location)}" placeholder="(opcional)" />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Descripción</label>
        <textarea id="sw-ev-description" rows="3" class="${inputClass}" placeholder="(opcional)">${escapeHtml(description)}</textarea>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Relacionado a</label>
          <select id="sw-ev-related_type" class="${inputClass}">
            <option value="" ${related_type ? '' : 'selected'}>(ninguno)</option>
            <option value="lead" ${related_type === 'lead' ? 'selected' : ''}>Lead</option>
            <option value="customer" ${related_type === 'customer' ? 'selected' : ''}>Cliente</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Relacionado</label>
          <input id="sw-ev-related_search" class="${inputClass}" placeholder="Escribe para buscar…" autocomplete="off" />
          <input id="sw-ev-related_id" type="hidden" value="${escapeHtml(related_id)}" />
          <div id="sw-ev-related_results" class="mt-2 max-h-44 overflow-auto rounded-lg border border-gray-200 bg-white p-1 text-left shadow-sm dark:border-slate-800 dark:bg-slate-950" style="display:none;"></div>
          <div id="sw-ev-related_hint" class="mt-1 text-xs text-gray-500 dark:text-slate-400">Selecciona un resultado para llenar el vínculo (opcional).</div>
        </div>
      </div>
    </div>
  `;

  const { default: Swal } = await import('sweetalert2');
  const { default: _unused } = { default: null };

  // We reuse the same styling through the global mixin in alerts.js by importing confirmDialog/toasts.
  // For inputs, we use SweetAlert directly here.

  const res = await Swal.fire({
    title: 'Evento',
    html,
    focusConfirm: false,
    showCancelButton: true,
    showDenyButton: !!allowDelete,
    confirmButtonText: 'Guardar',
    cancelButtonText: 'Cancelar',
    denyButtonText: 'Eliminar',
    buttonsStyling: false,
    didOpen: async () => {
      const typeEl = document.getElementById('sw-ev-related_type');
      const searchEl = document.getElementById('sw-ev-related_search');
      const idEl = document.getElementById('sw-ev-related_id');
      const resultsEl = document.getElementById('sw-ev-related_results');

      if (!typeEl || !searchEl || !idEl || !resultsEl) return;

      let debounceTimer = null;

      const clearResults = () => {
        resultsEl.innerHTML = '';
        resultsEl.style.display = 'none';
      };

      const setSelected = (item) => {
        idEl.value = item?.id ? String(item.id) : '';
        searchEl.value = item?.label ? String(item.label) : '';
        clearResults();
      };

      const setEnabled = () => {
        const t = typeEl.value || '';
        const enabled = !!t;
        searchEl.disabled = !enabled;
        searchEl.placeholder = enabled ? `Buscar ${t === 'lead' ? 'lead' : 'cliente'}…` : 'Selecciona tipo primero';
        if (!enabled) {
          setSelected(null);
        }
      };

      const renderResults = (items) => {
        resultsEl.innerHTML = '';
        if (!items?.length) {
          clearResults();
          return;
        }

        resultsEl.style.display = 'block';
        for (const it of items) {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className =
            'flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:text-slate-100 dark:hover:bg-slate-900 dark:focus:ring-blue-500/30';
          btn.textContent = it.label;
          btn.addEventListener('click', () => setSelected(it));
          resultsEl.appendChild(btn);
        }
      };

      const lookupById = async () => {
        const t = typeEl.value || '';
        const id = Number(idEl.value);
        if (!t || !Number.isFinite(id) || id < 1) return;
        try {
          const res = await axios.get('/related-lookup', { params: { type: t, id } });
          if (res?.data?.data?.label) {
            searchEl.value = res.data.data.label;
          } else {
            searchEl.value = `#${id}`;
          }
        } catch {
          // ignore
        }
      };

      const doSearch = async () => {
        const t = typeEl.value || '';
        const q = (searchEl.value || '').trim();
        if (!t || q.length < 2) {
          clearResults();
          return;
        }

        try {
          const res = await axios.get('/related-lookup', { params: { type: t, q, limit: 8 } });
          renderResults(res?.data?.data ?? []);
        } catch {
          clearResults();
        }
      };

      typeEl.addEventListener('change', () => {
        setEnabled();
        clearResults();
      });

      searchEl.addEventListener('input', () => {
        // If user edits text after selecting, force re-select.
        idEl.value = '';
        if (debounceTimer) window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => {
          doSearch();
        }, 250);
      });

      searchEl.addEventListener('blur', () => {
        // allow click selection first
        window.setTimeout(() => clearResults(), 150);
      });

      searchEl.addEventListener('focus', () => {
        // if already typed, show results again
        doSearch();
      });

      setEnabled();
      await lookupById();
    },
    customClass: {
      popup:
        'rounded-xl border border-gray-200 bg-white text-gray-900 shadow-xl dark:!border-slate-800 dark:!bg-slate-900 dark:!text-slate-100',
      backdrop: 'bg-black/60',
      title: 'text-base font-semibold text-gray-900 dark:text-slate-100',
      htmlContainer: 'text-sm text-gray-600 dark:text-slate-300',
      actions: 'gap-2',
      confirmButton:
        'inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-60',
      cancelButton:
        'inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800',
      denyButton:
        'inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-300 disabled:opacity-60',
    },
    preConfirm: () => {
      const t = document.getElementById('sw-ev-title')?.value?.trim() ?? '';
      const s = document.getElementById('sw-ev-start')?.value ?? '';
      const e = document.getElementById('sw-ev-end')?.value ?? '';
      const rm = document.getElementById('sw-ev-reminder')?.value ?? '';
      const loc = document.getElementById('sw-ev-location')?.value?.trim() ?? '';
      const desc = document.getElementById('sw-ev-description')?.value?.trim() ?? '';
      const rt = document.getElementById('sw-ev-related_type')?.value ?? '';
      const rid = document.getElementById('sw-ev-related_id')?.value ?? '';

      if (!t) {
        Swal.showValidationMessage('El título es requerido.');
        return false;
      }
      if (!s) {
        Swal.showValidationMessage('La fecha/hora de inicio es requerida.');
        return false;
      }
      if (e && e < s) {
        Swal.showValidationMessage('La fecha/hora fin no puede ser menor que inicio.');
        return false;
      }
      if (rt && !rid) {
        Swal.showValidationMessage('Selecciona un registro relacionado (lead/cliente) de la lista.');
        return false;
      }

      const reminderMinutes = rm ? Number(rm) : null;
      if (rm && (!Number.isFinite(reminderMinutes) || reminderMinutes < 1)) {
        Swal.showValidationMessage('El recordatorio debe ser un número válido.');
        return false;
      }

      return {
        title: t,
        start_at: new Date(s).toISOString(),
        end_at: e ? new Date(e).toISOString() : null,
        reminder_minutes: reminderMinutes,
        location: loc || null,
        description: desc || null,
        related_type: rt || null,
        related_id: rid ? Number(rid) : null,
      };
    },
  });

  if (res.isDenied) return { action: 'delete' };
  if (res.isConfirmed) return { action: 'save', payload: res.value };
  return null;
};

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay',
  },
  eventDisplay: 'block',
  eventClassNames: () => [
    'rounded-md',
    'px-2',
    'py-1',
    'text-xs',
    'font-semibold',
    'bg-blue-600',
    'text-white',
    'shadow-sm',
    'border',
    'border-blue-700/30',
    'dark:bg-blue-500/70',
    'dark:border-blue-300/20',
  ],
  selectable: true,
  editable: true,
  eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
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
      await axios.post('/calendar/events', payload);
      toastSuccess('Evento creado');
      arg.view.calendar.refetchEvents();
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
      title: ev.title,
      start_at: toLocalInput(ev.start),
      end_at: toLocalInput(ev.end),
      reminder_minutes: ext.reminder_minutes ?? '',
      location: ext.location ?? '',
      description: ext.description ?? '',
      related_type: ext.related_type ? (ext.related_type.endsWith('Lead') ? 'lead' : ext.related_type.endsWith('Customer') ? 'customer' : '') : '',
      related_id: ext.related_id ?? '',
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
      toastSuccess('Evento actualizado');
      clickInfo.view.calendar.refetchEvents();
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
        related_type: ext.related_type ? (ext.related_type.endsWith('Lead') ? 'lead' : ext.related_type.endsWith('Customer') ? 'customer' : null) : null,
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
        related_type: ext.related_type ? (ext.related_type.endsWith('Lead') ? 'lead' : ext.related_type.endsWith('Customer') ? 'customer' : null) : null,
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
    await axios.post('/calendar/events', payload);
    toastSuccess('Evento creado');
    refetchEvents();
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

  if (!relatedType && !relatedId && !title) return;

  const startParam = parseDateParam(url.searchParams.get('start'));
  const endParam = parseDateParam(url.searchParams.get('end'));

  const start = startParam ?? new Date();
  const end = endParam ?? new Date(start.getTime() + 60 * 60 * 1000);

  const result = await promptEvent({
    title: title || '',
    start_at: toLocalInput(start),
    end_at: toLocalInput(end),
    related_type: relatedType,
    related_id: relatedId,
  });

  // Clear params even if user cancels, to prevent re-opening.
  url.searchParams.delete('related_type');
  url.searchParams.delete('related_id');
  url.searchParams.delete('title');
  url.searchParams.delete('start');
  url.searchParams.delete('end');
  window.history.replaceState({}, '', url.pathname + url.search);

  if (!result || result.action !== 'save') return;
  const payload = result.payload;

  saving.value = true;
  try {
    await axios.post('/calendar/events', payload);
    toastSuccess('Evento creado');
    refetchEvents();
  } catch (e) {
    toastError(e?.response?.data?.message ?? 'No se pudo crear el evento');
  } finally {
    saving.value = false;
  }
};

const onCalendarRefetchEvent = () => refetchEvents();

onMounted(() => {
  window.addEventListener('calendar:refetch', onCalendarRefetchEvent);
  // allow calendar to mount first
  setTimeout(() => {
    maybePrefillFromUrl();
  }, 0);
});

onBeforeUnmount(() => {
  window.removeEventListener('calendar:refetch', onCalendarRefetchEvent);
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
