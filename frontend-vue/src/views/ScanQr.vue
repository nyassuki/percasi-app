<script setup>
import { ref, onMounted, onUnmounted, inject, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { Html5Qrcode } from 'html5-qrcode';
import socket from '../services/socket'; 
import api from '../services/api'; 
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const toast = inject('toast');

// --- STATE ---
const isScanning = ref(false);
const isProcessing = ref(false);
const errorMessage = ref('');
const isFlashOn = ref(false);
let html5QrCode = null;

const qrCodeId = 'qr-code-full-region';

// --- LOGIC PEMROSESAN DATA ---
const processScannedData = (rawValue) => {
  const data = rawValue.trim();
  console.log("Scanned Data:", data);

  // --- LOGIC SUKSES (Stop Camera) ---
  
  // 1. LOGIC GAME
  if (data.startsWith('game$$')) {
    handleGameJoin(data);
    return;
  }

  // 2. LOGIC TRANSFER
  if (data.startsWith('fncc$$')) {
    const recipient = data.replace('fncc$$', '');
    handleTransfer(data);
    return;
  }

  // 3. Fallback URL
  if (data.startsWith('http')) {
    stopScanning();
    window.open(data, '_blank');
    return;
  }

  // --- LOGIC GAGAL (Resume Camera) ---
  toast?.fire({
    icon: 'warning',
    title: t('scan_qr.toast.invalid_format'), // [UPDATED]
    text: t('scan_qr.toast.scan_valid') // [UPDATED]
  });
  
  // RESUME SCANNING SETELAH 2 DETIK
  setTimeout(() => {
    if (html5QrCode) {
        try {
            if (html5QrCode.getState() === 3) {
                html5QrCode.resume();
            }
        } catch (err) {
            console.error("Failed to resume:", err);
            startScanning();
        }
    }
  }, 2000);
};

// --- HANDLER GAME ---
const handleGameJoin = async (lobbyId) => {
  await stopScanning(); 
  isProcessing.value = true; 
  socket.emit('qr_scan', { lobbyId });
};

// --- HANDLER TRANSFER ---
const handleTransfer = async (recipient) => {
  try {
        await stopScanning();
        isProcessing.value = true;
        
        const res = await api.post('/wallet/qr/scan', { qr_string: recipient });
        
        if (res.data.status === 'success') {
            const recipient = res.data.data;
            
            router.replace({ 
                path: '/wallet/transfer/amount', 
                query: { 
                    trId: recipient, 
                    toId: recipient.id, 
                    toUsername: recipient.username, 
                    toAvatar_url: recipient.avatar_url, 
                } 
            });
        }
    } catch (err) {
        toast.fire({ icon: 'error', title: err});
        setTimeout(() => { isProcessing.value = false; startScanning(); }, 2000);
    }
    return;
};

// --- SCANNER ENGINE ---
const startScanning = async () => {
  isProcessing.value = false;
  errorMessage.value = '';
  await nextTick();

  // Clear previous instance
  if (html5QrCode) {
    try { 
      await html5QrCode.stop(); 
      await html5QrCode.clear(); 
    } catch (e) {
      console.log("Error clearing previous scanner:", e);
    }
  }

  const el = document.getElementById(qrCodeId);
  if(!el) {
    console.error("QR Code container not found");
    return;
  }

  try {
    html5QrCode = new Html5Qrcode(qrCodeId);
    
    // Try back camera first
    const cameraConfig = { facingMode: "environment" };
    
    await html5QrCode.start(
      cameraConfig, 
      { 
        fps: 15, 
        qrbox: { 
          width: Math.min(300, window.innerWidth - 100), 
          height: Math.min(300, window.innerWidth - 100) 
        }, 
        aspectRatio: 1.0,
        videoConstraints: {
          facingMode: "environment",
          width: { ideal: 1920 },
          height: { ideal: 1080 }
        }
      },
      (decodedText) => {
        html5QrCode.pause();
        processScannedData(decodedText);
      },
      (errorMessage) => {
        console.log("QR Code scan error:", errorMessage);
      }
    );
    
    isScanning.value = true;
  } catch (err) {
    console.error("Scanner error:", err);
    errorMessage.value = t('scan_qr.toast.error_access'); // [UPDATED]
    isScanning.value = false;
  }
};

const stopScanning = async () => {
  if (html5QrCode) {
    try {
      if (html5QrCode.isScanning) await html5QrCode.stop();
      await html5QrCode.clear(); 
    } catch (err) {
      console.log("Error stopping scanner:", err);
    }
    html5QrCode = null;
  }
  isScanning.value = false;
  isFlashOn.value = false;
};

const toggleFlash = async () => {
  if(!html5QrCode || !isScanning.value) return;
  
  try {
    const track = html5QrCode.getRunningTrack();
    if (track && track.getCapabilities().torch) {
      await track.applyConstraints({
        advanced: [{ torch: !isFlashOn.value }]
      });
      isFlashOn.value = !isFlashOn.value;
      
      toast?.fire({
        icon: 'success',
        title: isFlashOn.value ? t('scan_qr.toast.flash_on') : t('scan_qr.toast.flash_off'), // [UPDATED]
        timer: 1000
      });
    }
  } catch(err) {
    console.log("Flash not supported:", err);
    toast?.fire({
      icon: 'info',
      title: t('scan_qr.toast.flash_unsupported'), // [UPDATED]
      text: t('scan_qr.toast.flash_error') // [UPDATED]
    });
  }
}

// --- LIFECYCLE ---
onMounted(() => {
  // Start scanning immediately
  setTimeout(() => {
    startScanning();
  }, 300);

  // Listener Game Start
  socket.on('match_start', ({ matchId }) => {
    router.replace(`/game/${matchId}`);
  });

  socket.on('qr_error', (data) => {
    isProcessing.value = false;
    toast?.fire({ 
      icon: 'error', 
      title: data.message,
      text: 'Silakan scan ulang QR Code'
    });
    
    setTimeout(() => {
      startScanning();
    }, 1500);
  });
});

onUnmounted(async () => {
  socket.off('match_start');
  socket.off('qr_error');
  await stopScanning();
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-slate-900 flex flex-col relative overflow-hidden">
    
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute top-0 left-1/4 w-96 h-96 bg-teal-500/5 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-r from-teal-500/3 via-transparent to-cyan-500/3 rounded-full blur-2xl"></div>
    </div>

    <div v-if="isProcessing" class="fixed inset-0 z-[100] bg-gradient-to-br from-gray-900/95 via-black/95 to-slate-900/95 backdrop-blur-xl flex flex-col items-center justify-center transition-all duration-500">
      <div class="relative mb-8">
        <div class="w-32 h-32 border-4 border-gray-800 rounded-full"></div>
        <div class="absolute inset-0 w-32 h-32 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="w-16 h-16 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-full animate-pulse shadow-[0_0_40px_#2dd4bf]"></div>
        </div>
      </div>
      
      <h2 class="text-white text-3xl font-bold mb-3 bg-gradient-to-r from-teal-300 via-cyan-300 to-teal-300 bg-clip-text text-transparent animate-pulse">
        {{ t('scan_qr.loading.processing') }}
      </h2>
      <p class="text-gray-400 text-sm font-medium mb-6">{{ t('scan_qr.loading.preparing') }}</p>
      
      <div class="w-64 h-1 bg-gray-800 rounded-full overflow-hidden mb-2">
        <div class="h-full bg-gradient-to-r from-teal-500 via-cyan-500 to-teal-500 animate-progress rounded-full"></div>
      </div>
      <p class="text-gray-500 text-xs">{{ t('scan_qr.loading.wait') }}</p>
    </div>

    <div v-if="errorMessage && !isProcessing" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/95 backdrop-blur-sm">
      <div class="max-w-md w-full bg-gradient-to-br from-gray-900/90 to-slate-900/90 border border-gray-800 rounded-2xl p-8 shadow-2xl">
        <div class="relative mb-6">
          <div class="w-20 h-20 mx-auto bg-gradient-to-br from-red-500/10 to-pink-500/10 rounded-2xl flex items-center justify-center border border-red-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
        </div>
        
        <h3 class="text-white text-xl font-bold mb-3 text-center">{{ t('scan_qr.error.title') }}</h3>
        <p class="text-gray-300 text-center mb-6 leading-relaxed">{{ errorMessage }}</p>
        
        <div class="space-y-3">
          <button @click="startScanning" 
                  class="w-full bg-gradient-to-r from-teal-500 to-cyan-500 text-white px-6 py-3.5 rounded-xl font-bold hover:shadow-lg hover:shadow-teal-500/30 transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95">
            {{ t('scan_qr.error.btn_retry') }}
          </button>
          <button @click="router.back()" 
                  class="w-full bg-white/5 hover:bg-white/10 text-white px-6 py-3.5 rounded-xl font-bold border border-gray-700 transition-all duration-300">
            {{ t('scan_qr.error.btn_back') }}
          </button>
        </div>
        
        <div class="mt-6 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
          <p class="text-gray-400 text-xs text-center">{{ t('scan_qr.error.tip') }}</p>
        </div>
      </div>
    </div>

    <div class="relative z-10 flex flex-col min-h-screen">
      
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
              {{ t('scan_qr.header.title') }}
            </h1>
            <p class="text-xs text-gray-400 font-medium mt-1">
              {{ t('scan_qr.header.subtitle') }}
            </p>
          </div>
          
          <div class="w-10"></div>
        </div>
      </header>

      <main class="flex-1 flex items-center justify-center pt-20 pb-32 px-4">
        <div class="relative w-full max-w-2xl">
          
          <div class="relative aspect-square w-full max-w-md mx-auto rounded-3xl overflow-hidden shadow-2xl shadow-black/50 border border-gray-800">
            <div :id="qrCodeId" class="w-full h-full bg-black"></div>
            
            <div v-if="isScanning && !isProcessing" class="absolute inset-0 pointer-events-none">
              <div class="absolute inset-0 border-[60px] border-black/80 z-10"></div>
              
              <div class="absolute inset-0 flex items-center justify-center z-20">
                <div class="relative w-72 h-72">
                  <div class="absolute -top-2 -left-2 w-10 h-10 border-t-3 border-l-3 border-teal-400 rounded-tl-xl"></div>
                  <div class="absolute -top-2 -right-2 w-10 h-10 border-t-3 border-r-3 border-teal-400 rounded-tr-xl"></div>
                  <div class="absolute -bottom-2 -left-2 w-10 h-10 border-b-3 border-l-3 border-teal-400 rounded-bl-xl"></div>
                  <div class="absolute -bottom-2 -right-2 w-10 h-10 border-b-3 border-r-3 border-teal-400 rounded-br-xl"></div>
                  
                  <div class="absolute left-0 right-0 h-1.5 bg-gradient-to-r from-transparent via-teal-400 to-transparent shadow-[0_0_20px_#2dd4bf] animate-scan rounded-full"></div>
                  
                  <div class="absolute inset-0 border border-teal-400/20 rounded-2xl grid grid-cols-3 grid-rows-3">
                    <div class="border-r border-teal-400/10"></div>
                    <div class="border-r border-teal-400/10"></div>
                    <div></div>
                    <div class="border-r border-t border-teal-400/10"></div>
                    <div class="border-r border-t border-teal-400/10"></div>
                    <div class="border-t border-teal-400/10"></div>
                    <div class="border-r border-t border-teal-400/10"></div>
                    <div class="border-r border-t border-teal-400/10"></div>
                    <div class="border-t border-teal-400/10"></div>
                  </div>
                </div>
              </div>
              
              <div class="absolute bottom-6 left-0 right-0 text-center z-20">
                <div class="inline-flex items-center gap-3 px-5 py-3 bg-black/70 backdrop-blur-md rounded-xl border border-white/10 shadow-lg">
                  <div class="w-2.5 h-2.5 bg-teal-400 rounded-full animate-pulse"></div>
                  <p class="text-white text-sm font-medium">{{ t('scan_qr.scanner.instruction') }}</p>
                </div>
              </div>
            </div>
            
            <div v-if="!isScanning && !isProcessing && !errorMessage" class="absolute inset-0 bg-gradient-to-br from-gray-900 to-black flex items-center justify-center z-30">
              <div class="text-center p-8">
                <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-gray-800 to-slate-900 rounded-2xl flex items-center justify-center border border-gray-700 animate-pulse">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <p class="text-gray-400 font-medium mb-2">{{ t('scan_qr.loading.initializing') }}</p>
                <p class="text-gray-500 text-sm">{{ t('scan_qr.loading.wait') }}</p>
              </div>
            </div>
          </div>

          <div v-if="isScanning" class="mt-6 flex items-center justify-center gap-3">
            <div class="flex items-center gap-2">
              <div class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></div>
              <span class="text-sm text-gray-300">{{ t('scan_qr.scanner.scanning') }}</span>
            </div>
          </div>
        </div>
      </main>

      <footer class="fixed bottom-0 inset-x-0 z-40 bg-gradient-to-t from-black via-black/95 to-transparent pb-6 pt-8">
        <div class="max-w-md mx-auto px-6">
          <div class="flex justify-center">
            <button 
              @click="toggleFlash"
              :class="[
                'group relative flex flex-col items-center transition-all duration-300',
                'hover:scale-105 active:scale-95'
              ]"
              aria-label="Toggle Flash"
            >
              <div class="relative mb-2">
                <div :class="[
                  'p-4 rounded-2xl transition-all duration-300 shadow-lg',
                  isFlashOn 
                    ? 'bg-gradient-to-br from-amber-400/30 to-yellow-500/20 border border-amber-500/40' 
                    : 'bg-gradient-to-br from-white/10 to-gray-800/30 border border-white/20'
                ]">
                  <svg xmlns="http://www.w3.org/2000/svg" 
                    :class="[
                      'h-6 w-6 transition-all duration-300',
                      isFlashOn ? 'text-amber-300' : 'text-gray-300'
                    ]" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="isFlashOn ? 2 : 1.5" 
                      d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  
                  <div v-if="isFlashOn" class="absolute inset-0 bg-gradient-to-br from-amber-400/20 to-transparent rounded-2xl animate-pulse"></div>
                </div>
                
                <div v-if="isFlashOn" 
                     class="absolute -inset-2 border-2 border-amber-400/30 rounded-3xl animate-ping"></div>
                
                <div class="absolute -inset-2 bg-gradient-to-br from-amber-500/5 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
              </div>
              
              <span :class="[
                'text-xs font-medium transition-all duration-300',
                isFlashOn ? 'text-amber-300' : 'text-gray-400'
              ]">
                {{ isFlashOn ? t('scan_qr.controls.flash_on') : t('scan_qr.controls.flash_off') }}
              </span>
            </button>
          </div>

          <div class="mt-6 text-center">
            <p class="text-xs text-gray-500 px-4">
              {{ t('scan_qr.controls.help') }}
            </p>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<style scoped>
