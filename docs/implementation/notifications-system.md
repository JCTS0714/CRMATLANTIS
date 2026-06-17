# 🔔 Sistema de Notificaciones Push para CRM Atlantis

## ¿Qué incluye?

Este sistema implementa **notificaciones push reales del navegador** para los eventos del calendario, con sonido personalizado y configuración avanzada.

### ✨ **Características principales:**

- 📱 **Notificaciones nativas del navegador** (como las de WhatsApp/Facebook)
- 🔊 **Sonido personalizable** con fallback automático
- ⏰ **Recordatorios programables** (15 min, 5 min antes del evento)
- 🎛️ **Configuración completa** desde la interfaz
- 🚀 **Service Worker** para notificaciones en background
- 📋 **Acciones rápidas** (Ver evento, Posponer)

## 🛠️ **Instalación y configuración:**

### 1. **Compilar assets**
```bash
npm run build
```

### 2. **Ejecutar migraciones** (si hay nuevas)
```bash
php artisan migrate
```

### 3. **Generar archivo de sonido** (opcional)
- Visita: `http://tu-dominio/sounds/generate-audio.html`
- Genera y descarga el archivo de sonido
- Guárdalo como `/public/sounds/notification.mp3`

### 4. **Configurar permisos**
Asegurar que los usuarios tengan acceso a:
- `calendar.view` (para ver calendario)
- `calendar.create` (para crear eventos)
- `calendar.update` (para editar eventos)

## 🎯 **Cómo usar:**

### **Para usuarios:**
1. Ve a **"Notificaciones"** en el menú lateral
2. Haz clic en **"Activar Notificaciones"**
3. Acepta los permisos del navegador
4. Configura tus preferencias (sonido, tiempos de recordatorio)
5. **¡Listo!** Recibirás notificaciones de tus eventos del calendario

### **Para crear eventos con notificaciones:**
- Las notificaciones se programan automáticamente al crear/editar eventos
- Se cancelan automáticamente al eliminar eventos
- Los tiempos de recordatorio se pueden personalizar por usuario

## 🔧 **Archivos implementados:**

### **Backend:**
- `app/Http/Controllers/Api/NotificationController.php` - API de notificaciones
- `routes/web.php` - Rutas API agregadas

### **Frontend:**
- `resources/js/notifications.js` - Sistema base de notificaciones
- `resources/js/calendar-notifications.js` - Integración con calendario
- `resources/js/components/NotificationSettings.vue` - Configuración UI
- `resources/js/components/CalendarView.vue` - Integrado con notificaciones

### **Service Worker:**
- `public/sw.js` - Service Worker para notificaciones push

### **Audio:**
- `public/sounds/generate-audio.html` - Generador de sonido
- `public/sounds/notification-generator.js` - Generador programático

## 🌐 **APIs disponibles:**

### **Notificaciones:**
- `GET /api/notifications/upcoming-events` - Eventos próximos
- `POST /api/notifications/test` - Notificación de prueba
- `PUT /api/notifications/preferences` - Actualizar preferencias
- `GET /api/notifications/preferences` - Obtener preferencias
- `POST /api/notifications/closed` - Marcar notificación como cerrada

## ⚙️ **Configuración avanzada:**

### **Personalizar sonido:**
1. Reemplaza `/public/sounds/notification.mp3` con tu archivo
2. O usa el generador incluido para crear uno personalizado

### **Cambiar tiempos de recordatorio:**
- Modifica `defaultReminderTimes` en `calendar-notifications.js`
- O permite que usuarios configuren desde la UI

### **Personalizar Service Worker:**
- Edita `public/sw.js` para cambiar comportamiento de notificaciones
- Agrega más acciones, iconos, etc.

## 🔍 **Troubleshooting:**

### **Las notificaciones no aparecen:**
1. Verifica que el usuario haya dado permisos
2. Confirma que el Service Worker esté registrado
3. Revisa la consola del navegador para errores

### **El sonido no funciona:**
1. Verifica que existe `/public/sounds/notification.mp3`
2. Usa el generador incluido para crear uno
3. El sistema tiene fallbacks automáticos

### **Eventos no programan notificaciones:**
1. Verifica que `calendar-notifications.js` esté cargado
2. Confirma que el evento tenga fecha/hora válida
3. Revisa logs en consola del navegador

## 📱 **Compatibilidad:**

- ✅ Chrome/Edge 50+
- ✅ Firefox 44+
- ✅ Safari 16+ (con limitaciones)
- ❌ Internet Explorer (no compatible)

## 🚀 **Próximas mejoras:**

- [ ] Push notifications desde servidor (con cron jobs)
- [ ] Integración con email/SMS
- [ ] Notificaciones para otros módulos (leads, incidencias)
- [ ] Estadísticas de notificaciones

---

## 🎉 **¡Todo listo!**

El sistema de notificaciones está completamente integrado con tu calendario. Los usuarios pueden activar notificaciones desde la nueva sección "Notificaciones" en el menú lateral.

**¿Problemas?** Revisa los logs del navegador y del servidor para diagnosticar cualquier issue.