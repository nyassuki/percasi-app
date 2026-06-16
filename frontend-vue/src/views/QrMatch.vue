<script setup>
import { ref, onMounted, onUnmounted, inject, computed } from 'vue';
import { useRouter } from 'vue-router';
import QrcodeVue from 'qrcode.vue';
import socket from '../services/socket';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const toast = inject('toast');

const qrValue = ref('');
const loading = ref(true);
const isExpired = ref(false);
const countdown = ref(60);
const maxTime = 60;
let timerInterval = null;

// Computed untuk Progress Bar
const progressWidth = computed(() => (countdown.value / maxTime) * 100);

// --- LOGIC ---
const generateQr = () => {
  loading.value = true;
  isExpired.value = false;
  countdown.value = maxTime;
  socket.emit('qr_generate');
};

const startTimer = () => {
  if (timerInterval) clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    countdown.value--;
    if (countdown.value <= 0) {
      clearInterval(timerInterval);
      isExpired.value = true;
    }
  }, 1000);
};

const refreshQr = () => generateQr();

// LIFECYCLE
onMounted(() => {
  socket.on('qr_generated', (data) => {
    qrValue.value = data.lobbyId;
    loading.value = false;
    startTimer();
  });

  socket.on('qr_error', (data) => {
    loading.value = false;
    toast?.fire({ icon: 'error', title: data.message || t('qr_game.toast.error') }); // [UPDATED]
  });

  socket.on('match_start', ({ matchId }) => {
    router.push(`/game/${matchId}`);
  });

  generateQr();
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  socket.off('qr_generated');
  socket.off('qr_error');
  socket.off('match_start');
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-slate-950 flex flex-col relative overflow-hidden">
    
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-br from-teal-500/10 via-cyan-500/5 to-transparent rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-br from-purple-500/10 via-pink-500/5 to-transparent rounded-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-r from-teal-500/5 via-cyan-500/3 to-transparent rounded-full blur-2xl"></div>
    </div>

    <header class="fixed top-0 inset-x-0 z-40 bg-gradient-to-b from-black/95 via-black/80 to-transparent backdrop-blur-xl border-b border-white/5">
      <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
        <button 
          @click="router.back()"
          class="group relative bg-white/10 hover:bg-white/20 p-3 rounded-2xl transition-all duration-300 hover:scale-105 active:scale-95"
          :aria-label="t('common.back')"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <div class="absolute -inset-1 bg-white/5 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
        </button>
        
        <div class="text-center">
          <h1 class="text-xl font-bold bg-gradient-to-r from-white via-cyan-200 to-white bg-clip-text text-transparent tracking-tight">
            {{ t('qr_game.header.title') }}
          </h1>
          <p class="text-xs text-gray-400 font-medium mt-1">
            {{ t('qr_game.header.subtitle') }}
          </p>
        </div>
        
        <div class="w-10"></div>
      </div>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center pt-20 pb-32 px-6 relative z-10">
      
      <div class="w-full max-w-md bg-gradient-to-br from-gray-900/90 to-slate-900/90 backdrop-blur-xl border border-gray-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
        
        <div class="p-8 text-center border-b border-gray-800/50">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500/10 to-cyan-500/10 border border-teal-500/20 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-white mb-2">{{ t('qr_game.card.title') }}</h2>
          <p class="text-gray-400 text-sm">{{ t('qr_game.card.desc') }}</p>
        </div>

        <div class="p-8 flex flex-col items-center">
          <div class="relative w-64 h-64 flex items-center justify-center mb-6">
            <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-gray-800/50 to-slate-900/50 border border-gray-700/50 shadow-inner"></div>
            
            <div v-if="loading" class="absolute inset-0 flex items-center justify-center">
              <div class="relative">
                <div class="w-16 h-16 border-4 border-teal-500/30 rounded-full"></div>
                <div class="absolute inset-0 w-16 h-16 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="w-8 h-8 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-full animate-pulse"></div>
                </div>
              </div>
            </div>
            
            <div v-else class="absolute inset-0 flex items-center justify-center p-4">
              <div class="relative">
                <qrcode-vue 
                  :value="qrValue" 
                  :size="240" 
                  level="H" 
                  foreground="#ffffff" 
                  background="transparent"
                  class="qr-code-element"
                />
            
                <div class="absolute inset-0 pointer-events-none">
                  <div class="absolute -inset-4 border-2 border-teal-400/20 rounded-3xl"></div>
                  <div class="absolute -inset-6 border border-teal-400/10 rounded-[2rem]"></div>
                </div>
              </div>
            </div>
            
          
            <div v-if="isExpired" class="absolute inset-0 bg-black/80 backdrop-blur-sm rounded-3xl flex items-center justify-center">
              <div class="text-center p-6">
                <div class="w-12 h-12 mx-auto mb-3 bg-gradient-to-br from-red-500/20 to-pink-500/20 rounded-full flex items-center justify-center border border-red-500/30">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                </div>
                <p class="text-white font-medium mb-3">{{ t('qr_game.status.expired_title') }}</p>
                <button 
                  @click="refreshQr"
                  class="px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 text-white rounded-lg text-sm font-medium hover:shadow-lg hover:shadow-teal-500/30 transition-all duration-300"
                >
                  {{ t('qr_game.status.btn_refresh') }}
                </button>
              </div>
            </div>
          </div>

          
          <div class="w-full max-w-xs">
              
            <div class="relative h-2 bg-gray-800 rounded-full overflow-hidden mb-3">
              <div 
                class="absolute inset-y-0 left-0 bg-gradient-to-r from-teal-500 via-cyan-500 to-teal-500 transition-all duration-1000 ease-linear"
                :style="{ width: `${progressWidth}%` }"
              ></div>
              
              <div 
                class="absolute inset-y-0 left-0 bg-gradient-to-r from-teal-500/50 via-cyan-500/50 to-teal-500/50 blur-sm transition-all duration-1000 ease-linear"
                :style="{ width: `${progressWidth}%` }"
              ></div>
            </div>

         
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></div>
                <span class="text-sm text-gray-400">{{ t('qr_game.status.time_left') }}</span>
              </div>
              <div class="font-mono font-bold text-lg text-white">
                {{ countdown.toString().padStart(2, '0') }}<span class="text-gray-400 text-sm">s</span>
              </div>
            </div>

          
            <p class="text-xs text-gray-500 text-center mt-4">
              {{ t('qr_game.status.refresh_desc', { time: countdown }) }}
            </p>
          </div>
        </div>

      
        <div class="p-6 border-t border-gray-800/50 bg-gray-900/30">
          <div class="flex items-center justify-center gap-3 text-sm text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-center">{{ t('qr_game.status.network_check') }}</span>
          </div>
        </div>
      </div>

      
      <div class="mt-8 w-full max-w-md">
        <div class="bg-gradient-to-br from-gray-900/60 to-slate-900/60 backdrop-blur-lg border border-gray-800 rounded-2xl p-5">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500/10 to-cyan-500/10 border border-teal-500/20 flex items-center justify-center">
              <span class="text-xl">📱</span>
            </div>
            <div>
              <h3 class="font-bold text-white text-sm mb-1">{{ t('qr_game.tips.title') }}</h3>
              <p class="text-gray-400 text-xs leading-relaxed">
                {{ t('qr_game.tips.desc') }}
              </p>
            </div>
          </div>
        </div>
      </div>

    </main>

    
    <div v-if="isExpired" class="fixed bottom-24 inset-x-0 z-40 px-6">
      <button 
        @click="refreshQr"
        class="w-full max-w-md mx-auto bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold py-4 rounded-2xl shadow-2xl hover:shadow-teal-500/30 transition-all duration-300 hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-3"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        {{ t('qr_game.status.btn_refresh') }}
      </button>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.qr-code-element {
  animation: fadeIn 0.5s ease-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.2);
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #2dd4bf, #22d3ee);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #0d9488, #0891b2);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Glass effect */
.glass-effect {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>