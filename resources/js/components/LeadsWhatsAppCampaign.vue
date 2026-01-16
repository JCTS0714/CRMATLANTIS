<template>
  <section>
    <div class="grid gap-4 lg:grid-cols-2">
      <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-slate-900 dark:border-slate-800">
        <div class="p-4 border-b border-gray-200 dark:border-slate-800">
          <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">Nueva campaña WhatsApp (manual asistido)</div>
          <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
            El sistema genera mensajes personalizados y abre WhatsApp. No envía automáticamente (sin API).
          </div>
        </div>

        <div class="p-4 space-y-4">
          <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <button
              type="button"
              class="px-3 py-2 text-sm font-medium rounded-md transition"
              :class="source === 'leads' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-800'"
              @click="setSource('leads')"
            >
              Leads
            </button>
            <button
              type="button"
              class="px-3 py-2 text-sm font-medium rounded-md transition"
              :class="source === 'customers' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-800'"
              @click="setSource('customers')"
            >
              Clientes
            </button>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-slate-200">Nombre (opcional)</label>
            <input
              v-model="form.name"
              type="text"
              class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
              placeholder="Ej: Aviso de promoción Enero"
            />
          </div>

          <div>
            <div class="flex items-center justify-between gap-3">
              <label class="block text-xs font-medium text-gray-700 dark:text-slate-200">Mensaje</label>
              <div class="flex flex-wrap gap-2">
                <button
                  type="button"
                  class="rounded-md border border-gray-200 bg-white px-2 py-1 text-xs text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  @click="insertVar('{{first_name}}')"
                >
                  + first_name
                </button>
                <button
                  type="button"
                  class="rounded-md border border-gray-200 bg-white px-2 py-1 text-xs text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                  @click="insertVar('{{company_name}}')"
                >
                  + company_name
                </button>
              </div>
            </div>

            <textarea
              v-model="form.message_template"
              rows="6"
              class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30"
              placeholder="Hola {{first_name}}, te escribo por..."
            />
            <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">
              Variables: <span class="font-mono" v-pre>{{first_name}}</span>, <span class="font-mono" v-pre>{{company_name}}</span>
            </div>
          </div>

          <div class="grid gap-3 sm:grid-cols-3">
            <div v-if="source === 'leads'">
              <label class="block text-xs font-medium text-gray-700 dark:text-slate-200">Etapa</label>
              <select
                v-model.number="filters.stage_id"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
              >
                <option :value="null">Todas</option>
                <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>

            <div :class="source === 'leads' ? 'sm:col-span-2' : 'sm:col-span-3'">
              <label class="block text-xs font-medium text-gray-700 dark:text-slate-200">Buscar</label>
              <input
                v-model="filters.q"
                type="search"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 placeholder:text-gray-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30"
                placeholder="Nombre, empresa, teléfono…"
              />
            </div>
          </div>

          <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-700 dark:text-slate-200">Límite</label>
            <input
              v-model.number="filters.limit"
              type="number"
              min="1"
              max="500"
              class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/30"
            />

            <label class="ml-2 inline-flex items-center gap-2 text-xs text-gray-700 dark:text-slate-200">
              <input
                type="checkbox"
                v-model="filters.only_with_phone"
                :disabled="(counts.total ?? 0) > 0 && (counts.with_phone ?? 0) === 0"
              />
              Solo con teléfono
            </label>

            <button
              type="button"
              class="ml-auto inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-200"
              :disabled="loadingRecipients"
              @click="loadRecipients"
            >
              {{ loadingRecipients ? 'Cargando…' : 'Cargar destinatarios' }}
            </button>
          </div>

          <div v-if="recipientsError" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
            {{ recipientsError }}
          </div>

          <div v-else class="text-xs text-gray-600 dark:text-slate-300">
            Total: <span class="font-semibold">{{ counts.total }}</span>
            · Con teléfono: <span class="font-semibold">{{ counts.with_phone ?? 0 }}</span>
            · Sin teléfono: <span class="font-semibold">{{ counts.without_phone ?? 0 }}</span>
            <span v-if="filters.only_with_phone && availableContacts.length === 0 && (counts.without_phone ?? 0) > 0">
              · Desmarca <span class="font-semibold">Solo con teléfono</span> para verlos.
            </span>
          </div>

          <div class="rounded-lg border border-gray-200 dark:border-slate-800 overflow-hidden">
            <div class="flex items-center gap-3 bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:bg-slate-800 dark:text-slate-200">
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" />
                Seleccionar todos
              </label>
              <div class="ml-auto">Seleccionados: {{ selectedIds.size }} / {{ availableContacts.length }}</div>
            </div>

            <div class="max-h-[360px] overflow-y-auto bg-white dark:bg-slate-900">
              <div
                v-for="c in availableContacts"
                :key="c.id"
                class="flex items-center gap-3 border-t border-gray-100 px-3 py-2 text-sm dark:border-slate-800"
              >
                <input type="checkbox" :checked="selectedIds.has(c.id)" @change="toggleOne(c.id)" />
                <div class="min-w-0 flex-1">
                  <div class="truncate text-gray-900 dark:text-slate-100">
                    {{ c.display_name || 'Sin nombre' }}
                    <span v-if="c.secondary" class="text-gray-500 dark:text-slate-400">· {{ c.secondary }}</span>
                  </div>
                  <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-600 dark:text-slate-300">
                    <span>{{ c.phone || 'Sin teléfono' }}</span>
                    <span v-if="c.stage_name">· {{ c.stage_name }}</span>
                  </div>
                </div>
              </div>

              <div v-if="availableContacts.length === 0" class="p-3 text-sm text-gray-600 dark:text-slate-300">
                No hay resultados con esos filtros.
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 disabled:opacity-50"
              :disabled="creatingCampaign || selectedIds.size === 0 || !form.message_template.trim()"
              @click="createCampaign"
            >
              {{ creatingCampaign ? 'Creando…' : 'Crear campaña' }}
            </button>

            <div v-if="createdCampaignId" class="text-xs text-gray-600 dark:text-slate-300">
              Campaña creada: <span class="font-semibold">#{{ createdCampaignId }}</span>
            </div>
          </div>

          <div v-if="createError" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
            {{ createError }}
          </div>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-slate-900 dark:border-slate-800">
        <div class="p-4 border-b border-gray-200 dark:border-slate-800 flex items-center justify-between">
          <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">Ejecución</div>
            <div class="mt-1 text-xs text-gray-600 dark:text-slate-300">
              Abre WhatsApp con mensaje prellenado y marca el estado.
            </div>
          </div>

          <button
            type="button"
            class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
            :disabled="loadingHistory"
            @click="loadHistory"
          >
            {{ loadingHistory ? 'Cargando…' : 'Ver últimas campañas' }}
          </button>
        </div>

        <div class="p-4 space-y-4">
          <div v-if="activeCampaign" class="space-y-3">
            <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">
              Campaña #{{ activeCampaign.id }}
              <span v-if="activeCampaign.name" class="text-gray-500 dark:text-slate-400">· {{ activeCampaign.name }}</span>
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-slate-800 overflow-hidden">
              <div class="max-h-[520px] overflow-y-auto">
                <div
                  v-for="r in activeCampaign.recipients"
                  :key="r.id"
                  class="border-t border-gray-100 p-3 text-sm dark:border-slate-800"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <div class="truncate text-gray-900 dark:text-slate-100">
                        {{ r.display_name || 'Sin nombre' }}
                        <span class="text-gray-500 dark:text-slate-400">· {{ r.phone }}</span>
                      </div>
                      <div class="mt-1 whitespace-pre-wrap rounded-md bg-gray-50 p-2 text-xs text-gray-700 dark:bg-slate-800 dark:text-slate-200">
                        {{ r.rendered_message }}
                      </div>
                    </div>

                    <div class="flex flex-col gap-2">
                      <a
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-blue-700"
                        :href="waLink(r.phone, r.rendered_message)"
                        target="_blank"
                        rel="noopener"
                        @click="markOpened(r)"
                      >
                        Abrir WhatsApp
                      </a>

                      <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="markSent(r)"
                      >
                        Marcar enviado
                      </button>

                      <div class="text-[11px] text-gray-500 dark:text-slate-400">
                        Estado: <span class="font-semibold">{{ r.status }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="activeCampaign.recipients.length === 0" class="p-3 text-sm text-gray-600 dark:text-slate-300">
                  Sin destinatarios.
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-sm text-gray-600 dark:text-slate-300">
            Crea una campaña a la izquierda o carga una campaña reciente.
          </div>

          <div v-if="historyError" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
            {{ historyError }}
          </div>

          <div v-if="historyCampaigns.length > 0" class="space-y-2">
            <div class="text-xs font-semibold text-gray-700 dark:text-slate-200">Últimas campañas</div>
            <div class="grid gap-2">
              <button
                v-for="c in historyCampaigns"
                :key="c.id"
                type="button"
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="openCampaign(c.id)"
              >
                <span>
                  #{{ c.id }}
                  <span v-if="c.name" class="text-gray-500 dark:text-slate-400">· {{ c.name }}</span>
                </span>
                <span class="text-xs text-gray-500 dark:text-slate-400">{{ formatDate(c.created_at) }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';

const stages = ref([]);
const availableContacts = ref([]);
const source = ref('leads');
const counts = ref({ total: 0, without_phone: 0 });

const loadingRecipients = ref(false);
const recipientsError = ref('');

const creatingCampaign = ref(false);
const createError = ref('');
const createdCampaignId = ref(null);

const loadingHistory = ref(false);
const historyError = ref('');
const historyCampaigns = ref([]);

const activeCampaign = ref(null);

const selectedIds = ref(new Set());

const form = ref({
  name: '',
  message_template: 'Hola {{first_name}}, te escribo para…',
});

const filters = ref({
  stage_id: null,
  q: '',
  limit: 200,
  only_with_phone: false,
});

const allSelected = computed(() =>
  availableContacts.value.length > 0 && selectedIds.value.size === availableContacts.value.length
);

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedIds.value = new Set();
    return;
  }
  selectedIds.value = new Set(availableContacts.value.map((c) => c.id));
};

