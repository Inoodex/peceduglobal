import { defineStore } from 'pinia';

export const useToastStore = defineStore('toast', {
    state: () => ({
        toasts: []
    }),
    actions: {
        add(message, type = 'success', duration = 3000) {
            const id = Date.now();
            this.toasts.push({ id, message, type });

            setTimeout(() => {
                this.remove(id);
            }, duration);
        },
        success(message, duration) {
            this.add(message, 'success', duration);
        },
        error(message, duration) {
            this.add(message, 'error', duration);
        },
        warning(message, duration) {
            this.add(message, 'warning', duration);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }
});
