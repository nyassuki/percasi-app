<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Chess } from 'chess.js';
import socket, { connectSocket } from '../services/socket'; // Gunakan service socket yg sudah ada
import api from '../services/api';
import ChessBoard from '../components/ChessBoard.vue';
import CapturedPieces from '../components/CapturedPieces.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const matchId = route.params.id;

// --- STATE ---
const game = ref(new Chess());
const fen = ref('start');
const spectators = ref(0);
const loading = ref(true);
const lastMoveTime = ref(null);

// Players Data
const whitePlayer = ref({ name: 'Loading...', rating: '?', avatar: null });
const blackPlayer = ref({ name: 'Loading...', rating: '?', avatar: null });

// Timers
const whiteTime = ref(0);
const blackTime = ref(0);
const initialTime = ref(0);
let timerInterval = null;

// Game Status
const gameStatus = ref('live'); // live, checkmate, draw, finished
const winner = ref(null);

// History Data untuk Tabel
const moveHistory = ref([]);

// --- COMPUTED ---
const activeTurn = computed(() => game.value.turn() === 'w' ? 'white' : 'black');

const formattedHistory = computed(() => {
  const history = [];
  const moves = moveHistory.value; // Array of SAN strings or objects
  
  for (let i = 0; i < moves.length; i += 2) {
    history.push({
      num: Math.floor(i / 2) + 1,
      white: moves[i],
      black: moves[i + 1] || ''
    });
  }
  return history;
});

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

// --- METHODS ---

// Start local timer simulation
const startTimer = () => {
  if (timerInterval) clearInterval(timerInterval);
  
  timerInterval = setInterval(() => {
    if (gameStatus.value !== 'live') {
      clearInterval(timerInterval);
      return;
    }
    
    if (activeTurn.value === 'white') {
      whiteTime.value = Math.max(0, whiteTime.value - 1000);
    } else {
      blackTime.value = Math.max(0, blackTime.value - 1000);
    }
  }, 1000);
};

const fetchMatchData = async () => {
  try {
    const res = await api.get(`/matches/${matchId}`);
    const data = res.data.data;

    // Load Board State
    game.value.load(data.fen);
    fen.value = data.fen;
    moveHistory.value = game.value.history(); // Rehydrate history from chess.js

    // Set Players
    whitePlayer.value = {
        name: data.white_username,
        rating: data.white_rating,
        avatar: data.white_avatar
    };
    blackPlayer.value = {
        name: data.black_username,
        rating: data.black_rating,
        avatar: data.black_avatar
    };

    // Set Times
    whiteTime.value = parseInt(data.white_time_ms) || parseInt(data.player_time);
    blackTime.value = parseInt(data.black_time_ms) || parseInt(data.player_time);
    initialTime.value = parseInt(data.player_time);

    // Sync Time lag
    if (data.status !== 'completed' && data.last_move_time) {
        const now = new Date().getTime();
        const lastMove = new Date(data.last_move_time).getTime();
        const elapsed = now - lastMove;
        if (elapsed > 0) {
            if (game.value.turn() === 'w') whiteTime.value -= elapsed;
            else blackTime.value -= elapsed;
        }
    }

    // Check status
    if (data.status === 'completed') {
        gameStatus.value = 'finished';
        if (data.result === '1-0') winner.value = 'white';
        else if (data.result === '0-1') winner.value = 'black';
        else winner.value = 'draw';
    } else {
        startTimer();
    }

  } catch (err) {
    console.error("Gagal load match:", err);
  } finally {
    loading.value = false;
  }
};

const initSocket = () => {
    if (!socket.connected) connectSocket();
    
    socket.emit('joinMatch', matchId);

    // Listener: Update Gerakan
    socket.on('moveUpdate', (data) => {
        // Update Board
        if (data.fen) {
            game.value.load(data.fen);
            fen.value = data.fen;
            moveHistory.value = game.value.history();
        }

        // Sync Time
        if (data.whiteTime) whiteTime.value = parseInt(data.whiteTime);
        if (data.blackTime) blackTime.value = parseInt(data.blackTime);

        // Reset local timer interval to prevent drift
        startTimer();

        // Check Game Over via Socket
        if (data.isGameOver) {
            gameStatus.value = 'finished';
            clearInterval(timerInterval);
        }
    });

    // Listener: Spectator Count
    socket.on('spectatorUpdate', (count) => {
        spectators.value = count;
    });

    // Listener: Game Finished
    socket.on('gameFinished', (data) => {
        gameStatus.value = 'finished';
        if (data.result === '1-0') winner.value = 'white';
        else if (data.result === '0-1') winner.value = 'black';
        else winner.value = 'draw';
        clearInterval(timerInterval);
    });
};

