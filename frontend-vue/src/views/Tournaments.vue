<template>
  <div class="min-h-screen bg-gradient-to-br from-teal-50 via-blue-50 to-emerald-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 transition-all duration-500">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-teal-200 dark:bg-teal-900 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-200 dark:bg-blue-900 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse delay-1000"></div>
    </div>

    <div class="fixed top-0 left-0 right-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-gray-200 dark:border-slate-700 shadow-lg">
      <div class="px-4 py-3">
        <div class="max-w-6xl mx-auto">
          <div class="flex items-center justify-between mb-3">
            <button @click="router.back()" class="group relative p-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-xl shadow hover:shadow-md transition-all duration-300">
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="text-sm font-semibold text-teal-700 dark:text-teal-300 hidden md:inline">{{ t('tournament_list.header.back') }}</span>
              </div>
            </button>
            <div class="text-center">
              <h1 class="text-xl font-bold bg-gradient-to-r from-teal-600 via-blue-600 to-emerald-600 dark:from-teal-300 dark:via-blue-300 dark:to-emerald-300 bg-clip-text text-transparent">
                {{ t('tournament_list.header.title') }}
              </h1>
              <p class="text-xs text-gray-600 dark:text-gray-300 hidden sm:block">{{ t('tournament_list.header.subtitle') }}</p>
            </div>
            <div class="w-12"></div>
          </div>
          
          <div class="mt-2">
            <div class="flex flex-col sm:flex-row gap-3 items-center">
              <div class="flex-1 w-full">
                <div class="relative">
                  <input v-model="searchQuery" type="search" :placeholder="t('tournament_list.search_placeholder')" 
                         class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg outline-none transition text-sm text-gray-800 dark:text-gray-200 placeholder-gray-500 dark:placeholder-gray-400">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                </div>
              </div>
              <div class="flex gap-1">
                <button v-for="tab in [{ id: 'all', label: t('tournament_list.tabs.all'), icon: '📋' }, { id: 'registration', label: t('tournament_list.tabs.open'), icon: '📝' }, { id: 'active', label: t('tournament_list.tabs.active'), icon: '⚡' }, { id: 'waiting', label: t('tournament_list.tabs.waiting'), icon: '⏳' }]" 
                        :key="tab.id" 
                        @click="activeTab = tab.id" 
                        :class="['px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-300 whitespace-nowrap', 
                                activeTab === tab.id ? 'bg-teal-600 dark:bg-teal-500 text-white shadow' : 'bg-white/60 dark:bg-slate-700/60 text-gray-700 dark:text-gray-300']">
                  {{ tab.icon }} {{ tab.label }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="pt-24 mt-20 px-4 pb-20">
      <div class="max-w-6xl mx-auto">
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="n in 6" :key="n" class="h-64 bg-white/80 dark:bg-slate-800/80 rounded-2xl animate-pulse"></div>
        </div>

        <div v-else-if="filteredTournaments.length === 0" class="text-center py-16">
          <div class="max-w-md mx-auto">
            <div class="text-6xl mb-4 opacity-50">🏆</div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-2">{{ t('tournament_list.empty.title') }}</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ t('tournament_list.empty.desc') }}</p>
            <button @click="fetchTournaments" 
                    class="mt-4 px-4 py-2 bg-teal-600 dark:bg-teal-500 text-white rounded-lg hover:bg-teal-700 dark:hover:bg-teal-600 transition">
              {{ t('tournament_list.empty.refresh') }}
            </button>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="tournament in filteredTournaments" :key="tournament.id" 
               class="group bg-white/90 dark:bg-slate-800/90 rounded-2xl shadow-lg border border-gray-200 dark:border-slate-700/30 hover:-translate-y-2 transition-all duration-500 overflow-hidden">
            <div class="h-2" :class="getStatusColor(tournament.status).bg"></div>
            <div class="p-5">
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 line-clamp-2">{{ tournament.title || 'Turnamen' }}</h3>
                  <div class="mt-2 flex gap-2">
                    <span class="text-xs px-2 py-1 rounded-full" :class="[getStatusColor(tournament.status).bg, getStatusColor(tournament.status).text]">
                      {{ getStatusText(tournament.status) }}
                    </span>
                    <span v-if="tournament.isJoined" class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-full">
                      ✓ {{ t('tournament_list.card.joined') }}
                    </span>
                  </div>
                </div>
                <div class="text-3xl text-gray-700 dark:text-gray-300">{{ getTournamentIcon(tournament.format) }}</div>
              </div>

              <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-xl">
                  <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ t('tournament_list.card.start_time') }}</div>
                  <div class="font-bold text-gray-800 dark:text-gray-200">{{ fmtDate(tournament.start_time) }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-slate-700/50 rounded-xl">
                  <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ t('tournament_list.card.format') }}</div>
                  <div class="font-bold text-gray-800 dark:text-gray-200">
                    {{ tournament.format?.replace('_', ' ') || 'Swiss' }}
                  </div>
                </div>
              </div>

              <!-- Time Remaining -->
              <div v-if="tournament.status === 'registration'" class="mb-3 p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="text-xs text-gray-600 dark:text-gray-400">{{ t('tournament_list.card.registration_ends') }}</div>
                <div class="font-bold text-blue-700 dark:text-blue-300">{{ getTimeRemaining(tournament.start_time) }}</div>
              </div>

              <div class="mb-4 p-3 bg-gray-50 dark:bg-slate-700/30 rounded-xl flex justify-between">
                <div>
                  <div class="text-xs text-gray-600 dark:text-gray-400">{{ t('tournament_list.card.entry_fee') }}</div>
                  <div :class="['font-bold', parseFloat(tournament.entry_fee) > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-green-700 dark:text-green-400']">
                     {{ toIDR(tournament.entry_fee).replace('Rp', '🪙') }}
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-xs text-gray-600 dark:text-gray-400">{{ t('tournament_list.card.prize_pool') }}</div>
                  <div class="font-bold text-amber-700 dark:text-amber-400">{{ toIDR(tournament.prize_pool) }}</div>
                </div>
              </div>

              <!-- Participant Count -->
              <div class="mb-4 flex items-center justify-between text-sm">
               
              </div>

              <div class="space-y-2">
                <button v-if="tournament.status === 'registration' && !tournament.isJoined" @click="joinTournament(tournament.id)" :disabled="joiningId === tournament.id" class="w-full py-3 bg-teal-500 text-white rounded-xl font-bold">
                {{ joiningId === tournament.id ? '...' : '🎯 ' + t('tournament_list.button.register') }}
              </button>
              <button v-else @click="router.push(`/tournaments/${tournament.id}/lobby`)" class="w-full py-3 bg-blue-500 text-white rounded-xl font-bold">
                🏠 {{ t('tournament_list.button.enter_lobby') }}
              </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, computed } from 'vue';
