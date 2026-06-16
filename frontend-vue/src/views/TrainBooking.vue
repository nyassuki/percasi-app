<script setup>
import { ref, reactive, onMounted, inject, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const toast = inject('toast');

const loading = ref(true);
const stationList = ref([]);
const searchLoading = ref(false);
const showStationDropdown = ref({
    origin: false,
    destination: false
});

const form = reactive({
    originId: null, 
    originName: '',
    destinationId: null,
    destinationName: '',
    date: new Date().toISOString().split('T')[0],
    passengers: 1,
    trip_type: 'one_way'
});

// Filtered stations for dropdown
const filteredOriginStations = computed(() => {
    if (!form.originName) return stationList.value.slice(0, 5);
    return stationList.value
        .filter(station => 
            station.nama_stasiun.toLowerCase().includes(form.originName.toLowerCase()) ||
            station.singkatan.toLowerCase().includes(form.originName.toLowerCase())
        )
        .slice(0, 5);
});

const filteredDestinationStations = computed(() => {
    if (!form.destinationName) return stationList.value.slice(0, 5);
    return stationList.value
        .filter(station => 
            station.nama_stasiun.toLowerCase().includes(form.destinationName.toLowerCase()) ||
            station.singkatan.toLowerCase().includes(form.destinationName.toLowerCase())
        )
        .slice(0, 5);
});

// Fungsi untuk handle blur dengan setTimeout
const handleOriginBlur = () => {
    setTimeout(() => {
        showStationDropdown.value.origin = false;
    }, 200);
};

const handleDestinationBlur = () => {
    setTimeout(() => {
        showStationDropdown.value.destination = false;
    }, 200);
};

// --- LOAD DATA MASTER ---
const fetchStations = async () => {
    loading.value = true;
    try {
        const res = await api.get('/master/stasiun');
        if (res.data && res.data.status === 'success') {
            stationList.value = res.data.data;
        } else {
            stationList.value = [];
        }
    } catch (err) {
        toast.fire({ icon: 'error', title: t('train.toast.load_error') });
    } finally {
        loading.value = false;
    }
};

// --- LOGIC FORM ---
const swapStations = () => {
    [form.originName, form.destinationName] = [form.destinationName, form.originName];
    [form.originId, form.destinationId] = [form.destinationId, form.originId];
};

const selectStation = (station, type) => {
    if (type === 'origin') {
        form.originName = station.nama_stasiun;
        form.originId = station.id;
        showStationDropdown.value.origin = false;
    } else {
        form.destinationName = station.nama_stasiun;
        form.destinationId = station.id;
        showStationDropdown.value.destination = false;
    }
};

const searchTickets = async () => {
    if (!form.originId || !form.destinationId) {
        toast.fire({ 
            icon: 'error', 
            title: t('train.toast.incomplete'),
            text: t('train.toast.incomplete_desc')
        });
        return;
    }
    if (form.originId === form.destinationId) {
        toast.fire({ 
            icon: 'error', 
            title: t('train.toast.invalid'),
            text: t('train.toast.invalid_desc') 
        });
        return;
    }
    
    searchLoading.value = true;
    
    // Simulasi pencarian
    setTimeout(() => {
        searchLoading.value = false;
        toast.fire({ 
            icon: 'success', 
            title: t('train.toast.success'),
            text: `${t('train.toast.showing_schedule')} ${form.originName} -> ${form.destinationName}` 
        });
        // Navigasi ke hasil pencarian
        router.push({
            path: '/train/results',
            query: {
                origin: form.originId,
                destination: form.destinationId,
                date: form.date,
                passengers: form.passengers,
                trip_type: form.trip_type
            }
        });
    }, 1500);
};

// Date formatting helpers
const today = new Date().toISOString().split('T')[0];
const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
const nextWeek = new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0];

const quickDateSelect = (days) => {
    const date = new Date(Date.now() + days * 86400000);
    form.date = date.toISOString().split('T')[0];
};