const toggleOne = (id) => {
  const next = new Set(selectedIds.value);
  if (next.has(id)) next.delete(id);
  else next.add(id);
  selectedIds.value = next;
};

const insertVar = (v) => {
  form.value.message_template = `${form.value.message_template}${form.value.message_template.endsWith(' ') ? '' : ' '}${v}`;
};

const loadRecipients = async () => {
  loadingRecipients.value = true;
  recipientsError.value = '';

  try {
    const params = {
      source: source.value,
      q: filters.value.q,
      limit: filters.value.limit,
      only_with_phone: filters.value.only_with_phone ? 1 : 0,
    };

    if (source.value === 'leads' && filters.value.stage_id !== null) {
      params.stage_id = filters.value.stage_id;
    }

    const res = await axios.get('/leads/whatsapp/recipients', {
      params,
    });
    stages.value = res.data?.data?.stages ?? [];
    availableContacts.value = res.data?.data?.contacts ?? [];
    counts.value = res.data?.data?.counts ?? { total: 0, without_phone: 0 };

    // If there are zero contacts with phone for current filters, keep this off to avoid confusing empty results.
    if ((counts.value.total ?? 0) > 0 && (counts.value.with_phone ?? 0) === 0) {
      filters.value.only_with_phone = false;
    }

    selectedIds.value = new Set(availableContacts.value.map((c) => c.id));
  } catch (e) {
    recipientsError.value = e?.response?.data?.message || 'No se pudieron cargar destinatarios.';
  } finally {
    loadingRecipients.value = false;
  }
};

