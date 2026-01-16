import './bootstrap';

import { initTheme } from './theme';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import App from './components/App.vue';
import AnimatedBackgroundLogin from './components/AnimatedBackgroundLogin.vue';

window.Alpine = Alpine;

initTheme();

Alpine.start();

const appRoot = document.getElementById('app');
if (appRoot) {
	createApp(App).mount(appRoot);
}

// Mount animated background if container exists (login page)
const bgRoot = document.getElementById('animated-bg');
if (bgRoot) {
  createApp(AnimatedBackgroundLogin).mount(bgRoot);
}
