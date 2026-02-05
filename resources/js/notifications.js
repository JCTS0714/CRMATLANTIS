// Sistema de notificaciones push para CRM Atlantis
class NotificationManager {
    constructor() {
        this.isSupported = 'Notification' in window && 'serviceWorker' in navigator;
        this.permission = this.isSupported ? Notification.permission : 'denied';
        this.registration = null;
        this.soundEnabled = this.getSoundEnabled(); // Cargar desde localStorage
        this.notificationSound = null;
        
        this.init();
    }

    async init() {
        if (!this.isSupported) {
            console.warn('Notificaciones no soportadas en este navegador');
            return;
        }

        try {
            // Registrar Service Worker
            this.registration = await navigator.serviceWorker.register('/sw.js');
            
            // Cargar sonido de notificación
            this.loadNotificationSound();
        } catch (error) {
            console.error('Error registrando Service Worker:', error);
        }
    }

    loadNotificationSound() {
        // Evitar carga duplicada
        if (this.notificationSound) {
            return;
        }
        
        // Intentar cargar el nuevo archivo de audio personalizado
        this.notificationSound = new Audio('/sounds/universfield-new-notification-022-370046.mp3');
        this.notificationSound.preload = 'auto';
        this.notificationSound.volume = 0.7;
        
        // Fallback si no existe el archivo - usar generador de sonido DIRECTAMENTE
        this.notificationSound.addEventListener('error', () => {
            this.notificationSound = null;
            
            // Cargar generador de sonidos directamente (solo una vez)
            if (!window.NotificationSound) {
                const script = document.createElement('script');
                script.src = '/sounds/notification-generator.js';
                script.onload = () => {
                    console.log('Generador de sonidos cargado');
                };
                script.onerror = () => {
                    console.warn('No se pudo cargar el generador de sonidos');
                };
                document.head.appendChild(script);
            }
        });
        
        // Log solo cuando hay problemas
        this.notificationSound.addEventListener('canplaythrough', () => {
            // Audio cargado exitosamente - no necesita log
        });
    }

    async requestPermission() {
        if (!this.isSupported) {
            throw new Error('Notificaciones no soportadas');
        }

        if (this.permission === 'granted') {
            return 'granted';
        }

        // Pedir permiso
        try {
            this.permission = await Notification.requestPermission();
            
            if (this.permission === 'granted') {
                console.log('Permisos de notificación otorgados');
                
                // Mostrar notificación de bienvenida
                this.showLocalNotification(
                    '¡Notificaciones activadas!',
                    'Ahora recibirás notificaciones de tu calendario.',
                    { tag: 'welcome' }
                );
            } else {
                console.warn('Permisos de notificación denegados');
            }
            
            return this.permission;
        } catch (error) {
            console.error('Error pidiendo permisos:', error);
            return 'denied';
        }
    }

    playNotificationSound() {
        if (!this.soundEnabled) return;
        
        try {
            if (this.notificationSound && this.notificationSound.readyState >= 2) {
                // Usar archivo de audio si está disponible
                this.notificationSound.currentTime = 0;
                this.notificationSound.play().catch(err => {
                    console.warn('No se pudo reproducir el sonido personalizado:', err);
                    this.playGeneratedSound();
                });
            } else {
                // Usar generador de sonidos como fallback
                this.playGeneratedSound();
            }
        } catch (error) {
            console.warn('Error reproduciendo sonido:', error);
        }
    }

    playGeneratedSound() {
        if (window.NotificationSound) {
            window.NotificationSound.play();
        } else {
            // Último fallback - beep del sistema
            if ('beep' in window) {
                window.beep();
            }
        }
    }

