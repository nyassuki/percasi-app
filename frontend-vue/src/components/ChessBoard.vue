<script setup>
import { onMounted, onUnmounted, ref, watch, nextTick } from 'vue';
import { Chess } from 'chess.js';
import { Chessground } from 'chessground';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

// Import CSS Chessground
import 'chessground/assets/chessground.base.css'; 
import 'chessground/assets/chessground.brown.css'; 
import 'chessground/assets/chessground.cburnett.css'; 

const { t } = useI18n(); // [BARU] Init
const props = defineProps({
  fen: { type: String, default: 'start' },
  orientation: { type: String, default: 'white' },
  isInteractable: { type: Boolean, default: true },
  highlightSquares: {
    type: Array,
    default: () => []
  },
  soundEnabled: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['move']);
const boardRef = ref(null);
let cg = null;
const chess = new Chess();
let resizeObserver = null;

// --- STATE UI & PROMOSI ---
const showPromoModal = ref(false);
const pendingMove = ref(null);
const turnColor = ref('white');

// --- SOUND EFFECTS ---
const audioContext = ref(null);
const soundEnabled = ref(props.soundEnabled);
const prevFen = ref(null);
const isFirstLoad = ref(true);
const lastPlayerMoveTime = ref(0);

const sounds = {
  move: new Audio('/sounds/chess/move.mp3'),
  capture: new Audio('/sounds/chess/capture.mp3'),
  check: new Audio('/sounds/chess/check.mp3'),
  checkmate: new Audio('/sounds/chess/checkmate.mp3'),
  promotion: new Audio('/sounds/chess/move.mp3'),
};

// --- STATE LANGSUNG TERAKHIR ---
const lastOpponentMove = ref(null); // Menyimpan langkah terakhir lawan [from, to]

// Normalize FEN function
const normalizeFen = (fen) => {
  if (!fen || fen === 'start') {
    return 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
  }
  return fen;
};

// Validate FEN function
const isValidFen = (fen) => {
  if (!fen) return false;
  try {
    const tempChess = new Chess();
    tempChess.load(fen);
    return true;
  } catch (e) {
    return false;
  }
};

// Initialize Audio Context
const initAudioContext = () => {
  if (!audioContext.value && typeof window !== 'undefined') {
    try {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      audioContext.value = new AudioContext();
    } catch (e) {
      console.warn('Web Audio API not supported:', e);
    }
  }
};

// Enable audio after user interaction
const enableAudio = () => {
  if (!soundEnabled.value) {
    soundEnabled.value = true;
    initAudioContext();
    
    if (audioContext.value && audioContext.value.state === 'suspended') {
      audioContext.value.resume();
    }
  }
};

// Sound generation functions
const playSound = (type) => {
  if (!soundEnabled.value || !audioContext.value) return;
  
  try {
    const now = audioContext.value.currentTime;
    const oscillator = audioContext.value.createOscillator();
    const gainNode = audioContext.value.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.value.destination);
    
    oscillator.type = 'sine';
    gainNode.gain.setValueAtTime(0, now);
    
    switch(type) {
      case 'move':
        oscillator.frequency.setValueAtTime(350, now);
        gainNode.gain.linearRampToValueAtTime(0.08, now + 0.05);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
        oscillator.start(now);
        oscillator.stop(now + 0.3);
        break;
        
      case 'capture':
        oscillator.frequency.setValueAtTime(600, now);
        oscillator.frequency.exponentialRampToValueAtTime(300, now + 0.2);
        oscillator.type = 'sawtooth';
        gainNode.gain.linearRampToValueAtTime(0.15, now + 0.02);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
        oscillator.start(now);
        oscillator.stop(now + 0.25);
        break;
        
      case 'check':
        oscillator.frequency.setValueAtTime(440, now);
        oscillator.frequency.setValueAtTime(554.37, now + 0.1);
        oscillator.type = 'square';
        gainNode.gain.linearRampToValueAtTime(0.1, now + 0.05);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
        oscillator.start(now);
        oscillator.stop(now + 0.4);
        break;
        
      case 'checkmate':
        const times = [0, 0.15, 0.3, 0.45, 0.6];
        const notes = [349.23, 293.66, 261.63, 220, 174.61];
        
        oscillator.type = 'triangle';
        gainNode.gain.setValueAtTime(0.1, now);
        
        times.forEach((time, index) => {
          oscillator.frequency.setValueAtTime(notes[index], now + time);
        });
        
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
        oscillator.start(now);
        oscillator.stop(now + 0.8);
        break;
        
      case 'promotion':
        oscillator.frequency.setValueAtTime(523.25, now);
        oscillator.frequency.exponentialRampToValueAtTime(1046.50, now + 0.4);
        oscillator.type = 'sine';
        gainNode.gain.linearRampToValueAtTime(0.12, now + 0.1);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
        oscillator.start(now);
        oscillator.stop(now + 0.5);
        break;
    }
  } catch (error) {
    console.warn('Error playing sound:', error);
  }
};

