<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { Chess } from 'chess.js';
import api from '../services/api';
import ChessBoard from '../components/ChessBoard.vue';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n'; // [BARU]

// --- CONFIG ---
const VITE_BASE_URL = import.meta.env.VITE_BASE_URL;
const BASE_TIME_MS = 300000;

// --- STATE ---
const { t } = useI18n(); // [BARU]
const router = useRouter();
const auth = useAuthStore();
const chess = ref(new Chess());

const fen = ref('start');
const myColor = ref('white');
const botLoading = ref(false);
const isGameOver = ref(false);
const result = ref(null);
const lastUserMove = ref('');
const boardKey = ref(0);

// STATE HIGHLIGHT BOT
const lastBotMoveSquares = ref([]);

// STATE DIFFICULTY
const showDifficultyModal = ref(true);
const botDifficulty = ref(10);

// [UPDATED] Menggunakan computed agar reaktif terhadap ganti bahasa
const difficultyLevels = computed(() => [
  { label: t('bot_game.difficulty.easy'), elo: '~800', value: 1, color: 'bg-gradient-to-r from-teal-500 to-emerald-600', icon: '😴' },
  { label: t('bot_game.difficulty.medium'), elo: '~1200', value: 5, color: 'bg-gradient-to-r from-blue-500 to-cyan-600', icon: '🤔' },
  { label: t('bot_game.difficulty.hard'), elo: '~1800', value: 10, color: 'bg-gradient-to-r from-amber-500 to-orange-600', icon: '🧠' },
  { label: t('bot_game.difficulty.gm'), elo: '3000+', value: 20, color: 'bg-gradient-to-r from-red-600 to-pink-700', icon: '👑' }
]);

// Timer State
const userTime = ref(BASE_TIME_MS);
const botTime = ref(BASE_TIME_MS);
let timerInterval = null;

// Game Stats
const userMoves = ref(0);
const botMoves = ref(0);
const gameStartTime = ref(null);
const gameDuration = ref('00:00');

// Mobile State
const showSidebar = ref(false);
const isMobile = ref(window.innerWidth < 768);

// --- COMPUTED ---
const activeTurn = computed(() => chess.value.turn() === 'w' ? 'white' : 'black');

const isMyTurn = computed(() => {
  return !showDifficultyModal.value && activeTurn.value === myColor.value && !isGameOver.value && !botLoading.value;
});

const fmtTime = (ms) => {
  if (ms <= 0) return "00:00";
  const min = Math.floor(ms / 60000);
  const sec = Math.floor((ms % 60000) / 1000);
  return `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
};

const getAvatar = () => {
  if (auth.user?.avatar_url) return `${auth.user.avatar_url}`;
  return `https://ui-avatars.com/api/?name=${auth.user?.username || 'User'}&background=random`;
};

const getDifficultyLabel = () => {
  const level = difficultyLevels.value.find(l => l.value === botDifficulty.value);
  return level?.label || t('bot_game.difficulty.medium');
};

const getDifficultyColor = () => {
  const level = difficultyLevels.value.find(l => l.value === botDifficulty.value);
  return level?.color || 'bg-blue-500';
};

// --- METHODS ---
const selectDifficulty = (levelValue) => {
  botDifficulty.value = levelValue;
  showDifficultyModal.value = false;
  gameStartTime.value = Date.now();
  startTimer();
  startGameTimer();
  if (myColor.value === 'black') processBotMove();
};

const startTimer = () => {
  if (timerInterval) clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    if (isGameOver.value || showDifficultyModal.value) return;
    if (activeTurn.value === myColor.value) {
      userTime.value -= 1000;
      if (userTime.value <= 0) handleGameOver('0-1', 'time_out');
    } else {
      botTime.value -= 1000;
      if (botTime.value <= 0) handleGameOver('1-0', 'bot_time_out');
    }
  }, 1000);
};

