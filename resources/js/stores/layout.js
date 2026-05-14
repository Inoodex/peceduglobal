import { defineStore } from 'pinia';

export const useLayoutStore = defineStore('layout', {
  state: () => ({
    isDarkMode: true,
    isSidebarCollapsed: false,
    isSettingsOpen: false,
    isMobileMenuOpen: false,
    themeColor: '#00A76F',
    isCompact: false,
    isProfileOpen: false,
  }),
  actions: {
    toggleDarkMode() {
      this.isDarkMode = !this.isDarkMode;
      this.updateBodyClass();
    },
    toggleSidebar() {
      this.isSidebarCollapsed = !this.isSidebarCollapsed;
    },
    toggleSettings() {
      this.isSettingsOpen = !this.isSettingsOpen;
    },
    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
    },
    toggleProfile() {
      this.isProfileOpen = !this.isProfileOpen;
    },
    updateBodyClass() {
      if (this.isDarkMode) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    }
  },
});