import api from '../services/api';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const router = useRouter();
const tournaments = ref([]);
const loading = ref(true);
const joiningId = ref(null);
const activeTab = ref('all');
const searchQuery = ref('');

const swal = inject('swal');
const toast = inject('toast');

// Safety: Pastikan num ada nilainya sebelum diformat
const toIDR = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);

const fmtDate = (dateStr) => {
  if (!dateStr) return '-';
  const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
  return new Date(dateStr).toLocaleDateString(currentLocale, { 
    weekday: 'long', 
    day: 'numeric', 
    month: 'short', 
    hour: '2-digit', 
    minute:'2-digit' 
  });
};

const getTimeRemaining = (dateStr) => {
  if (!dateStr) return '-';
  const now = new Date();
  const target = new Date(dateStr);
  const diff = target - now;
  
  if (diff <= 0) return t('tournament_list.time.ended');
  
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  
  if (days > 0) return t('tournament_list.time.days', { d: days, h: hours });
  if (hours > 0) return t('tournament_list.time.hours', { h: hours, m: minutes });
  return t('tournament_list.time.mins', { m: minutes });
};

const fetchTournaments = async () => {
  try {
    const res = await api.get('/tournaments/open');
    if (res.data.status === 'success') {
      tournaments.value = res.data.data || [];
    }
  } catch (err) {
    toast.fire({ 
      icon: 'error', 
      title: t('tournament_list.toast.load_error'),
      position: 'top-end',
      timer: 3000
    });
  } finally {
    loading.value = false;
  }
};

