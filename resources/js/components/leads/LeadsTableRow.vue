<template>
  <tr class="border-b border-gray-100 bg-white hover:bg-blue-50/40 transition-colors dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60">
    <td class="px-4 py-3 font-medium text-gray-900 dark:text-slate-100">{{ lead.id }}</td>

    <td class="px-4 py-3">
      <select
        :value="lead.stage_id"
        class="inline-block w-auto min-w-[120px] rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
        :disabled="savingStage || isLeadLocked"
        @change="$emit('stage-change', lead, $event)"
      >
        <option
          v-if="!stageName"
          selected
          disabled
          class="text-sm text-gray-600"
        >
          Etapa: {{ lead.stage_name ?? 'Cargando…' }}
        </option>
        <option v-for="stage in stages" :key="stage.id" :value="stage.id">
          {{ stage.name }}
        </option>
      </select>
    </td>

    <td class="px-4 py-3">
      <button
        v-if="canArchive"
        type="button"
        class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="archiving"
        @click="$emit('archive', lead)"
      >
        {{ archiving ? 'Archivando…' : 'Archivar' }}
      </button>
      <span v-else class="text-xs text-gray-500 dark:text-slate-400">—</span>
    </td>

    <td class="px-4 py-3">{{ lead.name ?? '' }}</td>
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
    <td class="px-4 py-3">{{ lead.created_by ?? '' }}</td>
    <td class="px-4 py-3">{{ formatDateTime(lead.won_at) }}</td>
    <td class="px-4 py-3">{{ formatDateTime(lead.archived_at) }}</td>
    <td class="px-4 py-3">{{ formatDateTime(lead.created_at) }}</td>
    <td class="px-4 py-3">{{ formatDateTime(lead.updated_at) }}</td>
  </tr>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  lead: {
    type: Object,
    required: true
  },
  stages: {
    type: Array,
    required: true
  },
  stageNameById: {
    type: Map,
    required: true
  },
  savingStage: {
    type: Boolean,
    default: false
  },
  archiving: {
    type: Boolean,
    default: false
  }
});

defineEmits(['stage-change', 'archive']);

// Computed properties
const stageName = computed(() => props.stageNameById.get(props.lead.stage_id));
const isLeadLocked = computed(() => props.lead.archived_at || props.lead.won_at);
const canArchive = computed(() => !props.lead.archived_at && !props.lead.won_at);

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
</script>