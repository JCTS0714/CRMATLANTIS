import { ref } from 'vue';
import axios from 'axios';

export function useCalendarEventPrompt() {
  const leadStagesCache = ref([]);

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

  const promptEvent = async ({
    event_type = 'general',
    is_existing = false,
    title = '',
    start_at = '',
    end_at = '',
    all_day = false,
    reminder_minutes = '',
    related_type = '',
    related_id = '',
    lead_stage_id = '',
    meta = null,
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
        ${!is_existing && isLocalAutofillEnabled ? '<div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-800/40 dark:bg-amber-950/20 dark:text-amber-300">Solo local: autollenado para pruebas.<button id="sw-ev-autofill-create-local" type="button" class="ml-2 inline-flex items-center rounded-md border border-amber-300 bg-white px-2 py-1 text-xs font-medium text-amber-800 hover:bg-amber-100 dark:border-amber-700 dark:bg-slate-900 dark:text-amber-300 dark:hover:bg-amber-900/30">Rellenar test</button></div>' : ''}
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Tipo de evento</label>
          <select id="sw-ev-event_type" class="${inputClass}">
            <option value="general" ${event_type === 'general' ? 'selected' : ''}>General</option>
            <option value="meeting" ${event_type === 'meeting' ? 'selected' : ''}>Reunión</option>
            <option value="lead_followup" ${event_type === 'lead_followup' ? 'selected' : ''}>Seguimiento de lead</option>
            <option value="customer_payment" ${event_type === 'customer_payment' ? 'selected' : ''}>Fecha de pago</option>
            <option value="certificate_expiry" ${event_type === 'certificate_expiry' ? 'selected' : ''}>Vencimiento de certificado</option>
          </select>
        </div>

        <div id="sw-ev-lead_stage_wrap" style="display:none;">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Cambiar estado del lead</label>
          <select id="sw-ev-lead_stage_id" class="${inputClass}">
          </select>
          <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Solo aplica para tipo "Seguimiento de lead".</div>
        </div>

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
              <option value="certificate" ${related_type === 'certificate' ? 'selected' : ''}>Certificado</option>
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
        const eventTypeEl = document.getElementById('sw-ev-event_type');
        const startEl = document.getElementById('sw-ev-start');
        const endEl = document.getElementById('sw-ev-end');
        const reminderEl = document.getElementById('sw-ev-reminder');
        const typeEl = document.getElementById('sw-ev-related_type');
        const searchEl = document.getElementById('sw-ev-related_search');
        const idEl = document.getElementById('sw-ev-related_id');
        const resultsEl = document.getElementById('sw-ev-related_results');
        const leadStageWrapEl = document.getElementById('sw-ev-lead_stage_wrap');
        const leadStageEl = document.getElementById('sw-ev-lead_stage_id');
        const autofillButton = document.getElementById('sw-ev-autofill-create-local');

        if (!typeEl || !searchEl || !idEl || !resultsEl || !eventTypeEl || !startEl || !endEl || !reminderEl || !leadStageWrapEl || !leadStageEl) return;

        if (autofillButton) {
          const module = await getLocalAutofillModule();
          if (module?.autofillCalendarEventCreateModalForm) {
            autofillButton.addEventListener('click', () => {
              module.autofillCalendarEventCreateModalForm();
            });
          }
        }

        let debounceTimer = null;

        const clearResults = () => {
          resultsEl.innerHTML = '';
          resultsEl.style.display = 'none';
        };

        const syncLeadStageFromLeadItem = (item) => {
          if (!item || (typeEl.value || '') !== 'lead') return;

          const stageId = Number(item.stage_id ?? 0);
          if (!Number.isFinite(stageId) || stageId < 1) return;

          const targetValue = String(stageId);
          const hasOption = Array.from(leadStageEl.options).some((option) => option.value === targetValue);
          if (hasOption) {
            leadStageEl.value = targetValue;
          }
        };

        const setSelected = (item) => {
          idEl.value = item?.id ? String(item.id) : '';
          searchEl.value = item?.label ? String(item.label) : '';
          syncLeadStageFromLeadItem(item);
          clearResults();
        };

        const setEnabled = () => {
          const t = typeEl.value || '';
          const enabled = !!t;
          searchEl.disabled = !enabled;
          const label = t === 'lead' ? 'lead' : t === 'customer' ? 'cliente' : 'certificado';
          searchEl.placeholder = enabled ? `Buscar ${label}…` : 'Selecciona tipo primero';
          if (!enabled) {
            setSelected(null);
          }
        };

        const loadLeadStages = async () => {
          if (Array.isArray(leadStagesCache.value) && leadStagesCache.value.length > 0) {
            return leadStagesCache.value;
          }

          try {
            const boardResponse = await axios.get('/leads/board-data', { params: { limit: 0 } });
            const stagesFromBoard = boardResponse?.data?.data?.stages ?? [];
            if (Array.isArray(stagesFromBoard) && stagesFromBoard.length > 0) {
              leadStagesCache.value = stagesFromBoard.map((stage) => ({
                id: Number(stage.id),
                name: String(stage.name || '').trim(),
              })).filter((stage) => Number.isFinite(stage.id) && stage.id > 0 && stage.name !== '');

              return leadStagesCache.value;
            }
          } catch {
            // fallback below
          }

          try {
            const tableResponse = await axios.get('/leads/data', { params: { per_page: 1 } });
            const stagesFromTable = tableResponse?.data?.data?.stages ?? [];
            if (Array.isArray(stagesFromTable) && stagesFromTable.length > 0) {
              leadStagesCache.value = stagesFromTable.map((stage) => ({
                id: Number(stage.id),
                name: String(stage.name || '').trim(),
              })).filter((stage) => Number.isFinite(stage.id) && stage.id > 0 && stage.name !== '');
            }
          } catch {
            leadStagesCache.value = [];
          }

          return leadStagesCache.value;
        };

        const fillLeadStageOptions = async () => {
          const stages = await loadLeadStages();
          leadStageEl.innerHTML = '';

          for (const stage of stages) {
            const option = document.createElement('option');
            option.value = String(stage.id);
            option.textContent = stage.name;
            leadStageEl.appendChild(option);
          }

          if (!stages.length) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No hay etapas disponibles';
            leadStageEl.appendChild(option);
          }

          leadStageEl.disabled = !stages.length;

          if (lead_stage_id) {
            leadStageEl.value = String(lead_stage_id);
          } else if (stages.length > 0) {
            leadStageEl.value = String(stages[0].id);
          }
        };

        const applyEventTypeRules = () => {
          const eventType = eventTypeEl.value || 'general';
          const isAllDayType = eventType === 'customer_payment' || eventType === 'certificate_expiry';
          const isLockedAutoEvent = is_existing && isAllDayType;
          const isLeadFollowup = eventType === 'lead_followup';

          leadStageWrapEl.style.display = isLeadFollowup ? 'block' : 'none';
          if (!isLeadFollowup) {
            leadStageEl.value = '';
          }

          if (isLeadFollowup && !typeEl.value) {
            typeEl.value = 'lead';
          }

          if (eventType === 'customer_payment') {
            typeEl.value = 'customer';
          }

          if (eventType === 'certificate_expiry') {
            typeEl.value = 'certificate';
          }

          if (isAllDayType) {
            if (startEl.type !== 'date') {
              startEl.type = 'date';
            }

            if (startEl.value && startEl.value.length > 10) {
              startEl.value = startEl.value.slice(0, 10);
            }

            endEl.value = '';
            endEl.disabled = true;

            if (!String(reminderEl.value || '').trim()) {
              reminderEl.value = '1440';
            }
          } else {
            if (startEl.type !== 'datetime-local') {
              startEl.type = 'datetime-local';
              if (startEl.value && startEl.value.length === 10) {
                startEl.value = `${startEl.value}T09:00`;
              }
            }
            endEl.disabled = false;
          }

          eventTypeEl.disabled = isLockedAutoEvent;
          typeEl.disabled = isLockedAutoEvent;
          searchEl.disabled = isLockedAutoEvent ? true : searchEl.disabled;

          setEnabled();

          if (isLockedAutoEvent) {
            searchEl.disabled = true;
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
            const item = res?.data?.data ?? null;
            if (item?.label) {
              searchEl.value = item.label;
              syncLeadStageFromLeadItem(item);
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

        eventTypeEl.addEventListener('change', () => {
          applyEventTypeRules();
          clearResults();
        });

        searchEl.addEventListener('input', () => {
          idEl.value = '';
          if (debounceTimer) window.clearTimeout(debounceTimer);
          debounceTimer = window.setTimeout(() => {
            doSearch();
          }, 250);
        });

        searchEl.addEventListener('blur', () => {
          window.setTimeout(() => clearResults(), 150);
        });

        searchEl.addEventListener('focus', () => {
          doSearch();
        });

        await fillLeadStageOptions();
        applyEventTypeRules();
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
        const et = document.getElementById('sw-ev-event_type')?.value ?? 'general';
        const s = document.getElementById('sw-ev-start')?.value ?? '';
        const e = document.getElementById('sw-ev-end')?.value ?? '';
        const rm = document.getElementById('sw-ev-reminder')?.value ?? '';
        const loc = document.getElementById('sw-ev-location')?.value?.trim() ?? '';
        const desc = document.getElementById('sw-ev-description')?.value?.trim() ?? '';
        let rt = document.getElementById('sw-ev-related_type')?.value ?? '';
        const rid = document.getElementById('sw-ev-related_id')?.value ?? '';
        const leadStageRaw = document.getElementById('sw-ev-lead_stage_id')?.value ?? '';
        const isAllDayType = et === 'customer_payment' || et === 'certificate_expiry';

        if (et === 'customer_payment' && !rt) {
          rt = 'customer';
        }

        if (et === 'certificate_expiry' && !rt) {
          rt = 'certificate';
        }

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
          Swal.showValidationMessage('Selecciona un registro relacionado de la lista.');
          return false;
        }

        if (et === 'customer_payment' && rt !== 'customer') {
          Swal.showValidationMessage('Los pagos deben vincularse a un cliente.');
          return false;
        }

        const leadStageId = Number(leadStageRaw);
        if (!Number.isFinite(leadStageId) || leadStageId < 1) {
          Swal.showValidationMessage('Selecciona un estado de lead válido.');
          return false;
        }

        if (et === 'lead_followup' && (rt !== 'lead' || !rid)) {
          Swal.showValidationMessage('Para cambiar estado, debes vincular el evento a un lead.');
          return false;
        }

        if (et === 'lead_followup' && !leadStageRaw) {
          Swal.showValidationMessage('Selecciona una etapa para el lead.');
          return false;
        }

        const reminderMinutes = rm ? Number(rm) : (isAllDayType ? 1440 : null);
        if (rm && (!Number.isFinite(reminderMinutes) || reminderMinutes < 1)) {
          Swal.showValidationMessage('El recordatorio debe ser un número válido.');
          return false;
        }

        const startValue = isAllDayType ? `${s}T00:00` : s;

        const nextMeta = (meta && typeof meta === 'object' && !Array.isArray(meta)) ? { ...meta } : {};
        if (et === 'lead_followup') {
          nextMeta.lead_stage_id = leadStageId;
        } else {
          delete nextMeta.lead_stage_id;
        }

        return {
          event_type: et,
          title: t,
          all_day: isAllDayType,
          start_at: new Date(startValue).toISOString(),
          end_at: isAllDayType ? null : (e ? new Date(e).toISOString() : null),
          reminder_minutes: reminderMinutes,
          location: loc || null,
          description: desc || null,
          related_type: rt || null,
          related_id: rid ? Number(rid) : null,
          meta: Object.keys(nextMeta).length > 0 ? nextMeta : null,
        };
      },
    });

    if (res.isDenied) return { action: 'delete' };
    if (res.isConfirmed) return { action: 'save', payload: res.value };
    return null;
  };

  return {
    promptEvent,
  };
}