const joinTournament = async (id) => {
  const result = await swal.fire({
    title: t('tournament_list.confirm_modal.title'),
    text: t('tournament_list.confirm_modal.msg'),
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: t('tournament_list.confirm_modal.btn_yes'),
    cancelButtonText: t('tournament_list.confirm_modal.btn_cancel'),
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#ef4444'
  });

  if (result.isConfirmed) {
    try {
      joiningId.value = id;
      const res = await api.post(`/tournaments/${id}/join`);
      await swal.fire({
        icon: 'success',
        title: t('tournament_list.toast.join_success_title'),
        text: res.data.message || t('tournament_list.toast.join_success_msg'),
        timer: 2000,
        showConfirmButton: false
      });
      await fetchTournaments();
    } catch (err) {
      const errorData = err.response?.data;
      const errorMsg = errorData?.message || err.message || "Terjadi kesalahan sistem.";
      const errorCode = errorData?.code;
      
      let errorTitle = t('tournament_list.toast.join_failed');
      if (errorCode === 'INSUFFICIENT_BALANCE') errorTitle = t('tournament_list.toast.insufficient_balance');
      else if (errorCode === 'JADWAL_BENTROK') errorTitle = t('tournament_list.toast.schedule_conflict');

      swal.fire({
        icon: (errorCode === 'INSUFFICIENT_BALANCE' || errorCode === 'JADWAL_BENTROK') ? 'warning' : 'error',
        title: errorTitle,
        text: errorMsg,
        confirmButtonColor: '#10b981'
      });
    } finally {
      joiningId.value = null;
    }
  }
};

const filteredTournaments = computed(() => {
  let filtered = tournaments.value || [];
  
  if (activeTab.value !== 'all') {
    if (activeTab.value === 'waiting') {
      filtered = filtered.filter(item => item.status === 'waiting' && item.isJoined);
    } else {
      filtered = filtered.filter(item => item.status === activeTab.value);
    }
  } else {
    filtered = filtered.filter(item => {
      if (item.status === 'waiting' && !item.isJoined) return false;
      return true;
    });
  }
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item => 
      (item.title || '').toLowerCase().includes(query) ||
      (item.description || '').toLowerCase().includes(query) ||
      (item.format || '').toLowerCase().includes(query)
    );
  }
  return filtered;
});

const getStatusColor = (status) => {
  const colors = {
    registration: { 
      bg: 'bg-gradient-to-r from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-700', 
      text: 'text-white' 
    },
    active: { 
      bg: 'bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700', 
      text: 'text-white' 
    },
    completed: { 
      bg: 'bg-gradient-to-r from-gray-500 to-slate-600 dark:from-gray-600 dark:to-slate-700', 
      text: 'text-white' 
    },
    waiting: { 
      bg: 'bg-gradient-to-r from-amber-500 to-orange-600 dark:from-amber-600 dark:to-orange-700', 
      text: 'text-white' 
    }
  };
  return colors[status] || { bg: 'bg-gray-300 dark:bg-gray-600', text: 'text-gray-700 dark:text-gray-200' };
};

const getStatusText = (status) => {
  const texts = {
    registration: t('tournament_list.status.registration'),
    active: t('tournament_list.status.active'),
    completed: t('tournament_list.status.completed'),
    waiting: t('tournament_list.status.waiting')
  };
  return texts[status] || status;
};

const getTournamentIcon = (format) => {
  const icons = {
    swiss: '🏆',
    round_robin: '🔄',
    knockout: '🥊',
    arena: '⚔️'
  };
  return icons[format] || '🎯';
};

onMounted(fetchTournaments);
</script>

<style scoped>
@keyframes gradient-x {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.animate-gradient-x {
  background-size: 200% 200%;
  animation: gradient-x 3s ease infinite;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-in-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #0d9488, #10b981);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #0f766e, #059669);
}

.dark ::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

.dark ::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #0d9488, #10b981);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #0f766e, #059669);
}
</style>