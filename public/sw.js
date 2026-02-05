// Service Worker para notificaciones push
const CACHE_NAME = 'crm-atlantis-v1';
const NOTIFICATION_SOUND = '/sounds/notification.mp3';

self.addEventListener('install', (event) => {
  console.log('Service Worker: Instalado');
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activado');
  event.waitUntil(self.clients.claim());
});

// Manejar notificaciones push del servidor
self.addEventListener('push', (event) => {
  console.log('Service Worker: Push recibido');
  
  let data = {};
  if (event.data) {
    data = event.data.json();
  }

  const options = {
    title: data.title || 'CRM Atlantis',
    body: data.body || 'Nueva notificación',
    icon: '/images/LOGO.png',
    badge: '/images/LOGO.png',
    image: data.image || null,
    data: data.data || {},
    actions: [
      {
        action: 'open',
        title: 'Abrir',
        icon: '/images/open-icon.png'
      },
      {
        action: 'close',
        title: 'Cerrar',
        icon: '/images/close-icon.png'
      }
    ],
    tag: data.tag || `notification-${Date.now()}`, // Tag único para evitar solapamiento
    requireInteraction: true, // No se cierra automáticamente
    renotify: true, // Permitir re-notificación incluso con el mismo tag
    vibrate: [200, 100, 200], // Vibración en móviles
    timestamp: Date.now(),
    silent: true // El sonido se maneja desde el frontend
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'CRM Atlantis', options)
  );
});

// Manejar clics en notificaciones
self.addEventListener('notificationclick', (event) => {
  console.log('Service Worker: Notificación clickeada');
  
  event.notification.close();

  if (event.action === 'open') {
    // Abrir/enfocar la aplicación
    event.waitUntil(
      self.clients.matchAll({ type: 'window' }).then((clientList) => {
        // Si ya hay una ventana abierta, enfocarla
        for (const client of clientList) {
          if (client.url.includes(self.location.origin) && 'focus' in client) {
            return client.focus();
          }
        }
        
        // Si no hay ventana abierta, abrir una nueva
        if (self.clients.openWindow) {
          const url = event.notification.data?.url || '/calendar';
          return self.clients.openWindow(url);
        }
      })
    );
  }
  
  // Acción por defecto (clic en el cuerpo de la notificación)
  if (!event.action) {
    event.waitUntil(
      self.clients.matchAll({ type: 'window' }).then((clientList) => {
        for (const client of clientList) {
          if (client.url.includes(self.location.origin) && 'focus' in client) {
            return client.focus();
          }
        }
        
        if (self.clients.openWindow) {
          const url = event.notification.data?.url || '/calendar';
          return self.clients.openWindow(url);
        }
      })
    );
  }
});

// Manejar cierre de notificaciones
self.addEventListener('notificationclose', (event) => {
  console.log('Service Worker: Notificación cerrada');
  
  // Opcional: registrar que se cerró la notificación
  if (event.notification.data?.trackClose) {
    fetch('/api/notifications/closed', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        notificationId: event.notification.data.id,
        closedAt: new Date().toISOString()
      })
    }).catch(err => console.log('Error tracking notification close:', err));
  }
});