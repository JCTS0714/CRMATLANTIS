import { ref } from 'vue';
import axios from 'axios';

export function useLeadsBoardLeadActions({
  confirmDialog,
  toastSuccess,
  toastError,
  moveError,
  removeLeadFromStage,
}) {
  const archivingIds = ref(new Set());

  const formatMoney = (amount, currency) => {
    const number = Number(amount);
    if (Number.isNaN(number)) return '';
    if (currency === 'USD') return `$${number.toFixed(2)}`;
    return `S/ ${number.toFixed(2)}`;
  };

  const archiveLead = async (lead, stage) => {
    if (!lead?.id) return;
    if (!stage?.is_won && !lead?.won_at) return;

    const ok = await confirmDialog({
      title: 'Archivar lead',
      text: 'Se ocultara del pipeline, pero seguira visible en la tabla (historial).',
      confirmText: 'Archivar',
      cancelText: 'Cancelar',
      icon: 'warning',
    });
    if (!ok) return;

    archivingIds.value.add(lead.id);
    moveError.value = '';
    try {
      await axios.patch(`/leads/${lead.id}/archive`);

      removeLeadFromStage(stage.id, lead.id);
      toastSuccess('Lead archivado');
    } catch (e) {
      const msg = e?.response?.data?.message ?? 'No se pudo archivar el lead.';
      moveError.value = msg;
      toastError(msg);
    } finally {
      archivingIds.value.delete(lead.id);
    }
  };

  const goToCalendarWithLead = (lead) => {
    if (!lead?.id) return;

    const title = lead.name ? `Seguimiento lead: ${lead.name}` : 'Seguimiento lead';
    const details = [
      lead.company_name ? `Empresa: ${lead.company_name}` : null,
      lead.contact_name ? `Contacto: ${lead.contact_name}` : null,
      lead.contact_phone ? `Telefono: ${lead.contact_phone}` : null,
      lead.contact_email ? `Email: ${lead.contact_email}` : null,
      lead.observacion ? `Observacion: ${lead.observacion}` : null,
    ].filter(Boolean);

    const params = new URLSearchParams({
      related_type: 'lead',
      related_id: String(lead.id),
      title,
    });

    if (details.length > 0) {
      params.set('description', details.join(' | '));
    }

    window.location.assign(`/calendar?${params.toString()}`);
  };

  return {
    archivingIds,
    formatMoney,
    archiveLead,
    goToCalendarWithLead,
  };
}