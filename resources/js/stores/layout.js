import { defineStore } from 'pinia';

export const useLayoutStore = defineStore('layout', {
  state: () => {
    const savedState = localStorage.getItem('layoutState');
    const defaultState = {
      isDarkMode: true,
      isSidebarCollapsed: false,
      isSettingsOpen: false,
      isMobileMenuOpen: false,
      themeColor: '#00A76F',
      isCompact: false,
      isProfileOpen: false,
      fontFamily: 'Public Sans',
      fontSize: 16,
    };
    if (savedState) {
      return { ...defaultState, ...JSON.parse(savedState), isSettingsOpen: false, isMobileMenuOpen: false, isProfileOpen: false };
    }
    return defaultState;
  },
  actions: {
    saveState() {
      const stateToSave = {
        isDarkMode: this.isDarkMode,
        isSidebarCollapsed: this.isSidebarCollapsed,
        themeColor: this.themeColor,
        isCompact: this.isCompact,
        fontFamily: this.fontFamily,
        fontSize: this.fontSize,
      };
      localStorage.setItem('layoutState', JSON.stringify(stateToSave));
    },
    toggleDarkMode() {
      this.isDarkMode = !this.isDarkMode;
      this.updateBodyClass();
      this.saveState();
    },
    toggleSidebar() {
      this.isSidebarCollapsed = !this.isSidebarCollapsed;
      this.saveState();
    },
    setSidebarCollapsed(isCollapsed) {
      this.isSidebarCollapsed = isCollapsed;
      this.saveState();
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
    setThemeColor(hex) {
      this.themeColor = hex;
      this.applyThemeColor();
      this.saveState();
    },
    setFontFamily(font) {
      this.fontFamily = font;
      this.applyFontFamily();
      this.saveState();
    },
    setFontSize(size) {
      this.fontSize = size;
      this.applyFontSize();
      this.saveState();
    },
    applyThemeColor() {
      document.documentElement.style.setProperty('--color-primary', this.themeColor);
      document.documentElement.style.setProperty('--palette-primary-main', this.themeColor);
      document.documentElement.style.setProperty('--nav-item-root-active-color', this.themeColor);
    },
    applyFontFamily() {
      document.documentElement.style.setProperty('--font-sans', `"${this.fontFamily}", ui-sans-serif, system-ui, sans-serif`);
      document.body.style.fontFamily = `"${this.fontFamily}", ui-sans-serif, system-ui, sans-serif`;
    },
    applyFontSize() {
      document.documentElement.style.fontSize = `${this.fontSize}px`;
    },
    updateBodyClass() {
      if (this.isDarkMode) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    },
    applyAllSettings() {
      this.updateBodyClass();
      this.applyThemeColor();
      this.applyFontFamily();
      this.applyFontSize();
    }
  },
});
