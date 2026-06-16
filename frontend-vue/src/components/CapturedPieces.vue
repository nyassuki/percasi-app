<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const props = defineProps({
    fen: { type: String, required: true },
    capturingColor: { type: String, required: true } // 'white' atau 'black'
});

const capturedPieces = computed(() => {
    if (!props.fen) return [];

    const startCounts = {
        'P': 8, 'N': 2, 'B': 2, 'R': 2, 'Q': 1, 
        'p': 8, 'n': 2, 'b': 2, 'r': 2, 'q': 1  
    };

    const boardPart = props.fen.split(' ')[0];
    const currentCounts = {};
    for (const char of boardPart) {
        if (/[a-zA-Z]/.test(char)) {
            currentCounts[char] = (currentCounts[char] || 0) + 1;
        }
    }

    const targetPieces = props.capturingColor === 'white' 
        ? ['p', 'n', 'b', 'r', 'q'] 
        : ['P', 'N', 'B', 'R', 'Q']; 

    const result = [];
    targetPieces.forEach(piece => {
        const start = startCounts[piece];
        const current = currentCounts[piece] || 0;
        const missing = start - current;
        if (missing > 0) {
            for (let i = 0; i < missing; i++) result.push(piece);
        }
    });

    const valueMap = { 'q':9,'r':5,'b':3,'n':3,'p':1, 'Q':9,'R':5,'B':3,'N':3,'P':1 };
    return result.sort((a, b) => valueMap[b] - valueMap[a]);
});

const getEmoji = (code) => {
    const map = {
        'P': '♟', 'N': '♞', 'B': '♝', 'R': '♜', 'Q': '♛',
        'p': '♟', 'n': '♞', 'b': '♝', 'r': '♜', 'q': '♛'
    };
    return map[code] || '';
};

const getPieceClass = (pieceCode) => {
    return pieceCode === pieceCode.toUpperCase() ? 'piece-white' : 'piece-black';
};
</script>

<template>
  <div class="flex items-center gap-0.5 h-6 min-w-[20px]" :title="t('captured_pieces.tooltip')">
    
    <div v-for="(piece, idx) in capturedPieces" :key="idx" 
         class="text-xl leading-none select-none transition-transform hover:scale-110 cursor-default"
         :class="getPieceClass(piece)">
         {{ getEmoji(piece) }}
    </div>

    <span v-if="capturedPieces.length === 0" class="text-xs text-gray-300 dark:text-gray-600 opacity-50 select-none">
      {{ t('captured_pieces.empty') }}
    </span>

  </div>
</template>

<style scoped>
/* --- BIDAK PUTIH --- */
/* Tetap Putih dengan Outline Hitam di kedua mode (Light/Dark) agar kontras */
.piece-white {
    color: #ffffff;
    text-shadow: 
      -1px -1px 0 #1f2937, 
       1px -1px 0 #1f2937,
      -1px  1px 0 #1f2937,
       1px  1px 0 #1f2937;
    filter: drop-shadow(0 1px 1px rgba(0,0,0,0.3));
}

/* --- BIDAK HITAM (LIGHT MODE) --- */
/* Hitam polos */
.piece-black {
    color: #1f2937;
    filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));
}

/* --- BIDAK HITAM (DARK MODE) --- */
/* Menggunakan :global(.dark) untuk mendeteksi class 'dark' pada tag <html> 
   Bidak hitam diberi outline abu-abu terang agar terlihat di background gelap 
*/
:global(.dark) .piece-black {
    color: #0f172a; /* Slate 900 (Sangat Gelap) */
    text-shadow: 
      -1px -1px 0 #94a3b8, /* Slate 400 (Outline Terang) */
       1px -1px 0 #94a3b8,
      -1px  1px 0 #94a3b8,
       1px  1px 0 #94a3b8;
}

.text-xl {
    font-size: 1.25rem;
}
</style>