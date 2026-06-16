<script setup>
import { useLoadingStore } from '../stores/loading';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const loadingStore = useLoadingStore();
const { isLoading } = storeToRefs(loadingStore);
</script>

<template>
  <Transition name="fade">
    <div v-if="isLoading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/80 dark:bg-[#0f172a]/90 backdrop-blur-lg transition-all duration-300">
      
      <div class="flex flex-col items-center justify-center relative">
        
        <div class="relative w-32 h-32 flex items-center justify-center mb-8">
            
            <div class="absolute inset-0 rounded-full border border-gray-200 dark:border-gray-700"></div>
            
            <div class="absolute inset-0 rounded-full border-[3px] border-transparent border-t-emerald-500 border-r-emerald-500 animate-spin"></div>
            
            <div class="absolute inset-4 rounded-full border-[3px] border-gray-100 dark:border-gray-800 border-l-emerald-300 dark:border-l-emerald-700 animate-spin-reverse"></div>

            <div class="relative z-10 w-16 h-16 flex items-center justify-center animate-float">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-12 h-12 fill-emerald-600 dark:fill-emerald-400 drop-shadow-lg">
                    <path d="M256 48c15 0 27.8 10.6 30.9 24.8l20.6 92.9H352c17.7 0 32 14.3 32 32s-14.3 32-32 32h-17l18.4 82.7c1.7 7.7 15.6 20.3 23.3 20.3h16c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H119.3c-8.8 0-16-7.2-16-16v-32c0-8.8 7.2-16 16-16h16c7.7 0 21.6-12.6 23.3-20.3L177 229.7H160c-17.7 0-32-14.3-32-32s14.3-32 32-32h44.5l20.6-92.9C228.2 58.6 241 48 256 48z"/>
                </svg>
            </div>

            <div class="absolute inset-0 bg-emerald-500/10 dark:bg-emerald-500/20 blur-xl rounded-full animate-pulse"></div>
        </div>

        <div class="text-center space-y-2">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white tracking-widest uppercase animate-pulse">
                {{ t('loading.title') }}
            </h3>
            <div class="flex items-center justify-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-bounce" style="animation-delay: 0ms"></span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-bounce" style="animation-delay: 150ms"></span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-bounce" style="animation-delay: 300ms"></span>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 font-mono mt-2">{{ t('loading.subtitle') }}</p>
        </div>

      </div>
    </div>
  </Transition>
</template>

<style scoped>
/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease, backdrop-filter 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  backdrop-filter: blur(0);
}

/* Custom Animations */
@keyframes spin-reverse {
  from { transform: rotate(360deg); }
  to { transform: rotate(0deg); }
}

.animate-spin-reverse {
  animation: spin-reverse 3s linear infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}
</style>