<script setup>
// [UPDATED] Import i18n
import { useI18n } from 'vue-i18n';

const { t } = useI18n(); // [BARU] Init
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-t border-gray-100 dark:border-slate-800 pb-safe transition-colors duration-300">
    <div class="max-w-md mx-auto px-2">
      <div class="grid grid-cols-5 h-16 items-end pb-2">

        <router-link 
          to="/dashboard" 
          custom 
          v-slot="{ isActive, navigate }"
        >
          <button 
            @click="navigate"
            class="group flex flex-col items-center justify-center w-full h-full space-y-1"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 transition-colors duration-300" :class="isActive ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 group-hover:text-teal-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-300" :class="isActive ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400'">
              {{ t('bottom_nav.home') }}
            </span>
          </button>
        </router-link>

        <router-link 
          to="/wallet" 
          custom 
          v-slot="{ isActive, navigate }"
        >
          <button 
            @click="navigate"
            class="group flex flex-col items-center justify-center w-full h-full space-y-1"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 transition-colors duration-300" :class="isActive ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 group-hover:text-teal-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-300" :class="isActive ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400'">
              {{ t('bottom_nav.wallet') }}
            </span>
          </button>
        </router-link>
        
        <div class="relative flex justify-center items-end h-full">
          <router-link 
            to="/scan"
            class="absolute bottom-6 w-14 h-14 rounded-full bg-gradient-to-tr from-teal-500 to-emerald-500 text-white shadow-lg shadow-teal-500/40 dark:shadow-teal-900/60 flex items-center justify-center transform transition-transform duration-200 hover:scale-110 active:scale-95 border-4 border-white dark:border-slate-900 z-20"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h.01M16 3h5m0 0v5m0-5h-5m0 5v6m0 0h-6m6 0v6" />
            </svg>
          </router-link>
          <span class="text-[10px] font-medium text-gray-400 mb-[2px]">
            {{ t('bottom_nav.scan') }}
          </span>
        </div>

        <router-link 
          to="/market" 
          custom 
          v-slot="{ isActive, navigate }"
        >
          <button 
            @click="navigate"
            class="group flex flex-col items-center justify-center w-full h-full space-y-1"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 transition-colors duration-300" :class="isActive ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 group-hover:text-teal-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-300" :class="isActive ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400'">
              {{ t('bottom_nav.market') }}
            </span>
          </button>
        </router-link>

        <router-link 
          to="/profile" 
          custom 
          v-slot="{ isActive, navigate }"
        >
          <button 
            @click="navigate"
            class="group flex flex-col items-center justify-center w-full h-full space-y-1"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 transition-colors duration-300" :class="isActive ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 group-hover:text-teal-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-[10px] font-medium transition-colors duration-300" :class="isActive ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400'">
              {{ t('bottom_nav.profile') }}
            </span>
          </button>
        </router-link>

      </div>
    </div>
  </nav>
</template>

<style scoped>
/* Utility class untuk Safe Area di iPhone X ke atas */
.pb-safe {
  padding-bottom: env(safe-area-inset-bottom);
}
</style>