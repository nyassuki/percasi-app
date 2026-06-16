<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU]

const { t, locale } = useI18n(); // [BARU]
const router = useRouter();
const liveMatches = ref([]);
const loading = ref(true);
const lastUpdated = ref('');
const headerScrolled = ref(false);

// Auto-refresh setiap 10 detik
let refreshInterval;

const fetchLive = async () => {
    loading.value = true; // [OPTIONAL] Tampilkan loading saat refresh manual
    try {
        const res = await api.get('/matches/live');
        liveMatches.value = res.data.data;
        
        // [UPDATED] Format waktu dinamis berdasarkan locale
        const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
        lastUpdated.value = new Date().toLocaleTimeString(currentLocale, {
            hour: '2-digit',
            minute: '2-digit'
        });
    } finally {
        loading.value = false;
    }
};

const formatTime = (dateString) => {
    // [UPDATED] Format waktu dinamis berdasarkan locale
    const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
    return new Date(dateString).toLocaleTimeString(currentLocale, {
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Handle scroll untuk header effect
const handleScroll = () => {
    headerScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    fetchLive();
    refreshInterval = setInterval(fetchLive, 10000);
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 transition-colors">
        <header 
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
            :class="[
                headerScrolled 
                    ? 'bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-slate-200/50 dark:border-slate-800/50 shadow-lg' 
                    : 'bg-transparent'
            ]"
        >
            <div class="max-w-7xl mx-auto px-4 md:px-6 py-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-8 bg-emerald-500 rounded-full"></div>
                            <div>
                                <h1 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">
                                    {{ t('live_matches.header.title_1') }} <span class="text-emerald-500">{{ t('live_matches.header.title_2') }}</span>
                                </h1>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium hidden md:block">
                                    {{ t('live_matches.header.subtitle') }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 md:hidden">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                {{ liveMatches.length }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border border-slate-200 dark:border-slate-700">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                {{ t('live_matches.header.live_count', { count: liveMatches.length }) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div v-if="lastUpdated" class="hidden md:block">
                                <span class="text-xs text-slate-400 font-medium">
                                    {{ t('live_matches.header.updated', { time: lastUpdated }) }}
                                </span>
                            </div>
                            
                            <button @click="fetchLive" 
                                    class="px-4 py-2 bg-gradient-to-r from-slate-800 to-slate-900 dark:from-slate-700 dark:to-slate-800 text-white text-xs font-bold rounded-xl transition-all hover:shadow-lg active:scale-95 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                {{ t('live_matches.header.refresh') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="pt-24 pb-12 px-4 md:px-6">
            <div class="max-w-7xl mx-auto">
                <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="n in 6" :key="n" 
                         class="bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm rounded-3xl border border-slate-200/50 dark:border-slate-700/50 p-6 animate-pulse">
                        <div class="flex justify-between mb-6">
                            <div class="w-24 h-6 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
                            <div class="w-12 h-6 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
                        </div>
                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between items-center">
                                <div class="w-32 h-8 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
                                <div class="w-8 h-8 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                                <div class="w-32 h-8 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
                            </div>
                        </div>
                        <div class="w-full h-12 bg-slate-200 dark:bg-slate-700 rounded-2xl"></div>
                    </div>
                </div>

                <div v-else>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" v-if="liveMatches.length > 0">
                        <div v-for="match in liveMatches" :key="match.id"
                             class="group relative bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/5 hover:border-emerald-200 dark:hover:border-emerald-800">
                            
                            <div class="absolute -top-3 left-6">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-red-500 rounded-xl blur-sm animate-pulse"></div>
                                    <div class="relative px-4 py-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white text-[10px] font-black rounded-xl uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                        {{ t('live_matches.card.live_now') }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end mb-2">
                                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
                                    {{ t('live_matches.card.match_id', { id: match.id }) }}
                                </span>
                            </div>

                            <div class="mb-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="text-center flex-1">
                                        <div class="relative inline-block mb-2">
                                            <div class="absolute inset-0 bg-white dark:bg-slate-700 rounded-full blur"></div>
                                            <div class="relative w-12 h-12 bg-gradient-to-br from-slate-100 to-white dark:from-slate-700 dark:to-slate-800 rounded-full border-2 border-slate-200 dark:border-slate-600 flex items-center justify-center">
                                                <span class="text-lg font-black text-slate-700 dark:text-slate-300">⚪</span>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white truncate mb-1">
                                            {{ match.white_player }}
                                        </p>
                                        <p class="text-[10px] font-semibold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest">
                                            {{ t('live_matches.card.white') }}
                                        </p>
                                    </div>
                                    
                                    <div class="px-6">
                                        <div class="relative">
                                            <div class="w-14 h-14 bg-gradient-to-br from-slate-800 to-slate-900 dark:from-slate-700 dark:to-slate-800 rounded-2xl flex items-center justify-center">
                                                <span class="text-lg font-black text-white italic">VS</span>
                                            </div>
                                            <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                                                <span class="text-[9px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full whitespace-nowrap">
                                                    {{ formatTime(match.started_at || new Date()) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center flex-1">
                                        <div class="relative inline-block mb-2">
                                            <div class="absolute inset-0 bg-slate-800 dark:bg-slate-900 rounded-full blur"></div>
                                            <div class="relative w-12 h-12 bg-gradient-to-br from-slate-800 to-slate-900 dark:from-slate-900 dark:to-slate-950 rounded-full border-2 border-slate-700 dark:border-slate-800 flex items-center justify-center">
                                                <span class="text-lg font-black text-slate-300">⚫</span>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white truncate mb-1">
                                            {{ match.black_player }}
                                        </p>
                                        <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                            {{ t('live_matches.card.black') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-center gap-4 mb-6">
                                    <div class="text-center">
                                        <div class="text-2xl font-black text-slate-800 dark:text-white">0</div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase">{{ t('live_matches.card.moves') }}</div>
                                    </div>
                                    <div class="w-1 h-6 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                                    <div class="text-center">
                                        <div class="text-2xl font-black text-emerald-500">00:00</div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase">{{ t('live_matches.card.time') }}</div>
                                    </div>
                                </div>
                            </div>

                            <button @click="router.push(`/mirror/${match.id}`)" 
                                    class="group relative w-full overflow-hidden bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 dark:from-emerald-700 dark:via-emerald-600 dark:to-emerald-700 text-white py-3.5 rounded-2xl font-bold text-xs uppercase tracking-[0.2em] transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/20 active:scale-95">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-emerald-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                                <span class="relative flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    {{ t('live_matches.card.watch') }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div v-else class="max-w-md mx-auto text-center pt-16 pb-24">
                        <div class="relative inline-block mb-6">
                            <div class="absolute inset-0 bg-slate-200 dark:bg-slate-800 rounded-full blur-xl"></div>
                            <div class="relative w-24 h-24 bg-gradient-to-br from-slate-100 to-white dark:from-slate-800 dark:to-slate-900 rounded-full border-4 border-slate-200 dark:border-slate-700 flex items-center justify-center">
                                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-slate-700 dark:text-slate-300 mb-2">
                            {{ t('live_matches.empty.title') }}
                        </h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 whitespace-pre-line">
                            {{ t('live_matches.empty.desc') }}
                        </p>
                        <button @click="fetchLive" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-slate-800 to-slate-900 dark:from-slate-700 dark:to-slate-800 text-white rounded-xl font-bold text-sm transition-all hover:shadow-lg active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ t('live_matches.empty.btn_refresh') }}
                        </button>
                    </div>
                </div>

                <div v-if="liveMatches.length > 0" class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                    {{ t('live_matches.footer.title') }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ t('live_matches.footer.desc') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-xs text-slate-400 font-medium">
                            {{ t('live_matches.footer.total') }}: <span class="font-black text-emerald-500">{{ liveMatches.length }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Custom animations */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

.group:hover .floating {
    animation: float 2s ease-in-out infinite;
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Header backdrop blur enhancement */
header {
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* Ensure content doesn't jump on scroll */
main {
    min-height: calc(100vh - 6rem);
}
</style>