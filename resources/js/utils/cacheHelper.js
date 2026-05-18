import axios from '@/plugins/axios';
import { useCacheStore } from '@/stores/cache';

/**
 * Centrally fetch data with stale-while-revalidate caching.
 * Supports both Composition API (refs) and Options API (component 'this').
 */
export async function fetchWithCache({
    url,
    params = {},
    component = null,      // For Options API ('this')
    loadingKey = 'loading',
    dataKey = 'data',
    paginationKey = 'pagination',
    loadingRef = null,     // For Composition API (refs)
    dataRef = null,
    paginationRef = null,
    toast = null,
    errorMsg = 'Failed to load data'
}) {
    const cacheStore = useCacheStore();
    
    // Clean up empty, null, or undefined params
    const cleanParams = {};
    Object.keys(params).forEach(key => {
        if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
            cleanParams[key] = params[key];
        }
    });
    
    // Build a unique cache key based on the URL and active params
    const queryString = Object.keys(cleanParams)
        .map(key => `${key}=${cleanParams[key]}`)
        .join('&');
    const cacheKey = queryString ? `${url}?${queryString}` : url;

    // Helper to set reactive values safely
    const setVal = (refObj, keyName, value) => {
        console.log(`[CacheHelper] Setting ${keyName}:`, value);
        if (refObj) {
            refObj.value = value;
        } else if (component && keyName) {
            component[keyName] = value;
        }
    };

    // 1. Try to fetch from the Pinia Cache store instantly
    const cached = cacheStore.get(cacheKey);
    if (cached) {
        setVal(dataRef, dataKey, cached.data || []);
        setVal(paginationRef, paginationKey, cached.meta || null);
        setVal(loadingRef, loadingKey, false); // Instant render!
    } else {
        setVal(loadingRef, loadingKey, true); // Trigger subtle progress bar during filters
    }

    // 2. Fetch fresh data from the server in the background
    try {
        const response = await axios.get(url, { params: cleanParams });
        const res = response.data;
        if (res && res.success) {
            const data = res.data || [];
            const meta = res.meta || null;
            
            setVal(dataRef, dataKey, data);
            setVal(paginationRef, paginationKey, meta);
            
            // Save to dynamic Pinia cache
            cacheStore.set(cacheKey, { data, meta });
        } else {
            setVal(dataRef, dataKey, []);
            setVal(paginationRef, paginationKey, null);
        }
    } catch (error) {
        if (toast) {
            toast.error(errorMsg);
        } else {
            console.error(errorMsg, error);
        }
    } finally {
        setVal(loadingRef, loadingKey, false);
    }
}

/**
 * Easily clear dynamic cache by base endpoint prefix
 */
export function clearCache(url) {
    const cacheStore = useCacheStore();
    cacheStore.clear(url);
}
