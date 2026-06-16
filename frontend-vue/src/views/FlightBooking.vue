<script setup>
import { ref, reactive, onMounted, inject, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const toast = inject('toast');

const loading = ref(true);
const airportList = ref([]);
const searchLoading = ref(false);
const showAirportDropdown = ref({
    origin: false,
    destination: false
});
const passengerDropdown = ref(false);

const form = reactive({
    originId: null, 
    originName: '',
    destinationId: null,
    destinationName: '',
    date: new Date().toISOString().split('T')[0],
    returnDate: new Date(Date.now() + 86400000 * 2).toISOString().split('T')[0],
    passengers: { adults: 1, children: 0, infants: 0 },
    class: 'economy',
    trip_type: 'one_way'
});

// [BARU] Helper untuk mendapatkan nama kelas yang diterjemahkan
const getClassName = (cls) => {
    if (cls === 'economy') return t('flight.class.economy');
    if (cls === 'business') return t('flight.class.business');
    return t('flight.class.first');
};

// Filtered airports for dropdown
const filteredOriginAirports = computed(() => {
    if (!form.originName) return airportList.value.slice(0, 5);
    return airportList.value
        .filter(airport => 
            airport.nama_bandara.includes(form.originName) ||
            airport.kode_iata.toLowerCase().includes(form.originName.toLowerCase()) ||
            airport.kota
        )
        .slice(0, 5);
});

const filteredDestinationAirports = computed(() => {
    if (!form.destinationName) return airportList.value.slice(0, 5);
    return airportList.value
        .filter(airport => 
            airport.nama_bandara.toLowerCase().includes(form.destinationName.toLowerCase()) ||
            airport.kode_iata.toLowerCase().includes(form.destinationName.toLowerCase()) ||
            airport.kota
        )
        .slice(0, 5);
});

// Calculate total passengers
const totalPassengers = computed(() => {
    return form.passengers.adults + form.passengers.children + form.passengers.infants;
});

// --- FUNGSI UNTUK HANDLE DROPDOWN ---
const handleOriginBlur = () => {
    setTimeout(() => {
        showAirportDropdown.value.origin = false;
    }, 200);
};

const handleDestinationBlur = () => {
    setTimeout(() => {
        showAirportDropdown.value.destination = false;
    }, 200);
};

// --- LOAD DATA MASTER ---
const fetchAirports = async () => {
    loading.value = true;
    try {
        const res = await api.get('/master/bandara');
        if (res.data && res.data.status === 'success') {
            airportList.value = res.data.data;
        } else {
            airportList.value = [];
        }
    } catch (err) {
        toast.fire({ 
            icon: 'error', 
            title: t('flight.toast.load_error'),
            text: t('flight.toast.try_again')
        });
    } finally {
        loading.value = false;
    }
};

// --- LOGIC FORM ---
const swapAirports = () => {
    [form.originName, form.destinationName] = [form.destinationName, form.originName];
    [form.originId, form.destinationId] = [form.destinationId, form.originId];
};

const selectAirport = (airport, type) => {
    if (type === 'origin') {
        form.originName = airport.nama_bandara;
        form.originId = airport.id;
        showAirportDropdown.value.origin = false;
    } else {
        form.destinationName = airport.nama_bandara;
        form.destinationId = airport.id;
        showAirportDropdown.value.destination = false;
    }
};

const updatePassengerCount = (type, operation) => {
    if (operation === 'increment') {
        if (type === 'adults' && form.passengers.adults < 9) form.passengers.adults++;
        if (type === 'children' && form.passengers.children < 9) form.passengers.children++;
        if (type === 'infants' && form.passengers.infants < 9 && form.passengers.infants < form.passengers.adults) form.passengers.infants++;
    } else {
        if (type === 'adults' && form.passengers.adults > 1) form.passengers.adults--;
        if (type === 'children' && form.passengers.children > 0) form.passengers.children--;
        if (type === 'infants' && form.passengers.infants > 0) form.passengers.infants--;
    }
};

const searchFlights = async () => {
    if (!form.originId || !form.destinationId) {
        toast.fire({ 
            icon: 'error', 
            title: t('flight.toast.incomplete'),
            text: t('flight.toast.incomplete_desc')
        });
        return;
    }
    if (form.originId === form.destinationId) {
        toast.fire({ 
            icon: 'error', 
            title: t('flight.toast.invalid'),
            text: t('flight.toast.invalid_desc')
        });
        return;
    }
    if (form.trip_type === 'round_trip' && form.returnDate <= form.date) {
        toast.fire({ 
            icon: 'error', 
            title: t('flight.toast.invalid_date'),
            text: t('flight.toast.invalid_date_desc')
        });
        return;
    }
    
    searchLoading.value = true;
    
    // Simulasi pencarian
    setTimeout(() => {
        searchLoading.value = false;
        toast.fire({ 
            icon: 'success', 
            title: t('flight.toast.success'),
            text: `${t('flight.toast.showing_flights')} ${form.originName} -> ${form.destinationName}` 
        });
        // Navigasi ke hasil pencarian
        router.push({
            path: '/flight/results',
            query: {
                origin: form.originId,
                destination: form.destinationId,
                date: form.date,
                returnDate: form.returnDate,
                passengers: JSON.stringify(form.passengers),
                class: form.class,
                trip_type: form.trip_type
            }
        });
    }, 1500);
};

// Date formatting helpers
const today = new Date().toISOString().split('T')[0];
const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];

