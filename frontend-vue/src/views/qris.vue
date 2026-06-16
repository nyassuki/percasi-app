<script setup>
import { ref, inject, computed, onMounted, onUnmounted } from 'vue';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const amount = ref(50000);
const loading = ref(false);
const qrisData = ref(null);
const toast = inject('toast');
const isScrolled = ref(false);

// Nominal preset untuk pilihan cepat
const nominalPresets = [10000, 25000, 50000, 100000, 200000, 500000];

// Handle scroll untuk fixed header shadow
const handleScroll = () => {
  isScrolled.value = window.scrollY > 10;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

const generateQRIS = async () => {
  if (amount.value < 1000) {
    return toast.fire({ 
      icon: 'error', 
      title: t('topup.toast.min_amount') // [UPDATED]
    });
  }

  loading.value = true;
  qrisData.value = null;

  try {
    const response = await api.get(`/payment/qris?amount=${amount.value}`);
    
    if (response.data.status === 'success') {
      qrisData.value = response.data.data;
      toast.fire({ 
        icon: 'success', 
        title: t('topup.toast.success') // [UPDATED]
      });
      // Scroll ke atas saat QRIS dibuat
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  } catch (error) {
    const msg = error.response?.data?.message || t('topup.toast.error'); // [UPDATED]
    toast.fire({ 
      icon: 'error', 
      title: msg 
    });
  } finally {
    loading.value = false;
  }
}; 

// Format currency untuk tampilan (Tetap pakai ID format karena Rupiah)
const formattedAmount = computed(() => {
  return new Intl.NumberFormat('id-ID').format(amount.value);
});

// Reset ke halaman input
const resetPayment = () => {
  qrisData.value = null;
  // Scroll ke atas saat reset
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Close page (bisa disesuaikan dengan routing)
const closePage = () => {
  // Jika menggunakan vue-router:
   if (window.history.length > 1) {
    window.history.back();
  } else {
    // Jika tidak ada history, redirect ke home
    window.location.href = '/';
  }
};
</script>

<template>
  <div class="relative min-h-screen bg-gradient-to-b from-slate-50 to-white dark:from-gray-900 dark:to-slate-950">
    <header 
      :class="[
        'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
        isScrolled 
          ? 'bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-gray-200 dark:border-slate-800 shadow-lg'
          : 'bg-white dark:bg-slate-900 border-b border-transparent'
      ]"
    >
      <div class="w-full px-4 lg:px-6">
        <div class="flex items-center justify-between h-16 max-w-7xl mx-auto">
          <div class="flex items-center">
            <button 
              v-if="qrisData"
              @click="resetPayment"
              class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              <span class="text-sm font-medium hidden sm:inline">{{ t('topup.header.back') }}</span>
            </button>
            
            <button 
              v-else
              @click="closePage"
              class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              <span class="text-sm font-medium hidden sm:inline">{{ t('topup.header.close') }}</span>
            </button>
          </div>

          <div class="text-center flex-1 px-4">
            <h1 
              class="text-base font-bold text-gray-800 dark:text-white truncate"
              :class="qrisData ? 'text-sm' : 'text-base'"
            >
              <span v-if="!qrisData">{{ t('topup.header.title_input') }}</span>
              <span v-else>{{ t('topup.header.title_qris') }}</span>
            </h1>
            <p 
              v-if="!qrisData"
              class="text-xs text-gray-500 dark:text-gray-400 truncate hidden sm:block"
            >
              {{ t('topup.header.subtitle_input') }}
            </p>
            <p 
              v-else
              class="text-xs text-gray-500 dark:text-gray-400 truncate hidden sm:block"
            >
              {{ t('topup.header.subtitle_qris') }}
            </p>
          </div>

          <div class="flex items-center">
            <div 
              v-if="!qrisData"
              class="text-right"
            >
              <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('topup.header.amount') }}</div>
              <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                Rp {{ formattedAmount }}
              </div>
            </div>
            
            <div 
              v-else
              class="flex items-center gap-2"
            >
              <div class="hidden sm:block text-right">
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('topup.header.total') }}</div>
                <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                  Rp {{ formattedAmount }}
                </div>
              </div>
              <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-xs font-semibold rounded-full">
                QRIS
              </span>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="pt-16 pb-8">
      <div class="w-full px-4 lg:px-6">
        <div class="max-w-7xl mx-auto">
          <div class="text-center mb-8 md:mb-10" v-if="!qrisData">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800 dark:text-white mb-2">
              {{ t('topup.form.title') }}
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
              {{ t('topup.form.subtitle') }}
            </p>
          </div>

          <div v-if="!qrisData" class="grid lg:grid-cols-2 gap-6 lg:gap-8 items-start">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-gray-200 dark:border-slate-800 overflow-hidden h-fit">
              <div class="p-6 md:p-8">
                <div class="mb-6">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                    {{ t('topup.form.select_label') }}
                  </label>
                  
                  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 gap-3 mb-6">
                    <button
                      v-for="preset in nominalPresets"
                      :key="preset"
                      @click="amount = preset"
                      :class="[
                        'py-3 px-4 rounded-xl text-sm font-medium transition-all duration-200',
                        amount === preset 
                          ? 'bg-emerald-100 dark:bg-emerald-900/30 border-2 border-emerald-500 dark:border-emerald-400 text-emerald-700 dark:text-emerald-300'
                          : 'bg-gray-100 dark:bg-slate-800 border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700'
                      ]"
                    >
                      Rp {{ new Intl.NumberFormat('id-ID').format(preset) }}
                    </button>
                  </div>

                  <div class="relative mb-8">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                      {{ t('topup.form.custom_label') }}
                    </label>
                    <div class="relative">
                      <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 font-bold">
                        Rp
                      </span>
                      <input 
                        v-model.number="amount" 
                        type="number" 
                        min="1000"
                        step="1000"
                        :placeholder="t('topup.form.min_max_help')"
                        class="w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-slate-800 rounded-xl border-2 border-gray-300 dark:border-slate-700 text-lg font-bold text-gray-800 dark:text-white focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all"
                      />
                      <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm">
                        IDR
                      </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                      {{ t('topup.form.min_max_help') }}
                    </p>
                  </div>
                </div>

                <button 
                  @click="generateQRIS"
                  :disabled="loading"
                  :class="[
                    'w-full py-4 rounded-xl font-semibold text-white transition-all duration-300 flex items-center justify-center gap-3',
                    loading 
                      ? 'bg-emerald-500 cursor-not-allowed' 
                      : 'bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-lg hover:shadow-emerald-500/25 active:scale-[0.98]'
                  ]"
                >
                  <svg 
                    v-if="loading" 
                    class="animate-spin h-5 w-5 text-white" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ loading ? t('topup.form.btn_process') : t('topup.form.btn_submit') }}</span>
                </button>
              </div>
            </div>

            <div class="space-y-6">
              <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-gray-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 md:p-8">
                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ t('market.info.secure_title') }}
                  </h3>
                  <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      <span>Transaksi dilindungi dengan enkripsi SSL 256-bit</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      <span>Data pribadi Anda aman dan tidak dibagikan</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      <span>Sistem pembayaran tersertifikasi PCI DSS</span>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-gray-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 md:p-8">
                  <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Proses Cepat
                  </h3>
                  <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      <span>Pembayaran diproses dalam hitungan detik</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      <span>Saldo langsung masuk ke akun Anda</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      <span>Tidak ada biaya tambahan atau hidden cost</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-gray-200 dark:border-slate-800 overflow-hidden">
            <div class="p-6 md:p-8">
              <div class="text-center max-w-4xl mx-auto">
                <div class="mb-8 p-5 bg-gray-50 dark:bg-slate-800 rounded-xl">
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    {{ t('topup.qris.total_due') }}
                  </p>
                  <div class="flex items-center justify-center gap-3">
                    <span class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white">
                      Rp {{ formattedAmount }}
                    </span>
                    <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-semibold rounded-md">
                      QRIS
                    </span>
                  </div>
                  <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('topup.qris.trx_id') }}: <span class="font-mono font-semibold">{{ qrisData?.transaction_id || 'N/A' }}</span>
                  </p>
                  <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                    {{ t('topup.qris.expiry') }}
                  </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-start">
                  <div class="relative">
                    <div class="bg-white p-6 md:p-8 rounded-2xl border-2 border-gray-200 dark:border-slate-700 inline-block shadow-xl">
                      <div class="relative">
                        <img 
                          v-if="qrisData?.actions"
                          :src="qrisData.actions.find(a => a.action === 'DOWNLOAD_QR_CODE')?.url" 
                          alt="QR Code Pembayaran"
                          class="w-64 h-64 md:w-80 md:h-80 object-contain mx-auto"
                        />
                        
                        <div v-else class="w-64 h-64 md:w-80 md:h-80 flex items-center justify-center bg-gray-100 dark:bg-slate-800 rounded-lg">
                          <p class="text-gray-500 dark:text-gray-400 text-sm">{{ t('topup.qris.not_available') }}</p>
                        </div>
                        
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                          <div class="bg-white/80 dark:bg-black/60 p-3 rounded-full">
                            <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="mt-6 flex justify-center">
                      <a 
                        v-if="qrisData?.actions"
                        :href="qrisData.actions.find(a => a.action === 'DOWNLOAD_QR_CODE')?.url"
                        download="qris-payment.png"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ t('topup.qris.download') }}
                      </a>
                    </div>
                  </div>

                  <div class="text-left">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">
                      {{ t('topup.steps.title') }}
                    </h3>
                    <ul class="space-y-4 text-gray-600 dark:text-gray-400">
                      <li class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-slate-800 rounded-xl">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">1</span>
                        <div>
                          <h4 class="font-semibold text-gray-800 dark:text-white mb-1">{{ t('topup.steps.1_title') }}</h4>
                          <p class="text-sm">{{ t('topup.steps.1_desc') }}</p>
                        </div>
                      </li>
                      <li class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-slate-800 rounded-xl">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">2</span>
                        <div>
                          <h4 class="font-semibold text-gray-800 dark:text-white mb-1">{{ t('topup.steps.2_title') }}</h4>
                          <p class="text-sm">{{ t('topup.steps.2_desc') }}</p>
                        </div>
                      </li>
                      <li class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-slate-800 rounded-xl">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">3</span>
                        <div>
                          <h4 class="font-semibold text-gray-800 dark:text-white mb-1">{{ t('topup.steps.3_title') }}</h4>
                          <p class="text-sm">{{ t('topup.steps.3_desc') }}</p>
                        </div>
                      </li>
                      <li class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-slate-800 rounded-xl">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">4</span>
                        <div>
                          <h4 class="font-semibold text-gray-800 dark:text-white mb-1">{{ t('topup.steps.4_title') }}</h4>
                          <p class="text-sm">{{ t('topup.steps.4_desc') }}</p>
                        </div>
                      </li>
                    </ul>

                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-800">
                      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ t('topup.qris.supported_by') }}
                      </p>
                      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors">
                          <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">G</span>
                          </div>
                          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">GoPay</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors">
                          <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">D</span>
                          </div>
                          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dana</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors">
                          <div class="w-8 h-8 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">O</span>
                          </div>
                          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">OVO</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors">
                          <div class="w-8 h-8 rounded-full bg-gradient-to-r from-orange-500 to-orange-600 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">SP</span>
                          </div>
                          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">ShopeePay</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="!qrisData" class="mt-8">
            <div class="bg-gradient-to-r from-emerald-500 to-blue-500 dark:from-emerald-600 dark:to-blue-600 rounded-2xl p-6 md:p-8 text-white">
              <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="flex-shrink-0">
                  <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold mb-2">{{ t('topup.banner.title') }}</h3>
                  <p class="opacity-90">
                    {{ t('topup.banner.desc') }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Hide number input spinners */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}

/* Animation for QR Code */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-in {
  animation: fadeIn 0.5s ease-out;
}

/* Smooth transitions for fixed header */
.fixed-header {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>