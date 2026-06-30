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
    if (cached && Array.isArray(cached.data)) {
        setVal(dataRef, dataKey, cached.data || []);
        setVal(paginationRef, paginationKey, cached.meta || null);
        
        // If the cache is marked as expired, we render the stale data instantly 
        // to avoid skeleton flashing, but trigger the subtle background progress bar!
        if (cached.expired) {
            setVal(loadingRef, loadingKey, true);
        } else {
            setVal(loadingRef, loadingKey, false); // Instant clean render!
        }
    } else {
        setVal(loadingRef, loadingKey, true); // Trigger subtle progress bar during filters/first load
    }

    // 2. Fetch fresh data from the server in the background
    try {
        const response = await axios.get(url, { params: cleanParams });
        const res = response.data;
        if (res && (res.success !== false)) {
            // Support both standard success wrapper and direct resource collections
            let data = res.data || res || [];
            let meta = res.meta || res.pagination || null;
            
            // Handle Laravel's nested Resource Collection format (where data contains data, links, meta)
            if (!Array.isArray(data) && data !== null && typeof data === 'object' && Array.isArray(data.data)) {
                meta = data.meta || data.pagination || null;
                // Fallback: Laravel's paginate() puts pagination fields directly on the paginator object
                if (!meta && data.current_page) {
                    meta = {
                        current_page: data.current_page,
                        last_page: data.last_page,
                        total: data.total,
                        per_page: data.per_page,
                        from: data.from,
                        to: data.to,
                    };
                }
                data = data.data;
            }
            
            setVal(dataRef, dataKey, data);
            setVal(paginationRef, paginationKey, meta);
            
            // Save to dynamic Pinia cache and explicitly mark as NOT expired
            cacheStore.set(cacheKey, { data, meta, expired: false });
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
