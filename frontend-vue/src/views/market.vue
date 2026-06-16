<script setup>
import { ref, computed, reactive, onMounted, inject } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const auth = useAuthStore();
const todayTransaction = 0;

const loadWalletData = async () => {
  try {
    await auth.fetchProfile(); 
  } catch (err) {
    console.error("Gagal load wallet:", err);
  } finally {
   }
};

// Format Rupiah
const toIDR = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);

// [UPDATED] Definisikan daftar kategori produk sebagai COMPUTED agar bisa diterjemahkan
const categories = computed(() => [
    { 
        name: t('market.category.train'), 
        icon: '🚂', 
        route: '/market/train', 
        description: t('market.category.train_desc'),
        color: 'from-blue-500 to-cyan-500',
        bgColor: 'bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20'
    },
    { 
        name: t('market.category.flight'), 
        icon: '✈️', 
        route: '/market/flight', 
        description: t('market.category.flight_desc'),
        color: 'from-purple-500 to-pink-500',
        bgColor: 'bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20'
    },
    { 
        name: t('market.category.hotel'), 
        icon: '🏨', 
        route: '/market/hotel', 
        description: t('market.category.hotel_desc'),
        color: 'from-amber-500 to-orange-500',
        bgColor: 'bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20'
    },
    { 
        name: t('market.category.pulsa'), 
        icon: '📱', 
        route: '/market/pulsa', 
        description: t('market.category.pulsa_desc'),
        color: 'from-green-500 to-emerald-500',
        bgColor: 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20'
    },
    { 
        name: t('market.category.postpaid'), 
        icon: '📞', 
        route: '/market/postpaid', 
        description: t('market.category.postpaid_desc'),
        color: 'from-red-500 to-rose-500',
        bgColor: 'bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20'
    },
    { 
        name: t('market.category.pln'), 
        icon: '⚡', 
        route: '/market/pln', 
        description: t('market.category.pln_desc'),
        color: 'from-yellow-500 to-amber-500',
        bgColor: 'bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20'
    },
    { 
        name: t('market.category.pdam'), 
        icon: '💧', 
        route: '/market/pdam', 
        description: t('market.category.pdam_desc'),
        color: 'from-cyan-500 to-blue-500',
        bgColor: 'bg-gradient-to-br from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20'
    },
    { 
        name: t('market.category.finance'), 
        icon: '💳', 
        route: '/market/finance', 
        description: t('market.category.finance_desc'),
        color: 'from-indigo-500 to-purple-500',
        bgColor: 'bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20'
    },
    { 
        name: t('market.category.umrah'), 
        icon: '🕋', 
        route: '/market/umroh', 
        description: t('market.category.umrah_desc'),
        color: 'from-teal-500 to-emerald-500',
        bgColor: 'bg-gradient-to-br from-teal-50 to-emerald-50 dark:from-teal-900/20 dark:to-emerald-900/20'
    },
    { 
        name: t('market.category.religious'), 
        icon: '🕌', 
        route: '/market/wisata', 
        description: t('market.category.religious_desc'),
        color: 'from-orange-500 to-red-500',
        bgColor: 'bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20'
    },
]);

// Grid layout responsif
const gridCols = computed(() => {
    if (categories.value.length <= 4) return 'grid-cols-2 sm:grid-cols-2 lg:grid-cols-4';
    return 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5';
});

const navigateTo = (route) => {
    router.push(route);
};

