<script setup>
import { onMounted, onUnmounted, provide } from 'vue';
import { useAuthStore } from './stores/auth';
import { useNotificationStore } from './stores/notification';
import socket, { connectSocket } from './services/socket';

// --- KOMPONEN ---
import MatchInvitation from './components/MatchInvitation.vue';
import GlobalLoader from './components/GlobalLoader.vue';
 
// --- UTILS & SERVICES ---
import Swal from 'sweetalert2';
import { useDarkMode } from './composables/useDarkMode';
 
const auth = useAuthStore();
const notifStore = useNotificationStore();

// --- SETUP TOAST (SWEETALERT2) ---
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer);
    toast.addEventListener('mouseleave', Swal.resumeTimer);
  }
});

// Provide Toast ke semua komponen anak
provide('toast', Toast);

onMounted(async () => {
  // 1. KONEKSI SOCKET
  if (auth.token && auth.user) {
    console.log("[App] User logged in, initializing socket...");
    connectSocket();
  }

  // 2. SOCKET LISTENER: NOTIFIKASI UMUM
  socket.on('app_notification', (data) => {
      // Simpan ke store
      notifStore.handleNewNotification(data);
      
      // Tampilkan Toast
      Toast.fire({
          icon: data.type || 'info', 
          title: data.title,
          text: data.message
      });
  });

   
});

// Aktifkan Dark Mode Logic
useDarkMode();

onUnmounted(() => {
  socket.off('app_notification');
});
</script>

<template>
  <div class="min-h-screen bg-gray-900 text-white font-sans relative">
    <GlobalLoader />
    <MatchInvitation />
    <router-view></router-view>
    <div class="fixed top-4 right-4 z-50">
       
    </div>
  </div>
</template>

<style>
/* Opsional: Style global tambahan jika perlu */
</style>