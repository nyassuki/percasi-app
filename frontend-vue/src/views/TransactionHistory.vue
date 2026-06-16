<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t, locale } = useI18n(); // [BARU] Init
const router = useRouter();
const transactions = ref([]);
const loading = ref(true);

// --- Filter State ---
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1);

// --- Tahun dan Bulan Options ---
const yearOptions = computed(() => {
  const currentYear = new Date().getFullYear();
  const years = [];
  for (let i = currentYear; i >= currentYear - 15; i--) {
    years.push(i);
  }
  return years;
});

// [UPDATED] Menggunakan computed agar label bulan terjemahan
const monthOptions = computed(() => [
  { value: 1, label: t('transaction_history.filter.jan') },
  { value: 2, label: t('transaction_history.filter.feb') },
  { value: 3, label: t('transaction_history.filter.mar') },
  { value: 4, label: t('transaction_history.filter.apr') },
  { value: 5, label: t('transaction_history.filter.may') },
  { value: 6, label: t('transaction_history.filter.jun') },
  { value: 7, label: t('transaction_history.filter.jul') },
  { value: 8, label: t('transaction_history.filter.aug') },
  { value: 9, label: t('transaction_history.filter.sep') },
  { value: 10, label: t('transaction_history.filter.oct') },
  { value: 11, label: t('transaction_history.filter.nov') },
  { value: 12, label: t('transaction_history.filter.dec') }
]);

// --- Computed Properties ---
const currentMonthYear = computed(() => {
  const month = monthOptions.value.find(m => m.value === selectedMonth.value);
  return month ? `${month.label} ${selectedYear.value}` : `${selectedMonth.value}/${selectedYear.value}`;
});

const latestDate = computed(() => {
  if (transactions.value.length === 0) return '-';
  const latest = transactions.value[0].created_at;
  // [UPDATED] Format tanggal dinamis
  const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
  return new Date(latest).toLocaleDateString(currentLocale, { day: 'numeric', month: 'short' });
});

// --- HELPERS ---
const formatDate = (dateString) => {
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return dateString;
  
  // [UPDATED] Format tanggal dinamis
  const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
  return date.toLocaleDateString(currentLocale, {
    day: 'numeric', 
    month: 'short', 
    year: 'numeric',
    hour: '2-digit', 
    minute: '2-digit'
  });
};

const toIDR = (num) => {
  if (!num && num !== 0) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
  }).format(num);
};

// Menentukan Icon
const getTypeIcon = (type) => {
  switch(type?.toLowerCase()) {
    case 'topup_va': 
    case 'deposit': 
      return '💳';
    
    case 'withdraw': 
      return '🏦';
    
    case 'tournament_fee': 
    case 'game_fee': 
      return '🎮';
    
    case 'prize_payout': 
    case 'prize': 
      return '🏆';
    
    case 'transfer_in': 
    case 'transfer_out':
    case 'transaksi': 
    case 'transfer': 
      return '🔄';
    
    default: 
      return '📄';
  }
};

