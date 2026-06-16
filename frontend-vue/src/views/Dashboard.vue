<script setup>
import { ref, onMounted, onUnmounted, inject, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useSettingStore } from '../stores/settings';
import { useNotificationStore } from '../stores/notification';
import api from '../services/api';
import socket, { connectSocket, socketState } from '../services/socket';

// Import Swiper
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay } from 'swiper/modules';
import 'swiper/css';

// Import Dark Mode Composable
import { useDarkMode } from '../composables/useDarkMode';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const swal = inject('swal');
const toast = inject('toast');

const auth = useAuthStore();
const settingStore = useSettingStore();
const notifStore = useNotificationStore();
const router = useRouter();

// Dark Mode
const { isDark, toggleTheme } = useDarkMode();

// --- STATE ---
const banners = ref([]);
const newsList = ref([]);
const loading = ref(true);
const isSearching = ref(false);
const activeMatch = ref(null);
const showBalance = ref(true);
const floatingMatchVisible = ref(true);
const searchTime = ref(0);
const searchTimer = ref(null);
const estimatedTime = ref(30); // seconds
const matchmakingStats = ref({
    totalPlayers: 245,
    onlineNow: 183,
    similarRating: 12
});
const UserStatistic = ref('');

// State untuk dropdown bahasa
const showLanguageDropdown = ref(false);
const languages = [
  {c:'id', f:'id', n:'Indonesia'}, 
  {c:'en', f:'gb', n:'English'}, 
  {c:'cn', f:'cn', n:'Chinese'}, 
  {c:'jp', f:'jp', n:'Japanese'}, 
  {c:'ru', f:'ru', n:'Russian'}, 
  {c:'th', f:'th', n:'Thai'}, 
  {c:'ph', f:'ph', n:'Filipino'}, 
  {c:'vt', f:'vn', n:'Vietnamese'}
];

// Computed untuk bendera saat ini
const currentFlag = computed(() => {
  const lang = languages.find(l => l.c === locale.value);
  return lang ? lang.f : 'gb';
});

onMounted(() => {
    const savedShowBalance = localStorage.getItem('showBalance');
    if (savedShowBalance !== null) {
        showBalance.value = JSON.parse(savedShowBalance);
    }
    const savedLocale = localStorage.getItem('user-locale');
    if (savedLocale) {
        locale.value = savedLocale;
    }
    
    // Tambah event listener untuk click outside
    document.addEventListener('click', handleClickOutside);
});

// Method untuk ganti bahasa
const changeLanguage = (langCode) => {
  locale.value = langCode;
  showLanguageDropdown.value = false;
  localStorage.setItem('user-locale', langCode);
};

// Handle click outside untuk tutup dropdown
const handleClickOutside = (event) => {
  if (showLanguageDropdown.value && !event.target.closest('.language-dropdown-container')) {
    showLanguageDropdown.value = false;
  }
};

watch(showBalance, (newVal) => {
    localStorage.setItem('showBalance', JSON.stringify(newVal));
});

const isConnected = computed(() => socketState.connected);

// --- LOGIKA STATUS ---
const userStatus = computed(() => {
    const status = auth.user?.kyc_status;
    const wallet_status = auth.user?.wallet_status; // Mengambil status wallet
    const rating = auth.user?.rating || 1200;

    // 1. PRIORITAS UTAMA: Cek jika wallet dibekukan
    if (wallet_status === 'frozen') {
        return { 
            level: 'Frozen', 
            badge: '❄️',
            cardBg: 'bg-gradient-to-br from-blue-400 via-slate-500 to-slate-700', 
            textColor: 'text-red-500' // Memberi kesan peringatan
        };
    }

    // 2. Cek jika KYC sudah verified
    if (status === 'verified') {
        if (rating >= 2000) return { 
            level: 'Platinum', badge: '💎',
            cardBg: 'bg-gradient-to-br from-slate-700 via-slate-800 to-gray-950', 
            textColor: 'text-gray-300' 
        };
        
        if (rating >= 1800) return { 
            level: 'Gold', badge: '⭐',
            cardBg: 'bg-gradient-to-br from-yellow-500 via-amber-600 to-yellow-800', 
            textColor: 'text-amber-200'
        };

        return { 
            level: 'Silver', badge: '✓',
            cardBg: 'bg-gradient-to-br from-slate-400 via-gray-500 to-slate-600', 
            textColor: 'text-blue-100'
        };
    }

    // 3. DEFAULT: Unverified
    return { 
        level: 'Unverified', badge: '◯',
        cardBg: 'bg-gradient-to-br from-orange-700 via-amber-800 to-orange-950', 
        textColor: 'text-orange-200'
    };
});

