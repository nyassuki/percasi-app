<script setup>
import { ref, reactive, onMounted, inject, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const toast = inject('toast');
const router = useRouter();
const auth = useAuthStore();

// Data States
const paymentMethods = ref([]);
const cryptoPaymentMethods = ref([]);
const myBankAccounts = ref([]); 
const loading = ref(true);
const currentTime = ref('');

// State Modals
const showWithdrawModal = ref(false);
const showCryptoTopupModal = ref(false);
const showVAModal = ref(false); // Modal untuk daftar VA
const withdrawLoading = ref(false);
const cryptoTopupLoading = ref(false);
const swal = inject('swal');

const withdrawForm = reactive({
  amount: '',
  bankAccountId: '',
  pin: ''
});

const cryptoForm = reactive({
  amount: '',
  currency: 'USDT',
  walletAddress: '',
  network: 'TRC20'
});

// Format Rupiah
const toIDR = (num) => {
  return new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
  }).format(num || 0);
};

// Format USD
const toUSD = (num) => {
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD', 
    minimumFractionDigits: 2 
  }).format(num || 0);
};

// Format date/time dinamis
const formatDate = () => {
  const now = new Date();
  const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
  const options = { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  currentTime.value = now.toLocaleDateString(currentLocale, options);
};

// Crypto options (Labels hardcoded as requested/standard names)
const cryptoOptions = computed(() => [
  { value: 'USDT', label: 'Tether (USDT)', icon: '💵' },
  { value: 'BTC', label: 'Bitcoin (BTC)', icon: '₿' },
  { value: 'ETH', label: 'Ethereum (ETH)', icon: 'Ξ' },
  { value: 'BNB', label: 'BNB (BNB)', icon: '⛓️' }
]);

const networkOptions = computed(() => [
  { value: 'TRC20', label: 'TRC20', fee: '0%' },
  { value: 'ERC20', label: 'ERC20', fee: 'Gas fee' },
  { value: 'BEP20', label: 'BEP20', fee: 'Low fee' }
]);

// Load Data
const loadWalletData = async () => {
  loading.value = true;
  try {
    const [resMethods] = await Promise.all([
      api.get('/payment/my-methods'),
    ]);

    if (resMethods.data.status === 'success') {
      paymentMethods.value = resMethods.data.data;
    }

    try {
      const resCrypto = await api.get('/payment/crypto-methods');
      if (resCrypto.data.status === 'success') {
        cryptoPaymentMethods.value = resCrypto.data.data;
      }
    } catch (cryptoErr) {
      console.log('Crypto API not available');
    }

    await auth.fetchProfile();

  } catch (err) {
    console.error("Gagal load wallet:", err);
    toast.fire({ 
      icon: 'error', 
      title: t('wallet.toast.load_fail')
    });
  } finally {
    loading.value = false;
  }
};

// Fetch Bank Account List
const fetchUserBankAccount = async () => {
  try {
    const res = await api.get('/finance/bank-accounts');
    const data = res.data.data;

    if (Array.isArray(data)) {
      myBankAccounts.value = data;
    } else if (data) {
      myBankAccounts.value = [data];
    } else {
      myBankAccounts.value = [];
    }
  } catch (e) {
    console.error("Gagal load bank accounts", e);
    myBankAccounts.value = [];
  }
};

// Open Modal Withdraw
const openWithdraw = () => {
  withdrawForm.amount = '';
  withdrawForm.bankAccountId = '';
  withdrawForm.pin = '';
  if (auth.user?.wallet_status === 'frozen') {
        swal.fire({
            title: t('wallet.toast.error_title') || 'Frozen',
            text: t('wallet.toast.frozen_desc') || 'Your wallet is temporarily frozen.',
            icon: 'error',
            confirmButtonColor: '#3b82f6',
        });
        return;
  }
  if (myBankAccounts.value.length === 0) {
    toast.fire({ 
      icon: 'info', 
      title: t('wallet.toast.no_bank'),
      text: t('wallet.toast.no_bank_desc'),
      showCancelButton: true,
      confirmButtonText: 'Tambah Bank'
    }).then((result) => {
      if (result.isConfirmed) {
        router.push('/profile/bank-accounts');
      }
    });
    return;
  }

  showWithdrawModal.value = true;
};

// Open Crypto Topup Modal
const openCryptoTopup = () => {
  cryptoForm.amount = '';
  cryptoForm.currency = 'USDT';
  cryptoForm.walletAddress = '';
  cryptoForm.network = 'TRC20';
  if (auth.user?.wallet_status === 'frozen') {
        swal.fire({
            title: t('wallet.toast.error_title') || 'Frozen',
            text: t('wallet.toast.frozen_desc') || 'Your wallet is temporarily frozen.',
            icon: 'error',
            confirmButtonColor: '#3b82f6',
        });
        return;
  }
  showCryptoTopupModal.value = true;
};

// Open VA Modal
const openVAModal = () => {
  if (auth.user?.wallet_status === 'frozen') {
          swal.fire({
              title: t('wallet.toast.error_title') || 'Frozen',
              text: t('wallet.toast.frozen_desc') || 'Your wallet is temporarily frozen.',
              icon: 'error',
              confirmButtonColor: '#3b82f6',
          });
          return;
    }
  showVAModal.value = true;
};

// Generate Crypto Wallet Address
const generateCryptoWallet = async () => {
  try {
    const res = await api.post('/payment/generate-crypto-wallet', {
      currency: cryptoForm.currency,
      network: cryptoForm.network
    });
    
    if (res.data.status === 'success') {
      cryptoForm.walletAddress = res.data.data.wallet_address;
      toast.fire({
        icon: 'success',
        title: t('wallet.toast.wallet_generated')
      });
    }
  } catch (error) {
    console.error('Failed to generate crypto wallet:', error);
    toast.fire({
      icon: 'error',
      title: t('wallet.toast.wallet_fail')
    });
  }
};

// Handle Crypto Topup
const handleCryptoTopup = async () => {

  if (!cryptoForm.amount || parseFloat(cryptoForm.amount) <= 0) {
    toast.fire({ icon: 'warning', title: t('wallet.toast.invalid_amount') });
    return;
  }

  if (!cryptoForm.walletAddress) {
    await generateCryptoWallet();
    if (!cryptoForm.walletAddress) return;
  }

  cryptoTopupLoading.value = true;
  try {
    const res = await api.post('/payment/crypto-topup', {
      amount: parseFloat(cryptoForm.amount),
      currency: cryptoForm.currency,
      network: cryptoForm.network,
      wallet_address: cryptoForm.walletAddress
    });

    if (res.data.status === 'success') {
      toast.fire({
        icon: 'success',
        title: t('wallet.toast.topup_initiated'),
        text: t('wallet.toast.topup_instruction')
      });
      
      router.push({
        path: '/payment/crypto-deposit',
        query: {
          address: cryptoForm.walletAddress,
          amount: cryptoForm.amount,
          currency: cryptoForm.currency,
          network: cryptoForm.network
        }
      });
      
      showCryptoTopupModal.value = false;
    }
  } catch (error) {
    console.error('Crypto topup failed:', error);
    toast.fire({
      icon: 'error',
      title: t('wallet.toast.topup_failed'),
      text: error.response?.data?.message || t('wallet.toast.generic_error')
    });
  } finally {
    cryptoTopupLoading.value = false;
  }
};

// Submit Withdrawal
const handleWithdraw = async () => {
  if (!withdrawForm.amount || !withdrawForm.bankAccountId || !withdrawForm.pin) {
    toast.fire({ icon: 'warning', title: t('wallet.toast.incomplete') });
    return;
  }

  if (withdrawForm.pin.length !== 6) {
    toast.fire({ icon: 'warning', title: t('wallet.toast.pin_length') });
    return;
  }

  const amount = parseInt(withdrawForm.amount);
  if (amount > (auth.user?.balance || 0)) {
    toast.fire({ icon: 'error', title: t('wallet.toast.insufficient_balance') });
    return;
  }

  if (amount < 10000) {
    toast.fire({ icon: 'warning', title: t('wallet.toast.min_withdraw') });
    return;
  }

  withdrawLoading.value = true;
  try {
    await api.post('/finance/withdraw', {
      amount: amount,
      bankAccountId: withdrawForm.bankAccountId,
      pin: withdrawForm.pin
    });

    toast.fire({ 
      icon: 'success', 
      title: t('wallet.toast.success_withdraw'),
      text: t('wallet.toast.success_desc')
    });
    
    showWithdrawModal.value = false;
    loadWalletData();

  } catch (err) {
    const msg = err.response?.data?.message || t('wallet.toast.error_title');
    if (err.response?.data?.code === 'KYC_REQUIRED') {
      if(confirm(t('wallet.toast.kyc_required'))) {
        router.push('/kyc');
      }
    } else {
      toast.fire({ 
        icon: 'error', 
        title: t('wallet.toast.error_title'), 
        text: msg 
      });
    }
  } finally {
    withdrawLoading.value = false;
  }
};

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text);
  toast.fire({ 
    icon: 'success', 
    title: t('wallet.toast.copy_success'),
    text: t('wallet.toast.copy_desc')
  });
};

