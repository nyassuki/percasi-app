<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import QrcodeVue from 'qrcode.vue';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const qrValue = ref('');
const loading = ref(true);
const timeLeft = ref(60);
let timer = null;

// Generate QR Baru
const fetchQr = async () => {
  try {
    loading.value = true;
    const res = await api.get('/wallet/qr/generate');
    qrValue.value = res.data.qr_code;
    loading.value = false;
    resetTimer();
  } catch (err) {
    console.error(err);
    loading.value = false;
  }
};

// Hitung Mundur
const resetTimer = () => {
  clearInterval(timer);
  timeLeft.value = 60;
  timer = setInterval(() => {
    timeLeft.value--;
    if(timeLeft.value <= 0) fetchQr();
  }, 1000);
};

// Copy QR to clipboard
const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
    .then(() => {
      // [UPDATED] Gunakan t()
      alert(t('receive_funds.toast.copy_success'));
    })
    .catch(err => {
      console.error('Failed to copy:', err);
      alert(t('receive_funds.toast.copy_failed'));
    });
};

onMounted(fetchQr);
onUnmounted(() => clearInterval(timer));
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-slate-900 flex flex-col relative overflow-hidden">
    
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute top-0 left-1/4 w-96 h-96 bg-teal-500/5 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-r from-teal-500/3 via-transparent to-cyan-500/3 rounded-full blur-2xl"></div>
    </div>

    <header class="fixed top-0 inset-x-0 z-40 bg-gradient-to-b from-black/95 via-black/80 to-transparent backdrop-blur-xl border-b border-white/5">
      <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
        <button 
          @click="$router.back()"
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
            {{ t('receive_funds.header.title') }}
          </h1>
          <p class="text-xs text-gray-400 font-medium mt-1">
            {{ t('receive_funds.header.subtitle') }}
          </p>
        </div>
        
        <div class="w-10"></div>
      </div>
    </header>

    <main class="fixed inset-0 flex items-center justify-center pt-20 pb-24 px-6 z-10">
      <div class="w-full max-w-md">
        <div class="bg-gradient-to-br from-gray-900/90 to-slate-900/90 backdrop-blur-xl border border-gray-800 rounded-[2.5rem] shadow-2xl overflow-hidden">
          
          <div class="p-8 text-center border-b border-gray-800/50">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500/10 to-cyan-500/10 border border-teal-500/20 mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">{{ t('receive_funds.card.title') }}</h2>
            <p class="text-gray-400 text-sm">{{ t('receive_funds.card.desc') }}</p>
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
            </div>

            <div class="w-full max-w-xs">
              
              <div class="relative h-2 bg-gray-800 rounded-full overflow-hidden mb-3">
                <div 
                  class="absolute inset-y-0 left-0 bg-gradient-to-r from-teal-500 via-cyan-500 to-teal-500 transition-all duration-1000 ease-linear"
                  :style="{ width: `${(timeLeft / 60) * 100}%` }"
                ></div>
                
                <div 
                  class="absolute inset-y-0 left-0 bg-gradient-to-r from-teal-500/50 via-cyan-500/50 to-teal-500/50 blur-sm transition-all duration-1000 ease-linear"
                  :style="{ width: `${(timeLeft / 60) * 100}%` }"
                ></div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></div>
                  <span class="text-sm text-gray-400">{{ t('receive_funds.timer.label') }}</span>
                </div>
                <div class="font-mono font-bold text-lg text-white">
                  {{ timeLeft.toString().padStart(2, '0') }}<span class="text-gray-400 text-sm">s</span>
                </div>
              </div>
              
              <p class="text-xs text-gray-500 text-center mt-4">
                {{ t('receive_funds.timer.refresh_info') }}
              </p>
            </div>
          </div>

          <div class="p-6 border-t border-gray-800/50 bg-gray-900/30">
            <div class="flex items-center justify-center gap-3 text-sm text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ t('receive_funds.instruction') }}</span>
            </div>
          </div>
        </div>
        
        <div v-if="!loading" class="mt-6 text-center">
          <button 
            @click="copyToClipboard(qrValue)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800/50 hover:bg-gray-700/50 text-gray-300 rounded-xl border border-gray-700 transition-all duration-300 text-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            {{ t('receive_funds.button.copy') }}
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Fixed positioning untuk stabil */
main {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

/* Animasi untuk QR code */
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