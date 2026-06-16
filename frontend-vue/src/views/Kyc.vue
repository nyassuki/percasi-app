<script setup>
import { ref, onUnmounted, inject, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import * as faceapi from 'face-api.js'; 
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);
const toast = inject('toast');

// --- STATE DATA ---
const ktp = ref(null);
const selfie = ref(null);
const previewKtp = ref(null);
const previewSelfie = ref(null);

// --- STATE LIVENESS ---
const showLivenessModal = ref(false); 
const isCameraOpen = ref(false);
const livenessStep = ref(0); 
const instruction = ref(""); 
const videoRef = ref(null);
const canvasRef = ref(null);
let stream = null;
let detectionInterval = null;

const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';

// --- HANDLER KTP ---
const onKtpChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    ktp.value = file;
    previewKtp.value = URL.createObjectURL(file);
  }
};

// --- HANDLER LIVENESS (POPUP) ---
const openLivenessModal = () => {
    showLivenessModal.value = true;
    if (!selfie.value) {
        startLiveness();
    }
};

const closeLivenessModal = () => {
    stopCamera();
    showLivenessModal.value = false;
};

const startLiveness = async () => {
  isCameraOpen.value = true;
  livenessStep.value = 1; 
  instruction.value = t('kyc.modal.loading_ai'); // [UPDATED]
  
  await nextTick();

  try {
    await Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL)
    ]);

    stream = await navigator.mediaDevices.getUserMedia({ 
      video: { width: 640, height: 480, facingMode: 'user' } 
    });
    
    if (videoRef.value) {
      videoRef.value.srcObject = stream;
      videoRef.value.onloadedmetadata = () => {
        videoRef.value.play();
        startDetectionLoop();
      };
    }
  } catch (err) {
    alert(t('kyc.modal.error_load')); // [UPDATED]
    stopCamera();
  }
};

// [UPDATED] Menggunakan t() untuk instruksi dinamis
const pickChallenge = () => {
  const challenges = [
    { type: 'smile', text: '😊 ' + t('kyc.challenges.smile') },
    { type: 'mouth', text: '😮 ' + t('kyc.challenges.mouth') },
    { type: 'turn_left', text: '⬅️ ' + t('kyc.challenges.turn_left') },
    { type: 'turn_right', text: '➡️ ' + t('kyc.challenges.turn_right') }
  ];
  return challenges[Math.floor(Math.random() * challenges.length)];
};

const startDetectionLoop = () => {
  livenessStep.value = 2; 
  
  const currentChallenge = pickChallenge();
  instruction.value = currentChallenge.text;

  detectionInterval = setInterval(async () => {
    if (!videoRef.value) return;

    const detection = await faceapi.detectSingleFace(
      videoRef.value, 
      new faceapi.TinyFaceDetectorOptions()
    ).withFaceLandmarks();

    if (detection) {
      const landmarks = detection.landmarks;
      let isPassed = false;

      if (currentChallenge.type === 'smile') isPassed = checkSmile(landmarks);
      else if (currentChallenge.type === 'mouth') isPassed = checkMouthOpen(landmarks);
      else if (currentChallenge.type === 'turn_left') isPassed = checkTurnHead(landmarks, 'left');
      else if (currentChallenge.type === 'turn_right') isPassed = checkTurnHead(landmarks, 'right');

      if (isPassed) {
        clearInterval(detectionInterval);
        livenessStep.value = 3;
        instruction.value = "✅ " + t('kyc.modal.verified_hold'); // [UPDATED]
        
        setTimeout(() => {
          capturePhoto();
        }, 800);
      }
    }
  }, 100);
};

const checkSmile = (landmarks) => {
  const mouth = landmarks.getMouth();
  const left = mouth[0];
  const right = mouth[6];
  const mouthWidth = Math.hypot(right.x - left.x, right.y - left.y);
  const jaw = landmarks.getJawOutline();
  const jawWidth = Math.hypot(jaw[16].x - jaw[0].x, jaw[16].y - jaw[0].y);
  return (mouthWidth / jawWidth) > 0.45; 
};

const checkMouthOpen = (landmarks) => {
  const mouth = landmarks.getMouth();
  const top = mouth[14];
  const bottom = mouth[18];
  return Math.hypot(bottom.x - top.x, bottom.y - top.y) > 20; 
};