// Analyze move to determine sound type
const analyzeMove = (oldFen, newFen) => {
  try {
    const normalizedOldFen = normalizeFen(oldFen);
    const normalizedNewFen = normalizeFen(newFen);
    
    if (!isValidFen(normalizedOldFen) || !isValidFen(normalizedNewFen)) {
      return null;
    }
    
    if (normalizedOldFen === normalizedNewFen) return null;
    
    const oldChess = new Chess(normalizedOldFen);
    const newChess = new Chess(normalizedNewFen);
    
    if (newChess.isCheckmate()) {
      return 'checkmate';
    }
    
    if (newChess.inCheck()) {
      return 'check';
    }
    
    const oldBoard = oldChess.board();
    const newBoard = newChess.board();
    
    let oldPieceCount = 0;
    let newPieceCount = 0;
    
    for (let i = 0; i < 8; i++) {
      for (let j = 0; j < 8; j++) {
        if (oldBoard[i][j]) oldPieceCount++;
        if (newBoard[i][j]) newPieceCount++;
      }
    }
    
    if (newPieceCount < oldPieceCount) {
      return 'capture';
    }
    
    const moves = newChess.history({ verbose: true });
    if (moves.length > 0) {
      const lastMove = moves[moves.length - 1];
      if (lastMove.promotion) {
        return 'promotion';
      }
    }
    
    return 'move';
    
  } catch (error) {
    console.warn('Error analyzing move:', error);
    return null;
  }
};

// Fungsi untuk mendeteksi langkah terakhir dari perbedaan FEN
const detectLastMove = (oldFen, newFen) => {
  try {
    const oldChess = new Chess(oldFen);
    const newChess = new Chess(newFen);
    
    // Cari perbedaan antara dua posisi
    const oldBoard = oldChess.board();
    const newBoard = newChess.board();
    
    let from = null;
    let to = null;
    
    // Cari kotak yang berbeda
    for (let row = 0; row < 8; row++) {
      for (let col = 0; col < 8; col++) {
        const oldPiece = oldBoard[row][col];
        const newPiece = newBoard[row][col];
        const square = String.fromCharCode(97 + col) + (8 - row);
        
        // Jika ada perbedaan
        if (JSON.stringify(oldPiece) !== JSON.stringify(newPiece)) {
          if (oldPiece && !newPiece) {
            // Kotak kosong setelah ada bidak = from square
            from = square;
          } else if (!oldPiece && newPiece) {
            // Kotak terisi setelah kosong = to square
            to = square;
          } else if (oldPiece && newPiece && oldPiece.type !== newPiece.type) {
            // Promosi atau perubahan jenis bidak
            to = square;
          }
        }
      }
    }
    
    // Untuk kasus khusus (rokade)
    if (!from || !to) {
      // Cek apakah ada rokade
      const oldMoves = oldChess.moves({ verbose: true });
      const newMoves = newChess.moves({ verbose: true });
      
      // Jika jumlah langkah bertambah
      if (newMoves.length > oldMoves.length) {
        // Gunakan history untuk mendapatkan langkah terakhir
        const history = newChess.history({ verbose: true });
        if (history.length > 0) {
          const last = history[history.length - 1];
          return [last.from, last.to];
        }
      }
    }
    
    if (from && to) {
      return [from, to];
    }
    
    return null;
  } catch (error) {
    console.warn('Error detecting last move:', error);
    return null;
  }
};

