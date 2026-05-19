import { defineStore } from 'pinia';

export const useCacheStore = defineStore('cache', {
    state: () => ({
        // Key-Value map for API endpoints: { url_with_query_params: data_payload }
        cacheData: {}
    }),
    actions: {
        // Get cached data for a key
        get(key) {
            return this.cacheData[key] || null;
        },
        // Set cache data for a key
        set(key, data) {
            this.cacheData[key] = data;
        },
        // Clear cache for a specific key (and all its sub-pages/paginated variants) by marking them expired
        clear(key) {
            if (key) {
                // Extract base endpoint (e.g., /auth/admin/hero-sliders from /auth/admin/hero-sliders?page=1&per_page=15)
                const baseEndpoint = key.split('?')[0];
                
                // Mark any cache keys that start with the base endpoint prefix as expired instead of deleting
                Object.keys(this.cacheData).forEach(k => {
                    if (k.startsWith(baseEndpoint)) {
                        if (this.cacheData[k]) {
                            this.cacheData[k].expired = true;
                        }
                    }
                });
            } else {
                // Reset entire cache
                this.cacheData = {};
            }
        }
    }
});
