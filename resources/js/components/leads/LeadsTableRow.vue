<template>
  <tr class="border-b border-gray-100 bg-white hover:bg-blue-50/40 transition-colors dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60">
    <td class="px-4 py-3 font-medium text-gray-900 dark:text-slate-100">{{ lead.id }}</td>

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
    <td class="px-4 py-3">{{ formatDateTime(lead.created_at) }}</td>
    <td class="px-4 py-3">{{ formatDateTime(lead.updated_at) }}</td>
  </tr>
</template>

<script setup>

const props = defineProps({
  lead: {
    type: Object,
    required: true
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
</script>