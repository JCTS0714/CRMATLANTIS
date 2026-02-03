<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
  >
    <!-- Backdrop -->
    <div
      class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
      :class="{ 'opacity-100': !isAnimating, 'opacity-0': isAnimating }"
      @click="handleBackdrop"
    ></div>

    <!-- Modal -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
      <div
        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all dark:bg-slate-900"
        :class="[
          maxWidthClass,
          { 
            'translate-y-0 opacity-100 sm:scale-100': !isAnimating,
            'translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95': isAnimating
          }
        ]"
      >
        <!-- Header -->
        <div v-if="$slots.header || title" class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <slot name="header">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-slate-100" id="modal-title">
                  {{ title }}
                </h3>
                <p v-if="subtitle" class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                  {{ subtitle }}
                </p>
              </slot>
            </div>
            
            <button
              v-if="showCloseButton"
              type="button"
              class="rounded-md bg-white text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-slate-900 dark:hover:text-slate-300"
              @click="handleClose"
            >
              <span class="sr-only">Cerrar</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-4">
          <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50 border-t border-gray-200 dark:bg-slate-800 dark:border-slate-700">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useModal } from '../../composables/useModal.js';

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  maxWidth: {
    type: String,
    default: '2xl',
    validator: (value) => ['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl'].includes(value)
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  closeOnEscape: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['close', 'open']);

const maxWidthClasses = {
  'sm': 'sm:max-w-sm',
  'md': 'sm:max-w-md',
  'lg': 'sm:max-w-lg',
  'xl': 'sm:max-w-xl',
  '2xl': 'sm:max-w-2xl',
  '3xl': 'sm:max-w-3xl',
  '4xl': 'sm:max-w-4xl',
  '5xl': 'sm:max-w-5xl',
  '6xl': 'sm:max-w-6xl'
};

const maxWidthClass = computed(() => maxWidthClasses[props.maxWidth] || maxWidthClasses['2xl']);

const { isOpen, isAnimating, open: openModal, close: closeModal, handleBackdrop } = useModal({
  closeOnEscape: props.closeOnEscape,
  closeOnBackdrop: props.closeOnBackdrop
});

// Wrapper functions to emit events
const handleOpen = () => {
  openModal();
  emit('open');
};

const handleClose = () => {
  closeModal();
  emit('close');
};

// Expose methods to parent
defineExpose({
  open: handleOpen,
  close: handleClose,
  isOpen
});
</script>