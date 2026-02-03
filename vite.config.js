import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vue framework and state management
                    'vendor-vue': ['vue', 'pinia'],
                    // UI and Calendar libraries
                    'vendor-ui': [
                        '@fullcalendar/core',
                        '@fullcalendar/daygrid', 
                        '@fullcalendar/interaction',
                        '@fullcalendar/timegrid',
                        '@fullcalendar/vue3',
                        'sweetalert2'
                    ],
                    // Utilities
                    'vendor-utils': ['axios', 'alpinejs']
                },
            },
        },
        // Enable source maps for better debugging
        sourcemap: process.env.NODE_ENV === 'development',
        // Optimize chunks
        chunkSizeWarningLimit: 1000,
        // Minification options
        minify: 'esbuild',
        target: ['es2020', 'chrome90', 'firefox88', 'safari14'],
    },
    // Optimize dependencies
    optimizeDeps: {
        include: [
            'vue',
            'pinia',
            'axios',
            '@fullcalendar/core',
            'sweetalert2'
        ],
        exclude: ['@popperjs/core']
    },
});