const setSource = (next) => {
  source.value = next;
  filters.value.stage_id = null;
  loadRecipients();
};

const createCampaign = async () => {
  creatingCampaign.value = true;
  createError.value = '';
  createdCampaignId.value = null;

  try {
    const ids = Array.from(selectedIds.value);
    const res = await axios.post('/leads/whatsapp-campaigns', {
      name: form.value.name || null,
      message_template: form.value.message_template,
      source: source.value,
      ids,
    });

    createdCampaignId.value = res.data?.data?.campaign_id ?? null;

    if (createdCampaignId.value) {
      await openCampaign(createdCampaignId.value);
    }

    const skipped = res.data?.data?.skipped_missing_phone_ids ?? [];
    if (Array.isArray(skipped) && skipped.length > 0) {
      createError.value = `Se omitieron ${skipped.length} registros sin teléfono.`;
    }
  } catch (e) {
    createError.value = e?.response?.data?.message || 'No se pudo crear la campaña.';
  } finally {
    creatingCampaign.value = false;
  }
};

const loadHistory = async () => {
  loadingHistory.value = true;
  historyError.value = '';

  try {
    const res = await axios.get('/leads/whatsapp-campaigns');
    historyCampaigns.value = res.data?.data?.campaigns ?? [];
  } catch (e) {
    historyError.value = e?.response?.data?.message || 'No se pudo cargar el historial.';
  } finally {
    loadingHistory.value = false;
  }
};