// [UPDATED] Label Judul Transaksi Menggunakan t()
const getLabel = (type) => {
  if (!type) return t('transaction_history.transaction_type.default');
  
  const key = type.toLowerCase();
  // Cek apakah key ada di translasi, jika tidak format manual
  const translationKey = `transaction_history.transaction_type.${key}`;
  
  // Deteksi jika key ada di i18n messages (cara sederhana: bandingkan dengan key asli jika t() mengembalikan key)
  // Atau langsung pakai t() dan biarkan fallback
  return t(translationKey) !== translationKey 
    ? t(translationKey) 
    : type.replace(/_/g, ' ').toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Logika Custom Remark
const getTransactionRemark = (trx) => {
  const isTransfer = ['transfer_in', 'transfer_out', 'transaksi', 'transfer'].includes(trx.type?.toLowerCase());
  const personName = trx.lwt_fullname || '-';

  if (isTransfer) {
    if (trx.flow === 'in') {
      return t('transaction_history.remark.from', { name: personName });
    } else {
      return t('transaction_history.remark.to', { name: personName });
    }
  }

  return trx.description || getLabel(trx.type);
};

const getStatusColor = (status) => {
  if (!status) return 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-400';
  
  switch(status.toLowerCase()) {
    case 'success': 
    case 'berhasil':
      return 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400';
    case 'pending':
    case 'menunggu':
      return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400';
    case 'failed':
    case 'gagal':
      return 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400';
    default: 
      return 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-400';
  }
};

// [UPDATED] Menggunakan t()
const getStatusText = (status) => {
  if (!status) return t('transaction_history.status.unknown');
  
  switch(status.toLowerCase()) {
    case 'success': 
    case 'berhasil':
        return t('transaction_history.status.success');
    case 'pending': 
    case 'menunggu':
        return t('transaction_history.status.pending');
    case 'failed': 
    case 'gagal':
        return t('transaction_history.status.failed');
    default: return status;
  }
};

// --- API ---
const fetchTransactions = async () => {
  loading.value = true;
  try {
    const params = {
      year: selectedYear.value,
      month: selectedMonth.value
    };
    
    const res = await api.get('/wallet/history', { params });
    
    if (res.data.status === 'success') {
      transactions.value = Array.isArray(res.data.data) ? res.data.data : [];
    } else {
      transactions.value = [];
      console.warn('API response tidak sesuai:', res.data);
    }
  } catch (err) {
    console.error("Error fetching transactions:", err);
    transactions.value = [];
  } finally {
    loading.value = false;
  }
};

// Handle filter change
const handleFilterChange = () => {
  fetchTransactions();
};

// Reset to current month/year
const resetFilter = () => {
  const now = new Date();
  selectedYear.value = now.getFullYear();
  selectedMonth.value = now.getMonth() + 1;
  fetchTransactions();
};

// Watch for changes
const handleYearChange = (event) => {
  selectedYear.value = parseInt(event.target.value);
  fetchTransactions();
};

const handleMonthChange = (event) => {
  selectedMonth.value = parseInt(event.target.value);
  fetchTransactions();
};

onMounted(() => {
  fetchTransactions();
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 via-white to-gray-50/50 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900/50 flex flex-col transition-colors duration-300">
    
    <div class="sticky top-0 z-50 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 shadow-lg transition-all duration-300 text-white">
      
      <div class="px-6 pt-8 pb-2">
        <div class="flex items-center justify-between mb-2">
          <button @click="router.back()" class="nav-back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <div class="flex-1 text-center">
            <h1 class="text-xl font-bold tracking-tight">{{ t('transaction_history.header.title') }}</h1>
            <p class="text-sm text-emerald-100/90 dark:text-gray-300/80 mt-1 px-1">
              {{ currentMonthYear }}
            </p>
          </div>
          <div class="w-10"></div>
        </div>
      </div>

      <div class="px-6 pb-6">
        <div class="flex gap-3 justify-center">
          <div class="text-center px-3 py-2">
            <div class="text-xs text-emerald-100/80 dark:text-gray-300/70 mb-1">{{ t('transaction_history.header.total_transactions') }}</div>
            <div class="text-lg font-bold">{{ transactions.length }}</div>
          </div>
          <div class="h-10 w-px bg-white/20"></div>
          <div class="text-center px-3 py-2">
            <div class="text-xs text-emerald-100/80 dark:text-gray-300/70 mb-1">{{ t('transaction_history.header.latest') }}</div>
            <div class="text-sm font-medium">{{ latestDate }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="flex-1 -mt-4 rounded-t-[40px] pt-8 px-6 pb-24 bg-white dark:bg-slate-900 shadow-inner relative z-0">
      
      <div class="mb-6 space-y-4">

        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ t('transaction_history.filter.period_label') }}
          </div>
          <button 
            @click="resetFilter" 
            class="text-xs px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 
                   text-emerald-600 dark:text-emerald-400 rounded-full 
                   hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
            {{ t('transaction_history.filter.this_month') }}
          </button>
        </div>

        <div class="flex items-end gap-3">
          
          <div class="flex-1">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
              {{ t('transaction_history.filter.month') }}
            </label>
            <select 
              :value="selectedMonth" 
              @change="handleMonthChange"
              class="filter-select w-full"
            >
              <option 
                v-for="month in monthOptions" 
                :key="month.value" 
                :value="month.value"
              >
                {{ month.label }}
              </option>
            </select>
          </div>

          <div class="w-28">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
              {{ t('transaction_history.filter.year') }}
            </label>
            <select 
              :value="selectedYear" 
              @change="handleYearChange"
              class="filter-select w-full"
            >
              <option 
                v-for="year in yearOptions" 
                :key="year" 
                :value="year"
              >
                {{ year }}
              </option>
            </select>
          </div>

        </div>

      </div>


      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="transaction-card skeleton">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gray-200 dark:bg-slate-700 animate-pulse"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded animate-pulse w-3/4"></div>
              <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded animate-pulse w-1/2"></div>
              <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded animate-pulse w-1/3"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="transactions.length === 0" class="empty-state">
        <div class="empty-icon">
          <div class="relative">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center text-5xl mb-4">
              📭
            </div>
            <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500 flex items-center justify-center text-sm">
              ?
            </div>
          </div>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-2">
          {{ t('transaction_history.empty.title') }}
        </h3>
        <p class="text-gray-500 dark:text-gray-400 text-center max-w-xs">
          {{ t('transaction_history.empty.desc', { period: currentMonthYear }) }}
        </p>
        <div class="flex gap-3 mt-6">
          <button @click="resetFilter" 
                  class="px-4 py-2 bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 rounded-full text-sm font-medium hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors">
            {{ t('transaction_history.filter.this_month') }}
          </button>
          <button @click="router.push('/wallet')" 
                  class="px-6 py-2 bg-gradient-to-r from-teal-500 to-emerald-500 text-white rounded-full text-sm font-medium hover:shadow-lg transition-all duration-300">
            {{ t('transaction_history.empty.btn_topup') }}
          </button>
        </div>
      </div>

      <div v-else class="space-y-3">
        <div 
          v-for="trx in transactions" 
          :key="trx.id"
          class="transaction-card group"
          :class="{
            'border-l-4 border-l-emerald-500': trx.flow === 'in',
            'border-l-4 border-l-rose-500': trx.flow === 'out'
          }"
        >
          <div class="flex items-start gap-4">
            
            <div class="relative">
              <div class="w-12 h-12 rounded-xl transaction-icon" :class="trx.flow === 'in' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400'">
                <div class="flex items-center justify-center h-full text-xl">
                  {{ getTypeIcon(trx.type) }}
                </div>
              </div>
              <div v-if="trx.status && (trx.status.toLowerCase() === 'success' || trx.status.toLowerCase() === 'berhasil')" 
                   class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900 flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between mb-1">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm truncate">
                  {{ getLabel(trx.type) }}
                </h3>
                <div class="transaction-amount" :class="trx.flow === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                  {{ trx.flow === 'in' ? '+' : '-' }} {{ toIDR(trx.amount) }}
                </div>
              </div>

              <p class="text-xs text-gray-600 dark:text-gray-300 font-medium mb-2">
                {{ getTransactionRemark(trx) }}
              </p>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ formatDate(trx.created_at) }}
                  </div>
                  <span v-if="trx.status" :class="`text-xs px-2 py-1 rounded-full font-medium ${getStatusColor(trx.status)}`">
                    {{ getStatusText(trx.status) }}
                  </span>
                </div>
                
                <button v-if="trx.status && (trx.status.toLowerCase() === 'pending' || trx.status.toLowerCase() === 'menunggu')" 
                        class="text-xs px-3 py-1 rounded-full bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-900/50 transition-colors duration-200">
                  {{ t('transaction_history.btn_detail') }}
                </button>
              </div>

              <div v-if="trx.description && trx.description !== getTransactionRemark(trx)" 
                   class="mt-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-slate-800/50 rounded-lg px-3 py-2 border border-gray-100 dark:border-slate-700">
                {{ trx.description }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="transactions.length > 0" class="mt-8 text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
          {{ t('transaction_history.footer', { count: transactions.length, period: currentMonthYear }) }}
        </p>
      </div>

    </div>

    <div class="fixed bottom-24 right-6 z-40">
      <button @click="fetchTransactions" 
              class="floating-btn group">
        <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="h-5 w-5 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </button>
    </div>

  </div>
</template>

<style scoped>
/* Navigation Back Button */
.nav-back-btn {
  @apply w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 
         backdrop-blur-sm flex items-center justify-center 
         transition-all duration-300 active:scale-95 shadow-sm;
}

/* Transaction Card */
.transaction-card {
  @apply bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm 
         border border-gray-100 dark:border-slate-700 
         hover:shadow-md transition-all duration-300 
         hover:translate-x-1 hover:-translate-y-0.5;
}

.transaction-card:hover {
  @apply border-gray-200 dark:border-slate-600;
}

/* Transaction Icon */
.transaction-icon {
  @apply flex items-center justify-center shadow-sm 
         border border-gray-100 dark:border-slate-700 
         transition-all duration-300;
}

.group:hover .transaction-icon {
  @apply scale-110;
}

/* Transaction Amount */
.transaction-amount {
  @apply font-bold text-base whitespace-nowrap ml-2;
}

/* Filter Select */
.filter-select {
  @apply text-sm px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-700 
         bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 
         focus:outline-none focus:ring-2 focus:ring-teal-500/20 
         focus:border-teal-500 dark:focus:border-teal-500 
         transition-all duration-300 appearance-none cursor-pointer
         hover:border-gray-300 dark:hover:border-slate-600;
}

/* Empty State */
.empty-state {
  @apply flex flex-col items-center justify-center py-16 px-4 
         text-center;
}

.empty-icon {
  @apply relative mb-4;
}

/* Floating Button */
.floating-btn {
  @apply w-12 h-12 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500 
         text-white flex items-center justify-center shadow-xl 
         hover:shadow-2xl transition-all duration-300 
         active:scale-95 hover:scale-105;
}

/* Skeleton Animation */
.skeleton {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Custom Scrollbar */
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

/* Responsive adjustments */
@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}
</style>