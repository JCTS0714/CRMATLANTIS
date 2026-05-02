import { computed, nextTick, ref } from 'vue';
import axios from 'axios';

export function useLeadsBoardLeadModals({
  stages,
  getLocalAutofillModule,
  confirmDialog,
  toastSuccess,
  toastError,
}) {
  const quickOpen = ref(false);
  const quickSaving = ref(false);
  const quickError = ref('');

  const quickForm = ref({
    name: '',
    amount: null,
    currency: 'PEN',
    contact_name: '',
    contact_phone: '',
    contact_email: '',
    company_name: '',
    company_address: '',
    migracion: '',
    referencia: '',
    document_type: 'otro',
    document_number: '55555555',
    observacion: '',
  });

  const editOpen = ref(false);
  const editSaving = ref(false);
  const editError = ref('');
  const observacionInput = ref(null);
  const editForm = ref({
    id: null,
    name: '',
    amount: null,
    currency: 'PEN',
    contact_name: '',
    contact_phone: '',
    contact_email: '',
    company_name: '',
    company_address: '',
    migracion: '',
    referencia: '',
    document_type: 'otro',
    document_number: '55555555',
    observacion: '',
  });

  const documentPlaceholder = computed(() => {
    if (quickForm.value.document_type === 'dni') return 'Documento: DNI (8 dígitos)';
    if (quickForm.value.document_type === 'ruc') return 'Documento: RUC (11 dígitos)';
    if (quickForm.value.document_type === 'otro') return 'Documento: OTRO (por defecto 55555555)';
    return 'Documento: Número (opcional)';
  });

  const firstValidationMessage = (error) => {
    const errors = error?.response?.data?.errors;
    if (!errors || typeof errors !== 'object') return null;
    const firstKey = Object.keys(errors)[0];
    const first = firstKey ? errors[firstKey]?.[0] : null;
    return typeof first === 'string' ? first : null;
  };

  const showQuickModal = () => {
    quickError.value = '';
    quickForm.value = {
      name: '',
      amount: null,
      currency: 'PEN',
      contact_name: '',
      contact_phone: '',
      contact_email: '',
      company_name: '',
      company_address: '',
      migracion: '',
      referencia: '',
      document_type: 'otro',
      document_number: '55555555',
      observacion: '',
    };
    quickOpen.value = true;
  };

  const hideQuickModal = () => {
    quickOpen.value = false;
  };

  const fillQuickLeadForTest = async () => {
    const module = await getLocalAutofillModule();
    module?.autofillLeadQuickForm?.(quickForm);
  };

  const submitQuick = async () => {
    quickSaving.value = true;
    quickError.value = '';

    const payload = {
      name: quickForm.value.name,
      amount: quickForm.value.amount,
      currency: quickForm.value.currency,
      contact_name: quickForm.value.contact_name || null,
      contact_phone: quickForm.value.contact_phone || null,
      contact_email: quickForm.value.contact_email || null,
      company_name: quickForm.value.company_name || null,
      company_address: quickForm.value.company_address || null,
      migracion: quickForm.value.migracion || null,
      referencia: quickForm.value.referencia || null,
      document_type: quickForm.value.document_type || null,
      document_number: quickForm.value.document_number || null,
      observacion: quickForm.value.observacion || null,
    };

    try {
      const response = await axios.post('/leads', payload);
      const newLead = response.data.data;

      const firstStage = stages.value.find((s) => s.sort_order === 10 || s.key === 'follow_up') || stages.value[0];
      if (firstStage) {
        if (!Array.isArray(firstStage.leads)) firstStage.leads = [];
        firstStage.leads.unshift(newLead);
        firstStage.count = firstStage.leads.length;
      }

      toastSuccess('Lead creado exitosamente');
      hideQuickModal();
    } catch (error) {
      quickError.value = firstValidationMessage(error) ?? error?.response?.data?.message ?? 'No se pudo crear el lead.';
    } finally {
      quickSaving.value = false;
    }
  };

  const openEditModal = (lead) => {
    editError.value = '';
    editForm.value = {
      id: lead.id,
      name: lead.name || '',
      amount: lead.amount ?? null,
      currency: lead.currency ?? 'PEN',
      contact_name: lead.contact_name ?? '',
      contact_phone: lead.contact_phone ?? '',
      contact_email: lead.contact_email ?? '',
      company_name: lead.company_name ?? '',
      company_address: lead.company_address ?? '',
      migracion: lead.migracion ?? '',
      referencia: lead.referencia ?? '',
      document_type: lead.document_type ?? 'otro',
      document_number: lead.document_number ?? '55555555',
      observacion: lead.observacion ?? '',
    };
    editOpen.value = true;
    nextTick(() => {
      if (observacionInput.value?.focus) {
        observacionInput.value.focus();
        observacionInput.value.select?.();
      }
    });
  };

  const closeEditModal = () => {
    editOpen.value = false;
  };

  const submitEdit = async () => {
    editSaving.value = true;
    editError.value = '';

    const payload = {
      name: editForm.value.name,
      amount: editForm.value.amount,
      currency: editForm.value.currency,
      observacion: editForm.value.observacion || null,
      contact_name: editForm.value.contact_name || null,
      contact_phone: editForm.value.contact_phone || null,
      contact_email: editForm.value.contact_email || null,
      company_name: editForm.value.company_name || null,
      company_address: editForm.value.company_address || null,
      migracion: editForm.value.migracion || null,
      referencia: editForm.value.referencia || null,
      document_type: editForm.value.document_type || null,
      document_number: editForm.value.document_number || null,
    };

    try {
      const res = await axios.put(`/leads/${editForm.value.id}`, payload);
      const updated = res?.data?.data;
      if (updated) {
        for (const s of stages.value) {
          if (!Array.isArray(s.leads)) continue;
          const idx = s.leads.findIndex((l) => l.id === updated.id);
          if (idx !== -1) {
            s.leads.splice(idx, 1, updated);
            break;
          }
        }
      }
      toastSuccess('Lead actualizado');
      closeEditModal();
    } catch (e) {
      editError.value = firstValidationMessage(e) ?? e?.response?.data?.message ?? 'No se pudo actualizar el lead.';
    } finally {
      editSaving.value = false;
    }
  };

  const markDesistido = async () => {
    if (!editForm.value?.id) return;
    const ok = await confirmDialog({
      title: 'Marcar como desistido',
      text: '¿Estás seguro? Esto archivará el lead y lo moverá a la lista de desistidos.',
      confirmText: 'Sí, marcar',
      cancelText: 'Cancelar',
      icon: 'warning',
    });
    if (!ok) return;

    try {
      const res = await axios.post(`/leads/${editForm.value.id}/desist`, { observacion: editForm.value.observacion || '' });
      closeEditModal();

      for (const stage of stages.value) {
        const idx = stage.leads?.findIndex?.((l) => l.id === editForm.value.id) ?? -1;
        if (idx !== -1) {
          stage.leads.splice(idx, 1);
          stage.count = stage.leads.length;
          break;
        }
      }

      toastSuccess('Lead marcado como desistido');
      const target = res?.data?.location || '/desistidos';
      window.location.assign(target);
    } catch (e) {
      const msg = firstValidationMessage(e) ?? e?.response?.data?.message ?? 'No se pudo marcar como desistido.';
      toastError(msg);
    }
  };

  const sendToEspera = async () => {
    if (!editForm.value?.id) return;
    const ok = await confirmDialog({
      title: 'Enviar a zona de espera',
      text: '¿Estás seguro? Esto archivará el lead y lo moverá a la zona de espera.',
      confirmText: 'Sí, enviar',
      cancelText: 'Cancelar',
      icon: 'warning',
    });
    if (!ok) return;

    try {
      const res = await axios.post(`/leads/${editForm.value.id}/wait`, { observacion: editForm.value.observacion || '' });
      closeEditModal();

      for (const stage of stages.value) {
        const idx = stage.leads?.findIndex?.((l) => l.id === editForm.value.id) ?? -1;
        if (idx !== -1) {
          stage.leads.splice(idx, 1);
          stage.count = stage.leads.length;
          break;
        }
      }

      toastSuccess('Lead enviado a zona de espera');
      const target = res?.data?.location || '/espera';
      window.location.assign(target);
    } catch (e) {
      const msg = firstValidationMessage(e) ?? e?.response?.data?.message ?? 'No se pudo enviar a zona de espera.';
      toastError(msg);
    }
  };

  return {
    quickOpen,
    quickSaving,
    quickError,
    quickForm,
    documentPlaceholder,
    showQuickModal,
    hideQuickModal,
    fillQuickLeadForTest,
    submitQuick,
    editOpen,
    editSaving,
    editError,
    observacionInput,
    editForm,
    openEditModal,
    closeEditModal,
    submitEdit,
    markDesistido,
    sendToEspera,
  };
}