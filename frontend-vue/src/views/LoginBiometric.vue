<script setup>
import { startRegistration, startAuthentication } from '@simplewebauthn/browser';
import axios from 'axios';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
// const toast = inject('toast'); // Opsional: Jika ingin pakai toast

// 1. REGISTRASI (Daftarkan sidik jari baru)
const handleRegister = async () => {
  try {
    // Ambil opsi registrasi dari server
    const { data: options } = await axios.get('/api/auth/generate-registration-options');

    // Panggil dialog biometrik browser
    const attResp = await startRegistration(options);

    // Kirim hasil ke server untuk disimpan
    await axios.post('/api/auth/verify-registration', attResp);
    
    // [UPDATED] Gunakan t()
    alert(t('biometric.success_register')); 
  } catch (error) {
    console.error(error);
  }
};

// 2. LOGIN (Autentikasi biometrik)
const handleLogin = async () => {
  try {
    // Ambil opsi login dari server
    const { data: options } = await axios.get('/api/auth/generate-authentication-options');

    // Panggil dialog biometrik (FaceID/Fingerprint)
    const asseResp = await startAuthentication(options);

    // Verifikasi hasil di server
    const { data: result } = await axios.post('/api/auth/verify-authentication', asseResp);
    
    if (result.success) {
      // [UPDATED] Gunakan t()
      alert(t('biometric.success_login'));
      // Redirect ke dashboard
    }
  } catch (error) {
    // [UPDATED] Gunakan t()
    alert(t('biometric.error_auth'));
    console.error(error);
  }
};
</script>

<template>
  <div class="flex flex-col items-center gap-4 p-8 bg-[#262421] rounded-xl border border-white/10">
    <h2 class="text-white text-xl font-bold">{{ t('biometric.title') }}</h2>
    
    <div class="text-6xl my-4">👤</div>

    <button @click="handleLogin" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg flex items-center justify-center gap-2">
      <span>👆</span> {{ t('biometric.btn_login') }}
    </button>

    <button @click="handleRegister" class="text-sm text-gray-400 hover:text-white underline">
      {{ t('biometric.btn_register') }}
    </button>
  </div>
</template>