import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import ripple from './directives/ripple';

import { setupIdleTimeout } from './plugins/idleTimeout';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Register global directive
app.directive('ripple', ripple);

app.mount('#app');

// Set idle timeout (e.g., 15 minutes)
setupIdleTimeout(30);
