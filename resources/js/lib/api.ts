import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
    withCredentials: true,
});

// Add CSRF token to all requests
api.interceptors.request.use((config) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    }
    return config;
});

// Add response interceptor for error handling
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Handle unauthorized - could redirect to login
            console.error('Unauthorized request');
        } else if (error.response?.status === 419) {
            // CSRF token mismatch - reload page
            console.error('CSRF token mismatch - reloading page');
            window.location.reload();
        }
        return Promise.reject(error);
    },
);

export default api;
