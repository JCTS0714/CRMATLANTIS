<template>
  <tr class="border-b border-gray-100 bg-white hover:bg-blue-50/40 transition-colors dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60">
    <td class="px-4 py-3 font-medium text-gray-900 dark:text-slate-100">{{ lead.id }}</td>

    <td class="px-4 py-3">
      <div class="flex flex-col gap-1">
        <span>{{ lead.name ?? '' }}</span>
        <button
          type="button"
          class="inline-flex w-fit items-center rounded-lg border border-blue-200 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-300 dark:hover:bg-blue-900"
          @click="goToCalendar"
        >
          Agendar
        </button>
      </div>
    </td>
    <td class="px-4 py-3 text-right">{{ formatMoney(lead.amount, lead.currency) }}</td>
    <td class="px-4 py-3">{{ lead.currency ?? '' }}</td>
    <td class="px-4 py-3">{{ lead.contact_name ?? '' }}</td>
    <td class="px-4 py-3">{{ lead.contact_phone ?? '' }}</td>
    <td class="px-4 py-3">{{ lead.contact_email ?? '' }}</td>
    <td class="px-4 py-3">{{ lead.company_name ?? '' }}</td>
    <td class="px-4 py-3">{{ lead.company_address ?? '' }}</td>
    <td class="px-4 py-3">{{ lead.document_type ?? '' }}</td>
    <td class="px-4 py-3">{{ lead.document_number ?? '' }}</td>
    <td class="px-4 py-3">{{ lead.customer_id ?? '' }}</td>
    <td class="px-4 py-3">
      <select
        class="w-full rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-xs text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
        :value="lead.stage_id ?? ''"
        :disabled="isMoving || !Array.isArray(stages) || stages.length === 0"
        @change="handleStageChange"
      >
        <option
          v-for="stage in stages"
          :key="stage.id"
          :value="stage.id"
        >
          {{ stage.name }}
        </option>
      </select>
    </td>
    <td class="px-4 py-3">{{ lead.created_by_name || lead.created_by || '' }}</td>
    <td class="px-4 py-3">{{ formatDateTime(lead.won_at) }}</td>
    <td class="px-4 py-3">{{ formatDateTime(lead.created_at) }}</td>
    <td class="px-4 py-3">{{ formatDateTime(lead.updated_at) }}</td>
  </tr>
</template>

<script setup>
const emit = defineEmits(['change-stage']);

const props = defineProps({
  lead: {
    type: Object,
    required: true
  },
  stages: {
    type: Array,
    default: () => []
  },
  isMoving: {
    type: Boolean,
    default: false
  }
});

// Utility functions  
const formatMoney = (amount, currency = 'EUR') => {
  if (!amount) return '';
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: currency || 'EUR'
  }).format(amount);
};

const formatDateTime = (datetime) => {
  if (!datetime) return '';
  return new Date(datetime).toLocaleString('es-ES');
};

const goToCalendar = () => {
  const lead = props.lead || {};
  const title = lead.name ? `Seguimiento lead: ${lead.name}` : 'Seguimiento lead';
  const details = [
    lead.company_name ? `Empresa: ${lead.company_name}` : null,
    lead.contact_name ? `Contacto: ${lead.contact_name}` : null,
    lead.contact_phone ? `Teléfono: ${lead.contact_phone}` : null,
    lead.contact_email ? `Email: ${lead.contact_email}` : null,
    lead.observacion ? `Observación: ${lead.observacion}` : null,
  ].filter(Boolean);

  const params = new URLSearchParams({
    related_type: 'lead',
    related_id: String(lead.id ?? ''),
    title,
  });

  if (details.length > 0) {
    params.set('description', details.join(' | '));
  }

  window.location.assign(`/calendar?${params.toString()}`);
};

const handleStageChange = (event) => {
  const selectedStageId = Number(event.target.value);
  if (!Number.isFinite(selectedStageId) || selectedStageId <= 0) return;
  if (selectedStageId === Number(props.lead?.stage_id)) return;

  emit('change-stage', {
    leadId: props.lead?.id,
    stageId: selectedStageId,
  });
};
</script>