@keyframes scan {
  0% {
    top: 0%;
    opacity: 0;
    transform: translateY(-10px);
  }
  10% {
    opacity: 1;
  }
  50% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    top: 100%;
    opacity: 0;
    transform: translateY(10px);
  }
}

@keyframes progress {
  0% {
    width: 0%;
    left: 0%;
  }
  50% {
    width: 100%;
    left: 0%;
  }
  100% {
    width: 0%;
    left: 100%;
  }
}

@keyframes pulse-ring {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  50% {
    opacity: 0.5;
  }
  100% {
    transform: scale(1.2);
    opacity: 0;
  }
}

.animate-scan {
  animation: scan 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-progress {
  animation: progress 2s ease-in-out infinite;
}

.animate-ping {
  animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
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

/* Camera video styling */
:deep(#qr-code-full-region video) {
  object-fit: cover !important;
  width: 100% !important;
  height: 100% !important;
   
}

:deep(#qr-code-full-region) {
  width: 100% !important;
  height: 100% !important;
  min-height: 400px;
}

:deep(#qr-code-full-region .qr-video-feed) {
  border-radius: 24px;
}

/* Grid pattern for scanner */
.grid-pattern {
  background-image: 
    linear-gradient(to right, rgba(45, 212, 191, 0.1) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(45, 212, 191, 0.1) 1px, transparent 1px);
  background-size: 50px 50px;
}

/* Glass effect */
.glass-effect {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>