const startGameTimer = () => {
  const gameTimer = setInterval(() => {
    if (isGameOver.value || showDifficultyModal.value) {
      clearInterval(gameTimer);
      return;
    }
    const now = Date.now();
    const diff = now - gameStartTime.value;
    const min = Math.floor(diff / 60000);
    const sec = Math.floor((diff % 60000) / 1000);
    gameDuration.value = `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
  }, 1000);
};

const stopTimer = () => {
  if (timerInterval) clearInterval(timerInterval);
};

const handleGameOver = (res, reasonKey) => {
  isGameOver.value = true;
  // Jika reasonKey adalah key terjemahan, gunakan t(). Jika tidak, tampilkan raw text.
  const translatedReason = t(`bot_game.result.${reasonKey}`) !== `bot_game.result.${reasonKey}` 
      ? t(`bot_game.result.${reasonKey}`) 
      : reasonKey;
      
  result.value = translatedReason;
  stopTimer();
};

const processBotMove = async () => {
  if (isGameOver.value) return;
  botLoading.value = true;
  try {
    const res = await api.post('/bot/move', {
      fen: chess.value.fen(),
      userMove: lastUserMove.value || '',
      difficulty: botDifficulty.value
    });
    
    const data = res.data;
    if (data.bestMove) {
      chess.value.move(data.bestMove);
      fen.value = chess.value.fen();
      botMoves.value++;

      const moveStr = data.bestMove;
      const fromSquare = moveStr.substring(0, 2);
      const toSquare = moveStr.substring(2, 4);
      lastBotMoveSquares.value = [fromSquare, toSquare];
    }
    
    if (data.isGameOver || chess.value.isGameOver()) {
      let reasonKey = 'checkmate';
      if (chess.value.isDraw()) reasonKey = 'draw';
      handleGameOver(data.result || 'Game Over', reasonKey);
    }
  } catch (err) {
    chess.value.undo();
    fen.value = chess.value.fen();
  } finally {
    botLoading.value = false;
  }
};

const handleUserMove = async ({ from, to, promotion }) => {
  if (!isMyTurn.value) return;
  
  lastBotMoveSquares.value = [];

  let moveUci = from + to;
  if (promotion) moveUci += promotion;

  try {
    const moveResult = chess.value.move({ from, to, promotion: promotion || 'q' });
    if (!moveResult) return;

    lastUserMove.value = moveResult.uci;
    fen.value = chess.value.fen();
    userMoves.value++;

    if (chess.value.isGameOver()) {
      let r = '1/2-1/2';
      let reasonKey = 'draw';
      if (chess.value.isCheckmate()) {
        r = (chess.value.turn() === 'w' ? '0-1' : '1-0');
        reasonKey = 'checkmate';
      }
      handleGameOver(r, reasonKey);
      return;
    }
    setTimeout(() => { processBotMove(); }, 250);
  } catch (e) {
    fen.value = chess.value.fen();
  }
};

const resetGame = () => {
  stopTimer();
  chess.value = new Chess();
  fen.value = chess.value.fen();
  isGameOver.value = false;
  result.value = null;
  botLoading.value = false;
  userTime.value = BASE_TIME_MS;
  botTime.value = BASE_TIME_MS;
  boardKey.value++;
  userMoves.value = 0;
  botMoves.value = 0;
  gameDuration.value = '00:00';
  
  lastBotMoveSquares.value = [];
  
  showDifficultyModal.value = true;
};

const changeColor = () => {
  if (confirm(t('bot_game.confirm_reset'))) {
    myColor.value = myColor.value === 'white' ? 'black' : 'white';
    stopTimer();
    chess.value = new Chess();
    fen.value = chess.value.fen();
    isGameOver.value = false;
    userTime.value = BASE_TIME_MS;
    botTime.value = BASE_TIME_MS;
    boardKey.value++;
    userMoves.value = 0;
    botMoves.value = 0;
    gameDuration.value = '00:00';
    
    lastBotMoveSquares.value = [];

    showDifficultyModal.value = true;
  }
};

const handleResize = () => {
  isMobile.value = window.innerWidth < 768;
};

// --- LIFECYCLE ---
onMounted(() => {
  fen.value = 'start';
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  stopTimer();
  window.removeEventListener('resize', handleResize);
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-gray-900 to-black dark:from-slate-950 dark:via-gray-950 dark:to-black">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-gray-900/90 backdrop-blur-lg border-b border-gray-800">
      <div class="px-4 py-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <button @click="router.back()" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>
            <div>
              <h1 class="text-lg font-bold text-white">{{ t('bot_game.title') }}</h1>
              <p class="text-xs text-gray-400">{{ getDifficultyLabel() }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button @click="showSidebar = !showSidebar" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="relative pt-16 lg:pt-0">
      <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
        <div class="lg:hidden px-2 py-3">
          <div class="grid grid-cols-4 gap-2">
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 text-center border border-gray-700">
              <div class="text-xs text-gray-400 mb-1">{{ t('bot_game.level') }}</div>
              <div class="text-sm font-bold text-amber-400">{{ botDifficulty }}</div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 text-center border border-gray-700">
              <div class="text-xs text-gray-400 mb-1">{{ t('bot_game.turn') }}</div>
              <div class="text-sm font-bold text-teal-400">{{ isMyTurn ? t('bot_game.you') : 'Bot' }}</div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 text-center border border-gray-700">
              <div class="text-xs text-gray-400 mb-1">{{ t('bot_game.duration') }}</div>
              <div class="text-sm font-bold text-white font-mono">{{ gameDuration }}</div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 text-center border border-gray-700">
              <div class="text-xs text-gray-400 mb-1">{{ t('bot_game.moves') }}</div>
              <div class="text-sm font-bold text-blue-400">{{ userMoves + botMoves }}</div>
            </div>
          </div>
        </div>

        <div class="grid lg:grid-cols-5 gap-4 lg:gap-6 p-2 lg:p-4">
          <div class="lg:col-span-1 hidden lg:block">
            <div class="sticky top-6 space-y-6">
              <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-5 border border-gray-700 shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                  <div class="relative">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center text-2xl shadow-inner border border-gray-600">
                      🤖
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-r from-red-600 to-pink-700 flex items-center justify-center border-2 border-gray-900">
                      <span class="text-xs font-bold text-white">{{ botDifficulty }}</span>
                    </div>
                  </div>
                  <div>
                    <h3 class="font-bold text-white">{{ t('bot_game.bot_name') }}</h3>
                    <p class="text-xs text-gray-400">{{ getDifficultyLabel() }}</p>
                  </div>
                </div>
                
                <div class="space-y-4">
                  <div>
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                      <span>{{ t('bot_game.time_remaining') }}</span>
                      <span>{{ fmtTime(botTime) }}</span>
                    </div>
                    <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                      <div 
                        :class="botTime <= 60000 ? 'bg-gradient-to-r from-red-500 to-pink-600' : 'bg-gradient-to-r from-blue-500 to-cyan-600'"
                        class="h-full transition-all duration-300"
                        :style="{ width: `${(botTime / BASE_TIME_MS) * 100}%` }"
                      ></div>
                    </div>
                  </div>
                  
                  <div class="pt-3 border-t border-gray-700">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2">
                        <div v-if="activeTurn === 'black'" class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></div>
                        <span class="text-sm text-gray-300">
                          {{ activeTurn === 'black' ? t('bot_game.moving') : t('bot_game.waiting') }}
                        </span>
                      </div>
                      <span class="text-xs text-amber-400">{{ botMoves }} {{ t('bot_game.moves') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-5 border border-gray-700">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">{{ t('bot_game.game_stats') }}</h4>
                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">{{ t('bot_game.duration') }}</span>
                    <span class="font-mono text-sm text-white">{{ gameDuration }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">{{ t('bot_game.your_moves') }}</span>
                    <span class="text-sm text-blue-400 font-bold">{{ userMoves }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">{{ t('bot_game.bot_moves') }}</span>
                    <span class="text-sm text-red-400 font-bold">{{ botMoves }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">{{ t('bot_game.total_moves') }}</span>
                    <span class="text-sm text-white font-bold">{{ userMoves + botMoves }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="lg:col-span-3">
            <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-2 sm:p-3 rounded-2xl lg:rounded-3xl shadow-2xl border border-gray-700">
              <div class="relative aspect-square max-w-2xl mx-auto">
                <div v-if="botLoading" class="absolute inset-0 bg-black/60 backdrop-blur-sm z-20 rounded-xl flex items-center justify-center">
                  <div class="bg-gray-800/90 px-6 py-4 rounded-xl shadow-2xl border border-gray-700 flex items-center gap-4 animate-fade-in">
                    <div class="w-8 h-8 border-2 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
                    <div class="text-left">
                      <div class="text-base font-bold text-white">{{ t('bot_game.bot_thinking') }}</div>
                      <div class="text-xs text-gray-400 mt-1">{{ t('bot_game.level') }} {{ botDifficulty }} • {{ t('bot_game.analyzing') }}</div>
                    </div>
                  </div>
                </div>

                <div v-if="showDifficultyModal" class="absolute inset-0 bg-black/80 backdrop-blur-lg z-30 flex items-center justify-center p-4 rounded-xl">
                  <div class="bg-gray-800 border border-gray-700 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-scale-in">
                    <div class="p-6">
                      <div class="text-center mb-6">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-teal-500 to-blue-600 flex items-center justify-center text-3xl">
                          🤖
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">{{ t('bot_game.select_level') }}</h3>
                        <p class="text-sm text-gray-400">{{ t('bot_game.challenge_desc') }}</p>
                      </div>
                      
                      <div class="space-y-3">
                        <button 
                          v-for="level in difficultyLevels" 
                          :key="level.value"
                          @click="selectDifficulty(level.value)"
                          :class="level.color"
                          class="group relative w-full overflow-hidden text-white font-bold py-4 px-5 rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 active:scale-95 flex justify-between items-center"
                        >
                          <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ level.icon }}</span>
                            <div class="text-left">
                              <div class="text-base font-semibold">{{ level.label }}</div>
                              <div class="text-xs opacity-90">{{ level.elo }}</div>
                            </div>
                          </div>
                          <span class="text-white/90 text-sm bg-black/20 px-3 py-1 rounded-full">{{ t('bot_game.level') }} {{ level.value }}</span>
                          <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="isGameOver" class="absolute inset-0 bg-black/80 backdrop-blur-lg z-20 flex items-center justify-center p-4 rounded-xl">
                  <div class="bg-gray-800 border border-gray-700 rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden animate-scale-in">
                    <div class="p-6 text-center">
                      <div class="text-5xl mb-4 animate-bounce">
                        {{ result?.includes('Menang') || result?.includes('Win') || (result?.includes('1-0') && myColor === 'white') ? '🏆' : 
                           result?.includes('Remis') || result?.includes('Draw') ? '🤝' : '💀' }}
                      </div>
                      <h2 class="text-2xl font-bold text-white mb-2">{{ t('bot_game.game_over') }}</h2>
                      <p class="text-lg font-semibold text-gray-300 mb-1">{{ result }}</p>
                      <div class="text-sm text-gray-400 mb-6">
                        {{ gameDuration }} • {{ userMoves + botMoves }} {{ t('bot_game.moves') }}
                      </div>
                      <div class="grid grid-cols-2 gap-3">
                        <button @click="resetGame" class="bg-gradient-to-r from-teal-600 to-emerald-700 text-white px-4 py-3 rounded-lg font-bold hover:shadow-lg transition-all hover:-translate-y-0.5 active:scale-95">
                          {{ t('bot_game.play_again') }}
                        </button>
                        <button @click="router.push('/dashboard')" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-3 rounded-lg font-bold transition-all hover:-translate-y-0.5 active:scale-95">
                          {{ t('bot_game.dashboard') }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <ChessBoard 
                  :key="boardKey"
                  :fen="fen" 
                  :orientation="myColor" 
                  :isInteractable="isMyTurn" 
                  :highlightSquares="lastBotMoveSquares"
                  @move="handleUserMove" 
                  class="w-full h-full"
                />
              </div>

              <div class="lg:hidden mt-4">
                <div class="grid grid-cols-3 gap-2">
                  <button @click="resetGame" class="bg-gray-800 hover:bg-gray-700 text-gray-300 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                    <span class="text-lg">🔄</span>
                    <span class="text-sm">{{ t('bot_game.reset') }}</span>
                  </button>
                  <button @click="changeColor" class="bg-gray-800 hover:bg-gray-700 text-gray-300 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                    <span class="text-lg">🎨</span>
                    <span class="text-sm">{{ t('bot_game.change_color') }}</span>
                  </button>
                  <button @click="showSidebar = true" class="bg-gradient-to-r from-teal-600 to-emerald-700 text-white py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                    <span class="text-lg">📊</span>
                    <span class="text-sm">{{ t('bot_game.stats') }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="lg:col-span-1 hidden lg:block">
            <div class="sticky top-6 space-y-6">
              <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-5 border border-gray-700 shadow-xl"
                   :class="isMyTurn ? 'ring-2 ring-teal-500/50 shadow-teal-500/20' : ''">
                <div class="flex items-center gap-3 mb-4">
                  <div class="relative">
                    <img :src="getAvatar()" class="w-14 h-14 rounded-xl border-2 border-gray-600 object-cover shadow-md" />
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-r from-teal-600 to-emerald-700 flex items-center justify-center border-2 border-gray-900">
                      <span class="text-xs font-bold text-white">{{ auth.user?.rating || 1200 }}</span>
                    </div>
                  </div>
                  <div>
                    <h3 class="font-bold text-white">{{ auth.user?.username || t('bot_game.you') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-xs bg-gray-700 text-gray-300 px-2 py-0.5 rounded">
                        {{ myColor === 'white' ? t('bot_game.white') : t('bot_game.black') }}
                      </span>
                      <span v-if="isMyTurn" class="text-xs bg-gradient-to-r from-teal-600 to-emerald-700 text-white px-2 py-0.5 rounded animate-pulse">
                        {{ t('bot_game.your_turn') }}
                      </span>
                    </div>
                  </div>
                </div>
                
                <div class="space-y-4">
                  <div>
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                      <span>{{ t('bot_game.time_remaining') }}</span>
                      <span>{{ fmtTime(userTime) }}</span>
                    </div>
                    <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                      <div 
                        :class="userTime <= 60000 ? 'bg-gradient-to-r from-red-500 to-pink-600' : 'bg-gradient-to-r from-emerald-500 to-teal-600'"
                        class="h-full transition-all duration-300"
                        :style="{ width: `${(userTime / BASE_TIME_MS) * 100}%` }"
                      ></div>
                    </div>
                  </div>
                  
                  <div class="pt-3 border-t border-gray-700">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2">
                        <div v-if="isMyTurn" class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></div>
                        <span class="text-sm text-gray-300">
                          {{ isMyTurn ? t('bot_game.your_turn') : t('bot_game.wait_turn') }}
                        </span>
                      </div>
                      <span class="text-xs text-blue-400">{{ userMoves }} {{ t('bot_game.moves') }}</span>
                    </div>
                  </div>
                </div>
                
                <div class="mt-6 space-y-2">
                  <button @click="resetGame" class="w-full bg-gray-700 hover:bg-gray-600 text-gray-300 py-2.5 rounded-lg font-medium transition flex items-center justify-center gap-2">
                    <span>🔄</span> {{ t('bot_game.reset') }}
                  </button>
                  <button @click="changeColor" class="w-full bg-gray-700 hover:bg-gray-600 text-gray-300 py-2.5 rounded-lg font-medium transition flex items-center justify-center gap-2">
                    <span>🎨</span> {{ t('bot_game.change_color') }}
                  </button>
                </div>
              </div>

              <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-5 border border-gray-700">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">{{ t('bot_game.game_controls') }}</h4>
                <div class="space-y-3">
                  <button @click="router.push('/dashboard')" class="w-full bg-gray-700 hover:bg-gray-600 text-white py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ t('bot_game.dashboard') }}
                  </button>
                  <button @click="router.push('/tournaments')" class="w-full bg-gradient-to-r from-teal-600 to-emerald-700 text-white py-2.5 rounded-lg font-medium hover:shadow-lg transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <span>🏆</span>
                    {{ t('bot_game.tournament') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showSidebar" class="lg:hidden fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showSidebar = false"></div>
      <div class="absolute right-0 top-0 bottom-0 w-80 bg-gray-900 border-l border-gray-800 overflow-y-auto animate-slide-in-right">
        <div class="p-6">
          <button @click="showSidebar = false" class="absolute top-4 right-4 p-2 rounded-lg bg-gray-800 hover:bg-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <div class="flex items-center gap-3 mb-8">
            <img :src="getAvatar()" class="w-16 h-16 rounded-xl border-2 border-gray-700 object-cover" />
            <div>
              <h3 class="text-lg font-bold text-white">{{ auth.user?.username || t('bot_game.you') }}</h3>
              <p class="text-sm text-gray-400">{{ t('text.rating') }}: {{ auth.user?.rating || 1200 }}</p>
              <p class="text-xs text-gray-500">{{ myColor === 'white' ? t('bot_game.white') : t('bot_game.black') }}</p>
            </div>
          </div>

          <div class="space-y-6">
            <div>
              <h4 class="text-sm font-semibold text-gray-300 mb-3">{{ t('bot_game.game_stats') }}</h4>
              <div class="bg-gray-800/50 rounded-xl p-4 space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-400">{{ t('bot_game.duration') }}</span>
                  <span class="text-sm text-white">{{ gameDuration }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-400">{{ t('bot_game.your_moves') }}</span>
                  <span class="text-sm text-blue-400">{{ userMoves }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-400">{{ t('bot_game.bot_moves') }}</span>
                  <span class="text-sm text-red-400">{{ botMoves }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-400">{{ t('bot_game.select_level') }}</span>
                  <span class="text-sm text-amber-400">{{ getDifficultyLabel() }}</span>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <div class="flex justify-between text-sm text-gray-400 mb-1">
                  <span>{{ t('bot_game.time_remaining') }} ({{ t('bot_game.you') }})</span>
                  <span>{{ fmtTime(userTime) }}</span>
                </div>
                <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                  <div 
                    :class="userTime <= 60000 ? 'bg-gradient-to-r from-red-500 to-pink-600' : 'bg-gradient-to-r from-emerald-500 to-teal-600'"
                    class="h-full transition-all duration-300"
                    :style="{ width: `${(userTime / BASE_TIME_MS) * 100}%` }"
                  ></div>
                </div>
              </div>
              
              <div>
                <div class="flex justify-between text-sm text-gray-400 mb-1">
                  <span>{{ t('bot_game.time_remaining') }} (Bot)</span>
                  <span>{{ fmtTime(botTime) }}</span>
                </div>
                <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                  <div 
                    :class="botTime <= 60000 ? 'bg-gradient-to-r from-red-500 to-pink-600' : 'bg-gradient-to-r from-blue-500 to-cyan-600'"
                    class="h-full transition-all duration-300"
                    :style="{ width: `${(botTime / BASE_TIME_MS) * 100}%` }"
                  ></div>
                </div>
              </div>
            </div>

            <div class="space-y-2">
              <button @click="resetGame" class="w-full bg-gray-800 hover:bg-gray-700 text-white py-3 rounded-lg transition flex items-center justify-center gap-2">
                <span>🔄</span> {{ t('bot_game.reset') }}
              </button>
              <button @click="changeColor" class="w-full bg-gray-800 hover:bg-gray-700 text-white py-3 rounded-lg transition flex items-center justify-center gap-2">
                <span>🎨</span> {{ t('bot_game.change_color') }}
              </button>
              <button @click="router.push('/tournaments')" class="w-full bg-gradient-to-r from-teal-600 to-emerald-700 text-white py-3 rounded-lg font-medium hover:shadow-lg transition flex items-center justify-center gap-2">
                <span>🏆</span> {{ t('bot_game.tournament') }}
              </button>
              <button @click="router.push('/dashboard')" class="w-full bg-gray-700 hover:bg-gray-600 text-white py-3 rounded-lg transition flex items-center justify-center gap-2">
                <span>📊</span> {{ t('bot_game.dashboard') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes scale-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

@keyframes slide-in-right {
  from { transform: translateX(100%); }
  to { transform: translateX(0); }
}

@keyframes pulse-glow {
  0%, 100% { box-shadow: 0 0 10px rgba(20, 184, 166, 0.5); }
  50% { box-shadow: 0 0 20px rgba(20, 184, 166, 0.8); }
}

.animate-fade-in {
  animation: fade-in-up 0.3s ease-out;
}

.animate-scale-in {
  animation: scale-in 0.2s ease-out;
}

.animate-slide-in-right {
  animation: slide-in-right 0.3s ease-out;
}

.animate-pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: rgba(20, 184, 166, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(20, 184, 166, 0.7);
}

/* Touch friendly */
button:active {
  transform: scale(0.98);
  transition: transform 0.1s;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .chess-board-container {
    padding: 0 8px;
  }
  
  .mobile-stats {
    font-size: 0.8rem;
  }
  
  .mobile-controls button {
    padding: 12px 8px;
    font-size: 0.9rem;
  }
}

/* Ensure board is square on mobile */
.aspect-square {
  aspect-ratio: 1 / 1;
}

/* Better touch targets */
@media (max-width: 640px) {
  button, 
  [role="button"] {
    min-height: 44px;
    min-width: 44px;
  }
}
</style>