const checkTurnHead = (landmarks, dir) => {
  const nose = landmarks.getNose()[3]; 
  const jaw = landmarks.getJawOutline();
  const left = jaw[0];
  const right = jaw[16];
  const distL = Math.abs(nose.x - left.x);
  const distR = Math.abs(nose.x - right.x);
  return dir === 'left' ? distL < (distR * 0.5) : distR < (distL * 0.5);
};

const capturePhoto = () => {
  if (!videoRef.value || !canvasRef.value) return;
  
  const video = videoRef.value;
  const canvas = canvasRef.value;
  const ctx = canvas.getContext('2d');

  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  
  ctx.translate(canvas.width, 0);
  ctx.scale(-1, 1);
  ctx.drawImage(video, 0, 0);

  canvas.toBlob((blob) => {
    const file = new File([blob], "liveness_capture.jpg", { type: "image/jpeg" });
    selfie.value = file;
    previewSelfie.value = URL.createObjectURL(blob);
    
    setTimeout(() => {
        closeLivenessModal();
        toast.fire({ icon: 'success', title: t('kyc.toast.success_face') }); // [UPDATED]
    }, 1000);
    
  }, 'image/jpeg', 0.9);
};

const stopCamera = () => {
  if (detectionInterval) clearInterval(detectionInterval);
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
  isCameraOpen.value = false;
};

onUnmounted(() => stopCamera());

