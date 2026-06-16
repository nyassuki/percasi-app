<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const currentLang = ref('id');
const isGoogleReady = ref(false);
let trashInterval = null;

// ==========================================
// 1. LOGIC GANTI BAHASA
// ==========================================
function changeLanguage(langCode) {
  const googleSelect = document.querySelector('.goog-te-combo');
  
  if (googleSelect) {
    // Ubah nilai dropdown Google
    googleSelect.value = langCode;
    // Trigger event agar Google sadar ada perubahan
    googleSelect.dispatchEvent(new Event('change'));
    
    currentLang.value = langCode;
    console.log(`[Translate] Switched to: ${langCode}`);
  }
}

// ==========================================
// 2. INISIALISASI SCRIPT GOOGLE
// ==========================================
function initGoogleTranslate() {
  // Config Google Translate
  new window.google.translate.TranslateElement({
    pageLanguage: 'id', // Bahasa asli website
    includedLanguages: 'id,en,ja', // Bahasa tujuan
    layout: window.google.translate.TranslateElement.InlineLayout.SIMPLE,
    autoDisplay: false
  }, 'google_translate_element');

  // Mulai pantau apakah dropdown sudah dirender
  waitForDropdown();
}

// ==========================================
// 3. POLLING (MENUNGGU DROPDOWN SIAP)
// ==========================================
function waitForDropdown() {
  const interval = setInterval(() => {
    const googleSelect = document.querySelector('.goog-te-combo');
    if (googleSelect) {
      isGoogleReady.value = true;
      
      // Sinkronisasi tombol dengan state Google saat ini
      if (googleSelect.value) {
        currentLang.value = googleSelect.value;
      }
      
      console.log("✅ Google Translate Ready!");
      clearInterval(interval);
    }
  }, 500);
}

// ==========================================
// 4. PEMBERSIH SAMPAH GOOGLE (ANTI-BANNER)
// ==========================================
function cleanGoogleTrash() {
  // A. Hapus Banner Frame (Iframe jelek di atas)
  const frames = document.querySelectorAll('.goog-te-banner-frame');
  frames.forEach(frame => {
    frame.style.display = 'none';
    frame.remove();
  });

  // B. Target Iframe skiptranslate
  const skips = document.querySelectorAll('iframe.skiptranslate');
  skips.forEach(frame => {
    frame.style.display = 'none';
    frame.remove();
  });

  // C. Paksa Body naik ke atas (Google suka nambah top: 40px)
  document.body.style.top = '0px';
  document.body.style.position = 'static';
  document.body.style.marginTop = '0px';

  // D. Sembunyikan Logo Google
  const logos = document.querySelectorAll('.goog-logo-link');
  logos.forEach(el => el.style.display = 'none');
  
  const gadgets = document.querySelectorAll('.goog-te-gadget');
  gadgets.forEach(el => {
    el.style.color = 'transparent';
    el.style.fontSize = '0';
  });
}

// ==========================================
// 5. LIFECYCLE HOOKS
// ==========================================
onMounted(() => {
  // A. Definisikan Callback Global
  window.googleTranslateElementInit = initGoogleTranslate;

  // B. Cek apakah script sudah ada (mencegah double inject)
  if (!document.querySelector('script[src*="translate_a/element.js"]')) {
    const script = document.createElement('script');
    script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    script.async = true;
    document.body.appendChild(script);
  } else {
    // Jika script sudah ada (misal dari navigasi router sebelumnya), coba init manual
    if (window.google && window.google.translate) {
      initGoogleTranslate();
    }
  }

  // C. Jalankan Pembersih setiap 1 detik
  // (Karena Google suka memunculkan bar lagi setelah translate selesai)
  trashInterval = setInterval(cleanGoogleTrash, 1000);
});

onUnmounted(() => {
  if (trashInterval) clearInterval(trashInterval);
  // Hapus global callback agar bersih
  delete window.googleTranslateElementInit;
});
</script>

<template>
  <div class="custom-translate">
    <div v-if="!isGoogleReady" class="loading-state">
      Loading...
    </div>

    <div v-else class="buttons">
      <button 
        @click="changeLanguage('id')" 
        :class="{ active: currentLang === 'id' }"
        title="Indonesia"
      >
        🇮🇩 IND
      </button>

      <button 
        @click="changeLanguage('en')" 
        :class="{ active: currentLang === 'en' }"
        title="English"
      >
        🇺🇸 ENG
      </button>

      <button 
        @click="changeLanguage('ja')" 
        :class="{ active: currentLang === 'ja' }"
        title="Japanese"
      >
        🇯🇵 JPN
      </button>
    </div>
  </div>
</template>

<style scoped>
/* Styling Tombol Keren */
.custom-translate {
  display: inline-block;
}

.loading-state {
  font-size: 10px;
  color: #fbbf24; /* Yellow-400 */
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.buttons {
  display: flex;
  gap: 8px;
  background: rgba(31, 41, 55, 0.8); /* Dark Gray Transparan */
  padding: 6px;
  border-radius: 8px;
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

button {
  padding: 6px 10px;
  border: 1px solid transparent;
  background: transparent;
  color: #e5e7eb; /* Gray-200 */
  cursor: pointer;
  border-radius: 6px;
  font-size: 0.75rem; /* 12px */
  font-weight: 700;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 4px;
}

button:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

button.active {
  background-color: #2563eb; /* Blue-600 */
  color: white;
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: .5; }
}
</style>