<template>
  <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-slate-900 dark:border-slate-800">
    <!-- Header -->
    <div v-if="$slots.header || title" class="p-4 border-b border-gray-200 dark:border-slate-800">
      <slot name="header">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ title }}</h3>
            <p v-if="subtitle" class="mt-1 text-xs text-gray-600 dark:text-slate-300">{{ subtitle }}</p>
          </div>
          <slot name="headerActions" />
        </div>
      </slot>
    </div>

    <!-- Content -->
    <div :class="contentClasses">
      <slot />
    </div>

    <!-- Footer -->
    <div v-if="$slots.footer" class="p-4 border-t border-gray-200 dark:border-slate-800">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  padding: {
    type: Boolean,
    default: true
  },
  noPadding: {
    type: Boolean,
    default: false
  }
});

const contentClasses = computed(() => {
  if (props.noPadding) return '';
  if (!props.padding) return '';
  return 'p-4';
});
</script>