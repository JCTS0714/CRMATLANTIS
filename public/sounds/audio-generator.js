// Generador de archivo MP3 de notificación
// Este script crea un archivo de audio de notificación usando Web Audio API
// y lo guarda como un blob

const generateNotificationAudio = async () => {
    try {
        // Crear contexto de audio offline para generar el sonido
        const sampleRate = 44100;
        const duration = 0.8; // 800ms
        const offlineContext = new OfflineAudioContext(1, sampleRate * duration, sampleRate);
        
        // Crear oscilador para el primer tono
        const oscillator1 = offlineContext.createOscillator();
        oscillator1.type = 'sine';
        oscillator1.frequency.setValueAtTime(800, 0); // 800Hz
        
        // Crear oscilador para el segundo tono
        const oscillator2 = offlineContext.createOscillator();
        oscillator2.type = 'sine';
        oscillator2.frequency.setValueAtTime(600, 0.4); // 600Hz después de 400ms
        
        // Crear gain nodes para controlar el volumen
        const gain1 = offlineContext.createGain();
        const gain2 = offlineContext.createGain();
        
        // Configurar envelope para el primer tono
        gain1.gain.setValueAtTime(0, 0);
        gain1.gain.linearRampToValueAtTime(0.3, 0.05); // fade in
        gain1.gain.setValueAtTime(0.3, 0.35);
        gain1.gain.linearRampToValueAtTime(0, 0.4); // fade out
        
        // Configurar envelope para el segundo tono
        gain2.gain.setValueAtTime(0, 0.4);
        gain2.gain.linearRampToValueAtTime(0.25, 0.45); // fade in
        gain2.gain.setValueAtTime(0.25, 0.75);
        gain2.gain.linearRampToValueAtTime(0, 0.8); // fade out
        
        // Conectar nodos
        oscillator1.connect(gain1);
        gain1.connect(offlineContext.destination);
        
        oscillator2.connect(gain2);
        gain2.connect(offlineContext.destination);
        
        // Iniciar osciladores
        oscillator1.start(0);
        oscillator1.stop(0.4);
        
        oscillator2.start(0.4);
        oscillator2.stop(0.8);
        
        // Renderizar audio
        const audioBuffer = await offlineContext.startRendering();
        
        // Convertir a WAV para compatibilidad (más fácil que MP3)
        const wavBlob = audioBufferToWav(audioBuffer);
        
        // Crear URL temporal y descargar
        const url = URL.createObjectURL(wavBlob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'notification.wav';
        a.click();
        URL.revokeObjectURL(url);
        
        console.log('Archivo de notificación generado y descargado');
        
    } catch (error) {
        console.error('Error generando audio:', error);
    }
};

// Función para convertir AudioBuffer a WAV
function audioBufferToWav(buffer) {
    const length = buffer.length;
    const arrayBuffer = new ArrayBuffer(44 + length * 2);
    const view = new DataView(arrayBuffer);
    
    // WAV header
    const writeString = (offset, string) => {
        for (let i = 0; i < string.length; i++) {
            view.setUint8(offset + i, string.charCodeAt(i));
        }
    };
    
    writeString(0, 'RIFF');
    view.setUint32(4, 36 + length * 2, true);
    writeString(8, 'WAVE');
    writeString(12, 'fmt ');
    view.setUint32(16, 16, true);
    view.setUint16(20, 1, true);
    view.setUint16(22, 1, true);
    view.setUint32(24, buffer.sampleRate, true);
    view.setUint32(28, buffer.sampleRate * 2, true);
    view.setUint16(32, 2, true);
    view.setUint16(34, 16, true);
    writeString(36, 'data');
    view.setUint32(40, length * 2, true);
    
    // Convert float samples to 16-bit PCM
    const channelData = buffer.getChannelData(0);
    let offset = 44;
    for (let i = 0; i < length; i++) {
        const sample = Math.max(-1, Math.min(1, channelData[i]));
        view.setInt16(offset, sample * 0x7FFF, true);
        offset += 2;
    }
    
    return new Blob([arrayBuffer], { type: 'audio/wav' });
}

// Función para usar en la consola del navegador
window.generateNotificationAudio = generateNotificationAudio;

console.log('Para generar el archivo de notificación, ejecuta: generateNotificationAudio()');