onMounted(fetchStations);
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        
        <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 px-4 sm:px-6 lg:px-8 py-3 border-b border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-3 max-w-7xl mx-auto w-full">
                <button 
                    @click="router.back()" 
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </button>
                <div class="flex-1">
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('train.header.title') }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('train.header.subtitle') }}</p>
                </div>
            </div>
        </header>

        <main class="px-4 sm:px-6 lg:px-8 py-4 pb-20 max-w-7xl mx-auto w-full">
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 p-4 lg:p-6">
                
                <div class="flex mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-1 max-w-md">
                    <button 
                        type="button" 
                        @click="form.trip_type = 'one_way'"
                        :class="form.trip_type === 'one_way' 
                            ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-white shadow-sm' 
                            : 'text-gray-600 dark:text-gray-400'"
                        class="flex-1 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        {{ t('train.trip_type.one_way') }}
                    </button>
                    <button 
                        type="button" 
                        @click="form.trip_type = 'round_trip'"
                        :class="form.trip_type === 'round_trip' 
                            ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-white shadow-sm' 
                            : 'text-gray-600 dark:text-gray-400'"
                        class="flex-1 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        {{ t('train.trip_type.round_trip') }}
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-4">
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('train.form.origin') }}</label>
                        <div class="relative">
                            <input
                                v-model="form.originName"
                                @focus="showStationDropdown.origin = true"
                                @blur="handleOriginBlur"
                                type="text"
                                :placeholder="t('train.form.origin_placeholder')"
                                :disabled="loading"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            />
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            
                            <div v-if="showStationDropdown.origin && filteredOriginStations.length > 0" 
                                 class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto">
                                <div v-for="station in filteredOriginStations" 
                                     :key="'origin-' + station.id"
                                     @mousedown.prevent="selectStation(station, 'origin')"
                                     class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                    <div class="font-medium text-gray-900 dark:text-white text-sm">{{ station.nama_stasiun }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ station.singkatan }} • {{ station.provinsi }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center items-center lg:col-span-1 lg:justify-center lg:pt-6">
                        <button 
                            type="button"
                            @click="swapStations"
                            class="p-2 bg-gray-100 dark:bg-gray-700 rounded-full text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            :disabled="loading"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </button>
                    </div>

                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('train.form.destination') }}</label>
                        <div class="relative">
                            <input
                                v-model="form.destinationName"
                                @focus="showStationDropdown.destination = true"
                                @blur="handleDestinationBlur"
                                type="text"
                                :placeholder="t('train.form.destination_placeholder')"
                                :disabled="loading"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            />
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            
                            <div v-if="showStationDropdown.destination && filteredDestinationStations.length > 0" 
                                 class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto">
                                <div v-for="station in filteredDestinationStations" 
                                     :key="'dest-' + station.id"
                                     @mousedown.prevent="selectStation(station, 'destination')"
                                     class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                    <div class="font-medium text-gray-900 dark:text-white text-sm">{{ station.nama_stasiun }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ station.singkatan }} • {{ station.provinsi }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('train.form.departure') }}</label>
                        <input 
                            v-model="form.date" 
                            type="date" 
                            class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            :min="today"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('train.form.passengers') }}</label>
                        <select v-model="form.passengers" 
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                            <option v-for="n in 5" :key="n" :value="n">{{ t('train.form.adult', { count: n }) }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2 mb-6 overflow-x-auto">
                    <button 
                        @click="quickDateSelect(0)"
                        :class="form.date === today 
                            ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700' 
                            : 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'"
                        class="flex-shrink-0 px-4 py-2 text-sm rounded-lg border transition-colors hover:bg-blue-50 dark:hover:bg-blue-800/30"
                    >
                        {{ t('train.quick_date.today') }}
                    </button>
                    <button 
                        @click="quickDateSelect(1)"
                        :class="form.date === tomorrow 
                            ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700' 
                            : 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'"
                        class="flex-shrink-0 px-4 py-2 text-sm rounded-lg border transition-colors hover:bg-blue-50 dark:hover:bg-blue-800/30"
                    >
                        {{ t('train.quick_date.tomorrow') }}
                    </button>
                    <button 
                        @click="quickDateSelect(7)"
                        :class="form.date === nextWeek 
                            ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700' 
                            : 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'"
                        class="flex-shrink-0 px-4 py-2 text-sm rounded-lg border transition-colors hover:bg-blue-50 dark:hover:bg-blue-800/30"
                    >
                        {{ t('train.quick_date.days_7') }}
                    </button>
                </div>

                <div class="flex justify-center">
                    <button 
                        @click="searchTickets" 
                        :disabled="searchLoading || loading || !form.originId || !form.destinationId"
                        class="w-full max-w-md bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-8 rounded-lg transition-colors flex items-center justify-center gap-2 shadow-lg hover:shadow-xl"
                    >
                        <template v-if="searchLoading">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span>{{ t('train.button.searching') }}</span>
                        </template>
                        <template v-else>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="text-base font-semibold">{{ t('train.button.search') }}</span>
                        </template>
                    </button>
                </div>

                <div v-if="loading" class="text-center py-4">
                    <div class="inline-block w-6 h-6 border-2 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">{{ t('train.form.loading') }}</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ t('train.tips.title') }}</p>
                            <ul class="text-xs text-gray-600 dark:text-gray-300 space-y-1">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500">•</span>
                                    {{ t('train.tips.1') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500">•</span>
                                    {{ t('train.tips.2') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500">•</span>
                                    {{ t('train.tips.3') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ t('train.advantages.title') }}</p>
                            <ul class="text-xs text-gray-600 dark:text-gray-300 space-y-1">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500">•</span>
                                    {{ t('train.advantages.1') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500">•</span>
                                    {{ t('train.advantages.2') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500">•</span>
                                    {{ t('train.advantages.3') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('train.routes.title') }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <button 
                        @click="() => { 
                            form.originName = 'Gambir (GMR)'; 
                            form.destinationName = 'Bandung (BD)'; 
                            form.originId = 1; 
                            form.destinationId = 2; 
                        }"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left"
                    >
                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ t('train.routes.gambir') }} → {{ t('train.routes.bandung') }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">3 {{ t('train.routes.hours', { count: 3 }) }} • {{ t('train.routes.price_start', { price: 'Rp 90.000' }) }}</div>
                    </button>
                    <button 
                        @click="() => { 
                            form.originName = 'Bandung (BD)'; 
                            form.destinationName = 'Yogyakarta (YK)'; 
                            form.originId = 2; 
                            form.destinationId = 3; 
                        }"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left"
                    >
                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ t('train.routes.bandung') }} → {{ t('train.routes.yogyakarta') }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">8 {{ t('train.routes.hours', { count: 8 }) }} • {{ t('train.routes.price_start', { price: 'Rp 150.000' }) }}</div>
                    </button>
                    <button 
                        @click="() => { 
                            form.originName = 'Surabaya (SGU)'; 
                            form.destinationName = 'Malang (ML)'; 
                            form.originId = 4; 
                            form.destinationId = 5; 
                        }"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left"
                    >
                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ t('train.routes.surabaya') }} → {{ t('train.routes.malang') }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">2 {{ t('train.routes.hours', { count: 2 }) }} • {{ t('train.routes.price_start', { price: 'Rp 50.000' }) }}</div>
                    </button>
                    <button 
                        @click="() => { 
                            form.originName = 'Yogyakarta (YK)'; 
                            form.destinationName = 'Solo (SK)'; 
                            form.originId = 3; 
                            form.destinationId = 6; 
                        }"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left"
                    >
                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ t('train.routes.yogyakarta') }} → {{ t('train.routes.solo') }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">1 {{ t('train.routes.hours', { count: 1 }) }} • {{ t('train.routes.price_start', { price: 'Rp 30.000' }) }}</div>
                    </button>
                    <button 
                        @click="() => { 
                            form.originName = 'Jakarta Kota (JAKK)'; 
                            form.destinationName = 'Tangerang (TNG)'; 
                            form.originId = 7; 
                            form.destinationId = 8; 
                        }"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left"
                    >
                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ t('train.routes.jakarta_kota') }} → {{ t('train.routes.tangerang') }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">45 {{ t('train.routes.minutes', { count: 45 }) }} • {{ t('train.routes.price_start', { price: 'Rp 20.000' }) }}</div>
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.dark ::-webkit-scrollbar-track {
    background: #374151;
}

.dark ::-webkit-scrollbar-thumb {
    background: #6b7280;
}

/* Custom date input */
input[type="date"] {
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
    color-scheme: light dark;
}

.dark input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
}

/* Custom select */
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1em;
    padding-right: 2.5rem;
}

/* Animation for popular routes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

button {
    animation: fadeInUp 0.3s ease-out;
}
</style>