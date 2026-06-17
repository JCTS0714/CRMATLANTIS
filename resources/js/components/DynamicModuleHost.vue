<template>
  <component :is="resolvedComponent" v-if="resolvedComponent" />
  <div v-else class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/20 dark:text-amber-200">
    No se encontro el componente del modulo: {{ componentFile }}
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue';

const props = defineProps({
  componentFile: {
    type: String,
    required: true,
  },
});

const moduleComponents = import.meta.glob('./inbox/*.vue');

const resolvedComponent = computed(() => {
  const key = `./${props.componentFile}`;
  const loader = moduleComponents[key];
  if (!loader) {
    return null;
  }

  return defineAsyncComponent(loader);
});
</script>
