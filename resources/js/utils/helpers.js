import { useRoute } from 'vue-router';

/**
 * Helper to check if a route path is active
 * @param {string} path 
 * @returns {boolean}
 */
export const isActiveRoute = (path) => {
  const route = useRoute();
  if (!path || !route) return false;
  
  // Exact match or starts with path (for sub-routes)
  return route.path === path || route.path.startsWith(path + '/');
};

/**
 * Helper to check if any child in a list is active
 * @param {Array} children 
 * @returns {boolean}
 */
export const isAnyChildActive = (children) => {
  if (!children || !Array.isArray(children)) return false;
  return children.some(child => isActiveRoute(child.path));
};