onMounted(async () => {
  await fetchMatchData();
  initSocket();
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  if (socket) {
      socket.off('moveUpdate');
      socket.off('spectatorUpdate');
      socket.off('gameFinished');
      socket.emit('leaveMatch', matchId);
  }
});
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-200 p-4 md:p-6 font-sans">
    <div class="max-w-7xl mx-auto">
      
      <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex items-center gap-4 w-full md:w-auto">
          <button @click="router.back()" class="p-3 bg-slate-900 rounded-2xl hover:bg-slate-800 transition-all border border-slate-800 shadow-lg group">
            <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <div>
            <h1 class="text-2xl font-black italic uppercase tracking-tighter text-white flex items-center gap-2">
              <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-teal-500">
                {{ t('mirror.title') }}
              </span>
            </h1>
            <div class="flex items-center gap-2 mt-0.5">
              <span class="w-2 h-2 rounded-full animate-pulse" 
                    :class="gameStatus === 'finished' ? 'bg-gray-500' : 'bg-red-500'"></span>
              <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                {{ gameStatus === 'finished' ? t('mirror.status.finished') : t('mirror.status.live') }}
              </p>
            </div>
          </div>
        </div>

        <div class="flex gap-4 bg-slate-900/50 p-2 rounded-2xl border border-slate-800/50 backdrop-blur-sm shadow-xl w-full md:w-auto justify-center">
          <div class="px-6 py-2 text-center border-r border-slate-800">
            <p class="text-xl font-black text-emerald-500">{{ spectators }}</p>
            <p class="text-[9px] font-bold text-slate-500 uppercase">{{ t('mirror.stats.watchers') }}</p>
          </div>
          <div class="px-6 py-2 text-center">
            <p class="text-xl font-black text-white">{{ moveHistory.length }}</p>
            <p class="text-[9px] font-bold text-slate-500 uppercase">{{ t('mirror.stats.moves') }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-8 flex flex-col gap-4">
          
          <div class="flex items-center justify-between p-4 bg-slate-900 rounded-[1.5rem] border border-slate-800 shadow-lg"
               :class="activeTurn === 'black' && gameStatus === 'live' ? 'ring-2 ring-emerald-500/30' : ''">
            <div class="flex items-center gap-3">
              <img :src="getAvatar(blackPlayer.avatar, blackPlayer.name)" class="w-12 h-12 rounded-xl bg-slate-800 object-cover border border-slate-700" />
              <div>
                <div class="font-bold text-slate-200 text-sm flex items-center gap-2">
                    {{ blackPlayer.name }}
                    <span class="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400 border border-slate-700">{{ blackPlayer.rating }}</span>
                </div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-bold mt-0.5">{{ t('mirror.stats.black') }}</div>
              </div>
            </div>
            <div class="px-4 py-2 bg-slate-950 rounded-xl font-mono font-bold text-xl border border-slate-800"
                 :class="blackTime < 30000 ? 'text-red-500 animate-pulse' : 'text-slate-400'">
                {{ fmtTime(blackTime) }}
            </div>
          </div>

          <div class="relative w-full aspect-square bg-slate-900 rounded-[2rem] border-8 border-slate-900 shadow-2xl overflow-hidden flex items-center justify-center">
            <div v-if="loading" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-slate-900">
               <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-4"></div>
               <span class="text-xs text-emerald-500 font-bold tracking-widest animate-pulse">CONNECTING...</span>
            </div>

            <ChessBoard 
                v-if="fen"
                :fen="fen" 
                orientation="white"
                :isInteractable="false"
                class="w-full h-full"
            />
          </div>

          <div class="flex items-center justify-between p-4 bg-slate-900 rounded-[1.5rem] border border-slate-800 shadow-lg"
               :class="activeTurn === 'white' && gameStatus === 'live' ? 'ring-2 ring-emerald-500/30' : ''">
            <div class="flex items-center gap-3">
              <img :src="getAvatar(whitePlayer.avatar, whitePlayer.name)" class="w-12 h-12 rounded-xl bg-slate-800 object-cover border border-slate-700" />
              <div>
                <div class="font-bold text-slate-200 text-sm flex items-center gap-2">
                    {{ whitePlayer.name }}
                    <span class="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400 border border-slate-700">{{ whitePlayer.rating }}</span>
                </div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-bold mt-0.5">{{ t('mirror.stats.white') }}</div>
              </div>
            </div>
            <div class="px-4 py-2 bg-slate-950 rounded-xl font-mono font-bold text-xl border border-slate-800"
                 :class="whiteTime < 30000 ? 'text-red-500 animate-pulse' : 'text-slate-400'">
                {{ fmtTime(whiteTime) }}
            </div>
          </div>

          <div class="px-2">
             <CapturedPieces v-if="fen" :fen="fen" capturing-color="white" class="opacity-60 mb-1" />
             <CapturedPieces v-if="fen" :fen="fen" capturing-color="black" class="opacity-60" />
          </div>

        </div>

        <div class="lg:col-span-4 h-full min-h-[400px]">
          <div class="bg-slate-900 rounded-[2rem] border border-slate-800 p-6 h-full shadow-2xl flex flex-col">
            <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
              <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
              {{ t('mirror.history') }}
            </h3>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
              <table class="w-full text-sm border-collapse">
                <thead class="sticky top-0 bg-slate-900 z-10 text-xs text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-2 text-left w-12">#</th>
                        <th class="py-2 text-left">White</th>
                        <th class="py-2 text-left">Black</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-400 font-mono">
                  <tr v-for="move in formattedHistory" :key="move.num" class="hover:bg-slate-800/50 transition-colors">
                    <td class="py-2 text-slate-600">{{ move.num }}.</td>
                    <td class="py-2 font-bold text-slate-300">{{ move.white }}</td>
                    <td class="py-2 font-bold text-slate-300">{{ move.black }}</td>
                  </tr>
                </tbody>
              </table>
              
              <div v-if="formattedHistory.length === 0" class="flex flex-col items-center justify-center h-40 text-slate-600">
                  <span class="text-2xl mb-2">♟️</span>
                  <span class="text-xs">No moves yet</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom Scrollbar for Dark Theme */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.5);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #475569;
}
</style>