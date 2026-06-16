<script setup>
import { ref, onMounted, inject, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t, locale } = useI18n(); // [BARU] Init
const router = useRouter();
const toast = inject('toast');

const matches = ref([]);
const loading = ref(true);

// --- PAGINATION STATE ---
const currentPage = ref(1);
const itemsPerPage = 10;

// --- PAGINATION LOGIC ---
const totalPages = computed(() => Math.ceil(matches.value.length / itemsPerPage));

const paginatedMatches = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return matches.value.slice(start, end);
});

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

// --- HELPER FUNCTIONS ---

const getAvatar = (path, name) => {
    if (path) return `${import.meta.env.VITE_BASE_URL}/${path}`;
    return `https://ui-avatars.com/api/?name=${name || 'User'}&background=random&color=fff&size=128`;
};

// [UPDATED] Format tanggal dinamis
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
    return new Intl.DateTimeFormat(currentLocale, {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

// [UPDATED] Menggunakan t() untuk label hasil
const getResultLabel = (result, myRole) => {
    if (result === '1/2-1/2') return { 
        text: t('history.result.draw'), 
        class: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800' 
    };
    
    if (result === '1-0') {
        return myRole === 'white' 
            ? { text: t('history.result.win'), class: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800' }
            : { text: t('history.result.lose'), class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800' };
    }
    
    if (result === '0-1') {
        return myRole === 'black'
            ? { text: t('history.result.win'), class: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800' }
            : { text: t('history.result.lose'), class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800' };
    }
    
    return { text: '-', class: 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-gray-400' };
};

// [UPDATED] Menggunakan t() untuk alasan kemenangan
const formatReason = (reason) => {
    if (!reason) return '-';
    // Mapping key dari backend ke key i18n
    const key = reason.toLowerCase();
    const translationKey = `history.reason.${key}`;
    
    // Cek apakah terjemahan ada, jika tidak kembalikan raw text
    return t(translationKey) !== translationKey ? t(translationKey) : reason.replace(/_/g, ' ').toUpperCase();
};

// --- API CALL ---
const fetchMatches = async () => {
    loading.value = true;
    try {
        const response = await api.get('matches/history');
        matches.value = response.data.data || [];
    } catch (err) {
        console.error("Gagal load history:", err);
        if (toast) toast.fire({ icon: 'error', title: t('history.toast.load_error') });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchMatches();
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-slate-950 flex flex-col font-sans transition-colors duration-300">
        
        <div class="sticky top-0 z-30 bg-teal-700 dark:bg-slate-900 px-6 py-4 shadow-md transition-all duration-200">
            <div class="max-w-6xl mx-auto flex items-center justify-between text-white">
                <div class="flex items-center gap-4">
                    <button @click="router.push('/dashboard')" class="bg-white/20 p-2 rounded-full hover:bg-white/30 transition backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                    <h1 class="text-xl font-bold tracking-wide">{{ t('history.header.title') }} 📜</h1>
                </div>
                <div v-if="matches.length > 0" class="text-sm bg-teal-800 dark:bg-slate-800 px-3 py-1 rounded-full border border-teal-600 dark:border-slate-600">
                    {{ t('history.header.total_match', { count: matches.length }) }}
                </div>
            </div>
        </div>

        <div class="flex-1 max-w-6xl mx-auto w-full p-6">
            
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-100 dark:border-slate-700 overflow-hidden flex flex-col transition-colors duration-300">
                
                <div v-if="loading" class="p-20 text-center flex flex-col items-center justify-center">
                    <div class="w-10 h-10 border-4 border-teal-200 border-t-teal-600 rounded-full animate-spin mb-4"></div>
                    <span class="text-gray-500 dark:text-gray-400 font-medium">{{ t('history.loading') }}</span>
                </div>

                <div v-else-if="matches.length === 0" class="p-20 text-center flex flex-col items-center justify-center">
                    <div class="text-5xl mb-4 grayscale opacity-50">📭</div>
                    <h3 class="text-lg font-bold text-gray-700 dark:text-white">{{ t('history.empty.title') }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ t('history.empty.desc') }}</p>
                </div>

                <div v-else>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 text-xs uppercase text-gray-500 dark:text-gray-400 font-bold tracking-wider">
                                    <th class="px-6 py-4">{{ t('history.table.date') }}</th>
                                    <th class="px-6 py-4">{{ t('history.table.opponent') }}</th>
                                    <th class="px-6 py-4 text-center">{{ t('history.table.color') }}</th>
                                    <th class="px-6 py-4 text-center">{{ t('history.table.result') }}</th>
                                    <th class="px-6 py-4">{{ t('history.table.reason') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                                <tr v-for="match in paginatedMatches" :key="match.id" class="hover:bg-teal-50/30 dark:hover:bg-slate-700/50 transition group">
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-700 dark:text-gray-200 font-medium">{{ formatDate(match.date).split(',')[0] }}</div>
                                        <div class="text-gray-400 dark:text-gray-500 text-xs mt-0.5">{{ formatDate(match.date).split(',')[1] }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img :src="getAvatar(match.opponent_avatar, match.opponent_username)" 
                                                 class="w-10 h-10 rounded-full border border-gray-200 dark:border-slate-600 bg-gray-100 dark:bg-slate-700 object-cover shadow-sm group-hover:scale-105 transition" />
                                            <div>
                                                <div class="font-bold text-gray-800 dark:text-white">{{ match.opponent_username || t('history.player.unknown') }}</div>
                                                <div class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-slate-600 mt-1">
                                                    ⭐ {{ match.opponent_rating || '?' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border shadow-sm"
                                              :class="match.user_role === 'white' 
                                                ? 'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-gray-800 dark:text-gray-200' 
                                                : 'bg-gray-800 dark:bg-slate-900 border-gray-700 dark:border-slate-600 text-white'">
                                            <span class="w-2.5 h-2.5 rounded-full" 
                                                  :class="match.user_role === 'white' ? 'bg-gray-200 border border-gray-400 dark:bg-gray-400' : 'bg-white'"></span>
                                            {{ match.user_role === 'white' ? t('history.player.white') : t('history.player.black') }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold border shadow-sm mb-1"
                                                  :class="getResultLabel(match.result, match.user_role).class">
                                                {{ getResultLabel(match.result, match.user_role).text }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono bg-gray-50 dark:bg-slate-900 px-1 rounded">
                                                {{ match.result }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium capitalize flex items-center gap-2">
                                            {{ formatReason(match.win_reason) }}
                                        </span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 block mt-0.5">
                                            {{ match.time_control_label }}
                                        </span>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="totalPages > 1" class="px-6 py-4 bg-gray-50 dark:bg-slate-900 border-t border-gray-200 dark:border-slate-700 flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ t('history.pagination.page_info', { current: currentPage, total: totalPages }) }}
                        </span>
                        <div class="flex gap-2">
                            <button 
                                @click="prevPage" 
                                :disabled="currentPage === 1"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 font-bold bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-1">
                                <span>⬅️</span> {{ t('history.pagination.prev') }}
                            </button>
                            <button 
                                @click="nextPage" 
                                :disabled="currentPage === totalPages"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 font-bold bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-1">
                                {{ t('history.pagination.next') }} <span>➡️</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>