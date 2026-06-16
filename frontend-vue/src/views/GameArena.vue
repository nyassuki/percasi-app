<script setup>
import { ref, onMounted, onUnmounted, computed, inject, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import socket, { connectSocket } from '../services/socket';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';
import ChessBoard from '../components/ChessBoard.vue';
import CapturedPieces from '../components/CapturedPieces.vue';
import { useI18n } from 'vue-i18n'; // [BARU]

// --- INJECTIONS ---
const { t } = useI18n(); // [BARU]
const swal = inject('swal');
const toast = inject('toast');

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const matchId = route.params.id;

// --- STATE ---
const loading = ref(true);
const isGameOver = ref(false);
const winReason = ref('');
const gameResult = ref('');
const showGameControls = ref(false);
const showGameOverModal = ref(false);
const gameOverAnimation = ref(false);
const isDrawOffered = ref(false);
const isResigning = ref(false);

// Game Data
const fen = ref(null);
const myColor = ref('white');
const whiteTime = ref(0);
const blackTime = ref(0);
const time_control_base = ref(0);
const player_time = ref(0);
const lastMoveTime = ref(null);

const uniqueBoardKey = computed(() => `${matchId}-${myColor.value}`);

// Players
const whitePlayer = ref({ name: 'White', avatar: null, rating: '?' });
const blackPlayer = ref({ name: 'Black', avatar: null, rating: '?' });

let timerInterval = null;
let gameOverAutoCloseTimer = null;

// --- COMPUTED ---
const fmtTime = (ms) => {
  if (ms <= 0) return "00:00";
  const min = Math.floor(ms / 60000);
  const sec = Math.floor((ms % 60000) / 1000);
  return `${min}:${sec.toString().padStart(2, '0')}`;
};

const getAvatar = (path, name) => {
  if (path) return `${import.meta.env.VITE_BASE_URL}/${path}`;
  return `https://ui-avatars.com/api/?name=${name || 'User'}&background=random&color=fff&size=128&bold=true`;
};

const activeTurn = computed(() => {
  if (!fen.value || fen.value === 'start') return 'white';
  const parts = fen.value.split(' ');
  return parts[1] === 'w' ? 'white' : 'black';
});

const isMyTurn = computed(() => {
  if (isGameOver.value || isResigning.value) return false;
  if (time_control_base.value > 0) {
    const currentPlayerTime = activeTurn.value === 'white' ? whiteTime.value : blackTime.value;
    if (currentPlayerTime <= 0) return false;
  }
  return activeTurn.value === myColor.value;
});

const opponentInfo = computed(() => {
  return myColor.value === 'white' ? blackPlayer.value : whitePlayer.value;
});

const myInfo = computed(() => {
  return myColor.value === 'white' ? whitePlayer.value : blackPlayer.value;
});

const opponentTime = computed(() => {
  return myColor.value === 'white' ? blackTime.value : whiteTime.value;
});

const myTime = computed(() => {
  return myColor.value === 'white' ? whiteTime.value : blackTime.value;
});

const gameResultDisplay = computed(() => {
  if (!gameResult.value) return { white: '-', black: '-' };
  if (gameResult.value === '1/2-1/2') return { white: '½', black: '½' };
  if (typeof gameResult.value === 'string' && gameResult.value.includes('-')) {
    const [white, black] = gameResult.value.split('-');
    return { white, black };
  }
  return { white: '0', black: '0' };
});

// [UPDATED] Menggunakan t()
const resultTitle = computed(() => {
  if (!gameResult.value) return t('live_game.result.finished');
  
  if (gameResult.value === '1/2-1/2') {
    return t('live_game.result.draw_title');
  }
  
  const isMyWin = (gameResult.value === '1-0' && myColor.value === 'white') || 
                  (gameResult.value === '0-1' && myColor.value === 'black');
  
  return isMyWin ? t('live_game.result.victory') : t('live_game.result.finished');
});

const resultIcon = computed(() => {
  if (!gameResult.value) return 'trophy';
  
  if (gameResult.value === '1/2-1/2') {
    return 'handshake';
  }
  
  const isMyWin = (gameResult.value === '1-0' && myColor.value === 'white') || 
                  (gameResult.value === '0-1' && myColor.value === 'black');
  
  return isMyWin ? 'trophy' : 'chess-knight';
});

// [UPDATED] Menggunakan t() dinamis berdasarkan key reason
const resultDescription = computed(() => {
  const reasonKey = winReason.value?.toString().toLowerCase() || 'unknown';
  // Cek apakah key ada di locale, jika tidak pakai default
  const localeKey = `live_game.reason.${reasonKey}`;
  return t(localeKey) !== localeKey ? t(localeKey) : t('live_game.result.default_desc');
});

// --- METHODS ---
const syncTimerWithServer = (data) => {
  let wTime = parseInt(data.white_time_ms) || player_time.value;
  let bTime = parseInt(data.black_time_ms) || player_time.value;

  if (data.status !== 'completed' && data.last_move_time) {
    const now = new Date().getTime();
    const lastMove = new Date(data.last_move_time).getTime();
    const elapsed = now - lastMove;
    const turn = data.fen.split(' ')[1];

    if (elapsed > 0) {
      if (turn === 'w') wTime -= elapsed;
      else bTime -= elapsed;
    }
    lastMoveTime.value = data.last_move_time;
  }
  whiteTime.value = Math.max(0, wTime);
  blackTime.value = Math.max(0, bTime);
};

const checkTimeout = () => {
  if (timerInterval) clearInterval(timerInterval);
  const timedOutColor = whiteTime.value <= 0 ? 'white' : 'black';

  if (!isGameOver.value) {
    isGameOver.value = true;
    winReason.value = 'timeout';
    gameResult.value = timedOutColor === 'white' ? '0-1' : '1-0';
    
    if (time_control_base.value > 0 && socket.connected) {
      socket.emit('claimTimeout', { matchId: parseInt(matchId) });
    }
    socket.emit('update_lobby_user_status', { userIds: [auth.user.id]});
    
    showGameOverWithTimer();
  }
};

const startLocalTimer = () => {
  if (timerInterval) clearInterval(timerInterval);
  if (time_control_base.value === 0 || isGameOver.value) return;

  timerInterval = setInterval(() => {
    if (isGameOver.value) {
      clearInterval(timerInterval);
      return;
    }
    if (activeTurn.value === 'white') {
      whiteTime.value -= 1000;
      if (whiteTime.value <= 0) checkTimeout();
    } else {
      blackTime.value -= 1000;
      if (blackTime.value <= 0) checkTimeout();
    }
  }, 1000);
};

const fetchMatchData = async () => {
  try {
    const res = await api.get(`/matches/${matchId}`);
    const data = res.data.data;
    
    player_time.value = parseInt(data.player_time) || 300000;
    time_control_base.value = parseInt(data.time_control_base) || player_time.value;
    
    whitePlayer.value = { 
      name: data.white_username || 'Unknown', 
      avatar: data.white_avatar, 
      rating: data.white_rating || 1200 
    };
    blackPlayer.value = { 
      name: data.black_username || 'Unknown', 
      avatar: data.black_avatar, 
      rating: data.black_rating || 1200 
    };

    myColor.value = (data.user_role === 'black') ? 'black' : 'white';
    syncTimerWithServer(data);

    if (data.status === 'completed') {
      isGameOver.value = true;
      gameResult.value = data.result;
      winReason.value = data.win_reason;
      
      setTimeout(() => {
        showGameOverWithTimer();
      }, 500);
    } else {
      startLocalTimer();
    }
    fen.value = data.fen;
  } catch (err) {
    toast.fire({ icon: 'error', title: t('live_game.toast.load_error') });
    router.push('/');
  } finally {
    loading.value = false;
  }
};

const showGameOverWithTimer = () => {
  showGameOverModal.value = true;
  setTimeout(() => {
    gameOverAnimation.value = true;
  }, 100);
  
  gameOverAutoCloseTimer = setTimeout(() => {
    handleBackToDashboard();
  }, 5000);
};

const initSocket = () => {
  connectSocket();
  if (socket.connected) {
    socket.emit('joinMatch', matchId);
    if (!loading.value) fetchMatchData();
  }

  socket.on('connect', () => {
    socket.emit('joinMatch', matchId);
    fetchMatchData();
  });

  socket.off('moveUpdate');
  socket.on('moveUpdate', async (data) => {
    if (timerInterval) clearInterval(timerInterval);
    if (!data.fen) return;
    fen.value = data.fen;
    
    const wTime = data.whiteTime ?? data.white_time ?? data.white_time_ms;
    const bTime = data.blackTime ?? data.black_time ?? data.black_time_ms;

    if (wTime !== undefined && wTime !== null) whiteTime.value = parseInt(wTime);
    if (bTime !== undefined && bTime !== null) blackTime.value = parseInt(bTime);
    
    whiteTime.value = Math.max(0, whiteTime.value);
    blackTime.value = Math.max(0, blackTime.value);

    if (data.isGameOver) {
      isGameOver.value = true;
      gameResult.value = data.result;
      winReason.value = data.winReason || 'unknown';
      showGameOverWithTimer();
    } else {
      await nextTick();
      startLocalTimer();
    }
  });

  socket.off('drawOffered');
  socket.on('drawOffered', async (data) => {
    if (data.fromUserId === auth.user.id || data.userId === auth.user.id) {
      return; 
    }
    isDrawOffered.value = false;
  
  const result = await swal.fire({
    title: t('live_game.modal.draw_title'),
    text: t('live_game.modal.draw_desc'),
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: t('live_game.modal.btn_accept'),
    cancelButtonText: t('live_game.modal.btn_decline'),
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#ef4444',
    backdrop: 'rgba(0,0,0,0.7)',
    allowOutsideClick: false
  });
  
  if (result.isConfirmed) {
    socket.emit('acceptDraw', { matchId: parseInt(matchId) });
    toast.fire({ 
      icon: 'success', 
      title: t('live_game.toast.draw_accepted'), 
      text: t('live_game.toast.contacting_server') 
    });
  } else {
    socket.emit('declineDraw', { matchId: parseInt(matchId) });
    toast.fire({ 
      icon: 'info', 
      title: t('live_game.toast.draw_declined'), 
      text: t('live_game.toast.game_continued') 
    });
  }
});

// Listener untuk pengirim tawaran
socket.off('drawOfferSent');
socket.on('drawOfferSent', () => {
  isDrawOffered.value = true; 
  showGameControls.value = false;
  toast.fire({ 
    icon: 'info', 
    title: t('live_game.toast.draw_sent'),
    text: t('live_game.toast.waiting_opponent'),
    timer: 3000,
    showConfirmButton: false
  });
});

  socket.off('DrawAccepted');
  socket.on('DrawAccepted', async (data) => {
    if (timerInterval) clearInterval(timerInterval);
    if (!isGameOver.value) {
      isGameOver.value = true;
      gameResult.value = '1/2-1/2';
      winReason.value = 'agreement';
      showGameOverWithTimer();
    }
  });

  socket.off('drawDeclined');
  socket.on('drawDeclined', () => {
    toast.fire({ 
      icon: 'info', 
      title: t('live_game.toast.draw_declined'), 
      text: t('live_game.toast.opponent_declined') 
    });
    isDrawOffered.value = false;
  });

  socket.off('resignation');
  socket.on('resignation', async (data) => {
    if (timerInterval) clearInterval(timerInterval);
    if (!isGameOver.value) {
      isGameOver.value = true;
      gameResult.value = data.result;
      winReason.value = data.winReason;
      showGameOverWithTimer();
    }
  });

  socket.off('gameFinished');
  socket.on('gameFinished', (data) => {
    if (timerInterval) clearInterval(timerInterval);
    isGameOver.value = true;
    gameResult.value = data.result;
    winReason.value = data.winReason || 'unknown';
    showGameOverWithTimer();
  });
};

const handleMove = ({ from, to, promotion }) => {
  if (isGameOver.value || !isMyTurn.value || isResigning.value) return;
  const uci = from + to + (promotion || '');
  socket.emit('playerMove', { matchId: parseInt(matchId), uciMove: uci });
};

const resignGame = async () => {
  showGameControls.value = false;
  const result = await swal.fire({
    title: t('live_game.modal.resign_title'),
    text: t('live_game.modal.resign_desc'),
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: t('live_game.modal.btn_resign'),
    cancelButtonText: t('live_game.modal.btn_continue'),
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    backdrop: 'rgba(0,0,0,0.7)',
    allowOutsideClick: false
  });
  
  if (result.isConfirmed) {
    isResigning.value = true;
    socket.emit('resign', { matchId: parseInt(matchId), userId: auth.user.id });
    toast.fire({ 
      icon: 'info', 
      title: t('live_game.toast.resigning'), 
      text: t('live_game.toast.sending_resign') 
    });
  }
};

const offerDraw = async () => {
  showGameControls.value = false;
  const result = await swal.fire({ 
    title: t('live_game.modal.draw_ask_title'), 
    text: t('live_game.modal.draw_ask_desc'),
    icon: 'info',
    showCancelButton: true, 
    confirmButtonText: t('live_game.modal.btn_offer'),
    cancelButtonText: t('live_game.modal.btn_cancel'),
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280',
    backdrop: 'rgba(0,0,0,0.7)',
    allowOutsideClick: false
  });
  
  if (result.isConfirmed) {
    socket.emit('offerDraw', { matchId: parseInt(matchId) });
  }
};

const closeGameOverModal = () => {
  if (gameOverAutoCloseTimer) {
    clearTimeout(gameOverAutoCloseTimer);
    gameOverAutoCloseTimer = null;
  }
  gameOverAnimation.value = false;
  setTimeout(() => {
    showGameOverModal.value = false;
  }, 300);
};

const handleBackToDashboard = () => {
  if (gameOverAutoCloseTimer) {
    clearTimeout(gameOverAutoCloseTimer);
    gameOverAutoCloseTimer = null;
  }
  router.push('/dashboard');
};

const handleAnalyzeGame = () => {
  if (gameOverAutoCloseTimer) {
    clearTimeout(gameOverAutoCloseTimer);
    gameOverAutoCloseTimer = null;
  }
  toast.fire({ 
    icon: 'info', 
    title: t('live_game.controls.analyze'), 
    text: t('live_game.toast.analyze_info')
  });
};

onMounted(async () => {
  fen.value = null;
  loading.value = true;
  await fetchMatchData();
  initSocket();
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  if (gameOverAutoCloseTimer) clearTimeout(gameOverAutoCloseTimer);
  if (socket) {
    socket.off('moveUpdate');
    socket.off('gameFinished');
    socket.off('drawOffered');
    socket.off('DrawAccepted');
    socket.off('drawDeclined');
    socket.off('resignation');
    socket.off('drawOfferSent');
    socket.emit('leaveMatch', matchId);
  }
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-gray-100 dark:from-slate-900 dark:to-gray-950 flex flex-col transition-colors duration-300 font-sans pb-safe">
    <div class="sticky top-0 z-50 bg-white/90 dark:bg-slate-900/90 px-4 py-3 backdrop-blur-xl border-b border-gray-200/50 dark:border-slate-800/50">
      <div class="flex items-center justify-between">
        <button @click="router.push('/dashboard')" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition-all active:scale-95">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>
        
        <div class="flex flex-col items-center">
          <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('live_game.header.live_match') }}</div>
          <div class="flex items-center gap-2 mt-1">
            <div class="w-2 h-2 rounded-full animate-pulse" 
                 :class="isGameOver ? 'bg-gray-400' : (isMyTurn ? 'bg-green-500' : 'bg-amber-500')"></div>
            <span class="text-xs font-semibold" 
                  :class="isGameOver ? 'text-gray-600 dark:text-gray-400' : (isMyTurn ? 'text-green-600' : 'text-amber-600')">
              {{ isGameOver ? t('live_game.header.finished') : (isMyTurn ? t('live_game.header.your_turn') : t('live_game.header.opponent_turn')) }}
            </span>
          </div>
        </div>

        <button @click="showGameControls = !showGameControls" 
                class="p-2.5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition-all active:scale-95">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
          </svg>
        </button>
      </div>
    </div>

    <div v-if="loading" class="flex-1 flex flex-col items-center justify-center px-4">
      <div class="relative">
        <div class="w-16 h-16 border-4 border-indigo-200 dark:border-indigo-800 border-t-indigo-600 dark:border-t-indigo-500 rounded-full animate-spin mb-6"></div>
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full animate-pulse"></div>
        </div>
      </div>
      <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mt-4 animate-pulse">{{ t('live_game.loading') }}</p>
    </div>

    <div v-else class="flex-1 flex flex-col px-4 py-4 max-w-md mx-auto w-full">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-lg border border-gray-100 dark:border-slate-700 mb-4 transition-all duration-300"
           :class="activeTurn !== myColor && !isGameOver ? 'ring-2 ring-amber-400/30 shadow-amber-500/10' : ''">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="relative">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-200 to-gray-300 dark:from-slate-700 dark:to-gray-800 flex items-center justify-center overflow-hidden">
                <img :src="getAvatar(opponentInfo.avatar, opponentInfo.name)" 
                     class="w-full h-full object-cover"
                     :alt="opponentInfo.name">
              </div>
              <div v-if="activeTurn !== myColor && !isGameOver" 
                   class="absolute -bottom-1 -right-1 w-3 h-3 bg-amber-500 rounded-full border-2 border-white dark:border-slate-800 animate-pulse"></div>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <h3 class="font-bold text-gray-800 dark:text-white truncate max-w-[140px]">
                  {{ opponentInfo.name }}
                </h3>
                <span class="text-xs font-bold bg-gradient-to-r from-amber-500 to-orange-500 text-white px-2 py-0.5 rounded-full">
                  {{ opponentInfo.rating }}
                </span>
              </div>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ myColor === 'white' ? t('live_game.player.black') : t('live_game.player.white') }}
                </span>
                <div v-if="activeTurn !== myColor && !isGameOver" 
                     class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full">
                  {{ t('live_game.player.moving') }}
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex flex-col items-end">
            <div class="text-2xl font-mono font-bold" 
                 :class="opponentTime < 30000 ? 'text-red-500 animate-pulse' : 'text-gray-700 dark:text-gray-300'">
              {{ fmtTime(opponentTime) }}
            </div>
            <div class="w-24 h-1.5 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden mt-2">
              <div class="h-full transition-all duration-300" 
                   :class="opponentTime < 30000 ? 'bg-gradient-to-r from-red-500 to-pink-500' : 'bg-gradient-to-r from-slate-500 to-gray-600'"
                   :style="{ width: `${(opponentTime / player_time) * 100}%` }"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="px-2 mb-2">
        <CapturedPieces v-if="fen" 
                        :fen="fen" 
                        :capturing-color="myColor === 'white' ? 'black' : 'white'"
                        class="opacity-80" />
      </div>

      <div class="relative bg-gradient-to-br from-slate-100 to-gray-200 dark:from-slate-800 dark:to-gray-900 rounded-3xl p-3 shadow-2xl mb-4">
        <div v-if="isDrawOffered && !isGameOver" 
             class="absolute top-4 left-1/2 transform -translate-x-1/2 z-10">
          <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-2 rounded-full shadow-lg flex items-center gap-2 animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-semibold">{{ t('live_game.overlay.waiting_draw') }}</span>
          </div>
        </div>

        <div v-if="isResigning && !isGameOver" 
             class="absolute top-4 left-1/2 transform -translate-x-1/2 z-10">
          <div class="bg-gradient-to-r from-rose-500 to-pink-600 text-white px-4 py-2 rounded-full shadow-lg flex items-center gap-2 animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-semibold">{{ t('live_game.overlay.sending_resign') }}</span>
          </div>
        </div>

        <div v-if="isGameOver && !showGameOverModal" 
             class="absolute top-4 left-1/2 transform -translate-x-1/2 z-10">
          <div class="bg-gradient-to-r from-rose-500 to-pink-600 text-white px-4 py-2 rounded-full shadow-lg flex items-center gap-2 animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="text-sm font-semibold">{{ t('live_game.overlay.game_over') }}</span>
          </div>
        </div>

        <div class="aspect-square w-full">
          <ChessBoard  
            v-if="fen"
            :key="uniqueBoardKey"
            :fen="fen"  
            :orientation="myColor"  
            :isInteractable="!isGameOver && isMyTurn && !isDrawOffered && !isResigning"  
            @move="handleMove"  
            class="w-full h-full rounded-2xl overflow-hidden shadow-inner"
          />
        </div>
      </div>

      <div class="px-2 mt-2">
        <CapturedPieces v-if="fen" 
                        :fen="fen" 
                        :capturing-color="myColor"
                        class="opacity-80" />
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-lg border border-gray-100 dark:border-slate-700 mt-4 transition-all duration-300"
           :class="activeTurn === myColor && !isGameOver ? 'ring-2 ring-emerald-400/30 shadow-emerald-500/10' : ''">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="relative">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-200 to-teal-300 dark:from-emerald-800 dark:to-teal-900 flex items-center justify-center overflow-hidden">
                <img :src="getAvatar(myInfo.avatar, myInfo.name)" 
                     class="w-full h-full object-cover"
                     :alt="myInfo.name">
              </div>
              <div v-if="activeTurn === myColor && !isGameOver" 
                   class="absolute -bottom-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-800 animate-pulse"></div>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <h3 class="font-bold text-gray-800 dark:text-white truncate max-w-[140px]">
                  {{ myInfo.name }} <span class="text-emerald-600 dark:text-emerald-400">({{ t('live_game.player.you') }})</span>
                </h3>
                <span class="text-xs font-bold bg-gradient-to-r from-emerald-500 to-teal-500 text-white px-2 py-0.5 rounded-full">
                  {{ myInfo.rating }}
                </span>
              </div>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ myColor === 'white' ? t('live_game.player.white') : t('live_game.player.black') }}
                </span>
                <div v-if="activeTurn === myColor && !isGameOver" 
                     class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full animate-pulse">
                  {{ t('live_game.header.your_turn') }}
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex flex-col items-end">
            <div class="text-2xl font-mono font-bold" 
                 :class="myTime < 30000 ? 'text-red-500 animate-pulse' : 'text-gray-700 dark:text-gray-300'">
              {{ fmtTime(myTime) }}
            </div>
            <div class="w-24 h-1.5 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden mt-2">
              <div class="h-full transition-all duration-300" 
                   :class="myTime < 30000 ? 'bg-gradient-to-r from-red-500 to-pink-500' : 'bg-gradient-to-r from-emerald-500 to-teal-600'"
                   :style="{ width: `${(myTime / player_time) * 100}%` }"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showGameControls" 
           class="fixed inset-0 z-50 flex items-end bg-black/50 backdrop-blur-sm transition-opacity"
           @click="showGameControls = false">
        <div class="w-full bg-white dark:bg-slate-800 rounded-t-3xl p-6 shadow-2xl animate-slide-up"
             @click.stop>
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ t('live_game.controls.title') }}</h3>
            <button @click="showGameControls = false" 
                    class="p-2 rounded-lg bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="grid grid-cols-2 gap-3">
            <button @click="offerDraw" 
                    :disabled="isGameOver || isDrawOffered"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
              <div class="text-2xl mb-2">🤝</div>
              <span class="font-semibold text-blue-600 dark:text-blue-400 text-sm">{{ t('live_game.controls.offer_draw') }}</span>
              <span v-if="isDrawOffered" class="text-xs text-amber-500 dark:text-amber-400 mt-1">{{ t('live_game.controls.draw_sent') }}</span>
              <span v-else class="text-xs text-blue-500/70 dark:text-blue-400/70 mt-1">{{ t('live_game.controls.draw_label') }}</span>
            </button>
            
            <button @click="resignGame" 
                    :disabled="isGameOver || isResigning"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border border-red-100 dark:border-red-800 hover:shadow-md transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
              <div class="text-2xl mb-2">🏳️</div>
              <span class="font-semibold text-red-600 dark:text-red-400 text-sm">{{ t('live_game.controls.resign') }}</span>
              <span v-if="isResigning" class="text-xs text-amber-500 dark:text-amber-400 mt-1">{{ t('live_game.controls.resigning') }}</span>
              <span v-else class="text-xs text-red-500/70 dark:text-red-400/70 mt-1">{{ t('live_game.controls.resign_label') }}</span>
            </button>
          </div>
          
          <div class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
            <button @click="router.push('/dashboard')" 
                    class="w-full py-3.5 bg-gradient-to-r from-slate-100 to-gray-200 dark:from-slate-700 dark:to-gray-800 hover:from-slate-200 hover:to-gray-300 dark:hover:from-slate-600 dark:hover:to-gray-700 rounded-xl font-semibold text-gray-700 dark:text-gray-300 transition-all active:scale-98">
              {{ t('live_game.controls.back_dashboard') }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="showGameOverModal" 
           class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-md transition-opacity duration-300"
             @click="closeGameOverModal"></div>
        
        <div class="relative w-full max-w-md bg-gradient-to-br from-white to-gray-50 dark:from-slate-800 dark:to-gray-900 rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-slate-700 transition-all duration-300"
             :class="gameOverAnimation ? 'scale-100 opacity-100' : 'scale-95 opacity-0'">
          
          <div class="absolute top-4 right-4 z-10">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-center">
              <span class="text-white font-bold text-sm">{{ gameOverAutoCloseTimer ? 5 : 0 }}</span>
            </div>
          </div>
          
          <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8 text-center">
            <div class="text-5xl mb-4">
              <span v-if="resultIcon === 'trophy'">🏆</span>
              <span v-else-if="resultIcon === 'handshake'">🤝</span>
              <span v-else>♞</span>
            </div>
            <h2 class="text-2xl font-bold text-white">{{ resultTitle }}</h2>
            <p class="text-white/80 mt-2 text-sm">{{ resultDescription }}</p>
          </div>
          
          <div class="px-6 py-8">
            <div class="flex items-center justify-center gap-8 mb-6">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-white to-gray-100 dark:from-slate-700 dark:to-gray-800 flex items-center justify-center shadow-lg border border-gray-200 dark:border-slate-600 mb-3">
                  <span class="text-3xl font-black" 
                        :class="gameResultDisplay.white === '1' ? 'text-green-500' : 'text-gray-700 dark:text-gray-300'">
                    {{ gameResultDisplay.white }}
                  </span>
                </div>
                <div class="text-center">
                  <div class="font-semibold text-gray-800 dark:text-white text-sm">{{ whitePlayer.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('live_game.player.white') }}</div>
                </div>
              </div>
              
              <div class="flex flex-col items-center">
                <div class="text-gray-400 font-light text-xl mb-1">VS</div>
                <div class="w-12 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent dark:via-gray-600"></div>
              </div>
              
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-800 to-gray-900 flex items-center justify-center shadow-lg border border-gray-900 mb-3">
                  <span class="text-3xl font-black"
                        :class="gameResultDisplay.black === '1' ? 'text-green-500' : 'text-gray-300'">
                    {{ gameResultDisplay.black }}
                  </span>
                </div>
                <div class="text-center">
                  <div class="font-semibold text-gray-800 dark:text-white text-sm">{{ blackPlayer.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('live_game.player.black') }}</div>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-slate-700/50 rounded-2xl p-4 mb-6">
              <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                  <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ t('live_game.info.duration') }}</div>
                  <div class="font-semibold text-gray-800 dark:text-white">{{ fmtTime(player_time) }}</div>
                </div>
                <div class="text-center">
                  <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ t('live_game.info.mode') }}</div>
                  <div class="font-semibold text-gray-800 dark:text-white">
                    {{ time_control_base > 0 ? t('live_game.info.with_time') : t('live_game.info.no_time') }}
                  </div>
                </div>
              </div>
            </div>
            
            <div class="space-y-3">
              <button @click="handleBackToDashboard"
                      class="w-full py-4 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-emerald-500/25 active:scale-[0.98] flex items-center justify-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ t('live_game.controls.back_dashboard') }}
              </button>
              
              <button @click="handleAnalyzeGame"
                      class="w-full py-3.5 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 hover:from-indigo-100 hover:to-purple-100 dark:hover:from-indigo-800/30 dark:hover:to-purple-800/30 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 font-semibold rounded-xl transition-all duration-300 active:scale-[0.98] flex items-center justify-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                {{ t('live_game.controls.analyze') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!isGameOver && !showGameControls && !showGameOverModal" class="fixed bottom-6 right-4 z-40">
        <button @click="showGameControls = true"
                class="p-4 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-xl hover:shadow-2xl transition-all hover:scale-105 active:scale-95">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes slide-up {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes scale-in {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}

.scale-100 {
  transform: scale(1);
}

.scale-95 {
  transform: scale(0.95);
}

.opacity-100 {
  opacity: 1;
}

.opacity-0 {
  opacity: 0;
}

.transition-all {
  transition: all 0.3s ease;
}

.pb-safe {
  padding-bottom: calc(env(safe-area-inset-bottom) + 1rem);
}

/* Smooth scroll and touch */
* {
  -webkit-tap-highlight-color: transparent;
}

/* Better touch targets */
button {
  min-height: 44px;
  min-width: 44px;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: rgba(100, 116, 139, 0.3);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(100, 116, 139, 0.5);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .aspect-square {
    aspect-ratio: 1 / 1;
    max-height: 80vh;
  }
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.3);
  }
}
</style>