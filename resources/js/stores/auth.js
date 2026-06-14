import { defineStore } from 'pinia';
import axios from '../plugins/axios';
import Cookies from 'js-cookie';

// Helper: get parent domain (e.g., "apps.peceduglobal.com" → ".peceduglobal.com")
const getCookieDomain = () => {
    const hostname = window.location.hostname;
    const parts = hostname.split('.');
    if (parts.length >= 2) {
        return '.' + parts.slice(-2).join('.');
    }
    return hostname; // localhost fallback
};

const cookieDomain = getCookieDomain();

// Read token from cookie first (cross-subdomain), then localStorage (direct login)
const getStoredToken = () => {
    return Cookies.get('auth_token') || Cookies.get('token') || localStorage.getItem('token') || null;
};

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: getStoredToken(),
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.post('/auth/login', credentials);
                this.token = response.data.token;
                this.user = response.data.data;
                // Save to both localStorage and cookie (shared across subdomains)
                localStorage.setItem('token', this.token);
                Cookies.set('auth_token', this.token, { domain: cookieDomain, expires: 14, secure: true, sameSite: 'Lax' });
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Login failed';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async register(userData) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.post('/auth/register', userData);
                this.token = response.data.token;
                this.user = response.data.data;
                localStorage.setItem('token', this.token);
                Cookies.set('auth_token', this.token, { domain: cookieDomain, expires: 14, secure: true, sameSite: 'Lax' });
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Registration failed';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await axios.post('/auth/logout');
            } catch (error) {
                console.error('Logout failed', error);
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                // Remove from cookie as well (both domain-scoped and plain)
                Cookies.remove('auth_token', { domain: cookieDomain });
                Cookies.remove('auth_token');
                Cookies.remove('token', { domain: cookieDomain });
                Cookies.remove('token');
            }
        },

        async fetchUser() {
            if (!this.token) return;
            try {
                const response = await axios.get('/auth/me');
                this.user = response.data.data;
            } catch (error) {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                Cookies.remove('auth_token', { domain: cookieDomain });
            }
        },
    },
});
