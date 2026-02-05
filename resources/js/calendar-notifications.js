// Configuración de notificaciones para el calendario
import './notifications.js';

class CalendarNotifications {
    constructor() {
        this.events = new Map(); // Cache de eventos programados
        this.reminders = new Map(); // Timeouts activos
        this.defaultReminderTimes = [15, 5]; // 15 y 5 minutos antes
    }

    async init() {
        // Verificar si las notificaciones están soportadas
        const status = window.NotificationManager.getStatus();
        
        if (!status.supported) {
            console.warn('Notificaciones no soportadas en este navegador');
            return false;
        }

        // Pedir permisos si no los tenemos
        if (status.permission !== 'granted') {
            const permission = await window.NotificationManager.requestPermission();
            if (permission !== 'granted') {
                console.warn('Usuario denegó permisos de notificación');
                return false;
            }
        }

        return true;
    }

    // Programar recordatorios para un evento
    scheduleReminders(event) {
        // Cancelar recordatorios existentes para este evento
        this.cancelReminders(event.id);

        const eventTime = new Date(event.start_datetime);
        const now = new Date();

        // Programar recordatorios normales
        this.defaultReminderTimes.forEach(minutes => {
            const reminderTime = new Date(eventTime.getTime() - (minutes * 60 * 1000));
            
            if (reminderTime > now) {
                const timeoutId = setTimeout(() => {
                    this.showEventReminder(event, minutes);
                }, reminderTime.getTime() - now.getTime());

                const reminderId = `${event.id}-${minutes}`;
                this.reminders.set(reminderId, timeoutId);
                
                // Recordatorio programado silenciosamente
            } else {
                const eventTimeStr = eventTime.toLocaleString();
                const nowStr = now.toLocaleString();
                console.warn(`⚠️  El evento "${event.title}" (${eventTimeStr}) ya pasó o está muy cerca (ahora: ${nowStr}). Recordatorio no programado.`);
            }
        });

        // Guardar evento en cache
        this.events.set(event.id, event);
    }

    // Mostrar recordatorio de evento
    showEventReminder(event, minutesBefore) {
        const title = `📅 Recordatorio: ${event.title}`;
        let body = '';
        
        if (minutesBefore > 0) {
            body = `Comienza en ${minutesBefore} minutos`;
        } else {
            body = 'Comienza ahora';
        }
        
        if (event.description) {
            body += `\n📝 ${event.description}`;
        }

        const options = {
            tag: `calendar-${event.id}-${minutesBefore}`,
            icon: '/favicon.ico', // Usar favicon que siempre existe
            badge: '/favicon.ico', // Usar favicon que siempre existe
            requireInteraction: true,
            url: `/calendar?event=${event.id}`,
            data: {
                type: 'calendar',
                eventId: event.id,
                minutesBefore: minutesBefore,
                url: `/calendar?event=${event.id}`
            },
            vibrate: [200, 100, 200, 100, 200]
        };

        // Usar showNotification para incluir sonido y notificaciones push
        if (window.NotificationManager) {
            return window.NotificationManager.showNotification(title, body, options);
        } else {
            console.error('NotificationManager no disponible para mostrar recordatorio');
            return false;
        }
    }

    // Cancelar recordatorios de un evento
    cancelReminders(eventId) {
        this.defaultReminderTimes.forEach(minutes => {
            const reminderId = `${eventId}-${minutes}`;
            const timeoutId = this.reminders.get(reminderId);
            
            if (timeoutId) {
                clearTimeout(timeoutId);
                this.reminders.delete(reminderId);
                console.log(`Recordatorio cancelado para evento ${eventId} (${minutes} min)`);
            }
        });
    }

    // Programar recordatorios para múltiples eventos
    scheduleMultipleReminders(events) {
        events.forEach(event => {
            this.scheduleReminders(event);
        });
    }

    // Limpiar todos los recordatorios
    clearAllReminders() {
        this.reminders.forEach((timeoutId, reminderId) => {
            clearTimeout(timeoutId);
        });
        this.reminders.clear();
        this.events.clear();
        console.log('Todos los recordatorios cancelados');
    }

    // Configurar tiempos de recordatorio personalizados
    setReminderTimes(minutes) {
        this.defaultReminderTimes = minutes;
    }

    // Obtener eventos programados
    getScheduledEvents() {
        return Array.from(this.events.values());
    }

    // Obtener recordatorios activos
    getActiveReminders() {
        return Array.from(this.reminders.keys());
    }
}

// Instancia global
window.CalendarNotifications = new CalendarNotifications();

// Función para usar desde el calendario
window.scheduleCalendarReminder = (event) => {
    return window.CalendarNotifications.scheduleReminders(event);
};

window.cancelCalendarReminder = (eventId) => {
    return window.CalendarNotifications.cancelReminders(eventId);
};

// Auto-inicializar silenciosamente
document.addEventListener('DOMContentLoaded', async () => {
    const initialized = await window.CalendarNotifications.init();
    // Inicializado silenciosamente
});