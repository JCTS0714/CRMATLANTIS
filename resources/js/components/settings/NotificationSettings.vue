<template>
  <div class="notification-settings-integrated">
    <!-- Estado y activación -->
    <div v-if="notificationStatus.permission !== 'granted'" class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-8 text-center dark:border-slate-800 dark:bg-slate-950/40">
      <div class="mb-5">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">
          <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M10 2a5 5 0 00-5 5v2.382l-.724 1.447A1 1 0 005.171 12h9.658a1 1 0 00.895-1.447L15 9.382V7a5 5 0 00-5-5Zm0 16a2.5 2.5 0 002.45-2h-4.9A2.5 2.5 0 0010 18Z" />
          </svg>
        </div>
      </div>
      <h4 class="mb-2 text-lg font-semibold text-gray-900 dark:text-slate-100">
        Activa las notificaciones
      </h4>
      <p class="mx-auto mb-5 max-w-xl text-sm leading-6 text-gray-500 dark:text-slate-400">
        Recibe alertas de tus eventos del calendario directamente en tu dispositivo
      </p>
      
      <!-- Estados de compatibilidad -->
      <div class="mb-5 flex flex-wrap items-center justify-center gap-3">
        <span 
          :class="[
            'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
            notificationStatus.supported ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
          ]"
        >
          {{ notificationStatus.supported ? 'Soportado' : 'No soportado' }}
        </span>
        <span 
          :class="[
            'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
            notificationStatus.permission === 'granted' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 
            notificationStatus.permission === 'denied' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' :
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
          ]"
        >
          {{ getPermissionText() }}
        </span>
      </div>
      
      <button
        @click="requestPermission"
        :disabled="requesting || !notificationStatus.supported"
        class="inline-flex items-center rounded-xl border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 dark:ring-offset-slate-900"
      >
        <span v-if="requesting" class="mr-2">
          <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </span>
        {{ requesting ? 'Solicitando...' : 'Activar Notificaciones' }}
      </button>
    </div>

    <!-- Configuraciones (solo si están activadas) -->
    <div v-else class="space-y-6">
      <!-- Estado actual activado -->
      <div class="rounded-2xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
              ¡Notificaciones activadas!
            </h3>
            <div class="mt-1 text-sm text-green-700 dark:text-green-300">
              Recibirás alertas de tus eventos del calendario.
            </div>
          </div>
        </div>
      </div>

      <!-- Grid de configuraciones -->
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Configuración de sonido -->
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/50">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="mr-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path d="M9.707 4.293A1 1 0 008 5v10a1 1 0 001.707.707L13.414 12H15a3 3 0 000-6h-1.586L9.707 4.293Z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-slate-100">
                  Sonido de notificación
                </p>
                <p class="text-xs text-gray-500 dark:text-slate-400">
                  Reproducir sonido cuando lleguen notificaciones
                </p>
              </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input 
                type="checkbox" 
                v-model="preferences.sound_enabled"
                @change="updatePreferences"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>

        <!-- Botón de prueba -->
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/50">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="mr-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-200">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path d="M8 2a1 1 0 000 2v3.382l-2.447 4.894A3 3 0 008.236 17h3.528a3 3 0 002.683-4.724L12 7.382V4a1 1 0 100-2H8Z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-slate-100">
                  Probar notificación
                </p>
                <p class="text-xs text-gray-500 dark:text-slate-400">
                  Enviar una notificación de prueba
                </p>
              </div>
            </div>
            <button
              @click="testNotification"
              :disabled="testing"
              class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:ring-offset-slate-900"
            >
              <span v-if="testing" class="mr-2">
                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ testing ? 'Enviando...' : 'Probar' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Configuración de recordatorios -->
      <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/50">
        <div class="mb-4 flex items-center">
          <div class="mr-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-200">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16Zm.75-11a.75.75 0 00-1.5 0v3.69c0 .199.079.39.22.53l2.25 2.25a.75.75 0 101.06-1.06l-2.03-2.03V7Z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-slate-100">
              Recordatorios por defecto
            </p>
            <p class="text-xs text-gray-500 dark:text-slate-400">
              Cuándo recibir notificaciones si el evento no tiene recordatorio específico
            </p>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
          <div 
            v-for="time in reminderOptions" 
            :key="time.value"
            class="flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-900"
          >
            <input 
              :id="`reminder-${time.value}`"
              type="checkbox" 
              :value="time.value"
              v-model="preferences.reminder_minutes"
              @change="updatePreferences"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-slate-600 dark:bg-slate-700 rounded"
            >
            <label 
              :for="`reminder-${time.value}`" 
              class="ml-2 text-sm text-gray-700 dark:text-slate-300"
            >
              {{ time.label }}
            </label>
          </div>
        </div>
        
        <div class="mt-4 rounded-xl bg-blue-50 p-3 dark:bg-blue-900/20">
          <p class="text-xs text-blue-800 dark:text-blue-200">
            <strong>Nota:</strong> Si un evento tiene su propio recordatorio configurado, se usará ese tiempo en lugar de estos valores por defecto.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotificationSettings',
  data() {
    return {
      requesting: false,
      testing: false,
      notificationStatus: {
        supported: false,
        permission: 'default',
        serviceWorkerReady: false
      },
      preferences: {
        calendar_notifications: true,
        sound_enabled: true,
        reminder_minutes: [15, 5]
      },
      reminderOptions: [
        { value: 5, label: '5 minutos antes' },
        { value: 10, label: '10 minutos antes' },
        { value: 15, label: '15 minutos antes' },
        { value: 30, label: '30 minutos antes' },
        { value: 60, label: '1 hora antes' },
        { value: 120, label: '2 horas antes' }
      ]
    }
  },
  async mounted() {
    await this.checkNotificationStatus();
    await this.loadPreferences();
  },
  methods: {
    async checkNotificationStatus() {
      if (window.NotificationManager) {
        this.notificationStatus = window.NotificationManager.getStatus();
      } else {
        // Esperar a que se cargue
        setTimeout(() => {
          if (window.NotificationManager) {
            this.notificationStatus = window.NotificationManager.getStatus();
          }
        }, 1000);
      }
    },

    async requestPermission() {
      if (!window.NotificationManager) {
        alert('Sistema de notificaciones no disponible');
        return;
      }

      this.requesting = true;
      try {
        const permission = await window.NotificationManager.requestPermission();
        this.notificationStatus.permission = permission;
        
        if (permission === 'granted') {
          this.preferences.calendar_notifications = true;
          await this.updatePreferences();
        }
      } catch (error) {
        console.error('Error solicitando permisos:', error);
      } finally {
        this.requesting = false;
      }
    },

    async testNotification() {
      this.testing = true;
      try {
        if (window.NotificationManager) {
          await window.NotificationManager.showNotification(
            '🧪 Notificación de prueba',
            'Si puedes ver esto, ¡las notificaciones están funcionando correctamente!',
            {
              tag: `test-notification-${Date.now()}`, // Tag único para evitar duplicados
              renotify: true,
              requireInteraction: false
            }
          );
        }
        
        // También llamar al backend para registrar la prueba
        await fetch('/api/notifications/test', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          body: JSON.stringify({
            title: '🧪 Notificación de prueba',
            body: 'Si puedes ver esto, ¡las notificaciones están funcionando correctamente!',
            type: 'test'
          })
        });
        
      } catch (error) {
        console.error('Error enviando notificación de prueba:', error);
        alert('Error enviando notificación de prueba');
      } finally {
        this.testing = false;
      }
    },

    async loadPreferences() {
      try {
        // Cargar desde servidor
        const response = await fetch('/api/notifications/preferences');
        const data = await response.json();
        
        // Combinar con localStorage para sonido (más inmediato)
        const localSoundEnabled = localStorage.getItem('notification-sound-enabled');
        
        this.preferences = {
          ...data.preferences,
          // Priorizar localStorage para sonido si existe
          sound_enabled: localSoundEnabled !== null ? localSoundEnabled === 'true' : data.preferences.sound_enabled
        };
        
        console.log('Preferencias cargadas:', this.preferences);
      } catch (error) {
        console.error('Error cargando preferencias:', error);
        
        // Fallback a localStorage
        const localSoundEnabled = localStorage.getItem('notification-sound-enabled');
        if (localSoundEnabled !== null) {
          this.preferences.sound_enabled = localSoundEnabled === 'true';
        }
      }
    },

    async updatePreferences() {
      try {
        // Actualizar localStorage inmediatamente para sonido
        localStorage.setItem('notification-sound-enabled', this.preferences.sound_enabled.toString());
        
        // Enviar al servidor
        await fetch('/api/notifications/preferences', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          body: JSON.stringify(this.preferences)
        });

        // Actualizar configuración local del NotificationManager
        if (window.NotificationManager) {
          window.NotificationManager.setSoundEnabled(this.preferences.sound_enabled);
        }
        
        if (window.CalendarNotifications) {
          window.CalendarNotifications.setReminderTimes(this.preferences.reminder_minutes);
        }
        
        console.log('Preferencias actualizadas:', this.preferences);

      } catch (error) {
        console.error('Error actualizando preferencias:', error);
        // Al menos mantener localStorage actualizado
        localStorage.setItem('notification-sound-enabled', this.preferences.sound_enabled.toString());
      }
    },

    getPermissionText() {
      switch (this.notificationStatus.permission) {
        case 'granted':
          return '✅ Permitido';
        case 'denied':
          return '❌ Denegado';
        case 'default':
          return '⚠️ Sin configurar';
        default:
          return '❓ Desconocido';
      }
    }
  }
}
</script>

<style scoped>
.notification-settings-integrated {
  /* Los estilos base se heredan del contenedor padre en SettingsView */
}

/* Asegurar que el grid se vea bien en móviles */
@media (max-width: 768px) {
  .grid.grid-cols-1.md\\:grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .grid.grid-cols-2.md\\:grid-cols-3 {
    grid-template-columns: 1fr 1fr;
  }
}
</style>