const getUserStats = async () => {
    try {
        api.get('/dashboard/stats').then(res => {
            if(res.data.status=="success") {
                UserStatistic.value = res.data.data;
            }
        })
    } catch (err) {}
}

const userStats = computed(() => [
    { label: t('text.total_game'), value: UserStatistic.value.totalGames, icon: '♟️' },
    { label: t('text.win_rate'), value: UserStatistic.value.wins +'%', icon: '📈' },
    { label: t('text.rating'), value: UserStatistic.value.rating, icon: '🏅' },
    { label: t('text.rating_trend'), value:UserStatistic.value.ratingTrend, icon: '👑' }
]);

// --- METHODS ---
const toIDR = (num) => {
    if (!showBalance.value) return '••••••••';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);
};

const getAvatar = () => {
    if (auth.user?.avatar_url) return `${auth.user.avatar_url}`;
    return `https://ui-avatars.com/api/?name=${auth.user?.username || 'User'}&background=6366f1&color=fff&bold=true`;
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const startSearchTimer = () => {
    searchTime.value = 0;
    clearInterval(searchTimer.value);
    searchTimer.value = setInterval(() => {
        searchTime.value++;
        if (searchTime.value % 5 === 0) {
            matchmakingStats.value.similarRating = Math.min(24, matchmakingStats.value.similarRating + Math.floor(Math.random() * 3) + 1);
        }
    }, 1000);
};

const stopSearchTimer = () => {
    clearInterval(searchTimer.value);
    searchTime.value = 0;
};

const fetchDashboardData = async () => {
    try {
        loading.value = true;
        await Promise.allSettled([
            auth.fetchProfile(),
            !settingStore.appName ? settingStore.fetchSettings() : Promise.resolve(),
            api.get('/content/banners').then(res => {
                if (res.data?.status === 'success') banners.value = res.data.data;
            }),
            api.get('/content/news?limit=4').then(res => {
                if (res.data?.status === 'success') newsList.value = res.data.data;
            })
        ]);
    } catch (err) {
        console.error("Dashboard Fetch Error:", err);
    } finally {
        loading.value = false;
    }
};

const checkActiveMatch = async () => {
    try {
        const res = await api.get('/matches/active');
        activeMatch.value = (res.data?.status === 'success') ? res.data.data : null;
    } catch (err) {}
};

const getOpponentName = () => {
    if (!activeMatch.value) return 'Opponent';
    return (activeMatch.value.white_player_id === auth.user?.id) 
        ? (activeMatch.value.black_username || 'Unknown') 
        : (activeMatch.value.white_username || 'Unknown');
};

const rejoinMatch = () => router.push(`/game/${activeMatch.value.id}`);

const findMatch = async () => {
    if (!isConnected.value) { 
        connectSocket(); 
        toast.fire({ icon: 'warning', title: t('toast.connecting') }); 
        return; 
    }
    if (activeMatch.value) { 
        toast.fire({ icon: 'warning', title: t('toast.finish_active') }); 
        return; 
    }
    try {
        isSearching.value = true; 
        startSearchTimer();
        socket.emit('findMatch', { userId: auth.user.id });
        matchmakingStats.value = {
            totalPlayers: 200 + Math.floor(Math.random() * 100),
            onlineNow: 150 + Math.floor(Math.random() * 50),
            similarRating: 5 + Math.floor(Math.random() * 10)
        };
    } catch (err) { 
        toast.fire({ icon: 'error', title: t('toast.req_failed') }); 
        isSearching.value = false; 
        stopSearchTimer();
    }
};

const cancelMatch = async () => {
    try { 
        socket.emit('cancelFindMatch'); 
        isSearching.value = false; 
        stopSearchTimer();
        toast.fire({ icon: 'info', title: t('toast.search_cancelled') }); 
    } catch (err) { 
        toast.fire({ icon: 'error', title: t('toast.cancel_failed') }); 
    }
};

const handleLogout = async () => {
    const result = await swal.fire({ 
        title: t('dialog.logout_title'), 
        text: t('dialog.logout_desc'),
        icon: 'question', 
        showCancelButton: true, 
        confirmButtonColor: '#6366f1', 
        cancelButtonColor: '#9ca3af',
        confirmButtonText: t('button.logout'),
        cancelButtonText: t('button.cancel')
    });
    if (result.isConfirmed) { 
        await auth.logout(); 
        router.push('/login'); 
    }
};

const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return t('text.greeting.morning');
    if (hour < 15) return t('text.greeting.afternoon');
    if (hour < 18) return t('text.greeting.evening');
    return t('text.greeting.night');
};

