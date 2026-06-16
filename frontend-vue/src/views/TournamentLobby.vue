<script setup>
import { ref, onMounted, inject, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t, locale } = useI18n(); // [BARU] Init
const route = useRoute();
const router = useRouter();
const tournament = ref(null);
const participants = ref([]);
const loading = ref(true);
const toast = inject('toast');
const activeTab = ref('participants'); // 'participants' or 'bracket'

/**
 * Format URL Avatar: Menangani path lokal & eksternal
 */
const formatAvatarUrl = (url) => {
  if (!url) return null;
  if (url.startsWith('http')) return url;
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:5000';
  return `/${url}`;
};

/**
 * Ambil data turnamen dan peserta dari Backend
 */
const fetchLobbyData = async () => {
  loading.value = true;
  try {
    const tournamentId = route.params.id;
    const res = await api.get(`/tournaments/${tournamentId}/lobby`);
    if (res.data.status === 'success') {
      tournament.value = res.data.data.tournament;
      participants.value = res.data.data.participants || [];
      
      // Sort participants by rating (descending)
      participants.value.sort((a, b) => (b.current_rating || 1200) - (a.current_rating || 1200));
    }
  } catch (err) {
    const msg = err.response?.data?.message || t('tournament_lobby.toast.load_failed'); // [UPDATED]
    toast.fire({ 
      icon: 'error', 
      title: t('tournament_lobby.toast.error'), // [UPDATED]
      text: msg 
    });
    router.back();
  } finally {
    loading.value = false;
  }
};

/**
 * Styling Nomor Rank (Emas, Perak, Perunggu)
 */
const getRankClass = (index) => {
  if (index === 0) return 'bg-gradient-to-br from-yellow-400 to-yellow-600 text-white';
  if (index === 1) return 'bg-gradient-to-br from-gray-300 to-gray-500 text-white';
  if (index === 2) return 'bg-gradient-to-br from-amber-700 to-amber-900 text-white';
  return 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400';
};

/**
 * Format waktu turnamen
 */
const formatTimeControl = computed(() => {
  if (!tournament.value) return 'Rapid';
  const { time_control_base, time_control_increment } = tournament.value;
  const minutes = Math.floor(time_control_base / 60);
  return `${minutes}+${time_control_increment}`;
});

/**
 * Format tanggal turnamen [UPDATED] Dinamis locale
 */
