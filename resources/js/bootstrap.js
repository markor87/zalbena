import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const baseURL = import.meta.env.DEV ? '/api' : '/zalbena/api';
window.axios.defaults.baseURL = baseURL;
window.axios.defaults.withCredentials = true;
