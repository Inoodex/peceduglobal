import { defineStore } from 'pinia';
import axios from '../plugins/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
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
                localStorage.setItem('token', this.token);
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
            }
        },
    },
});
