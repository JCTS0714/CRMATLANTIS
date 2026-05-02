import { nextTick } from 'vue';

export function useCalendarRuntime({ refetchEvents }) {
  const onCalendarRefetchEvent = () => {
    if (typeof refetchEvents === 'function') {
      refetchEvents();
    }
  };

  const scheduleEventNotifications = (eventData) => {
    if (!window.CalendarNotifications || !eventData) return;

    const formattedEvent = {
      id: eventData.id,
      title: eventData.title,
      description: eventData.description || '',
      start_datetime: eventData.start_at,
      end_datetime: eventData.end_at,
      start_time: new Date(eventData.start_at).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
      }),
      reminder_minutes: eventData.reminder_minutes,
    };

    if (formattedEvent.reminder_minutes && formattedEvent.reminder_minutes > 0) {
      window.CalendarNotifications.setReminderTimes([formattedEvent.reminder_minutes]);
    } else {
      window.CalendarNotifications.setReminderTimes([15, 5]);
    }

    window.CalendarNotifications.scheduleReminders(formattedEvent);
  };

  const cancelEventNotifications = (eventId) => {
    if (window.CalendarNotifications && eventId) {
      window.CalendarNotifications.cancelReminders(eventId);
    }
  };

  const start = ({ onReady, onAfterReady } = {}) => {
    window.addEventListener('calendar:refetch', onCalendarRefetchEvent);

    nextTick(() => {
      if (typeof onReady === 'function') {
        onReady();
      }

      Promise.resolve().then(() => {
        if (window.CalendarNotifications) {
          window.CalendarNotifications.init();
        }
      });

      if (typeof onAfterReady === 'function') {
        onAfterReady();
      }
    });
  };

  const stop = () => {
    window.removeEventListener('calendar:refetch', onCalendarRefetchEvent);
  };

  return {
    start,
    stop,
    scheduleEventNotifications,
    cancelEventNotifications,
  };
}