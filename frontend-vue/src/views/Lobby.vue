<script setup>
import { ref, onMounted, onUnmounted, computed, inject } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import socket, { connectSocket, socketState } from '../services/socket';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const auth = useAuthStore();
const toast = inject('toast');

// --- STATE ---
const onlineUsers = ref([]);
const searchQuery = ref('');
const loading = ref(true);

// --- COMPUTED ---
const sortedUsers = computed(() => {
  let users = onlineUsers.value;

  // 1. Filter: Jangan tampilkan diri sendiri
  users = users.filter(u => u.id !== auth.user?.id);

  // 2. Filter: Search Query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    users = users.filter(u => u.username.toLowerCase().includes(query));
  }

  // 3. Sort: Rating Tertinggi ke Terendah
  return users.sort((a, b) => b.rating - a.rating);
});

// --- HELPER ---
const getAvatar = (user) => {
  if (user.avatar_url) return `${user.avatar_url}`;
  return `https://ui-avatars.com/api/?name=${user.username}&background=random&color=fff`;
};

// [UPDATED] Menggunakan t() untuk status label
const getStatusLabel = (status) => {
  return status === 'playing' ? t('lobby.status.playing') : t('lobby.status.online');
};

// --- ACTIONS ---
const sendChallenge = (targetUser) => {
  if (targetUser.status === 'playing') {
    toast.fire({ icon: 'warning', title: t('lobby.toast.player_busy') });
    console.log(targetUser);
    return;
  }

  // Emit event ke server untuk menantang
  socket.emit('send_challenge', { 
    targetUserId: targetUser.id,
    targetUsername: targetUser.username 
  });

  toast.fire({ 
    icon: 'success', 
    title: t('lobby.toast.challenge_sent', { username: targetUser.username }) 
  });
};

// --- LIFECYCLE ---
onMounted(() => {
  // 1. Request data user online terbaru
  socket.emit('request_lobby_list');

  // 2. Listen: Update daftar user
  socket.on('lobby_update', (users) => {
    onlineUsers.value = users;
    loading.value = false;
  });

  // 3. Listen: Tantangan Ditolak
  socket.on('challenge_declined', (data) => {
     toast.fire({ icon: 'info', title: t('lobby.toast.challenge_declined', { username: data.username }) });
  });

  socket.on('match_invitation', (data) => {
    console.log(data);
  });

  // 4. [FIX] Listen: Match Dimulai (Redirect ke Game)
  socket.on('match_start', (data) => {
      console.log("Match started from Lobby:", data);
      // Tampilkan notifikasi singkat
      toast.fire({ 
        icon: 'success', 
        title: t('lobby.toast.match_start') 
      });

      // Redirect ke halaman game
      // Pastikan route '/game/:id' sudah ada di router Anda
      router.push(`/game/${data.match_dbid}`);
  });
});

onUnmounted(() => {
  socket.off('lobby_update');
  socket.off('challenge_declined');
  socket.off('match_start'); // Jangan lupa bersihkan listener ini juga
    
});
</script>
<template>
  <div class="min-h-screen bg-teal-700 dark:bg-slate-950 flex flex-col transition-colors duration-300">
    
    <div class="sticky top-0 z-50 bg-teal-700 dark:bg-slate-900 px-6 pt-6 pb-6 shadow-md transition-all duration-200">
      <div class="flex items-center gap-3 text-white">
        <button @click="router.back()" class="bg-white/20 p-2 rounded-full hover:bg-white/30 transition backdrop-blur-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>
        <div>
            <h1 class="text-xl font-bold tracking-wide">{{ t('lobby.header.title') }}</h1>
            <p class="text-teal-100 dark:text-gray-400 text-xs">
                {{ t('lobby.header.subtitle', { count: sortedUsers.length }) }}
            </p>
        </div>
      </div>

      <div class="mt-4 relative">
        <input 
            v-model="searchQuery"
            type="text" 
            :placeholder="t('lobby.search.placeholder')" 
            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/10 dark:bg-slate-800 border border-white/20 dark:border-slate-700 text-white placeholder-teal-100 dark:placeholder-gray-500 focus:outline-none focus:bg-white/20 transition backdrop-blur-sm"
        >
        <div class="absolute left-3 top-2.5 text-teal-100 dark:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
      </div>
    </div>

    <div class="ctn flex-1 bg-gray-50 dark:bg-slate-900 rounded-t-[35px] pt-6 px-6 pb-24 shadow-inner min-h-[80vh] relative z-0 mt-[-20px] transition-colors duration-300">
      
      <div v-if="loading" class="space-y-3 pt-4">
         <div v-for="n in 5" :key="n" class="h-20 bg-white dark:bg-slate-800 rounded-2xl animate-pulse shadow-sm"></div>
      </div>

      <div v-else-if="sortedUsers.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
        <div class="text-6xl mb-4 grayscale opacity-50">😴</div>
        <p class="font-medium text-center">{{ t('lobby.empty.title') }}</p>
        <p class="text-xs mt-1">{{ t('lobby.empty.desc') }}</p>
      </div>

      <div v-else class="space-y-3 pt-4">
        <transition-group name="list">
            <div 
              v-for="user in sortedUsers" 
              :key="user.id" 
              class="bg-white dark:bg-slate-800 p-3 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 flex items-center justify-between group hover:shadow-md transition-all duration-300"
            >
              <div class="flex items-center gap-3">
                <div class="relative">
                    <img :src="getAvatar(user)" class="w-12 h-12 rounded-full bg-gray-200 dark:bg-slate-700 object-cover border-2 border-white dark:border-slate-600 shadow-sm" />
                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white dark:border-slate-800"
                          :class="user.status === 'playing' ? 'bg-orange-500' : 'bg-green-500'">
                    </span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ user.username }}</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-mono bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 px-1.5 rounded font-bold">
                            ⭐ {{ user.rating }}
                        </span>
                        <span class="text-[10px]" :class="user.status === 'playing' ? 'text-orange-500' : 'text-green-600 dark:text-green-400'">
                            {{ getStatusLabel(user.status) }}
                        </span>
                    </div>
                </div>
              </div>

              <button 
                @click="sendChallenge(user)"
                :disabled="user.status === 'playing'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm active:scale-95"
                :class="user.status === 'playing' 
                    ? 'bg-gray-100 dark:bg-slate-700 text-gray-400 cursor-not-allowed' 
                    : 'bg-teal-600 dark:bg-teal-700 text-white hover:bg-teal-700 dark:hover:bg-teal-600 shadow-teal-200 dark:shadow-none'"
              >
                <span v-if="user.status !== 'playing'">⚔️ {{ t('lobby.button.duel') }}</span>
                <span v-else>👁️ {{ t('lobby.button.watch') }}</span>
              </button>
            </div>
        </transition-group>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* List Transitions */
.list-enter-active,
.list-leave-active {
  transition: all 0.4s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>