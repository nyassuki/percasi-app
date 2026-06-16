<script setup>
import { ref, onMounted, onUnmounted, inject, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import socket from '../services/socket'; 
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const auth = useAuthStore();
const toast = inject('toast'); 
const swal = inject('swal');

// UI State
const showModal = ref(false);
const matchData = ref(null); 
const timeLeft = ref(0);
const hasAccepted = ref(false);
const isNavigatingToGame = ref(false); 

// Refs untuk data lawan
const B_opponent = ref("");
const W_opponent = ref("");
const opponentName = ref("");

let timerInterval = null;

// --- LOGIC TIMER ---
const startCountdown = (expireAt) => {
    if (timerInterval) clearInterval(timerInterval);

    const updateTime = () => {
        const now = Date.now();
        const diff = Math.ceil((expireAt - now) / 1000);
        timeLeft.value = diff > 0 ? diff : 0;

        if (diff <= 0) {
            clearInterval(timerInterval);
            if (!isNavigatingToGame.value) {
                showModal.value = false; 
            }
        }
    };

    updateTime(); 
    timerInterval = setInterval(updateTime, 1000);
};

// --- ACTION USER ---
const acceptMatch = () => {
    if (!matchData.value || hasAccepted.value) return;
    socket.emit('player_accept_match', { 
        matchId: matchData.value.matchId, 
        userId: auth.user.id 
    });
};

// --- HANDLERS ---

const handleMatchInvitation = (data) => {
    // Tolak invite jika sedang proses masuk game
    if (isNavigatingToGame.value) return; 
    if (Date.now() > data.expireAt) return;
    
    console.log("[Inv] Received:", data);
    matchData.value = data;
    hasAccepted.value = false; 
    showModal.value = true;
    
    B_opponent.value = data.B_opponent;
    W_opponent.value = data.W_opponent;
    // [UPDATED] Menggunakan t() untuk fallback title
    opponentName.value = data.opponentName || t('incoming_match.default_title');

    startCountdown(data.expireAt);
};

const handleMatchAcceptedStatus = () => {
    hasAccepted.value = true;
    if (!showModal.value) showModal.value = true;
};

// [FIX UTAMA: HANDLE START]
const handleMatchStart = async ({ matchId }) => {
    console.log(`[Inv] MATCH START: ${matchId}`);
    if (!matchId) return;

    // 1. KUNCI STATE SEGERA
    isNavigatingToGame.value = true;

    // 2. MATIKAN PENDENGARAN (Listener)
    // Ini mencegah event cancel yang datang telat untuk diproses
    socket.off('match_cancelled'); 

    // 3. HANCURKAN DATA MATCH (Kill Switch)
    // Ini jaminan terakhir. Jika handler cancel tetap jalan, dia akan cek matchData
    matchData.value = null; 
    
    // 4. Bersihkan UI
    if (timerInterval) clearInterval(timerInterval);
    showModal.value = false;

    await nextTick();

    // 5. Pindah Halaman
    try {
        await router.replace(`/game/${matchId}`);
    } catch (err) {
        window.location.href = `/game/${matchId}`;
    }
};

// [FIX UTAMA: HANDLE CANCEL]
const handleMatchCancelled = (data) => {
    // 1. Cek Flag Navigasi
    if (isNavigatingToGame.value) {
        console.warn("[Inv] Cancel ignored: Navigating to game.");
        return;
    }

    // 2. Cek Eksistensi Data (Penting!)
    // Jika matchData sudah null (karena handleMatchStart jalan duluan), stop disini.
    if (!matchData.value) {
        console.warn("[Inv] Cancel ignored: No active match data.");
        return;
    }

    console.log("[Inv] Cancelled:", data);
    showModal.value = false;
    matchData.value = null; // Reset data
    
    if (timerInterval) clearInterval(timerInterval);
    
    // Reset status user di backend
    if (auth.user?.id) {
        socket.emit('update_lobby_user_status', { userIds: [auth.user.id]});
    }

    //let msg = data.result === 'aborted' ? 'Pertandingan dibatalkan.' : 'Pertandingan berakhir (Timeout/WO).';
    //swal.fire({ icon: 'info', title: msg });
};

// --- LIFECYCLE ---
onMounted(() => {
    // Clean up old listeners
    socket.off('match_invitation');
    socket.off('match_accepted_status');
    socket.off('match_start');
    socket.off('tmatch_start');
    socket.off('cmatch_start');
    socket.off('match_cancelled');

    // Attach listeners
    socket.on('match_invitation', handleMatchInvitation);
    socket.on('match_accepted_status', handleMatchAcceptedStatus);
    
    // Start Handlers
    socket.on('match_start', handleMatchStart);
    socket.on('tmatch_start', handleMatchStart);
    socket.on('cmatch_start', handleMatchStart);
    
    // Cancel Handler
    socket.on('match_cancelled', handleMatchCancelled);

    if (auth.user?.id && socket.connected) {
        socket.emit('check_pending_match');
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);

    // Matikan semua listener untuk mencegah memory leak atau event zombie
    socket.off('match_invitation');
    socket.off('match_accepted_status');
    socket.off('match_cancelled');
    
    // Opsional: Matikan match_start juga jika komponen ini hancur
    // socket.off('match_start'); 
    // socket.off('tmatch_start');
    // socket.off('cmatch_start');
});
</script>
<template>
  <Transition name="pop">
    <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
      
      <div class="bg-white dark:bg-slate-900 border-2 border-teal-500 dark:border-teal-600 rounded-2xl w-full max-w-md p-6 text-center shadow-[0_0_30px_rgba(20,184,166,0.3)] relative overflow-hidden transition-colors duration-300">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gray-200 dark:bg-gray-700">
             <div class="h-full bg-teal-500 dark:bg-teal-400 transition-all duration-1000 ease-linear"
                  :style="{ width: `${(timeLeft / 30) * 100}%` }"></div>
        </div>

        <h2 class="text-2xl font-black text-gray-800 dark:text-white uppercase mb-2 tracking-wider">{{ opponentName }}</h2>
        <hr>
        <p class="text-gray-600 dark:text-gray-400   text-1xl font-black text-gray-800 dark:text-white italic uppercase mb-2 tracking-wider">
            {{ B_opponent }} VS {{ W_opponent }}
            <span class="font-bold text-teal-600 dark:text-teal-400 uppercase">{{ matchData?.role || t('incoming_match.role_default') }}</span>
        </p>

        <div class="mb-6 flex justify-center relative">
            <div class="w-24 h-24 rounded-full border-4 flex items-center justify-center text-4xl font-mono font-bold bg-gray-50 dark:bg-gray-800 shadow-inner transition-colors"
                 :class="timeLeft < 10 
                    ? 'border-red-500 text-red-500 animate-pulse' 
                    : 'border-teal-500 text-teal-600 dark:text-teal-400'">
                {{ timeLeft }}
            </div>
            <span class="absolute -bottom-2 text-xs text-gray-500 dark:text-gray-400 bg-white dark:bg-slate-900 px-2">{{ t('incoming_match.seconds') }}</span>
        </div>

        <div v-if="!hasAccepted">
            <button 
                @click="acceptMatch" 
                class="w-full bg-gradient-to-r from-teal-500 to-teal-700 hover:from-teal-400 hover:to-teal-600 text-white font-bold py-4 rounded-xl text-lg transition-all shadow-lg transform active:scale-95 mb-3 border-b-4 border-teal-800 active:border-b-0 active:mt-1">
                {{ t('incoming_match.btn_accept') }}
            </button>
            <p class="text-xs text-red-500 dark:text-red-400 mt-2 animate-pulse">{{ t('incoming_match.warning_wo') }}</p>
        </div>

        <div v-else class="py-4 bg-gray-100 dark:bg-slate-800/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 animate-pulse flex flex-col items-center justify-center gap-2">
            <div class="w-6 h-6 border-2 border-teal-500 dark:border-teal-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-teal-600 dark:text-teal-400 font-bold tracking-wide">{{ t('incoming_match.waiting') }}</p>
        </div>

      </div>
    </div>
  </Transition>
</template>

<style scoped>
.pop-enter-active, .pop-leave-active { 
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
}
.pop-enter-from, .pop-leave-to { 
    opacity: 0; 
    transform: scale(0.8) translateY(20px); 
}
</style>