// --- LOGIKA UTAMA (Chess.js) ---
const safeLoad = (fenStr) => {
  try {
    if (!fenStr || fenStr === 'start') {
      chess.reset();
    } else {
      chess.load(fenStr);
    }
    return chess.fen();
  } catch (e) {
    console.error("FEN Error:", e, "Loading default position");
    chess.reset();
    return chess.fen();
  }
};

const toDests = (chessInstance) => {
  const dests = new Map();
  const moves = chessInstance.moves({ verbose: true });
  moves.forEach((m) => {
    if (!dests.has(m.from)) dests.set(m.from, []);
    dests.get(m.from).push(m.to);
  });
  return dests;
};

// --- HANDLER GERAKAN & PROMOSI ---
const isPromotion = (orig, dest) => {
  const piece = chess.get(orig);
  const targetRank = dest[1];
  
  if (piece?.type === 'p') {
      if (piece.color === 'w' && targetRank === '8') return true;
      if (piece.color === 'b' && targetRank === '1') return true;
  }
  return false;
};

const handleMove = (orig, dest) => {
  enableAudio();
  lastPlayerMoveTime.value = Date.now();
  
  // Reset tanda langkah terakhir lawan saat player bergerak
  lastOpponentMove.value = null;
  
  if (isPromotion(orig, dest)) {
    pendingMove.value = { from: orig, to: dest };
    turnColor.value = chess.turn() === 'w' ? 'white' : 'black';
    showPromoModal.value = true;
    return;
  }

  submitMove(orig, dest, null);
};

const onPromotionSelect = (pieceChar) => {
  if (!pendingMove.value) return;
  
  lastPlayerMoveTime.value = Date.now();
  // Reset tanda langkah terakhir lawan saat player bergerak
  lastOpponentMove.value = null;
  
  submitMove(pendingMove.value.from, pendingMove.value.to, pieceChar);
  showPromoModal.value = false;
  pendingMove.value = null;
};

const submitMove = (orig, dest, promoChar) => {
  emit('move', { 
    from: orig, 
    to: dest, 
    promotion: promoChar 
  });

  if (cg) cg.set({ viewOnly: true, movable: { dests: new Map() } });
};

// --- RENDER ENGINE (Chessground) ---
const reloadBoard = async () => {
  const el = boardRef.value;
  if (!el) return;

  await nextTick();

  showPromoModal.value = false;
  pendingMove.value = null;

  if (cg) {
      el.innerHTML = ''; 
      cg = null;
  }

  safeLoad(props.fen);

  const currentTurnColor = chess.turn() === 'w' ? 'white' : 'black';
  const playerColor = props.orientation;
  const canMove = props.isInteractable && (currentTurnColor === playerColor);

  // Prioritaskan highlightSquares dari props, jika tidak ada gunakan lastOpponentMove
  const lastMoveToShow = props.highlightSquares.length === 2 
    ? props.highlightSquares 
    : (lastOpponentMove.value || null);

  const config = {
    fen: chess.fen(),
    orientation: props.orientation,
    viewOnly: !canMove,
    turnColor: currentTurnColor,
    check: chess.inCheck(),
    disableContextMenu: true,
    
    lastMove: lastMoveToShow,

    movable: {
      color: playerColor,
      free: false,
      dests: canMove ? toDests(chess) : new Map(),
      events: { after: handleMove },
    },
    premovable: { enabled: true },
    animation: { enabled: true, duration: 250 },
    highlight: { 
      lastMove: true, 
      check: true 
    },
    drawable: { enabled: true }
  };

  cg = Chessground(el, config);
  cg.redrawAll();
};

