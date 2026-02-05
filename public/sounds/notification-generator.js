// Archivo de audio generado programáticamente para notificaciones
// Este script crea un sonido de notificación simple usando Web Audio API

// Evitar declaración duplicada
if (typeof window.NotificationSound === 'undefined') {
    class NotificationSound {
        constructor() {
            this.audioContext = null;
            this.audioBuffer = null;
            this.initialized = false;
        }

        async init() {
            if (this.initialized) return;

            try {
                // Crear contexto de audio
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                // Crear buffer de audio con un sonido de notificación agradable
                this.audioBuffer = await this.createNotificationTone();
                this.initialized = true;
                
                console.log('NotificationSound inicializado correctamente');
            } catch (error) {
                console.error('Error inicializando NotificationSound:', error);
            }
        }

        async createNotificationTone() {
            // Crear un sonido de dos tonos (como WhatsApp)
            const sampleRate = this.audioContext.sampleRate;
            const duration = 0.6; // 600ms
            const buffer = this.audioContext.createBuffer(1, sampleRate * duration, sampleRate);
            const data = buffer.getChannelData(0);

            for (let i = 0; i < buffer.length; i++) {
                const time = i / sampleRate;
                let frequency = 800; // Frecuencia base
                
                // Primer tono (0-300ms): 800Hz
                if (time < 0.3) {
                    frequency = 800;
                }
                // Segundo tono (300-600ms): 600Hz
                else {
                    frequency = 600;
                }

                // Generar onda senoidal con envelope
                let amplitude = 0.3;
                
                // Fade in/out para evitar clicks
                if (time < 0.05) {
                    amplitude *= time / 0.05;
                } else if (time > duration - 0.05) {
                    amplitude *= (duration - time) / 0.05;
                }

                data[i] = amplitude * Math.sin(2 * Math.PI * frequency * time);
            }

            return buffer;
        }

        async play() {
            if (!this.initialized) {
                await this.init();
            }

            if (!this.audioBuffer) {
                console.warn('Audio buffer no disponible');
                return;
            }

            try {
                // Resume el contexto si está suspendido
                if (this.audioContext.state === 'suspended') {
                    await this.audioContext.resume();
                }

                // Crear source node
                const source = this.audioContext.createBufferSource();
                source.buffer = this.audioBuffer;
                
                // Conectar al destino (altavoces)
                source.connect(this.audioContext.destination);
                
                // Reproducir
                source.start();
                
            } catch (error) {
                console.error('Error reproduciendo sonido:', error);
            }
        }

        // Crear versión más sutil
        async createSubtleTone() {
            const sampleRate = this.audioContext.sampleRate;
            const duration = 0.4; // Más corto
            const buffer = this.audioContext.createBuffer(1, sampleRate * duration, sampleRate);
            const data = buffer.getChannelData(0);

            for (let i = 0; i < buffer.length; i++) {
                const time = i / sampleRate;
                
                // Un solo tono suave
                const frequency = 880; // A5 - más agradable
                let amplitude = 0.15; // Más suave
                
                // Envelope suave
                if (time < 0.1) {
                    amplitude *= time / 0.1;
                } else if (time > duration - 0.1) {
                    amplitude *= (duration - time) / 0.1;
                }

                data[i] = amplitude * Math.sin(2 * Math.PI * frequency * time);
            }

            return buffer;
        }

        async setSubtle(isSubtle = true) {
            if (isSubtle) {
                this.audioBuffer = await this.createSubtleTone();
            } else {
                this.audioBuffer = await this.createNotificationTone();
            }
        }
    }

    // Instancia global (solo si no existe ya)
    window.NotificationSound = new NotificationSound();
}
if (!window.NotificationSound) {
    window.NotificationSound = new NotificationSound();
}