    // Método unificado principal para todas las notificaciones
    async showNotification(title, body, options = {}) {
        if (this.permission !== 'granted') {
            console.warn('Sin permisos para mostrar notificaciones');
            return false;
        }

        try {
            // Reproducir sonido primero
            this.playNotificationSound();
            
            // Configuración por defecto
            const defaultOptions = {
                body: body,
                icon: '/images/LOGO.png',
                badge: '/images/LOGO.png',
                requireInteraction: true, // No se cierra automáticamente
                silent: false, // Permitir sonido del sistema también
                vibrate: [200, 100, 200, 100, 200], // Vibración en móviles
                timestamp: Date.now(),
                renotify: true, // Permitir re-notificar con el mismo tag
                data: {
                    ...options.data,
                    timestamp: Date.now()
                }
            };

            const notificationOptions = { ...defaultOptions, ...options };
            
            // Siempre usar Notification API directa (más confiable)
            const notification = new Notification(title, notificationOptions);
            
            // Manejar clic en notificación
            notification.addEventListener('click', () => {
                window.focus();
                notification.close();
                
                // Navegar si se especifica una URL
                if (options.url) {
                    window.location.href = options.url;
                }
            });
            
            // Notificación enviada correctamente
            return true;
        } catch (error) {
            console.error('Error mostrando notificación:', error);
            return false;
        }
    }

    // Mantener método legacy para compatibilidad
    async showLocalNotification(title, body, options = {}) {
        return this.showNotification(title, body, options);
    }

    async showCalendarNotification(event) {
        const title = `📅 Recordatorio: ${event.title}`;
        const body = `${event.description || 'Sin descripción'}\n⏰ ${event.start_time}`;
        
        const options = {
            tag: `calendar-${event.id}`,
            icon: '/images/calendar-icon.png',
            requireInteraction: true,
            url: `/calendar?event=${event.id}`,
            data: {
                type: 'calendar',
                eventId: event.id,
                url: `/calendar?event=${event.id}`
            },
            actions: [
                {
                    action: 'view',
                    title: '👁️ Ver evento'
                },
                {
                    action: 'snooze',
                    title: '⏰ Recordar en 5 min'
                }
            ]
        };

        return this.showNotification(title, body, options);
    }

    // Programar notificación para un evento
    scheduleNotification(event, minutesBefore = 15) {
        const eventTime = new Date(event.start_datetime);
        const notificationTime = new Date(eventTime.getTime() - (minutesBefore * 60 * 1000));
        const now = new Date();

        if (notificationTime <= now) {
            console.warn('El tiempo de notificación ya pasó');
            return;
        }

        const timeUntilNotification = notificationTime.getTime() - now.getTime();
        
        console.log(`Notificación programada para: ${notificationTime.toLocaleString()}`);
        
        setTimeout(() => {
            this.showCalendarNotification(event);
        }, timeUntilNotification);
    }

    // Cancelar sonido
    setSoundEnabled(enabled) {
        this.soundEnabled = enabled;
        localStorage.setItem('notification-sound-enabled', enabled.toString());
    }

    getSoundEnabled() {
        const saved = localStorage.getItem('notification-sound-enabled');
        return saved ? saved === 'true' : true;
    }

    // Verificar soporte y permisos
    getStatus() {
        return {
            supported: this.isSupported,
            permission: this.permission,
            soundEnabled: this.soundEnabled,
            serviceWorkerReady: !!this.registration
        };
    }
}

// Instancia global
window.NotificationManager = new NotificationManager();

// Función global para facilitar uso
window.showNotification = (title, body, options) => {
    return window.NotificationManager.showNotification(title, body, options);
};

window.requestNotificationPermission = () => {
    return window.NotificationManager.requestPermission();
};

// Auto-inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    // Restaurar configuración de sonido silenciosamente
    const soundEnabled = localStorage.getItem('notification-sound-enabled');
    if (soundEnabled !== null) {
        window.NotificationManager.setSoundEnabled(soundEnabled === 'true');
        window.NotificationManager.soundEnabled = soundEnabled === 'true';
    } else {
        window.NotificationManager.setSoundEnabled(true);
    }
});