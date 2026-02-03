import { ref, onMounted, onUnmounted } from 'vue';

/**
 * Composable for modal management
 * @param {Object} config - Configuration options
 */
export function useModal(config = {}) {
  const {
    closeOnEscape = true,
    closeOnBackdrop = true,
    preventBodyScroll = true
  } = config;

  const isOpen = ref(false);
  const isAnimating = ref(false);

  const open = () => {
    isOpen.value = true;
    if (preventBodyScroll) {
      document.body.style.overflow = 'hidden';
    }
  };

  const close = () => {
    if (isAnimating.value) return;
    
    isAnimating.value = true;
    
    setTimeout(() => {
      isOpen.value = false;
      isAnimating.value = false;
      
      if (preventBodyScroll) {
        document.body.style.overflow = '';
      }
    }, 150); // Animation duration
  };

  const toggle = () => {
    if (isOpen.value) {
      close();
    } else {
      open();
    }
  };

  const handleEscape = (event) => {
    if (closeOnEscape && event.key === 'Escape' && isOpen.value) {
      close();
    }
  };

  const handleBackdrop = (event) => {
    if (closeOnBackdrop && event.target === event.currentTarget) {
      close();
    }
  };

  onMounted(() => {
    if (closeOnEscape) {
      document.addEventListener('keydown', handleEscape);
    }
  });

  onUnmounted(() => {
    if (closeOnEscape) {
      document.removeEventListener('keydown', handleEscape);
    }
    
    // Clean up body scroll when component unmounts
    if (preventBodyScroll && isOpen.value) {
      document.body.style.overflow = '';
    }
  });

  return {
    isOpen,
    isAnimating,
    open,
    close,
    toggle,
    handleBackdrop
  };
}