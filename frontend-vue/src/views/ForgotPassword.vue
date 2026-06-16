<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const auth = useAuthStore();
const router = useRouter();

const email = ref('');
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const handleReset = async () => {
  // Reset state
  isLoading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    await auth.requestPasswordReset(email.value);
    
    // [UPDATED] Gunakan t() untuk pesan sukses
    successMessage.value = t('forgot_password.success_message');
    email.value = ''; // Kosongkan form
  } catch (err) {
    // [UPDATED] Gunakan t() untuk pesan error default
    errorMessage.value = err.message || t('forgot_password.error_default');
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-teal-700 dark:bg-slate-950 p-4 transition-colors duration-300">
    
    <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-2xl w-full max-w-md animate-fade-in-up transition-colors duration-300">
      
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl transition-colors">
          🔒
        </div>
        <h2 class="text-2xl font-black text-gray-800 dark:text-white">{{ t('forgot_password.title') }}</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-2 leading-relaxed">
          {{ t('forgot_password.subtitle') }}
        </p>
      </div>

      <div v-if="successMessage" class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-xl mb-6 text-sm text-center border border-green-200 dark:border-green-800">
        <p class="font-bold mb-1">{{ t('forgot_password.success_title') }}</p>
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 p-4 rounded-xl mb-6 text-sm text-center border border-red-200 dark:border-red-800">
        {{ errorMessage }}
      </div>

      <form v-if="!successMessage" @submit.prevent="handleReset" class="space-y-5">
        <div>
          <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('forgot_password.email_label') }}</label>
          
          <input 
            v-model="email" 
            type="email" 
            class="w-full border border-gray-300 dark:border-slate-600 p-3 rounded-xl focus:ring-2 focus:ring-teal-400 dark:focus:ring-teal-600 focus:border-teal-400 outline-none transition bg-white dark:bg-slate-700 dark:text-white dark:placeholder-gray-400" 
            required 
            :placeholder="t('forgot_password.email_placeholder')"
          />
        </div>

        <button 
          type="submit" 
          :disabled="isLoading"
          class="w-full bg-teal-600 dark:bg-teal-500 text-white font-bold py-3 rounded-xl hover:bg-teal-700 dark:hover:bg-teal-600 transition shadow-lg shadow-teal-200 dark:shadow-none active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
        >
          <span v-if="isLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          {{ isLoading ? t('forgot_password.btn_sending') : t('forgot_password.btn_submit') }}
        </button>
      </form>

      <div class="mt-8 text-center">
        <router-link to="/login" class="text-gray-500 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 font-bold text-sm transition flex items-center justify-center gap-2">
          <span>←</span> {{ t('forgot_password.back_to_login') }}
        </router-link>
      </div>

    </div>
  </div>
</template>

<style scoped>
@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
  animation: fade-in-up 0.4s ease-out;
}
</style>