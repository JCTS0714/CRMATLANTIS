<template>
  <footer class="bg-white border-t border-gray-200 dark:bg-slate-950 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <span class="text-sm text-gray-500 dark:text-slate-300">© {{ year }} CRM ATLANTIS</span>
        <div class="flex gap-4">
          <a href="#" class="text-sm text-gray-500 hover:text-gray-900 dark:text-slate-300 dark:hover:text-slate-100">Ayuda</a>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-900 dark:text-slate-300 dark:hover:text-slate-100">Privacidad</a>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-900 dark:text-slate-300 dark:hover:text-slate-100">Términos</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear();
</script>