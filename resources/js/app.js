import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const app = createApp(App);
const pinia = createPinia();

app.component('VueDatePicker', VueDatePicker);
app.use(pinia);
app.use(router);
app.mount('#app');
