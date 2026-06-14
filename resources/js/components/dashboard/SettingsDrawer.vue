<template>
  <div v-if="layout.isSettingsOpen" class="fixed inset-0 z-[60]">
    <!-- Backdrop: Subtle overlay as seen in the original -->
    <div @click="layout.toggleSettings" class="absolute inset-0 bg-black/20 transition-opacity duration-300"></div>

    <!-- Panel: Increased to 360px width for a more spacious feel -->
    <div 
      class="absolute top-0 right-0 h-full w-[360px] shadow-2xl transition-transform duration-300 transform translate-x-0 flex flex-col border-l border-white/5"
      :class="layout.isDarkMode ? 'bg-[#161C24] text-white' : 'bg-white text-[#212B36]'"
    >
      <!-- Header -->
      <div class="px-5 py-4 flex items-center justify-between border-b border-dashed border-gray-500/10">
        <h6 class="text-lg font-bold">Settings</h6>
        <div class="flex items-center gap-1">
          <!-- Fullscreen -->
          <button  class="p-2 rounded-full hover:bg-gray-500/10 text-gray-500 transition-colors">
            <Maximize :size="18" />
          </button>
          <!-- Reset -->
          <button  class="p-2 rounded-full hover:bg-gray-500/10 text-gray-500 transition-colors relative">
            <RotateCcw :size="18" />
            <span class="absolute top-2 right-2 w-2 h-2 bg-orange-500 rounded-full border border-[#161C24]"></span>
          </button>
          <!-- Close -->
          <button  @click="layout.toggleSettings" class="p-2 rounded-full hover:bg-gray-500/10 text-gray-500 transition-colors">
            <X :size="18" />
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto px-5 pb-10 space-y-8 custom-scrollbar pt-6">
        
        <!-- Toggle Sections (Mode, Contrast, RTL, Compact) -->
        <div class="grid grid-cols-2 gap-4">
          <!-- Mode Toggle -->
          <button  @click="layout.toggleDarkMode" class="p-4 rounded-2xl border border-gray-500/10 bg-gray-500/5 text-left transition-all hover:bg-gray-500/10 relative">
            <div class="flex justify-between items-center mb-4">
               <Moon :size="22" class="text-gray-400" />
               <!-- Switch -->
               <div :class="['w-9 h-5 rounded-full p-0.5 transition-colors', layout.isDarkMode ? 'bg-primary' : 'bg-gray-400']">
                  <div :class="['w-4 h-4 rounded-full bg-white transition-transform', layout.isDarkMode ? 'translate-x-4' : '']"></div>
               </div>
            </div>
            <span class="text-[13px] font-bold opacity-80">Mode</span>
          </button>

          <!-- Contrast Toggle -->
          <button  class="p-4 rounded-2xl border border-gray-500/10 bg-gray-500/5 text-left opacity-50 relative">
            <div class="flex justify-between items-center mb-4">
               <Contrast :size="22" class="text-gray-400" />
               <div class="w-9 h-5 rounded-full p-0.5 bg-gray-400">
                  <div class="w-4 h-4 rounded-full bg-white"></div>
               </div>
            </div>
            <span class="text-[13px] font-bold opacity-80">Contrast</span>
          </button>

          <!-- RTL Toggle -->
          <button  class="p-4 rounded-2xl border border-gray-500/10 bg-gray-500/5 text-left opacity-50 relative">
            <div class="flex justify-between items-center mb-4">
               <AlignRight :size="22" class="text-gray-400" />
               <div class="w-9 h-5 rounded-full p-0.5 bg-gray-400">
                  <div class="w-4 h-4 rounded-full bg-white"></div>
               </div>
            </div>
            <span class="text-[13px] font-bold opacity-80">Right to left</span>
          </button>

          <!-- Compact Toggle -->
          <button  @click="layout.toggleSidebar" class="p-4 rounded-2xl border border-gray-500/10 bg-gray-500/5 text-left relative">
            <div class="flex justify-between items-center mb-4">
               <Maximize2 :size="22" class="text-gray-400" />
               <div :class="['w-9 h-5 rounded-full p-0.5 transition-colors', layout.isSidebarCollapsed ? 'bg-primary' : 'bg-gray-400']">
                  <div :class="['w-4 h-4 rounded-full bg-white transition-transform', layout.isSidebarCollapsed ? 'translate-x-4' : '']"></div>
               </div>
            </div>
            <div class="flex items-center gap-1">
               <span class="text-[13px] font-bold opacity-80">Compact</span>
               <Info :size="12" class="text-gray-500" />
            </div>
          </button>
        </div>

        <!-- Nav Section -->
        <div class="space-y-4">
           <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-500/10 w-fit rounded-full">
              <span class="text-[11px] font-bold uppercase tracking-wider">Nav</span>
              <Info :size="12" class="text-gray-400" />
           </div>
           
           <div class="border border-gray-500/10 rounded-2xl p-6 space-y-6">
              <div>
                 <h4 class="text-[11px] font-bold text-gray-500 mb-4 uppercase tracking-wider">Layout</h4>
                 <div class="grid grid-cols-3 gap-3">
                    <!-- Standard (Vertical) -->
                    <div @click="layout.setSidebarCollapsed(false)"
                         class="aspect-[4/3] rounded-lg p-1 flex gap-1 cursor-pointer transition-all"
                         :class="!layout.isSidebarCollapsed ? 'border-2 border-primary bg-primary/5' : 'border border-gray-500/10 bg-gray-500/5 opacity-40 hover:opacity-100'">
                       <div class="w-1/3 h-full rounded-sm opacity-60" :class="!layout.isSidebarCollapsed ? 'bg-primary' : 'bg-gray-500/20'"></div>
                       <div class="flex-1 flex flex-col gap-1">
                          <div class="h-1.5 w-full rounded-sm" :class="!layout.isSidebarCollapsed ? 'bg-primary/40' : 'bg-gray-500/20'"></div>
                          <div class="flex-1 rounded-sm" :class="!layout.isSidebarCollapsed ? 'bg-primary/20' : 'bg-gray-500/10'"></div>
                       </div>
                    </div>
                    <!-- Mini -->
                    <div @click="layout.setSidebarCollapsed(true)"
                         class="aspect-[4/3] rounded-lg p-1 flex gap-1 cursor-pointer transition-all"
                         :class="layout.isSidebarCollapsed ? 'border-2 border-primary bg-primary/5' : 'border border-gray-500/10 bg-gray-500/5 opacity-40 hover:opacity-100'">
                       <div class="w-1.5 h-full rounded-sm" :class="layout.isSidebarCollapsed ? 'bg-primary' : 'bg-gray-500/20'"></div>
                       <div class="flex-1 flex flex-col gap-1">
                          <div class="h-1.5 w-full rounded-sm" :class="layout.isSidebarCollapsed ? 'bg-primary/40' : 'bg-gray-500/20'"></div>
                          <div class="flex-1 rounded-sm" :class="layout.isSidebarCollapsed ? 'bg-primary/20' : 'bg-gray-500/10'"></div>
                       </div>
                    </div>
                    <!-- Top (Not Implemented Yet) -->
                    <div class="aspect-[4/3] rounded-lg border border-gray-500/10 bg-gray-500/5 p-1 flex flex-col gap-1 cursor-not-allowed opacity-20" title="Coming soon">
                       <div class="h-1.5 w-full bg-gray-500/20 rounded-sm"></div>
                       <div class="flex-1 bg-gray-500/10 rounded-sm"></div>
                    </div>
                 </div>
              </div>

              <div>
                 <h4 class="text-[11px] font-bold text-gray-500 mb-4 uppercase tracking-wider">Color</h4>
                 <div class="grid grid-cols-2 gap-4">
                    <div  class="p-3 rounded-xl border-2 border-primary bg-primary/5 flex items-center gap-3 cursor-pointer">
                       <Layout :size="20" class="text-primary" />
                       <span class="text-[13px] font-bold text-primary">Integrate</span>
                    </div>
                    <div  class="p-3 rounded-xl border border-gray-500/10 bg-gray-500/5 flex items-center gap-3 cursor-pointer opacity-50">
                       <Layout :size="20" class="text-gray-400" />
                       <span class="text-[13px] font-bold text-gray-400">Apparent</span>
                    </div>
                 </div>
              </div>
           </div>
        </div>

        <!-- Presets Section -->
        <div class="space-y-4">
           <div class="px-3 py-1 bg-gray-500/10 w-fit rounded-full text-[11px] font-bold uppercase tracking-wider">
              Presets
           </div>
           <div class="border border-gray-500/10 rounded-2xl p-6 grid grid-cols-3 gap-5">
              <div v-for="color in presets" :key="color.hex" 
                @click="layout.setThemeColor(color.hex)"
                class="aspect-square rounded-xl flex items-center justify-center cursor-pointer transition-all hover:scale-105"
                :class="layout.themeColor === color.hex ? 'bg-primary/10 border-2 border-primary shadow-lg shadow-primary/20' : 'bg-gray-500/5 border border-transparent'"
              >
                <div class="w-8 h-8 flex items-center justify-center" :style="{ color: color.hex }">
                   <!-- Custom columns icon from your snippet -->
                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12 21H9V3H12V21Z" fill="currentColor" opacity="0.4"></path>
                      <path d="M22 11V13C22 16.771 22 18.657 20.828 19.828C19.657 21 17.771 21 14 21V3C17.771 3 19.657 3 20.828 4.172C22 5.343 22 7.229 22 11Z" fill="currentColor"></path>
                      <path d="M2 13V11C2 7.229 2 5.343 3.172 4.172C4.146 3.191 6.364 3.027 9 3V21C6.364 20.988 4.146 20.797 3.172 19.822C2 18.651 2 16.771 2 13Z" fill="currentColor"></path>
                   </svg>
                </div>
              </div>
           </div>
        </div>

        <!-- Font Section -->
        <div class="space-y-4">
           <div class="px-3 py-1 bg-gray-500/10 w-fit rounded-full text-[11px] font-bold uppercase tracking-wider">
              Font
           </div>
           <div class="border border-gray-500/10 rounded-2xl p-6 space-y-8">
              <div>
                 <h4 class="text-[11px] font-bold text-gray-500 mb-4 uppercase tracking-wider">Family</h4>
                 <div class="grid grid-cols-2 gap-4">
                    <div v-for="font in fonts" :key="font" 
                      @click="layout.setFontFamily(font)"
                      class="p-4 rounded-2xl border flex flex-col items-center gap-2 cursor-pointer transition-all"
                      :class="layout.fontFamily === font ? 'border-primary bg-primary/5' : 'border-gray-500/10 bg-gray-500/5 opacity-50 hover:opacity-100'"
                    >
                       <span class="text-2xl font-bold" :class="layout.fontFamily === font ? 'text-primary' : 'text-gray-400'">Aa</span>
                       <span class="text-[11px] font-bold">{{ font }}</span>
                    </div>
                 </div>
              </div>

              <div>
                 <h4 class="text-[11px] font-bold text-gray-500 mb-6 uppercase tracking-wider">Size</h4>
                 <div class="relative px-2 mt-4">
                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-[10px] font-bold rounded shadow-lg whitespace-nowrap z-20">
                       {{ layout.fontSize }}px
                    </div>
                    <input 
                      type="range" 
                      min="12" 
                      max="20" 
                      step="1" 
                      :value="layout.fontSize"
                      @input="e => layout.setFontSize(e.target.value)"
                      class="w-full h-1.5 bg-gray-800/20 rounded-full appearance-none cursor-pointer outline-none accent-primary relative z-10"
                    />
                 </div>
              </div>
           </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useLayoutStore } from '@/stores/layout';
import { 
  X, RotateCcw, Maximize, Moon, Contrast, 
  AlignRight, Maximize2, Info, Layout
} from 'lucide-vue-next';

const layout = useLayoutStore();

const presets = [
  { name: 'Green', hex: '#00A76F' },
  { name: 'Blue', hex: '#078DEE' },
  { name: 'Purple', hex: '#7635DC' },
  { name: 'Cyan', hex: '#2065D1' },
  { name: 'Yellow', hex: '#FFAB00' },
  { name: 'Red', hex: '#FF5630' },
];

const fonts = ['Public Sans', 'Inter', 'DM Sans', 'Nunito Sans'];
</script>

<style scoped>
@reference "../../css/app.css";

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-gray-500/20 rounded-full;
}
</style>
