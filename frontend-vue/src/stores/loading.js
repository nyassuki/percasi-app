import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLoadingStore = defineStore('loading', () => {
    const isLoading = ref(false);

    function start() {
        isLoading.value = true;
    }

    function finish() {
        // Beri sedikit delay agar tidak 'berkedip' terlalu cepat
        setTimeout(() => {
            isLoading.value = false;
        }, 300); 
    }

    return { isLoading, start, finish };
});