const submitKyc = async () => {
  if (!ktp.value || !selfie.value) {
    toast.fire({ icon: 'warning', title: t('kyc.toast.incomplete') }); // [UPDATED]
    return;
  }
  loading.value = true;
  try {
    const formData = new FormData();
    formData.append('ktp', ktp.value);
    formData.append('selfie', selfie.value);

    await api.post('/finance/kyc', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    toast.fire({ icon: 'success', title: t('kyc.toast.success_submit') }); // [UPDATED]
    await auth.fetchProfile(); 
    router.push('/'); 
  } catch (err) {
    toast.fire({ icon: 'error', title: err.response?.data?.message || t('kyc.toast.upload_failed') }); // [UPDATED]
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-teal-700 dark:bg-slate-950 flex flex-col transition-colors duration-300">
    
    <div class="sticky top-0 z-40 bg-teal-700 dark:bg-slate-900 px-6 pt-6 pb-8 shadow-md transition-all duration-200">
      <div class="flex items-center gap-3 text-white">
        <button @click="router.back()" class="bg-white/20 p-2 rounded-full hover:bg-white/30 transition backdrop-blur-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>
        <h1 class="text-xl font-bold tracking-wide">{{ t('kyc.header.title') }}</h1>
      </div>
      <p class="text-teal-100 text-sm mt-2 ml-1 px-1">{{ t('kyc.header.subtitle') }}</p>
    </div>

    <div class="flex-1 bg-gray-50 dark:bg-slate-900 rounded-t-[35px] pt-8 px-6 pb-20 shadow-inner flex flex-col gap-8 min-h-[80vh] relative z-0 transition-colors duration-300">
      
      <div class="space-y-3">
        <div class="flex items-center justify-between">
           <label class="block font-bold text-gray-800 dark:text-white text-lg">{{ t('kyc.step1.title') }}</label>
           <span v-if="ktp" class="text-green-600 dark:text-green-400 text-xs font-bold bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded-full border border-green-200 dark:border-green-800">{{ t('kyc.step1.status_success') }}</span>
        </div>
        
        <div class="border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-2xl p-4 text-center cursor-pointer relative h-40 flex flex-col items-center justify-center overflow-hidden bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 transition group shadow-sm active:scale-[0.98]">
          <input type="file" class="absolute inset-0 opacity-0 cursor-pointer z-10" @change="onKtpChange" accept="image/*" />
          <img v-if="previewKtp" :src="previewKtp" class="absolute inset-0 w-full h-full object-cover rounded-2xl" />
          <div v-else class="text-gray-400 dark:text-gray-500 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">
            <div class="w-12 h-12 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:bg-teal-50 dark:group-hover:bg-teal-900/20">
               <span class="text-2xl">🪪</span>
            </div>
            <p class="text-sm font-medium">{{ t('kyc.step1.upload_text') }}</p>
          </div>
        </div>
      </div>

      <div class="space-y-3">
        <div class="flex items-center justify-between">
           <label class="block font-bold text-gray-800 dark:text-white text-lg">{{ t('kyc.step2.title') }}</label>
           <span v-if="selfie" class="text-green-600 dark:text-green-400 text-xs font-bold bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded-full border border-green-200 dark:border-green-800">{{ t('kyc.step2.status_success') }}</span>
        </div>

        <div @click="openLivenessModal" class="relative w-full aspect-video bg-gray-900 rounded-2xl overflow-hidden shadow-lg border-4 border-gray-800 dark:border-slate-700 cursor-pointer group hover:border-teal-500 transition-all active:scale-[0.98]">
            <img v-if="previewSelfie" :src="previewSelfie" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition" />
            
            <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-gray-800 dark:bg-slate-800 group-hover:bg-gray-700 transition">
                <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mb-3 backdrop-blur-md">
                    <span class="text-4xl">📸</span>
                </div>
                <p class="text-white font-bold text-lg">{{ t('kyc.step2.start_liveness') }}</p>
                <p class="text-gray-400 text-xs mt-1">{{ t('kyc.step2.click_camera') }}</p>
            </div>

            <div v-if="previewSelfie" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition backdrop-blur-sm">
                <span class="text-white font-bold border-2 border-white px-4 py-2 rounded-full">🔄 {{ t('kyc.step2.retake') }}</span>
            </div>
        </div>
      </div>

      <div class="mt-auto pt-4">
         <button 
          @click="submitKyc" 
          :disabled="loading || !ktp || !selfie"
          class="w-full bg-teal-600 dark:bg-teal-700 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-teal-700 dark:hover:bg-teal-600 transition disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
        >
          <span v-if="loading" class="flex items-center justify-center gap-2">
             <span class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
             {{ t('kyc.button.sending') }}
          </span>
          <span v-else>{{ t('kyc.button.submit') }}</span>
        </button>
      </div>

    </div>

    <div v-if="showLivenessModal" class="fixed inset-0 z-[100] bg-black flex flex-col items-center justify-center animate-fade-in">
        
        <button @click="closeLivenessModal" class="absolute top-6 right-6 z-50 bg-white/20 p-2 rounded-full text-white hover:bg-red-500/80 transition backdrop-blur-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>

        <div class="relative w-full h-full max-w-lg max-h-[800px] bg-black overflow-hidden flex items-center justify-center">
            
            <video ref="videoRef" class="w-full h-full object-cover transform -scale-x-100" autoplay playsinline muted></video>
            
            <canvas ref="canvasRef" class="hidden"></canvas>
            <div class="absolute top-0 left-0 w-full p-8 bg-gradient-to-b from-black/90 to-transparent text-center pt-16">
                <p v-if="livenessStep === 1" class="text-teal-400 font-bold animate-pulse text-lg">⏳ {{ t('kyc.modal.loading_ai') }}</p>
                <div v-else-if="livenessStep === 2">
                    <p class="text-gray-300 text-sm uppercase tracking-widest mb-1">{{ t('kyc.modal.instruction') }}</p>
                    <p class="text-2xl font-black text-white drop-shadow-lg leading-tight animate-bounce-slow">{{ instruction }}</p>
                </div>
                <div v-else-if="livenessStep === 3">
                    <p class="text-3xl font-bold text-white drop-shadow-md">{{ t('kyc.modal.success_title') }}</p>
                </div>
            </div>

            <div class="absolute w-[280px] h-[350px] border-2 border-white/30 rounded-[50%] pointer-events-none box-shadow-overlay"></div>
        </div>

        <div v-if="!isCameraOpen" class="absolute bottom-10">
             <button @click="startLiveness" class="bg-teal-600 text-white px-8 py-3 rounded-full font-bold shadow-lg hover:bg-teal-500 transition">
                {{ t('kyc.modal.btn_camera') }}
             </button>
        </div>
    </div>

  </div>
</template>

<style scoped>
/* Animasi Fade In Modal */
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.animate-fade-in {
    animation: fadeIn 0.2s ease-out forwards;
}

/* Shadow Overlay untuk Frame Wajah */
.box-shadow-overlay {
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.7);
}
</style>