const quickDateSelect = (days) => {
    const date = new Date(Date.now() + days * 86400000);
    form.date = date.toISOString().split('T')[0];
    
    if (form.trip_type === 'round_trip') {
        const returnDate = new Date(date.getTime() + 2 * 86400000);
        form.returnDate = returnDate.toISOString().split('T')[0];
    }
};

onMounted(fetchAirports);
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
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('flight.title') }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('flight.subtitle') }}</p>
                </div>
            </div>
        </header>

        <main class="px-4 sm:px-6 lg:px-8 py-4 pb-20 max-w-7xl mx-auto w-full">
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 p-4 lg:p-6 max-w-4xl mx-auto">
                
                <div class="flex mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-1 max-w-md">
                    <button 
                        type="button" 
                        @click="form.trip_type = 'one_way'"
                        :class="form.trip_type === 'one_way' 
                            ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-white shadow-sm' 
                            : 'text-gray-600 dark:text-gray-400'"
                        class="flex-1 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        {{ t('flight.trip_type.one_way') }}
                    </button>
                    <button 
                        type="button" 
                        @click="form.trip_type = 'round_trip'"
                        :class="form.trip_type === 'round_trip' 
                            ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-white shadow-sm' 
                            : 'text-gray-600 dark:text-gray-400'"
                        class="flex-1 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        {{ t('flight.trip_type.round_trip') }}
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-4">
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('flight.form.from') }}</label>
                        <div class="relative">
                            <input
                                v-model="form.originName"
                                @focus="showAirportDropdown.origin = true"
                                @blur="handleOriginBlur"
                                type="text"
                                :placeholder="t('flight.form.origin_placeholder')"
                                :disabled="loading"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 pl-10 text-gray-900 dark:text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            />
                            <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </div>
                            
                            <div v-if="showAirportDropdown.origin && filteredOriginAirports.length > 0" 
                                 class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto">
                                <div v-for="airport in filteredOriginAirports" 
                                     :key="'origin-' + airport.id"
                                     @mousedown.prevent="selectAirport(airport, 'origin')"
                                     class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ airport.nama_bandara }}</div>
                                        <span class="text-xs font-medium px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ airport.kode_iata }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ airport.kota }}, {{ airport.provinsi }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center items-center lg:col-span-1 lg:justify-center lg:pt-6">
                        <button 
                            type="button"
                            @click="swapAirports"
                            class="p-2 bg-gray-100 dark:bg-gray-700 rounded-full text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            :disabled="loading"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </button>
                    </div>

                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('flight.form.to') }}</label>
                        <div class="relative">
                            <input
                                v-model="form.destinationName"
                                @focus="showAirportDropdown.destination = true"
                                @blur="handleDestinationBlur"
                                type="text"
                                :placeholder="t('flight.form.destination_placeholder')"
                                :disabled="loading"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 pl-10 text-gray-900 dark:text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            />
                            <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3" />
                                </svg>
                            </div>
                            
                            <div v-if="showAirportDropdown.destination && filteredDestinationAirports.length > 0" 
                                 class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto">
                                <div v-for="airport in filteredDestinationAirports" 
                                     :key="'dest-' + airport.id"
                                     @mousedown.prevent="selectAirport(airport, 'destination')"
                                     class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ airport.nama_bandara }}</div>
                                        <span class="text-xs font-medium px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ airport.kode_iata }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ airport.kota }}, {{ airport.provinsi }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('flight.form.departure') }}</label>
                        <input 
                            v-model="form.date" 
                            type="date" 
                            class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            :min="today"
                        />
                    </div>

                    <div v-if="form.trip_type === 'round_trip'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('flight.form.return') }}</label>
                        <input 
                            v-model="form.returnDate" 
                            type="date" 
                            class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                            :min="form.date"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('flight.form.passengers_class') }}</label>
                        <button 
                            type="button"
                            @click="passengerDropdown = !passengerDropdown"
                            class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-left flex items-center justify-between hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900 dark:text-white">{{ totalPassengers }} {{ t('flight.passengers.label') }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                    {{ getClassName(form.class) }}
                                </span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
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
                        {{ t('flight.quick_date.today') }}
                    </button>
                    <button 
                        @click="quickDateSelect(1)"
                        :class="form.date === tomorrow 
                            ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700' 
                            : 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'"
                        class="flex-shrink-0 px-4 py-2 text-sm rounded-lg border transition-colors hover:bg-blue-50 dark:hover:bg-blue-800/30"
                    >
                        {{ t('flight.quick_date.tomorrow') }}
                    </button>
                    <button 
                        @click="quickDateSelect(3)"
                        :class="form.date === new Date(Date.now() + 3 * 86400000).toISOString().split('T')[0]
                            ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700' 
                            : 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'"
                        class="flex-shrink-0 px-4 py-2 text-sm rounded-lg border transition-colors hover:bg-blue-50 dark:hover:bg-blue-800/30"
                    >
                        {{ t('flight.quick_date.days_3') }}
                    </button>
                    <button 
                        @click="quickDateSelect(7)"
                        :class="form.date === new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0]
                            ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700' 
                            : 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'"
                        class="flex-shrink-0 px-4 py-2 text-sm rounded-lg border transition-colors hover:bg-blue-50 dark:hover:bg-blue-800/30"
                    >
                        {{ t('flight.quick_date.days_7') }}
                    </button>
                </div>

                <div v-if="passengerDropdown" 
                     class="mt-4 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 lg:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ t('flight.passengers.title') }}</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('flight.passengers.adults') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('flight.passengers.adults_desc') }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            @click="updatePassengerCount('adults', 'decrement')"
                                            :disabled="form.passengers.adults <= 1"
                                            class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">{{ form.passengers.adults }}</span>
                                        <button 
                                            @click="updatePassengerCount('adults', 'increment')"
                                            :disabled="form.passengers.adults >= 9"
                                            class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('flight.passengers.children') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('flight.passengers.children_desc') }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            @click="updatePassengerCount('children', 'decrement')"
                                            :disabled="form.passengers.children <= 0"
                                            class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">{{ form.passengers.children }}</span>
                                        <button 
                                            @click="updatePassengerCount('children', 'increment')"
                                            :disabled="form.passengers.children >= 9"
                                            class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('flight.passengers.infants') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('flight.passengers.infants_desc') }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            @click="updatePassengerCount('infants', 'decrement')"
                                            :disabled="form.passengers.infants <= 0"
                                            class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">{{ form.passengers.infants }}</span>
                                        <button 
                                            @click="updatePassengerCount('infants', 'increment')"
                                            :disabled="form.passengers.infants >= 9 || form.passengers.infants >= form.passengers.adults"
                                            class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ t('flight.class.title') }}</h3>
                            <div class="space-y-3">
                                <button 
                                    type="button"
                                    @click="form.class = 'economy'"
                                    :class="form.class === 'economy' 
                                        ? 'bg-blue-50 text-blue-700 border-blue-300 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700' 
                                        : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
                                    class="w-full py-3 px-4 rounded-lg border text-sm font-medium transition-colors flex items-center justify-between"
                                >
                                    <span>{{ t('flight.class.economy') }}</span>
                                    <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200">{{ t('flight.class.label_popular') }}</span>
                                </button>
                                <button 
                                    type="button"
                                    @click="form.class = 'business'"
                                    :class="form.class === 'business' 
                                        ? 'bg-blue-50 text-blue-700 border-blue-300 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700' 
                                        : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
                                    class="w-full py-3 px-4 rounded-lg border text-sm font-medium transition-colors flex items-center justify-between"
                                >
                                    <span>{{ t('flight.class.business') }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ t('flight.class.label_comfort') }}</span>
                                </button>
                                <button 
                                    type="button"
                                    @click="form.class = 'first'"
                                    :class="form.class === 'first' 
                                        ? 'bg-blue-50 text-blue-700 border-blue-300 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700' 
                                        : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
                                    class="w-full py-3 px-4 rounded-lg border text-sm font-medium transition-colors flex items-center justify-between"
                                >
                                    <span>{{ t('flight.class.first') }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ t('flight.class.label_premium') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center">
                    <button 
                        @click="searchFlights" 
                        :disabled="searchLoading || loading || !form.originId || !form.destinationId"
                        class="w-full max-w-md bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-8 rounded-lg transition-colors flex items-center justify-center gap-2 shadow-lg hover:shadow-xl"
                    >
                        <template v-if="searchLoading">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span>{{ t('flight.button.searching') }}</span>
                        </template>
                        <template v-else>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="text-base font-semibold">{{ t('flight.button.search') }}</span>
                        </template>
                    </button>
                </div>

                <div v-if="loading" class="text-center py-4">
                    <div class="inline-block w-6 h-6 border-2 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">{{ t('flight.form.loading') }}</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ t('flight.tips.title') }}</p>
                            <ul class="text-xs text-gray-600 dark:text-gray-300 space-y-1">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500">•</span>
                                    {{ t('flight.tips.1') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500">•</span>
                                    {{ t('flight.tips.2') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500">•</span>
                                    {{ t('flight.tips.3') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-500">•</span>
                                    {{ t('flight.tips.4') }}
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
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ t('flight.routes.title') }}</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button 
                                    @click="() => { 
                                        form.originName = 'Soekarno-Hatta (CGK)'; 
                                        form.destinationName = 'Ngurah Rai (DPS)'; 
                                        form.originId = 1; 
                                        form.destinationId = 2; 
                                    }"
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left text-xs"
                                >
                                    <div class="font-medium text-gray-900 dark:text-white">CGK → DPS</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ t('flight.routes.jakarta') }} → {{ t('flight.routes.bali') }}</div>
                                </button>
                                <button 
                                    @click="() => { 
                                        form.originName = 'Juanda (SUB)'; 
                                        form.destinationName = 'Ngurah Rai (DPS)'; 
                                        form.originId = 3; 
                                        form.destinationId = 2; 
                                    }"
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left text-xs"
                                >
                                    <div class="font-medium text-gray-900 dark:text-white">SUB → DPS</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ t('flight.routes.surabaya') }} → {{ t('flight.routes.bali') }}</div>
                                </button>
                                <button 
                                    @click="() => { 
                                        form.originName = 'Soekarno-Hatta (CGK)'; 
                                        form.destinationName = 'Juanda (SUB)'; 
                                        form.originId = 1; 
                                        form.destinationId = 3; 
                                    }"
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left text-xs"
                                >
                                    <div class="font-medium text-gray-900 dark:text-white">CGK → SUB</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ t('flight.routes.jakarta') }} → {{ t('flight.routes.surabaya') }}</div>
                                </button>
                                <button 
                                    @click="() => { 
                                        form.originName = 'Hasanuddin (UPG)'; 
                                        form.destinationName = 'Ngurah Rai (DPS)'; 
                                        form.originId = 4; 
                                        form.destinationId = 2; 
                                    }"
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left text-xs"
                                >
                                    <div class="font-medium text-gray-900 dark:text-white">UPG → DPS</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ t('flight.routes.makassar') }} → {{ t('flight.routes.bali') }}</div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Custom scrollbar yang lebih sederhana */
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
</style>