const formatTournamentDate = computed(() => {
  if (!tournament.value?.start_time) return '';
  const date = new Date(tournament.value.start_time);
  const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
  return date.toLocaleDateString(currentLocale, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
});

/**
 * Hitung waktu tersisa hingga turnamen dimulai [UPDATED] Dinamis locale
 */
const timeUntilStart = computed(() => {
  if (!tournament.value?.start_time) return '';
  const now = new Date();
  const start = new Date(tournament.value.start_time);
  const diffMs = start - now;
  
  if (diffMs <= 0) return t('tournament_lobby.status.ongoing');
  
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
  const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
  
  if (diffHours > 24) {
    const days = Math.floor(diffHours / 24);
    return t('tournament_lobby.schedule.start_in_days', { d: days });
  } else if (diffHours > 0) {
    return t('tournament_lobby.schedule.start_in_hours', { h: diffHours, m: diffMinutes });
  } else {
    return t('tournament_lobby.schedule.start_in_mins', { m: diffMinutes });
  }
});

/**
 * Kirim chat di lobby
 */
const sendChatMessage = (message) => {
  // Implementasi chat
  console.log('Sending chat:', message);
};

onMounted(() => {
  fetchLobbyData();
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-gray-100 dark:from-slate-950 dark:to-gray-900">
    
    <header class="fixed top-0 inset-x-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-gray-200/50 dark:border-slate-800/50 shadow-sm">
      <div class="max-w-lg mx-auto px-4 sm:px-6 py-4">
        <div class="flex items-center justify-between">
          <button 
            @click="router.back()" 
            class="p-2 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition-shadow"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          
          <div class="text-center">
            <h1 class="text-lg font-bold text-gray-800 dark:text-white truncate max-w-[200px]">
              {{ tournament?.title || t('tournament_lobby.header.loading') }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
              {{ t('tournament_lobby.header.title') }}
            </p>
          </div>
          
          <div class="w-10 flex justify-end">
            <button 
              @click="fetchLobbyData"
              class="p-2 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition-shadow"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="pt-24 pb-32 px-4 max-w-lg mx-auto">
      
      <section class="mb-6">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 dark:from-blue-800 dark:to-indigo-900 rounded-3xl p-6 shadow-xl shadow-blue-500/20 relative overflow-hidden">
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>
          <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/5 rounded-full blur-3xl"></div>
          
          <div class="relative z-10">
            <div class="flex items-center gap-2 mb-3">
              <div class="flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span class="text-xs font-semibold text-white">{{ t('tournament_lobby.status.active') }}</span>
              </div>
              <div class="text-xs text-white/80 bg-white/10 px-2 py-1 rounded-full">
                {{ tournament?.format || 'Swiss' }}
              </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4 mb-4">
              <div class="text-center">
                <div class="text-sm text-white/80 mb-1">{{ t('tournament_lobby.info.format') }}</div>
                <div class="text-lg font-bold text-white">{{ tournament?.format || '-' }}</div>
              </div>
              <div class="text-center">
                <div class="text-sm text-white/80 mb-1">{{ t('tournament_lobby.info.time') }}</div>
                <div class="text-lg font-bold text-white">{{ formatTimeControl }}</div>
              </div>
              <div class="text-center">
                <div class="text-sm text-white/80 mb-1">{{ t('tournament_lobby.info.participants') }}</div>
                <div class="text-lg font-bold text-white">
                  {{ participants.length }} / {{ tournament?.max_participants || '∞' }}
                </div>
              </div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 mt-4">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-xs text-white/80">{{ t('tournament_lobby.schedule.start') }}</div>
                  <div class="text-sm font-medium text-white">{{ formatTournamentDate }}</div>
                </div>
                <div class="text-right">
                  <div class="text-xs text-white/80">{{ t('tournament_lobby.schedule.remaining') }}</div>
                  <div class="text-sm font-bold text-amber-300">{{ timeUntilStart }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="mb-6">
        <div class="flex bg-gray-100 dark:bg-slate-800 rounded-2xl p-1">
          <button 
            @click="activeTab = 'participants'"
            :class="[
              'flex-1 py-3 px-4 rounded-xl text-sm font-medium transition-all',
              activeTab === 'participants' 
                ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' 
                : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300'
            ]"
          >
            {{ t('tournament_lobby.tabs.participants') }}
          </button>
          <button 
            @click="activeTab = 'bracket'"
            :class="[
              'flex-1 py-3 px-4 rounded-xl text-sm font-medium transition-all',
              activeTab === 'bracket' 
                ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' 
                : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300'
            ]"
          >
            {{ t('tournament_lobby.tabs.bracket') }}
          </button>
        </div>
      </section>

      <section v-if="activeTab === 'participants'">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ t('tournament_lobby.participants.title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('tournament_lobby.participants.subtitle') }}</p>
          </div>
          <div class="px-3 py-1.5 bg-gray-100 dark:bg-slate-800 rounded-full text-xs font-medium text-gray-600 dark:text-gray-300">
            {{ t('tournament_lobby.participants.count', { count: participants.length }) }}
          </div>
        </div>

        <div v-if="loading" class="space-y-3">
          <div v-for="n in 5" :key="n" class="h-16 bg-gray-100 dark:bg-slate-800 rounded-2xl animate-pulse"></div>
        </div>

        <div v-else-if="participants.length === 0" class="text-center py-12 bg-white dark:bg-slate-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-slate-700">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 3.75l-2.25-1.25" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('tournament_lobby.participants.empty_title') }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ t('tournament_lobby.participants.empty_desc') }}</p>
          <button 
            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
            @click="() => {}"
          >
            {{ t('tournament_lobby.participants.btn_register') }}
          </button>
        </div>

        <div v-else class="space-y-3">
          <div 
            v-for="(participant, index) in participants" 
            :key="participant.id"
            class="group bg-white dark:bg-slate-800 rounded-2xl p-4 border border-gray-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-500 transition-colors hover:shadow-md"
          >
            <div class="flex items-center gap-4">
              <div class="flex-shrink-0">
                <div 
                  :class="[
                    'w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm',
                    getRankClass(index)
                  ]"
                >
                  {{ index + 1 }}
                </div>
              </div>

              <div class="relative flex-shrink-0">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-900 flex items-center justify-center overflow-hidden">
                  <img 
                    v-if="participant.avatar_url" 
                    :src="formatAvatarUrl(participant.avatar_url)" 
                    class="w-full h-full object-cover"
                    @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${participant.full_name}&background=3b82f6&color=fff&bold=true`"
                  />
                  <div 
                    v-else
                    class="w-full h-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 font-bold text-lg"
                  >
                    {{ participant.full_name.charAt(0).toUpperCase() }}
                  </div>
                </div>
                <div 
                  v-if="participant.is_online"
                  class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white dark:border-slate-800"
                  title="Online"
                ></div>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="font-semibold text-gray-800 dark:text-white truncate">
                    {{ participant.full_name }}
                  </h3>
                  <span 
                    v-if="index < 3"
                    class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                    :class="{
                      'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300': index === 0,
                      'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300': index === 1,
                      'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300': index === 2
                    }"
                  >
                    {{ index === 0 ? '🥇' : index === 1 ? '🥈' : '🥉' }}
                  </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  @{{ participant.username }}
                </p>
              </div>

              <div class="flex-shrink-0 text-right">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ t('tournament_lobby.participants.rating') }}</div>
                <div class="text-sm font-bold text-gray-800 dark:text-white">
                  {{ participant.current_rating || 1200 }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section v-else class="animate-in fade-in duration-300">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ t('tournament_lobby.bracket.title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('tournament_lobby.bracket.subtitle') }}</p>
          </div>
          <div class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">
            {{ t('tournament_lobby.bracket.system') }}
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700">
          <div class="text-center py-12">
            <div class="w-20 h-20 mx-auto mb-6 text-gray-300 dark:text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('tournament_lobby.bracket.placeholder_title') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ t('tournament_lobby.bracket.placeholder_desc') }}
            </p>
            <button 
              class="mt-6 px-6 py-2 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors"
              @click="activeTab = 'participants'"
            >
              {{ t('tournament_lobby.bracket.btn_view_participants') }}
            </button>
          </div>
        </div>
      </section>

      <section class="mt-8">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-5 border border-blue-100 dark:border-blue-800/30">
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 pt-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div>
              <h4 class="font-semibold text-blue-800 dark:text-blue-300 text-sm mb-2">{{ t('tournament_lobby.rules.title') }}</h4>
              <ul class="text-xs text-blue-700/80 dark:text-blue-400/80 space-y-1">
                <li class="flex items-start gap-2">
                  <span class="mt-1">•</span>
                  <span>{{ t('tournament_lobby.rules.time_control', { time: formatTimeControl }) }}</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="mt-1">•</span>
                  <span>{{ t('tournament_lobby.rules.format_round', { format: tournament?.format || 'Swiss', round: tournament?.rounds || 5 }) }}</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="mt-1">•</span>
                  <span>{{ t('tournament_lobby.rules.penalty') }}</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="mt-1">•</span>
                  <span>{{ t('tournament_lobby.rules.prize') }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>
    </main>

    <div class="fixed bottom-0 left-0 right-0 z-40 bg-gradient-to-t from-white dark:from-slate-900 via-white/95 dark:via-slate-900/95 to-transparent pointer-events-none">
      <div class="max-w-lg mx-auto px-4 pb-6 pt-8 pointer-events-auto">
        <div class="flex gap-3">
          <button 
            class="flex-1 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 py-3.5 rounded-xl font-semibold text-gray-700 dark:text-gray-300 text-sm shadow-sm hover:shadow-md transition-all active:scale-95 flex items-center justify-center gap-2"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ t('tournament_lobby.actions.rules') }}
          </button>
          <button 
            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-semibold text-sm shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all active:scale-95 flex items-center justify-center gap-2"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            {{ t('tournament_lobby.actions.chat') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>



/* Animation for tab content */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: fadeIn 0.3s ease-out;
}

/* Custom scrollbar */
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