watch(isSearching, (newVal) => {
    if (!newVal) {
        stopSearchTimer();
    }
});

// --- LIFECYCLE ---
onMounted(() => { 
    fetchDashboardData(); 
    checkActiveMatch(); 
    notifStore.fetchNotifications(); 
    notifStore.fetchUnreadCount(); 
    getUserStats();
    if (!socket.connected && auth.token) { connectSocket(); }

    socket.on('matchFound', (data) => { 
        isSearching.value = false; 
        stopSearchTimer();
        router.push(`/game/${data.dbMatchId}`); 
    });
    
    socket.on('matchmaking_error', (msg) => { 
        isSearching.value = false; 
        stopSearchTimer();
        toast.fire({ icon: 'error', title: msg }); 
    });
});

const reveiveFunds = () => {
  if (auth.user?.wallet_status === 'frozen') {
      swal.fire({
          title: t('wallet.toast.error_title') || 'Frozen',
          text: t('wallet.toast.frozen_desc') || 'Your wallet is temporarily frozen.',
          icon: 'error',
          confirmButtonColor: '#3b82f6',
      });
      return;
    }
  router.push("/wallet/receive");
};

onUnmounted(() => { 
    socket.off('matchFound'); 
    socket.off('matchmaking_error');
    stopSearchTimer();
    if (isSearching.value) cancelMatch();
    // Hapus event listener
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 font-sans pb-20 transition-colors duration-300">
    
  <transition name="fade">
      <div v-if="isSearching" class="fixed inset-0 bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 z-[100] flex flex-col safe-area-padding">
        
        <div class="w-full p-6 flex justify-between items-center shrink-0">
          <div class="text-white">
            <div class="flex items-center gap-3">
              <img :src="getAvatar()" class="w-10 h-10 rounded-full border-2 border-white/30" />
              <div>
                <h3 class="font-semibold">{{ auth.user?.username }}</h3>
                <p class="text-sm text-blue-200">{{ t('text.rating') }}: {{ auth.user?.rating }}</p>
              </div>
            </div>
          </div>
          <div class="text-white text-right">
            <div class="text-2xl font-bold">{{ formatTime(searchTime) }}</div>
            <div class="text-sm text-blue-200">{{ t('text.searching') }}</div>
          </div>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center w-full px-6 overflow-hidden min-h-0">
          
          <div class="relative w-64 h-64 sm:w-80 sm:h-80 shrink-0 mb-6 flex items-center justify-center">
            
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20 text-blue-200">
                <svg viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="w-full h-full p-8">
                    <defs>
                        <clipPath id="radar-clip">
                            <circle cx="256" cy="256" r="256" />
                        </clipPath>
                    </defs>
                    <g clip-path="url(#radar-clip)">
                        <path d="M256,48C141.1,48,48,141.1,48,256s93.1,208,208,208s208-93.1,208-208S370.9,48,256,48z M256,446.7
                            c-46.1,0-88.5-16.4-121.8-43.7c31.6-36.5,76.6-60.6,121.8-60.6c45.2,0,90.2,24.1,121.8,60.6C344.5,430.3,302.1,446.7,256,446.7z
                             M398.8,382.4c-35.3-41.2-86.4-68-142.8-68c-56.4,0-107.5,26.8-142.8,68C79.4,346.7,60,303.6,60,256c0-108.1,87.9-196,196-196
                            s196,87.9,196,196C452,303.6,432.6,346.7,398.8,382.4z"/>
                        <path d="M256,112c-79.5,0-144,64.5-144,144s64.5,144,144,144s144-64.5,144-144S335.5,112,256,112z M256,384
                            c-70.7,0-128.8-54.7-131.8-124h263.6C384.8,329.3,326.7,384,256,384z M387.8,244H124.2C127.2,174.7,185.3,120,256,120
                            s128.8,54.7,131.8,124z"/>
                        <path d="M129.6,368c-4.4,0-8-3.6-8-8c0-26.5-21.5-48-48-48c-4.4,0-8-3.6-8-8s3.6-8,8-8c35.3,0,64,28.7,64,64
                            C137.6,364.4,134,368,129.6,368z"/>
                        <path d="M84.3,275.6c-4.1-1.5-6.2-6.1-4.7-10.2c9.2-24.8,32.9-41.4,59.4-41.4c4.4,0,8,3.6,8,8s-3.6,8-8,8
                            c-18.7,0-35.5,11.7-41.9,29.3C95.5,273.4,91,275.5,86.9,275.6C86,275.6,85.2,275.6,84.3,275.6z"/>
                        <path d="M149.3,203.3c-4.4,0-8-3.6-8-8c0-31.6,23.1-58.1,53.8-63c4.3-0.7,8.4,2.3,9.1,6.6c0.7,4.3-2.3,8.4-6.6,9.1
                            c-22.1,3.5-38.6,22.3-38.6,45.2C159,198.8,155.1,203.3,149.3,203.3z"/>
                        <path d="M213.3,117.3c-4.4,0-8-3.6-8-8c0-11,8.9-20,20-20c4.4,0,8,3.6,8,8s-3.6,8-8,8c-2.2,0-4,1.8-4,4
                            C221.3,113.8,217.8,117.3,213.3,117.3z"/>
                        <path d="M382.4,368c-4.4,0-8-3.6-8-8c0-35.3,28.7-64,64-64c4.4,0,8,3.6,8,8s-3.6,8-8,8c-26.5,0-48,21.5-48,48
                            C390.4,364.4,386.8,368,382.4,368z"/>
                        <path d="M373,232c26.5,0,50.2,16.6,59.4,41.4c1.5,4.1-0.6,8.7-4.7,10.2c-0.9,0.3-1.8,0.5-2.7,0.5c-3.2,0-6.2-1.9-7.5-5.1
                            c-6.5-17.5-23.2-29.3-41.9-29.3c-4.4,0-8-3.6-8-8S368.6,232,373,232z"/>
                        <path d="M362.7,203.3c-5.8,0-9.8-4.5-9.8-9.4c0-22.9-16.5-41.7-38.6-45.2c-4.3-0.7-7.3-4.8-6.6-9.1c0.7-4.3,4.8-7.3,9.1-6.6
                            c30.7,4.9,53.8,31.4,53.8,63C370.7,199.8,367.1,203.3,362.7,203.3z"/>
                        <path d="M286.7,117.3c-4.4,0-8-3.6-8-8c0-2.2-1.8-4-4-4c-4.4,0-8-3.6-8-8s3.6-8,8-8c11,0,20,9,20,20
                            C294.7,113.8,291.1,117.3,286.7,117.3z"/>
                    </g>
                </svg>
            </div>

            <div class="absolute inset-0 rounded-full border-2 border-blue-400/20 animate-pulse"></div>
            <div class="absolute inset-8 rounded-full border-2 border-blue-400/30 animate-pulse" style="animation-delay: 0.5s"></div>
            <div class="absolute inset-16 rounded-full border-2 border-blue-400/40 animate-pulse" style="animation-delay: 1s"></div>
            
            <div class="absolute inset-0 rounded-full overflow-hidden">
                <div class="radar-sweep opacity-50"></div>
            </div>

            <div class="relative z-10">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-2xl border-2 border-white/20">
                    <div class="text-2xl text-white">♔</div>
                </div>
                <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white text-xs z-20 shadow-lg">
                    <div class="animate-ping absolute inset-0 rounded-full bg-green-400 opacity-75"></div>
                    <span class="relative font-bold">YOU</span>
                </div>
            </div>

            <div v-for="n in matchmakingStats.similarRating" :key="n" 
                 class="absolute w-2 h-2 rounded-full bg-yellow-400 animate-bounce shadow-[0_0_8px_rgba(250,204,21,0.8)]"
                 :style="{
                   left: `${50 + Math.cos(n * 0.8) * 40}%`, 
                   top: `${50 + Math.sin(n * 0.8) * 40}%`,
                   animationDelay: `${n * 0.2}s`
                 }">
            </div>

          </div>

          <div class="grid grid-cols-3 gap-3 w-full max-w-sm mb-6 z-10">
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center border border-white/20 shadow-lg">
              <div class="text-xl font-bold text-white">{{ matchmakingStats.totalPlayers }}</div>
              <div class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">{{ t('text.players') }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center border border-white/20 shadow-lg">
              <div class="text-xl font-bold text-white">{{ matchmakingStats.onlineNow }}</div>
              <div class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">{{ t('text.online') }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center border border-white/20 shadow-lg">
              <div class="text-xl font-bold text-white">{{ matchmakingStats.similarRating }}</div>
              <div class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">{{ t('text.similar') }}</div>
            </div>
          </div>

          <div class="w-full max-w-sm z-10">
            <div class="flex justify-between text-xs text-blue-200 mb-1 font-medium">
              <span>{{ t('text.matchmaking_progress') }}</span>
              <span>{{ Math.min(100, Math.floor(searchTime / estimatedTime * 100)) }}%</span>
            </div>
            <div class="h-1.5 bg-black/20 rounded-full overflow-hidden mb-2 backdrop-blur-sm border border-white/5">
              <div class="h-full bg-gradient-to-r from-blue-400 via-indigo-500 to-purple-500 rounded-full transition-all duration-300 shadow-[0_0_10px_rgba(139,92,246,0.5)]"
                   :style="{ width: `${Math.min(100, Math.floor(searchTime / estimatedTime * 100))}%` }"></div>
            </div>
            <div class="text-center text-blue-200 text-xs">
              {{ t('text.estimated_wait') }} <span class="font-semibold text-white">{{ Math.max(0, estimatedTime - searchTime) }}s</span>
            </div>
          </div>

        </div>

        <div class="w-full p-6 pb-8 flex flex-col items-center shrink-0 gap-4">
          <button @click="cancelMatch" 
                  class="px-8 py-3 w-full max-w-xs bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-full font-bold shadow-lg shadow-red-500/30 hover:shadow-red-500/50 transition-all active:scale-95 text-xs uppercase tracking-widest border border-white/10">
            {{ t('button.cancel_search') }}
          </button>

          <div class="bg-black/30 backdrop-blur-md rounded-lg py-2 px-4 border border-white/10 shadow-sm">
            <p class="text-center text-blue-200 text-xs">
              💡 <span class="font-medium text-white">{{ t('text.tip') }}</span> {{ t('text.tip_desc') }}
            </p>
          </div>
        </div>

      </div>
    </transition>

    <div v-if="!isSearching">
      <header class="sticky top-0 w-full z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between max-w-7xl mx-auto w-full">
          <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center">
                  <span class="text-white font-bold">♔</span>
              </div>
              <div>
                  <h1 class="font-semibold text-gray-800 dark:text-white">{{ settingStore.appName || 'Chess Elite' }}</h1>
                  <p class="text-xs" :class="userStatus.textColor">{{ userStatus.level }} {{ t('text.account') }}</p>
              </div>
          </div>
          
          <div class="flex items-center gap-2">
              <!-- Language Selector Dropdown -->
              <div class="relative language-dropdown-container">
                <button @click="showLanguageDropdown = !showLanguageDropdown"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 dark:bg-gray-700/50 backdrop-blur-sm rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600/50 transition-all duration-200 border border-gray-300 dark:border-gray-600">
                  <img :src="`https://flagcdn.com/w40/${currentFlag}.png`" class="w-4 h-4 rounded object-cover" />
                  <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ locale.toUpperCase() }}</span>
                  <svg class="w-3 h-3 text-gray-500 transition-transform duration-200" 
                       :class="{ 'rotate-180': showLanguageDropdown }" 
                       fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                
                <!-- Language Dropdown -->
                <transition name="fade-down">
                  <div v-if="showLanguageDropdown" 
                       class="absolute right-0 mt-2 w-48 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                    <div class="py-1 max-h-60 overflow-y-auto">
                      <button v-for="lang in languages" 
                              :key="lang.c"
                              @click="changeLanguage(lang.c)"
                              class="flex items-center gap-3 w-full px-3 py-2.5 text-left hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
                              :class="{ 'bg-teal-50 dark:bg-teal-900/30': locale === lang.c }">
                        <img :src="`https://flagcdn.com/w40/${lang.f}.png`" class="w-6 h-6 rounded object-cover" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ lang.n }}</span>
                        <svg v-if="locale === lang.c" class="w-4 h-4 text-teal-500 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                </transition>
              </div>

              <button @click="toggleTheme" class="w-9 h-9 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition">
                  <span v-if="isDark" class="text-gray-400">🌙</span>
                  <span v-else class="text-gray-600">☀️</span>
              </button>
              <router-link to="/inbox" class="relative w-9 h-9 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition">
                  <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                  </svg>
                  <span v-if="notifStore.unreadCount > 0" class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
              </router-link>
              <router-link to="/settings" class="relative w-9 h-9 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                  <span v-if="notifStore.unreadCount > 0" class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
              </router-link>
          </div>
        </div>
      </header>

      <main class="px-4 sm:px-6 lg:px-8 py-6 space-y-6 max-w-7xl mx-auto w-full">
        
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ getGreeting() }}</p>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">👤 {{ auth.user?.full_name || t('text.guest') }}</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">📧 {{ auth.user?.email  }}</span>
            </div>

            <router-link to="/profile" class="relative">
                <img :src="getAvatar()" class="w-12 h-12 rounded-full border-2 border-white dark:border-gray-800 shadow-sm" />
                <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white"
                     :class="isConnected ? 'bg-green-500' : 'bg-gray-400'"></div>
            </router-link>
        </div>

        <div :class="userStatus.cardBg" class="rounded-2xl p-5 lg:p-6 text-white shadow-lg transition-colors duration-500">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm opacity-90">{{ t('text.total_balance') }}</p>
                    <div class="flex items-baseline gap-2 mt-1">

                        <h3 class="text-3xl lg:text-4xl font-bold tracking-tight">{{ toIDR(auth.user?.balance).replace('Rp', '🪙') }}</h3>
                        <span class="text-xs px-2 py-1 rounded-full font-bold shadow-sm backdrop-blur-md bg-white/20 text-white border border-white/30">
                            {{ userStatus.badge }} {{ userStatus.level }}
                        </span>
                    </div>
                </div>
                <button @click="showBalance = !showBalance" class="p-1.5 rounded-lg hover:bg-white/10 transition">
                    <svg v-if="showBalance" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            
            <div class="flex gap-3">
                <router-link to="/wallet" class="flex-1 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl py-3 flex items-center justify-center gap-2 text-sm font-medium transition border border-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    {{ t('button.topup') }}
                </router-link>

                <button 
                    @click="reveiveFunds" 
                    class="flex-1 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl py-3 flex items-center justify-center gap-2 text-sm font-medium transition border border-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    {{ t('button.receive_funds') }}
                </button>
            </div>
        </div>

        <div v-if="auth.user?.kyc_status !== 'verified'" class="mb-6">
            <div class="relative overflow-hidden rounded-xl p-4 shadow-sm border transition-all duration-300"
                 :class="auth.user?.kyc_status === 'pending' 
                    ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800' 
                    : 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800'">
                
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg shadow-sm shrink-0"
                             :class="auth.user?.kyc_status === 'pending' ? 'bg-white text-amber-500' : 'bg-white text-red-500'">
                            {{ auth.user?.kyc_status === 'pending' ? '⏳' : '🛡️' }}
                        </div>
                        <div>
                            <h4 class="font-bold text-sm" 
                                :class="auth.user?.kyc_status === 'pending' ? 'text-amber-700 dark:text-amber-400' : 'text-red-700 dark:text-red-400'">
                                {{ auth.user?.kyc_status === 'pending' ? t('kyc.pending_title') : t('kyc.unverified_title') }}
                            </h4>
                            <p class="text-xs opacity-80" 
                               :class="auth.user?.kyc_status === 'pending' ? 'text-amber-600 dark:text-amber-500' : 'text-red-600 dark:text-red-500'">
                                {{ auth.user?.kyc_status === 'pending' ? t('kyc.pending_desc') : t('kyc.unverified_desc') }}
                            </p>
                        </div>
                    </div>

                    <router-link v-if="auth.user?.kyc_status !== 'pending'" to="/kyc" 
                       class="px-4 py-2 rounded-lg text-xs font-bold shadow-md transition-transform active:scale-95 flex items-center gap-1 shrink-0"
                       :class="'bg-red-600 hover:bg-red-700 text-white shadow-red-500/30'">
                        {{ t('button.verify') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </router-link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
            <div v-for="stat in userStats" :key="stat.label" 
                 class="bg-white dark:bg-gray-800 rounded-xl p-4 lg:p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl lg:text-3xl font-bold text-gray-800 dark:text-white">{{ stat.value }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ stat.label }}</p>
                    </div>
                    <div class="text-2xl lg:text-3xl">{{ stat.icon }}</div>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-lg lg:text-xl font-semibold text-gray-800 dark:text-white mb-4 lg:mb-6">{{t('text.game_mode')}}</h3>
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                <button @click="findMatch" :disabled="activeMatch"
                    class="col-span-2 lg:col-span-4 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl p-4 lg:p-5 flex items-center justify-between shadow-md hover:shadow-lg transition-all active:scale-95">
                    <div class="text-left">
                        <h4 class="text-lg lg:text-xl font-semibold">{{t('button.search_opponents')}}</h4>
                        <p class="text-sm opacity-90">{{t('text.search_desc')}}</p>
                    </div>
                    <div class="text-2xl lg:text-3xl">🏆</div>
                </button>
                <router-link to="/tournaments" 
                    class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl p-3 lg:p-4 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-3 hover:shadow-md">
                    <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 text-lg lg:text-xl">🏅</div>
                    <div>
                        <span class="text-sm lg:text-base font-medium text-gray-700 dark:text-gray-300">{{t('button.tournaments')}}</span>
                        <p class="text-xs text-gray-500 hidden lg:block">{{t('text.tournaments_desc')}}</p>
                    </div>
                </router-link>
                
                <router-link to="/lobby" 
                    class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl p-3 lg:p-4 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-3 hover:shadow-md">
                    <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 text-lg lg:text-xl">🛋️</div>
                    <div>
                        <span class="text-sm lg:text-base font-medium text-gray-700 dark:text-gray-300">{{t('button.lobby')}}</span>
                        <p class="text-xs text-gray-500 hidden lg:block">{{t('text.lobby_desc')}}</p>
                    </div>
                </router-link>
                <router-link to="/play/qr" 
                    class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl p-3 lg:p-4 border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition group hover:shadow-md">
                    <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-2 text-lg lg:text-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 100" width="200" height="100">
                          <rect width="200" height="100" fill="white"/>
                          <g transform="translate(10, 10)">
                            <rect x="0" y="0" width="2" height="60" fill="black"/>
                            <rect x="4" y="0" width="2" height="60" fill="black"/>
                            <rect x="8" y="0" width="4" height="60" fill="black"/>
                            <rect x="14" y="0" width="2" height="60" fill="black"/>
                            <rect x="18" y="0" width="6" height="60" fill="black"/>
                            <rect x="26" y="0" width="2" height="60" fill="black"/>
                            <rect x="30" y="0" width="2" height="60" fill="black"/>
                            <rect x="34" y="0" width="4" height="60" fill="black"/>
                            <rect x="40" y="0" width="2" height="60" fill="black"/>
                            <rect x="44" y="0" width="2" height="60" fill="black"/>
                            <rect x="50" y="0" width="4" height="60" fill="black"/>
                            <rect x="56" y="0" width="2" height="60" fill="black"/>
                            <rect x="60" y="0" width="4" height="60" fill="black"/>
                            <rect x="66" y="0" width="2" height="60" fill="black"/>
                            <rect x="72" y="0" width="2" height="60" fill="black"/>
                            <rect x="76" y="0" width="6" height="60" fill="black"/>
                            <rect x="84" y="0" width="2" height="60" fill="black"/>
                            <rect x="88" y="0" width="2" height="60" fill="black"/>
                            <rect x="94" y="0" width="4" height="60" fill="black"/>
                            <rect x="100" y="0" width="2" height="60" fill="black"/>
                            <rect x="104" y="0" width="2" height="60" fill="black"/>
                            <rect x="108" y="0" width="6" height="60" fill="black"/>
                            <rect x="116" y="0" width="2" height="60" fill="black"/>
                            <rect x="120" y="0" width="2" height="60" fill="black"/>
                            <rect x="124" y="0" width="4" height="60" fill="black"/>
                            <rect x="130" y="0" width="2" height="60" fill="black"/>
                            <rect x="134" y="0" width="2" height="60" fill="black"/>
                            <rect x="138" y="0" width="2" height="60" fill="black"/>
                            
                            <text x="70" y="75" font-family="monospace" font-size="14" text-anchor="middle" fill="black">1234 5678 9012</text>
                          </g>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800 dark:text-white text-sm lg:text-base">{{ t('button.play_offline') }}</h4>
                        <p class="text-xs text-gray-500">{{ t('text.offline_desc') }}</p>
                    </div>
                </router-link>

                <router-link to="/bot" 
                    class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl p-3 lg:p-4 border border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-600 transition hover:shadow-md">
                    <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 mb-2 text-lg lg:text-xl">
                        🤖
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800 dark:text-white text-sm lg:text-base">{{ t('button.vs_bot') }}</h4>
                        <p class="text-xs text-gray-500">{{ t('text.bot_desc') }}</p>
                    </div>
                </router-link>
            </div>
        </div>

        <div v-if="banners.length > 0">
            <h3 class="text-lg lg:text-xl font-semibold text-gray-800 dark:text-white mb-3 lg:mb-4">{{ t('text.promotions') }}</h3>
            <swiper
              :modules="[Autoplay]"
              :autoplay="{ delay: 4000, disableOnInteraction: false }"
              class="w-full rounded-xl lg:rounded-2xl overflow-hidden"
            >
              <swiper-slide v-for="banner in banners" :key="banner.id">
                <div class="aspect-[3/1] lg:aspect-[4/1] rounded-xl lg:rounded-2xl overflow-hidden">
                  <img :src="`/${banner.image_url}`" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
                </div>
              </swiper-slide>
            </swiper>
        </div>

        <div v-if="newsList.length > 0">
            <div class="flex justify-between items-center mb-3 lg:mb-4">
                <h3 class="text-lg lg:text-xl font-semibold text-gray-800 dark:text-white">{{ t('text.chess_news') }}</h3>
                <router-link to="/news" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ t('button.view_all') }}</router-link>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 lg:gap-4">
                <div v-for="news in newsList" :key="news.id" 
                     class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl p-4 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition hover:scale-[1.02]">
                    <div class="flex gap-4">
                        <div class="w-16 h-16 lg:w-20 lg:h-20 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden shrink-0">
                            <img v-if="news.news_image_url" :src="news.news_image_url" class="w-full h-full object-cover" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-gray-800 dark:text-white line-clamp-1 text-sm lg:text-base">{{ news.title }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ news.excerpt }}</p>
                            <div class="mt-2 text-xs text-gray-400">
                                {{ new Date(news.created_at).toLocaleDateString(locale == 'id' ? 'id-ID' : 'en-US') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      </main>

      <transition name="slide-up">
          <div v-if="activeMatch && floatingMatchVisible" 
               class="fixed bottom-20 left-4 right-4 lg:left-auto lg:right-8 lg:max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-4 z-40">
              <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                          ⚔️
                      </div>
                      <div>
                          <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ t('text.live_match') }}</p>
                          <p class="font-semibold text-gray-800 dark:text-white">{{ t('text.vs') }} {{ getOpponentName() }}</p>
                      </div>
                  </div>
                  <div class="flex items-center gap-2">
                      <button @click="rejoinMatch" 
                              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                          {{ t('button.rejoin') }}
                      </button>
                      <button @click="floatingMatchVisible = false" 
                              class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition">
                          ✕
                      </button>
                  </div>
              </div>
          </div>
      </transition>

      <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-30">
        <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
          <div class="flex h-16 items-center justify-between">
            <router-link to="/" class="flex flex-col items-center justify-center w-20">
              <div class="w-6 h-6 text-blue-600 dark:text-blue-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
              </div>
              <span class="text-xs mt-1 text-blue-600 dark:text-blue-400 font-medium">{{ t('menu.home') }}</span>
            </router-link>

            <router-link to="/wallet" class="flex flex-col items-center justify-center w-20">
              <div class="w-6 h-6 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
              </div>
              <span class="text-xs mt-1 text-gray-500 dark:text-gray-400">{{ t('menu.wallet') }}</span>
            </router-link>
            
            <div class="relative">
              <router-link to="/play/qr" 
                  class="absolute bottom-2 w-14 h-14 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg flex items-center justify-center -translate-x-1/2 left-1/2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h.01M16 3h5m0 0v5m0-5h-5m0 5v6m0 0h-6m6 0v6"/>
                </svg>
              </router-link>
            </div>

            <router-link to="/market" class="flex flex-col items-center justify-center w-20">
              <div class="w-6 h-6 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
              </div>
              <span class="text-xs mt-1 text-gray-500 dark:text-gray-400">{{ t('menu.market') }}</span>
            </router-link>

            <router-link to="/profile" class="flex flex-col items-center justify-center w-20">
              <div class="w-6 h-6 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              <span class="text-xs mt-1 text-gray-500 dark:text-gray-400">{{ t('menu.profile') }}</span>
            </router-link>
          </div>
        </div>
      </nav>

      <button @click="handleLogout" 
              class="fixed bottom-24 right-4 lg:right-8 w-12 h-12 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:shadow-xl transition z-40">
        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
      </button>

    </div>

  </div>
</template>

<style scoped>
/* Radar Animation */
.radar-sweep {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: conic-gradient(
    from 0deg,
    transparent 0deg,
    rgba(59, 130, 246, 0.3) 45deg,
    rgba(139, 92, 246, 0.6) 90deg,
    transparent 120deg
  );
  animation: rotateRadar 2s linear infinite;
}

@keyframes rotateRadar {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Fade Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Slide Up Transition */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

/* Dropdown Animation */
.fade-down-enter-active {
  animation: fadeDown 0.2s ease-out;
}

.fade-down-leave-active {
  animation: fadeDown 0.2s ease-out reverse;
}

@keyframes fadeDown {
  from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Router Link Active State */
.router-link-active.router-link-exact-active {
  @apply text-blue-600 dark:text-blue-400;
}

.router-link-active.router-link-exact-active svg {
  @apply text-blue-600 dark:text-blue-400;
}

.router-link-active.router-link-exact-active span {
  @apply text-blue-600 dark:text-blue-400;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.3);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.5);
}
</style>