onMounted(() => {
  loadWalletData();
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-teal-600 to-teal-700 dark:from-slate-900 dark:to-slate-950 flex flex-col transition-colors duration-300">
        
        <header class="sticky top-0 z-50 bg-gradient-to-r from-teal-700/95 to-teal-600/95 dark:from-slate-900/95 dark:to-slate-800/95 backdrop-blur-lg px-4 sm:px-6 lg:px-8 pt-8 pb-6 shadow-lg border-b border-white/10 transition-all duration-200">
            <div class="flex items-center justify-between text-white mb-2 max-w-7xl mx-auto w-full">
                <div class="flex items-center gap-3">
                    <button 
                        @click="router.back()" 
                        class="bg-white/10 p-3 rounded-xl hover:bg-white/20 transition-all duration-200 backdrop-blur-sm active:scale-95 shadow-lg"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                    <div class="flex-1">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight">{{ t('market.header.title') }}</h1>
                        <p class="text-teal-100/90 dark:text-gray-300 text-xs sm:text-sm mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ t('market.header.subtitle') }}
                        </p>
                    </div>
                </div>
                <button class="bg-white/10 p-3 rounded-xl hover:bg-white/20 transition-all duration-200 backdrop-blur-sm shadow-lg active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            
            <div class="mt-4 p-3 bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 max-w-7xl mx-auto w-full">
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-xs text-teal-100/80 dark:text-gray-300">{{ t('market.header.available_balance') }}</div>
                        <div class="text-lg font-bold text-white truncate">{{ toIDR(auth.user?.balance).replace('Rp', '🪙') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-teal-100/80 dark:text-gray-300">{{ t('market.header.today_transaction') }}</div>
                        <div class="text-lg font-bold text-white truncate">{{ toIDR(todayTransaction).replace('Rp', '🪙') }} </div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-teal-100/80 dark:text-gray-300">{{ t('market.header.points') }}</div>
                        <div class="text-lg font-bold text-white">1.250</div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 bg-gray-50 dark:bg-slate-900 rounded-t-[40px] pt-8 px-4 sm:px-6 lg:px-8 pb-28 shadow-2xl min-h-[80vh] relative z-0 transition-colors duration-300">
            <div class="max-w-7xl mx-auto w-full">
                
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="font-bold text-lg sm:text-xl text-gray-800 dark:text-white">{{ t('market.section.available_services') }}</h2>
                        <span class="text-xs font-medium px-3 py-1 rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300">
                            {{ t('market.section.services_count', { count: categories.length }) }}
                        </span>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ t('market.section.services_desc') }}</p>
                </div>

                <div :class="['grid gap-4 sm:gap-6', gridCols]">
                    <div 
                        v-for="category in categories"
                        :key="category.name"
                        @click="navigateTo(category.route)"
                        class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-700 flex flex-col transition-all duration-300 cursor-pointer hover:shadow-2xl hover:-translate-y-1 active:scale-[0.98] group"
                        :class="category.bgColor"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center text-2xl shadow-lg"
                                :class="['bg-gradient-to-br', category.color]">
                                {{ category.icon }}
                            </div>
                            <span class="text-gray-300 dark:text-gray-600 group-hover:text-teal-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800 dark:text-white mb-1 leading-tight text-sm sm:text-base">
                                {{ category.name }}
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed line-clamp-2 sm:line-clamp-3">
                                {{ category.description }}
                            </p>
                        </div>
                        
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50">
                            <span class="text-xs font-medium px-2 py-1 rounded-full bg-white/50 dark:bg-slate-700/50 text-gray-700 dark:text-gray-300">
                                {{ t('market.button.access_now') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-200 dark:border-slate-700/50">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 rounded-lg bg-teal-500 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ t('market.info.title') }}</h3>
                    </div>
                    
                    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 p-4 rounded-2xl border border-teal-100 dark:border-teal-800/30">
                        <div class="flex items-start gap-3">
                            <div class="mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 font-medium mb-1">
                                    {{ t('market.info.secure_title') }}
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    {{ t('market.info.secure_desc') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.dark ::-webkit-scrollbar-track {
    background: #1e293b;
}

.dark ::-webkit-scrollbar-thumb {
    background: #475569;
}

/* Smooth transitions */
* {
    transition: background-color 0.3s ease, border-color 0.3s ease;
}

/* Gradient text animation */
.gradient-text {
    background: linear-gradient(45deg, #0d9488, #0891b2, #0ea5e9);
    background-size: 200% 200%;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: gradient 3s ease infinite;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Hover effects */
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>