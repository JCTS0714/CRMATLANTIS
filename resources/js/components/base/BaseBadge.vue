<template>
  <span :class="badgeClasses">
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { getVariant, clsx } from '@/utils/designSystem.js';

const props = defineProps({
  variant: {
    type: String,
    default: 'secondary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'danger', 'info'].includes(value)
  },
  size: {
    type: String,
    default: 'sm',
    validator: (value) => ['xs', 'sm', 'md'].includes(value)
  },
  rounded: {
    type: Boolean,
    default: false
  }
});

const badgeClasses = computed(() => {
  const sizes = {
    xs: 'px-1.5 py-0.5 text-xs',
    sm: 'px-2 py-1 text-xs',
    md: 'px-2.5 py-1.5 text-sm'
  };

  return clsx(
    // Base styles
    'inline-flex items-center font-medium',
    
    // Shape
    props.rounded ? 'rounded-full' : 'rounded-md',
    
    // Size
    sizes[props.size],
    
    // Variant colors from design system
    getVariant('badge', props.variant)
  );
});
</script>