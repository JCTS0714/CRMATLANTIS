import { onBeforeUnmount, onMounted } from 'vue';

export function useLeadsBoardLifecycle({ showQuickModal, load }) {
  const onExternalCreateQuick = () => showQuickModal();

  onMounted(async () => {
    window.addEventListener('leads:create-quick', onExternalCreateQuick);
    await load();
  });

  onBeforeUnmount(() => {
    window.removeEventListener('leads:create-quick', onExternalCreateQuick);
  });
}