const goToTopUp = () => {
  if (auth.user?.wallet_status === 'frozen') {
        swal.fire({
            title: t('wallet.toast.error_title') || 'Frozen',
            text: t('wallet.toast.frozen_desc') || 'Your wallet is temporarily frozen.',
            icon: 'error',
            confirmButtonColor: '#3b82f6',
        });
        return;
  }
  router.push('/payment/qris');
};

const goToBankAccounts = () => {
  router.push('/profile/bank-accounts');
};

onMounted(() => {
  loadWalletData();
  fetchUserBankAccount();
  formatDate();
  setInterval(formatDate, 60000);
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-gray-100 dark:from-slate-950 dark:to-gray-900">
    
    <header class="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-gray-200/50 dark:border-slate-800/50 shadow-sm">
      <div class="w-full px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
          <button 
            @click="router.back()" 
            class="p-2 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition-shadow"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          
          <div class="text-center">
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ t('wallet.header.title') }}</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentTime }}</p>
          </div>
          
          <div class="w-10 flex justify-end">
            <router-link 
              to="/help/wallet" 
              class="p-2 rounded-xl bg-grey dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition-shadow"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <main class="pt-24 pb-8 px-4 sm:px-6 lg:px-8">
      <div class="w-full max-w-7xl mx-auto">
        
        <section class="mb-8">
          <div class="bg-gradient-to-br from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700 rounded-3xl p-6 shadow-xl shadow-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-emerald-100">{{ t('wallet.balance.available') }}</span>
                    <span v-if="auth.user?.wallet_status === 'frozen'" class="px-2 py-0.5 bg-red-600 text-white text-[10px] font-black rounded-md animate-pulse border border-white/20 shadow-sm flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        FROZEN
                    </span>
                </div>
                <span class="text-xs text-emerald-100/80 bg-emerald-500/20 px-3 py-1 rounded-full">
                  {{ t('wallet.balance.currency') }}
                </span>
              </div>
              
              <div class="text-4xl sm:text-5xl font-bold text-white mb-2 tracking-tight">
                {{ toIDR(auth.user?.balance).replace('Rp', '🪙') }}
              </div>
              
              <p class="text-sm text-emerald-100/80 mb-6">
                {{ t('wallet.balance.description') }}
              </p>
              
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <button 
                  @click="openWithdraw"
                  class="bg-white text-emerald-600 hover:bg-emerald-50 font-semibold py-3 px-4 rounded-xl transition-all active:scale-95 flex items-center justify-center gap-2"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  {{ t('wallet.actions.withdraw') }}
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="mb-8">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <button 
              @click="goToTopUp"
              class="quick-action-card group"
            >
              <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-600 dark:text-gray-300 text-center block">{{ t('wallet.actions.topup') }}</span>
            </button>

            <button 
              @click="openCryptoTopup"
              class="quick-action-card group"
            >
              <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-600 dark:text-gray-300 text-center block">{{ t('wallet.crypto.btn_topup') }}</span>
            </button>

            <button 
              @click="openVAModal"
              class="quick-action-card group"
            >
              <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-600 dark:text-gray-300 text-center block">{{ t('wallet.va.title') }}</span>
            </button>

            <router-link 
              to="/transactions" 
              class="quick-action-card group"
            >
              <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-600 dark:text-gray-300 text-center block">{{ t('wallet.actions.history') }}</span>
            </router-link>
          </div>
        </section>

        <section class="mb-8" v-if="cryptoPaymentMethods.length > 0">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ t('wallet.crypto.wallets_title') }}</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('wallet.crypto.wallets_subtitle') }}</p>
            </div>
            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-full text-xs font-medium text-purple-600 dark:text-purple-300">
              {{ t('wallet.crypto.wallets_count', { count: cryptoPaymentMethods.length }) }}
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="wallet in cryptoPaymentMethods" 
              :key="wallet.id"
              class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-2xl p-5 border border-purple-200 dark:border-purple-800/30"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-bold">
                    {{ wallet.currency.substring(0, 2) }}
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">{{ wallet.currency }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ wallet.network }}</p>
                  </div>
                </div>
                <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
              </div>
              
              <div class="mb-3">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ t('wallet.crypto.address_label') }}</p>
                <p class="text-xs font-mono text-gray-800 dark:text-white truncate" :title="wallet.wallet_address">
                  {{ wallet.wallet_address }}
                </p>
              </div>
              
              <div class="flex gap-2">
                <button 
                  @click="copyToClipboard(wallet.wallet_address)"
                  class="flex-1 py-2 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors font-medium text-sm flex items-center justify-center gap-1"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  {{ t('wallet.crypto.btn_copy') }}
                </button>
                <button 
                  @click="router.push(`/payment/crypto-deposit?address=${wallet.wallet_address}&currency=${wallet.currency}`)"
                  class="flex-1 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors font-medium text-sm"
                >
                  {{ t('wallet.crypto.btn_deposit') }}
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="mb-6">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-5 border border-blue-100 dark:border-blue-800/30">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 pt-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h4 class="font-semibold text-blue-800 dark:text-blue-300 text-base mb-2">{{ t('wallet.tips.title') }}</h4>
                <p class="text-sm text-blue-700/80 dark:text-blue-400/80 leading-relaxed">
                  • {{ t('wallet.tips.1') }}<br>
                  • {{ t('wallet.tips.2') }}<br>
                  • {{ t('wallet.tips.3') }}<br>
                  • {{ t('wallet.tips.4') }}
                </p>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>

    <Teleport to="body">
      <div 
        v-if="showCryptoTopupModal" 
        class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-0 md:p-4"
      >
        <div 
          class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
          @click="showCryptoTopupModal = false"
        ></div>
        
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl md:rounded-3xl shadow-2xl animate-in slide-in-from-bottom duration-300 max-h-[90vh] overflow-y-auto">
          <div class="w-12 h-1.5 bg-gray-300 dark:bg-slate-600 rounded-full mx-auto mt-4 mb-2 md:hidden"></div>
          
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ t('wallet.crypto.modal.title') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('wallet.crypto.modal.subtitle') }}</p>
              </div>
              <button 
                @click="showCryptoTopupModal = false"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
              >
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <form @submit.prevent="handleCryptoTopup" class="space-y-5">
              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('wallet.crypto.modal.amount_label') }}</label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 text-lg">$</span>
                  <input 
                    v-model.number="cryptoForm.amount"
                    type="number"
                    step="0.01"
                    min="10"
                    :placeholder="t('wallet.crypto.modal.amount_placeholder')"
                    class="w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-300 dark:border-slate-700 text-lg font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-400 outline-none transition-all"
                    required
                  />
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ t('wallet.crypto.modal.amount_help') }}
                </p>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('wallet.crypto.modal.currency_label') }}</label>
                <div class="grid grid-cols-2 gap-2">
                  <button
                    v-for="option in cryptoOptions"
                    :key="option.value"
                    type="button"
                    @click="cryptoForm.currency = option.value"
                    :class="[
                      'p-3 rounded-xl border transition-all flex items-center justify-center gap-2',
                      cryptoForm.currency === option.value
                        ? 'bg-purple-50 dark:bg-purple-900/30 border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400'
                        : 'bg-gray-50 dark:bg-slate-800 border-gray-300 dark:border-slate-700 text-gray-600 dark:text-gray-400 hover:border-purple-300'
                    ]"
                  >
                    <span class="text-lg">{{ option.icon }}</span>
                    <span class="font-medium">{{ option.label.split(' ')[0] }}</span>
                  </button>
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('wallet.crypto.modal.network_label') }}</label>
                <div class="grid grid-cols-3 gap-2">
                  <button
                    v-for="network in networkOptions"
                    :key="network.value"
                    type="button"
                    @click="cryptoForm.network = network.value"
                    :class="[
                      'p-3 rounded-xl border transition-all flex flex-col items-center',
                      cryptoForm.network === network.value
                        ? 'bg-purple-50 dark:bg-purple-900/30 border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400'
                        : 'bg-gray-50 dark:bg-slate-800 border-gray-300 dark:border-slate-700 text-gray-600 dark:text-gray-400 hover:border-purple-300'
                    ]"
                  >
                    <span class="font-medium text-sm">{{ network.label }}</span>
                    <span class="text-xs opacity-75 mt-1">{{ network.fee }}</span>
                  </button>
                </div>
              </div>

              <div class="space-y-2" v-if="cryptoForm.walletAddress">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('wallet.crypto.modal.address_label') }}</label>
                <div class="relative">
                  <input 
                    :value="cryptoForm.walletAddress"
                    type="text"
                    readonly
                    class="w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-300 dark:border-slate-700 text-sm font-mono text-gray-800 dark:text-white truncate"
                  />
                  <button 
                    type="button"
                    @click="copyToClipboard(cryptoForm.walletAddress)"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-500 hover:text-purple-600 dark:hover:text-purple-400"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ t('wallet.crypto.modal.address_help', { currency: cryptoForm.currency, network: cryptoForm.network }) }}
                </p>
              </div>

              <div class="bg-gray-50 dark:bg-slate-800 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">{{ t('wallet.crypto.modal.summary_amount') }}</span>
                  <span class="font-medium text-gray-800 dark:text-white">{{ toUSD(parseFloat(cryptoForm.amount) || 0) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">{{ t('wallet.crypto.modal.summary_currency') }}</span>
                  <span class="font-medium text-gray-800 dark:text-white">{{ cryptoForm.currency }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">{{ t('wallet.crypto.modal.summary_network') }}</span>
                  <span class="font-medium text-gray-800 dark:text-white">{{ cryptoForm.network }}</span>
                </div>
                <div class="border-t border-gray-300 dark:border-slate-700 pt-2 mt-2">
                  <div class="flex justify-between text-base font-semibold">
                    <span class="text-gray-800 dark:text-white">{{ t('wallet.crypto.modal.summary_receive') }}</span>
                    <span class="text-purple-600 dark:text-purple-400">
                      {{ toIDR(auth.user?.balance).replace('Rp', '🪙') }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="pt-2">
                <button 
                  type="submit"
                  :disabled="cryptoTopupLoading"
                  :class="[
                    'w-full py-4 rounded-xl font-semibold text-white transition-all duration-300 flex items-center justify-center gap-3',
                    cryptoTopupLoading 
                      ? 'bg-purple-500 cursor-not-allowed' 
                      : 'bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 shadow-lg hover:shadow-purple-500/25 active:scale-[0.98]'
                  ]"
                >
                  <svg 
                    v-if="cryptoTopupLoading" 
                    class="animate-spin h-5 w-5 text-white" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ cryptoTopupLoading ? t('wallet.crypto.modal.btn_processing') : (cryptoForm.walletAddress ? t('wallet.crypto.modal.btn_deposit') : t('wallet.crypto.modal.btn_generate')) }}</span>
                </button>
                
                <button 
                  type="button"
                  @click="showCryptoTopupModal = false"
                  class="w-full mt-3 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm font-medium transition-colors"
                >
                  {{ t('wallet.crypto.modal.btn_cancel') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div 
        v-if="showWithdrawModal" 
        class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-0 md:p-4"
      >
        <div 
          class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
          @click="showWithdrawModal = false"
        ></div>
        
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl md:rounded-3xl shadow-2xl animate-in slide-in-from-bottom duration-300 max-h-[90vh] overflow-y-auto">
          <div class="w-12 h-1.5 bg-gray-300 dark:bg-slate-600 rounded-full mx-auto mt-4 mb-2 md:hidden"></div>
          
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ t('wallet.modal_withdraw.title') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('wallet.modal_withdraw.subtitle') }}</p>
              </div>
              <button 
                @click="showWithdrawModal = false"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
              >
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <form @submit.prevent="handleWithdraw" class="space-y-5">
              <div class="space-y-2">
                <div class="flex justify-between items-center">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('wallet.modal_withdraw.amount_label') }}</label>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ t('wallet.modal_withdraw.balance_info', { amount: toIDR(auth.user?.balance || 0) }) }}
                  </span>
                </div>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 text-lg">Rp</span>
                  <input 
                    v-model.number="withdrawForm.amount"
                    type="number"
                    min="10000"
                    placeholder="Minimal 10.000"
                    class="w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-300 dark:border-slate-700 text-lg font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:border-emerald-400 outline-none transition-all"
                    required
                  />
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ t('wallet.modal_withdraw.min_max_info') }}
                </p>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('wallet.modal_withdraw.bank_label') }}</label>
                <select 
                  v-model="withdrawForm.bankAccountId"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-300 dark:border-slate-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:border-emerald-400 outline-none transition-all cursor-pointer"
                  required
                >
                  <option value="" disabled selected>{{ t('wallet.modal_withdraw.select_bank') }}</option>
                  <option 
                    v-for="bank in myBankAccounts" 
                    :key="bank.id" 
                    :value="bank.id"
                    class="py-2"
                  >
                    {{ bank.bank_name }} - {{ bank.account_number }}
                  </option>
                </select>
                <button 
                  type="button"
                  @click="goToBankAccounts"
                  class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline"
                >
                  {{ t('wallet.modal_withdraw.add_bank') }}
                </button>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('wallet.modal_withdraw.pin_label') }}</label>
                <input 
                  v-model="withdrawForm.pin"
                  type="password"
                  maxlength="6"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  :placeholder="t('wallet.modal_withdraw.pin_placeholder')"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-300 dark:border-slate-700 text-center text-lg tracking-widest font-mono text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:border-emerald-400 outline-none transition-all"
                  required
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                  {{ t('wallet.modal_withdraw.pin_desc') }}
                </p>
              </div>

              <div class="bg-gray-50 dark:bg-slate-800 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">{{ t('wallet.modal_withdraw.amount_label') }}</span>
                  <span class="font-medium text-gray-800 dark:text-white">Rp {{ parseInt(withdrawForm.amount || 0).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">{{ t('wallet.modal_withdraw.summary_fee') }}</span>
                  <span class="font-medium text-gray-800 dark:text-white">Rp 2.500</span>
                </div>
                <div class="border-t border-gray-300 dark:border-slate-700 pt-2 mt-2">
                  <div class="flex justify-between text-base font-semibold">
                    <span class="text-gray-800 dark:text-white">{{ t('wallet.modal_withdraw.summary_total') }}</span>
                    <span class="text-emerald-600 dark:text-emerald-400">
                      Rp {{ (parseInt(withdrawForm.amount || 0) - 2500).toLocaleString('id-ID') }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="pt-2">
                <button 
                  type="submit"
                  :disabled="withdrawLoading"
                  :class="[
                    'w-full py-4 rounded-xl font-semibold text-white transition-all duration-300 flex items-center justify-center gap-3',
                    withdrawLoading 
                      ? 'bg-emerald-500 cursor-not-allowed' 
                      : 'bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-lg hover:shadow-emerald-500/25 active:scale-[0.98]'
                  ]"
                >
                  <svg 
                    v-if="withdrawLoading" 
                    class="animate-spin h-5 w-5 text-white" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ withdrawLoading ? t('wallet.modal_withdraw.btn_processing') : t('wallet.modal_withdraw.btn_confirm') }}</span>
                </button>
                
                <button 
                  type="button"
                  @click="showWithdrawModal = false"
                  class="w-full mt-3 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm font-medium transition-colors"
                >
                  {{ t('wallet.modal_withdraw.btn_cancel') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div 
        v-if="showVAModal" 
        class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-0 md:p-4"
      >
        <div 
          class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
          @click="showVAModal = false"
        ></div>
        
        <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-t-3xl md:rounded-3xl shadow-2xl animate-in slide-in-from-bottom duration-300 max-h-[90vh] overflow-y-auto">
          <div class="w-12 h-1.5 bg-gray-300 dark:bg-slate-600 rounded-full mx-auto mt-4 mb-2 md:hidden"></div>
          
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ t('wallet.va.title') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('wallet.va.subtitle') }}</p>
              </div>
              <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 rounded-full text-xs font-medium text-emerald-600 dark:text-emerald-300">
                  {{ t('wallet.va.available_count', { count: paymentMethods.length }) }}
                </span>
                <button 
                  @click="showVAModal = false"
                  class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                >
                  <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>

            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="n in 4" :key="n" class="h-32 bg-gray-100 dark:bg-slate-800 rounded-2xl animate-pulse"></div>
            </div>

            <div v-else-if="paymentMethods.length === 0" class="text-center py-12 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ t('wallet.va.empty_title') }}</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">{{ t('wallet.va.empty_desc') }}</p>
              <button 
                @click="goToTopUp"
                class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:shadow-lg hover:shadow-emerald-500/25 transition-all duration-200 font-semibold inline-flex items-center gap-2"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ t('wallet.va.btn_create') }}
              </button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div 
                v-for="method in paymentMethods" 
                :key="method.va_number"
                class="bg-gradient-to-br from-white to-gray-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-5 border border-gray-200 dark:border-slate-700 hover:border-emerald-300 dark:hover:border-emerald-500 transition-colors hover:shadow-lg"
              >
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-900 shadow-inner flex items-center justify-center">
                      <span class="font-bold text-gray-800 dark:text-gray-300 text-sm">{{ method.bank_code }}</span>
                    </div>
                    <div>
                      <h4 class="font-bold text-gray-900 dark:text-white">{{ method.bank_name || method.bank_code }}</h4>
                      <div class="flex items-center gap-2 mt-1">
                        <span class="px-2 py-1 text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-full">
                          Active
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="w-3 h-3 bg-emerald-500 rounded-full"></div>
                </div>
                
                <div class="mb-4">
                  <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ t('wallet.va.va_number') }}</p>
                  <p class="text-lg font-mono font-bold text-gray-900 dark:text-white tracking-wider">
                    {{ method.va_number }}
                  </p>
                </div>
                
                <div class="flex gap-2">
                  <button 
                    @click="copyToClipboard(method.va_number)"
                    class="flex-1 py-3 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-800 text-gray-800 dark:text-gray-200 rounded-xl hover:shadow-md transition-all duration-200 font-semibold text-sm flex items-center justify-center gap-2"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    {{ t('wallet.va.btn_copy') }}
                  </button>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4">
                <div class="flex items-start gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div>
                    <p class="text-sm text-blue-700/80 dark:text-blue-400/80">
                      • Gunakan Virtual Account untuk deposit cepat dan aman<br>
                      • Salin nomor VA dan transfer melalui bank/ewallet<br>
                      • Deposit akan masuk otomatis dalam 1-3 menit
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <button 
              @click="showVAModal = false"
              class="w-full mt-6 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm font-medium transition-colors border border-gray-200 dark:border-slate-700 rounded-xl"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
/* Quick Action Cards */
.quick-action-card {
  @apply flex flex-col items-center p-4 bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all active:scale-95 cursor-pointer;
}

/* Hide number input spinners */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}

/* Animation for modal */
@keyframes slideInFromBottom {
  from {
    opacity: 0;
    transform: translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: slideInFromBottom 0.3s ease-out;
}

/* Custom scrollbar for modal */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-slate-800;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-slate-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-400 dark:bg-slate-500;
}
</style>