import { ref } from 'vue';
import axios from 'axios';

export function useLeadsBoardDragDrop({ stages, load, confirmDialog, toastSuccess }) {
  const moveError = ref('');

  const draggedLead = ref(null);
  const draggedFromStage = ref(null);
  const dragOverStageId = ref(null);
  const dropPreviewPosition = ref(null);

  const stageElementCache = new Map();

  const isLeadLocked = (lead, stage) => {
    return !!(lead?.archived_at || lead?.won_at || stage?.is_won);
  };

  const removeLeadFromStage = (stageId, leadId) => {
    const stage = stages.value.find((s) => s.id === stageId);
    if (!stage?.leads) return;

    const index = stage.leads.findIndex((l) => l.id === leadId);
    if (index !== -1) {
      stage.leads.splice(index, 1);
      stage.count = stage.leads.length;
    }
  };

  const addLeadToStage = (stageId, lead, position = 0) => {
    const stage = stages.value.find((s) => s.id === stageId);
    if (!stage) return;

    if (!Array.isArray(stage.leads)) stage.leads = [];

    const existingIndex = stage.leads.findIndex((l) => l.id === lead.id);
    if (existingIndex !== -1) {
      stage.leads.splice(existingIndex, 1);
    }

    const insertIndex = Math.min(Math.max(0, position), stage.leads.length);
    stage.leads.splice(insertIndex, 0, lead);
    stage.count = stage.leads.length;
  };

  const calculateDropPosition = (stage, dropY, draggedLeadId = null) => {
    let stageElement = stageElementCache.get(stage.id);
    if (!stageElement || !document.contains(stageElement)) {
      stageElement = document.querySelector(`[data-stage-id="${stage.id}"] .p-3`);
      if (!stageElement) return 0;
      stageElementCache.set(stage.id, stageElement);
    }

    const leadCards = stageElement.querySelectorAll('article[data-lead-id]');
    let position = 0;

    for (let i = 0; i < leadCards.length; i++) {
      const card = leadCards[i];
      const leadId = parseInt(card.getAttribute('data-lead-id'));

      if (draggedLeadId && leadId === draggedLeadId) continue;

      const rect = card.getBoundingClientRect();
      if (dropY < rect.top + rect.height / 2) {
        return position;
      }
      position++;
    }

    return position;
  };

  const reorderLeadsInStage = async (stageId, leadId, position) => {
    const stage = stages.value.find((s) => s.id === stageId);
    if (!stage || !Array.isArray(stage.leads)) return;

    const currentIndex = stage.leads.findIndex((l) => l.id === leadId);
    if (currentIndex === -1) return;
    if (currentIndex === position) return;

    const [lead] = stage.leads.splice(currentIndex, 1);
    const newIndex = Math.min(Math.max(0, position), stage.leads.length);
    stage.leads.splice(newIndex, 0, lead);
    stage.count = stage.leads.length;

    const orderedIds = stage.leads.map((l) => l.id);

    axios.patch('/leads/reorder', {
      stage_id: stageId,
      ordered_ids: orderedIds,
    }).catch(() => {
      load({ showLoading: false });
    });
  };

  const onDragStart = (lead, stage, event) => {
    if (isLeadLocked(lead, stage)) return;

    draggedLead.value = lead;
    draggedFromStage.value = stages.value.find((s) => s.id === lead.stage_id);

    if (event?.target) {
      event.target.style.transform = 'scale(1.02)';
      event.target.style.opacity = '0.7';
      event.target.style.transition = 'transform 0.1s ease';
    }
  };

  const onDragEnd = (event) => {
    if (event?.target) {
      event.target.style.transform = '';
      event.target.style.opacity = '';
      event.target.style.transition = '';
    }

    dragOverStageId.value = null;
    dropPreviewPosition.value = null;
    draggedLead.value = null;
    draggedFromStage.value = null;
  };

  const onDragOver = (event) => {
    if (!draggedLead.value) return;
    event.preventDefault();

    const stageElement = event.currentTarget.closest('section[data-stage-id]');
    if (!stageElement) return;

    const stageId = parseInt(stageElement.getAttribute('data-stage-id'));
    const targetStage = stages.value.find((s) => s.id === stageId);
    if (!targetStage) return;

    dragOverStageId.value = stageId;
    const dropY = event.clientY;
    dropPreviewPosition.value = calculateDropPosition(targetStage, dropY, draggedLead.value.id);
  };

  const onDropOnStage = async (targetStage, event) => {
    event.preventDefault();

    if (!draggedLead.value || !targetStage) return;

    const lead = draggedLead.value;
    const fromStage = draggedFromStage.value;

    if (isLeadLocked(lead, fromStage)) {
      moveError.value = 'Este lead está bloqueado y no se puede mover.';
      return;
    }

    if (targetStage.is_won && !lead.customer_id) {
      const ok = await confirmDialog({
        title: 'Confirmar GANADO',
        text: '¿Marcar este lead como GANADO? Esto lo convertirá en cliente automáticamente.',
        confirmText: 'Sí, marcar como ganado',
        cancelText: 'Cancelar',
        icon: 'warning',
      });
      if (!ok) return;
    }

    moveError.value = '';

    try {
      const isSameStage = fromStage.id === targetStage.id;

      if (isSameStage) {
        const dropY = event.clientY;
        const newPosition = calculateDropPosition(targetStage, dropY, lead.id);
        reorderLeadsInStage(targetStage.id, lead.id, newPosition);
      } else {
        const isLeadConversionToWon = targetStage.is_won && !lead.customer_id;

        if (isLeadConversionToWon) {
          const response = await axios.patch(`/leads/${lead.id}/move-stage`, {
            stage_id: targetStage.id,
          });

          const convertedCustomerId = Number(response?.data?.data?.customer_id ?? 0);
          const params = new URLSearchParams();
          if (Number.isInteger(convertedCustomerId) && convertedCustomerId > 0) {
            params.set('edit_customer_id', String(convertedCustomerId));
          }
          params.set('source', 'lead-conversion');

          toastSuccess('Lead convertido a cliente. Completa los datos del cliente.');
          window.location.assign(`/postventa/clientes?${params.toString()}`);
          return;
        }

        const dropY = event.clientY;
        const newPosition = calculateDropPosition(targetStage, dropY, lead.id);

        removeLeadFromStage(fromStage.id, lead.id);
        addLeadToStage(targetStage.id, { ...lead, stage_id: targetStage.id }, newPosition);

        axios.patch(`/leads/${lead.id}/move-stage`, {
          stage_id: targetStage.id,
        }).then(() => {
          const orderedIds = targetStage.leads.map((l) => l.id);
          return axios.patch('/leads/reorder', {
            stage_id: targetStage.id,
            ordered_ids: orderedIds,
          });
        }).catch(() => {
          moveError.value = 'Error al mover el lead. Recargando...';
          load({ showLoading: false });
        });
      }
    } catch {
      moveError.value = 'Error al mover el lead. Inténtalo de nuevo.';
      await load({ showLoading: false });
    }
  };

  return {
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
  };
}