const openCampaign = async (id) => {
  historyError.value = '';

  try {
    const res = await axios.get(`/leads/whatsapp-campaigns/${id}`);
    activeCampaign.value = res.data?.data?.campaign ?? null;
  } catch (e) {
    historyError.value = e?.response?.data?.message || 'No se pudo abrir la campaña.';
  }
};

const normalizePhoneDigits = (phone) => {
  if (!phone) return '';
  const digits = String(phone).replace(/\D+/g, '');
  if (!digits) return '';

  // Peru: ensure starts with 51 if phone has 9 digits only
  if (digits.length === 9 && digits.startsWith('9')) {
    return `51${digits}`;
  }

  // If already includes country code (like 51xxxxxxxxx), keep.
  if (digits.startsWith('51')) return digits;

  // Fallback: return digits as-is
  return digits;
};

const waLink = (phone, text) => {
  const digits = normalizePhoneDigits(phone);
  if (!digits) return '#';
  const msg = encodeURIComponent(text || '');
  return `https://wa.me/${digits}?text=${msg}`;
};

const patchRecipient = async (recipientId, payload) => {
  try {
    const res = await axios.patch(`/leads/whatsapp-campaign-recipients/${recipientId}`, payload);
    const updated = res.data?.data?.recipient;
    if (!activeCampaign.value || !updated) return;

    const idx = activeCampaign.value.recipients.findIndex((r) => r.id === recipientId);
    if (idx === -1) return;

    activeCampaign.value.recipients[idx] = {
      ...activeCampaign.value.recipients[idx],
      ...updated,
    };
  } catch {
    // Keep silent for MVP (don’t break flow)
  }
};

const markOpened = (r) => {
  if (!r?.id) return;
  if (r.status === 'pending') {
    patchRecipient(r.id, { status: 'opened' });
    r.status = 'opened';
  }
};

const markSent = (r) => {
  if (!r?.id) return;
  patchRecipient(r.id, { status: 'sent' });
  r.status = 'sent';
};

const formatDate = (iso) => {
  if (!iso) return '';
  try {
    return new Date(iso).toLocaleString();
  } catch {
    return String(iso);
  }
};

onMounted(() => {
  loadRecipients();
});
</script>
