import './bootstrap';

import { initTheme } from './theme';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import App from './components/App.vue';

window.Alpine = Alpine;

initTheme();

Alpine.start();

const appRoot = document.getElementById('app');
if (appRoot) {
	createApp(App).mount(appRoot);
}
