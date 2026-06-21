/**
 * Persist table filter states in sessionStorage so pagination and selected
 * filters remain intact after navigation.
 */

export function saveFiltersState(key, state) {
  try {
    sessionStorage.setItem(`filters_state_${key}`, JSON.stringify(state));
  } catch (e) {
    console.error('Failed to save filters state', e);
  }
}

export function restoreFiltersState(key, defaultState = {}) {
  try {
    const saved = sessionStorage.getItem(`filters_state_${key}`);
    if (saved) {
      return { ...defaultState, ...JSON.parse(saved) };
    }
  } catch (e) {
    console.error('Failed to restore filters state', e);
  }
  return defaultState;
}

export function clearFiltersState(key) {
  sessionStorage.removeItem(`filters_state_${key}`);
}
