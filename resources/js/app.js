import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import ripple from './directives/ripple';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Register global directive
app.directive('ripple', ripple);

app.mount('#app');
