import { defineStore } from 'pinia';

export const useConfirmStore = defineStore('confirm', {
    state: () => ({
        show: false,
        title: '',
        message: '',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'danger',
        loading: false,
        resolve: null
    }),
    actions: {
        ask({ title, message, confirmText = 'Delete Now', cancelText = 'Cancel', variant = 'danger' }) {
            this.title = title;
            this.message = message;
            this.confirmText = confirmText;
            this.cancelText = cancelText;
            this.variant = variant;
            this.show = true;
            this.loading = false;

            return new Promise((res) => {
                this.resolve = res;
            });
        },
        confirm() {
            if (this.resolve) this.resolve(true);
            this.show = false;
        },
        cancel() {
            if (this.resolve) this.resolve(false);
            this.show = false;
        }
    }
});
