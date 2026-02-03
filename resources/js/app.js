import './bootstrap';

import { initTheme } from './theme';

import Alpine from 'alpinejs';
import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';

// Lazy load main components for better performance
const App = defineAsyncComponent(() => import('./components/App.vue'));
const AnimatedBackgroundLogin = defineAsyncComponent(() => import('./components/AnimatedBackgroundLogin.vue'));

window.Alpine = Alpine;

initTheme();

Alpine.start();

// Create Pinia store
const pinia = createPinia();

// Mount main app if container exists (dashboard)
const appRoot = document.getElementById('app');
if (appRoot) {
	const app = createApp(App);
	app.use(pinia);
	app.mount(appRoot);
}

// Mount animated background if container exists (login page)
const bgRoot = document.getElementById('animated-bg');
if (bgRoot) {
  const bgApp = createApp(AnimatedBackgroundLogin);
  bgApp.mount(bgRoot);
}
