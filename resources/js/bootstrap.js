import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const baseURL = import.meta.env.DEV ? '/api' : '/zalbena/api';
window.axios.defaults.baseURL = baseURL;
window.axios.defaults.withCredentials = true;

const basePath = import.meta.env.VITE_API_BASE_URL || '';
let isRedirecting = false;

// Get CSRF token from meta tag and set it in axios headers
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token не пронађен у meta тагу');
}

// Response interceptor for global error handling
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            switch (error.response.status) {
                case 401:
                    // Unauthenticated - redirect to login (unless already there)
                    if (!isRedirecting && !window.location.pathname.includes('/login')) {
                        isRedirecting = true;
                        console.warn('Неаутентификован корисник, редирект на login страницу');
                        window.location.replace(`${basePath}/login`);
                    }
                    break;
                case 419:
                    // CSRF token mismatch - session expired, redirect to login with full reload
                    if (!isRedirecting) {
                        isRedirecting = true;
                        console.warn('CSRF token неподударање, редирект на login страницу');
                        window.location.replace(`${basePath}/login`);
                    }
                    break;
                case 403:
                    // Forbidden
                    console.warn('Забрањен приступ - корисник нема дозволу');
                    alert('Немате дозволу за извршавање ове акције.');
                    break;
            }
        } else if (error.request) {
            // Network error
            console.error('Грешка мреже:', error.request);
        }
        return Promise.reject(error);
    }
);
