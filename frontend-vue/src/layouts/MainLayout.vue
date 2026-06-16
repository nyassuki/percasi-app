<script setup>
import BottomNav from '../components/BottomNav.vue';
import { useSettingStore } from '../stores/settings';

// Fetch settings sekali saja di layout agar tersedia global
const settingStore = useSettingStore();
settingStore.fetchSettings();
</script>

<template>
  <div class="min-h-screen bg-gray-50 relative">
    <main class="pb-20">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    <BottomNav />
  </div>
</template>

<style scoped>
/* Animasi transisi antar halaman (opsional) */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