// --- WATCHERS ---
// Watch FEN changes - HANYA UNTUK GERAKAN LAWAN
watch(() => props.fen, (newFen, oldFen) => {
  if (isFirstLoad.value) {
    isFirstLoad.value = false;
    prevFen.value = normalizeFen(newFen);
    reloadBoard();
    return;
  }

  const oldValidFen = prevFen.value;
  const normalizedNewFen = normalizeFen(newFen);
  prevFen.value = normalizedNewFen;
  
  reloadBoard();
  
  if (oldValidFen && newFen && oldValidFen !== newFen) {
    const normalizedOldFen = normalizeFen(oldValidFen);
    
    if (isValidFen(normalizedOldFen) && isValidFen(normalizedNewFen)) {
      const oldChess = new Chess(normalizedOldFen);
      const newChess = new Chess(normalizedNewFen);
      
      const oldTurn = oldChess.turn() === 'w' ? 'white' : 'black';
      const newTurn = newChess.turn() === 'w' ? 'white' : 'black';
      const playerColor = props.orientation;
      
      const timeSincePlayerMove = Date.now() - lastPlayerMoveTime.value;
      const isLikelyOpponentMove = timeSincePlayerMove > 500;
      
      if (oldTurn !== newTurn && newTurn === playerColor && isLikelyOpponentMove) {
        // Lawan baru saja bergerak
        const soundType = analyzeMove(normalizedOldFen, normalizedNewFen);
        if (soundType) {
          playSound(soundType);
          console.log(soundType);
        }
        
        // Deteksi dan simpan langkah terakhir lawan
        const detectedMove = detectLastMove(normalizedOldFen, normalizedNewFen);
        if (detectedMove) {
          lastOpponentMove.value = detectedMove;
        }
      } else {
        // Reset tanda langkah terakhir lawan jika bukan gerakan lawan
        lastOpponentMove.value = null;
      }
    }
  }
}, { immediate: false });

// Watch other props
watch(() => props.orientation, () => { if(cg) reloadBoard(); });
watch(() => props.isInteractable, () => { if(cg) reloadBoard(); });
watch(() => props.highlightSquares, (newSquares) => {
  if (cg) {
    cg.set({ 
      lastMove: newSquares.length === 2 ? newSquares : (lastOpponentMove.value || null)
    });
  }
});

// --- LIFECYCLE ---
onMounted(() => {
  initAudioContext();
  prevFen.value = normalizeFen(props.fen);
  isFirstLoad.value = true;
  
  const el = boardRef.value;
  if (el) {
    el.addEventListener('click', enableAudio, { once: true });
  }
  
  const el2 = boardRef.value;
  if (!el2) return;

  reloadBoard();
  Object.values(sounds).forEach(audio => {
    audio.load();
    audio.volume = 0.5; // Set volume default 50%
  });
  resizeObserver = new ResizeObserver((entries) => {
    for (const entry of entries) {
      if (entry.contentRect.width > 0 && !cg) {
        reloadBoard();
      } else if (cg) {
        cg.redrawAll();
      }
    }
  });
  resizeObserver.observe(el2);
});

onUnmounted(() => {
  if (resizeObserver) resizeObserver.disconnect();
  if (audioContext.value) {
    audioContext.value.close();
  }
});
</script>

<template>
  <div class="board-wrapper bg-[#b58863] relative">
    <button 
      @click="soundEnabled = !soundEnabled; enableAudio()"
      class="absolute top-2 right-2 z-10 w-8 h-8 rounded-full bg-white/80 hover:bg-white flex items-center justify-center shadow-md transition-all hover:scale-110"
      :title="soundEnabled ? t('chess_board.controls.mute') : t('chess_board.controls.unmute')"
    >
      <span v-if="soundEnabled" class="text-lg">🔊</span>
      <span v-else class="text-lg">🔇</span>
    </button>
    
    <div v-if="lastOpponentMove && soundEnabled" 
         class="absolute top-2 left-2 z-10 bg-green-600 text-white text-xs px-2 py-1 rounded-full shadow-md">
      <span class="font-bold">♟️</span> 
      {{ t('chess_board.status.opponent_move', { from: lastOpponentMove[0], to: lastOpponentMove[1] }) }}
    </div>
    
    <div ref="boardRef" class="cg-container" @click="enableAudio"></div>

    <div v-if="showPromoModal" class="promo-modal">
      <div class="promo-content">
        <div class="promo-options">
          <div @click="onPromotionSelect('q')" class="promo-piece" :title="t('chess_board.promotion.queen')">
            {{ turnColor === 'white' ? '♕' : '♛' }}
          </div>
          <div @click="onPromotionSelect('n')" class="promo-piece" :title="t('chess_board.promotion.knight')">
            {{ turnColor === 'white' ? '♘' : '♞' }}
          </div>
          <div @click="onPromotionSelect('r')" class="promo-piece" :title="t('chess_board.promotion.rook')">
            {{ turnColor === 'white' ? '♖' : '♜' }}
          </div>
          <div @click="onPromotionSelect('b')" class="promo-piece" :title="t('chess_board.promotion.bishop')">
            {{ turnColor === 'white' ? '♗' : '♝' }}
          </div>
        </div>
        <div class="promo-text">{{ t('chess_board.promotion.title') }}</div>
      </div>
    </div>
  </div>
</template>

<style>
/* Board Layout */
.board-wrapper {
  width: 100%;
  aspect-ratio: 1 / 1;
  position: relative;
  display: block;
  touch-action: none !important;
}

.cg-container, .cg-wrap, .cg-board {
  width: 100%;
  height: 100%;
  touch-action: none !important;
}

/* Custom Highlight untuk langkah terakhir lawan */
.cg-wrap square.last-move {
  background-color: rgba(255, 255, 0, 0.45) !important;
}

/* Opsi: Warna berbeda untuk langkah lawan */
.cg-wrap square.opponent-last-move {
  background-color: rgba(0, 255, 0, 0.3) !important;
}

/* Pieces */
piece {
  cursor: pointer !important;
  z-index: 10 !important;
  pointer-events: auto !important;
  transition: transform 0.1s;
}

piece:hover {
  transform: scale(1.05);
}

/* Legal Moves */
square.move-dest {
  background: radial-gradient(rgba(20, 85, 30, 0.5) 19%, rgba(0, 0, 0, 0) 20%);
  pointer-events: none !important;
  z-index: 5;
}

/* Check Indicator */
.cg-wrap square.check {
  background-color: rgba(255, 0, 0, 0.2) !important;
}

/* Promotion Modal */
.promo-modal {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(3px);
}

.promo-content {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
  text-align: center;
  pointer-events: auto;
  animation: fadeIn 0.3s ease;
}

.promo-options {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.promo-piece {
  font-size: 36px;
  width: 56px;
  height: 56px;
  line-height: 56px;
  cursor: pointer;
  border: 2px solid #ddd;
  border-radius: 8px;
  background: #f9f9f9;
  user-select: none;
  transition: all 0.2s ease;
  color: black;
}

.promo-piece:hover {
  background: #e8f0fe;
  transform: scale(1.15);
  border-color: #4285f4;
  box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
}

.promo-piece:active {
  transform: scale(0.95);
}

.promo-text {
  font-size: 12px;
  margin-top: 12px;
  color: #666;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Animasi untuk tanda langkah terakhir */
@keyframes pulseMove {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.opponent-move-indicator {
  animation: pulseMove 1s ease-in-out 2;
}

/* Responsive Adjustments */
@media (max-width: 640px) {
  .promo-piece {
    font-size: 28px;
    width: 48px;
    height: 48px;
    line-height: 48px;
  }
  
  .promo-content {
    padding: 16px;
  }
  
  .board-wrapper .absolute.top-2.left-2 {
    font-size: 10px;
    padding: 1px 4px;
  }
}

/* Untuk layar sangat kecil */
@media (max-width: 400px) {
  .board-wrapper .absolute.top-2.left-2 {
    display: none; /* Sembunyikan teks di layar sangat kecil */
  }
}
</style>