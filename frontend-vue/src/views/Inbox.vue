<script setup>
import { onMounted, ref, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '../stores/notification';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t, locale } = useI18n(); // [BARU] Init
const router = useRouter();
const notifStore = useNotificationStore();

// State untuk modal
const selectedNotif = ref(null);
const showModal = ref(false);

// [UPDATED] Format date dinamis berdasarkan locale
const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  const now = new Date();
  const diff = (now - date) / 1000;

  if (diff < 60) return t('notification.card.just_now');
  if (diff < 3600) return t('notification.card.min_ago', { count: Math.floor(diff / 60) });
  if (diff < 86400) return t('notification.card.hour_ago', { count: Math.floor(diff / 3600) });
  
  const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
  const day = date.getDate();
  const month = date.toLocaleDateString(currentLocale, { month: 'short' });
  const year = date.getFullYear();
  const currentYear = new Date().getFullYear();
  
  if (year === currentYear) {
    return `${day} ${month}`;
  } else {
    return `${day} ${month} ${year}`;
  }
};

// [UPDATED] Format waktu lengkap untuk modal
const formatFullDate = (dateStr) => {
  const date = new Date(dateStr);
  const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
  
  return date.toLocaleDateString(currentLocale, {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Get icon berdasarkan type notifikasi
const getNotifIcon = (type) => {
  switch(type) {
    case 'success': return '✅';
    case 'warning': return '⚠️';
    case 'danger': return '🚨';
    case 'payment': return '💳';
    case 'tournament': return '🏆';
    case 'match': return '♔';
    default: return '📧';
  }
};

// Get icon color berdasarkan type
const getIconColor = (type) => {
  switch(type) {
    case 'success': return 'bg-gradient-to-br from-green-500 to-emerald-600 text-white';
    case 'warning': return 'bg-gradient-to-br from-amber-500 to-orange-600 text-white';
    case 'danger': return 'bg-gradient-to-br from-red-500 to-rose-600 text-white';
    case 'payment': return 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white';
    case 'tournament': return 'bg-gradient-to-br from-purple-500 to-violet-600 text-white';
    case 'match': return 'bg-gradient-to-br from-amber-600 to-yellow-700 text-white';
    default: return 'bg-gradient-to-br from-gray-500 to-slate-600 text-white';
  }
};

// Get title color berdasarkan type
const getTitleColor = (type) => {
  switch(type) {
    case 'success': return 'text-green-600 dark:text-green-400';
    case 'warning': return 'text-amber-600 dark:text-amber-400';
    case 'danger': return 'text-red-600 dark:text-red-400';
    case 'payment': return 'text-blue-600 dark:text-blue-400';
    case 'tournament': return 'text-purple-600 dark:text-purple-400';
    case 'match': return 'text-amber-600 dark:text-amber-400';
    default: return 'text-gray-700 dark:text-gray-300';
  }
};

// Get background color untuk notifikasi unread
const getCardBg = (isRead, type) => {
  if (isRead) return 'bg-white dark:bg-gray-800';
  
  switch(type) {
    case 'success': return 'bg-green-50 dark:bg-green-900/10 border-l-4 border-green-500';
    case 'warning': return 'bg-amber-50 dark:bg-amber-900/10 border-l-4 border-amber-500';
    case 'danger': return 'bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500';
    case 'payment': return 'bg-blue-50 dark:bg-blue-900/10 border-l-4 border-blue-500';
    case 'tournament': return 'bg-purple-50 dark:bg-purple-900/10 border-l-4 border-purple-500';
    case 'match': return 'bg-amber-50 dark:bg-amber-900/10 border-l-4 border-amber-500';
    default: return 'bg-gray-50 dark:bg-gray-900/10 border-l-4 border-gray-500';
  }
};

// Get border color untuk notifikasi unread
const getCardBorder = (isRead) => {
  if (isRead) return 'border border-gray-200 dark:border-gray-700';
  return 'border border-transparent';
};

const handleNotifClick = (item) => {
  // Tandai sebagai sudah dibaca
  if (!item.is_read) {
    notifStore.markAsRead(item.id);
  }
  
  // Simpan notifikasi yang dipilih
  selectedNotif.value = item;
  showModal.value = true;
};

const handleAction = () => {
  if (selectedNotif.value?.action_url) {
    router.push(selectedNotif.value.action_url);
    closeModal();
  }
};

const closeModal = () => {
  showModal.value = false;
  setTimeout(() => {
    selectedNotif.value = null;
  }, 300);
};

const markAllAsRead = () => {
  notifStore.markAsRead(null);
};

onMounted(() => {
  notifStore.fetchNotifications();
});

// Handle ESC key to close modal
const handleKeydown = (e) => {
  if (e.key === 'Escape' && showModal.value) {
    closeModal();
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    
    <header class="sticky top-0 w-full z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between max-w-7xl mx-auto w-full">
        <div class="flex items-center gap-3">
          <button @click="router.back()" class="w-9 h-9 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center">
              <span class="text-white font-bold">✉️</span>
            </div>
            <div>
              <h1 class="font-semibold text-gray-800 dark:text-white">{{ t('notification.header.title') }}</h1>
              <p class="text-xs text-gray-500 dark:text-gray-400" v-if="notifStore.unreadCount > 0">
                {{ t('notification.header.unread_count', { count: notifStore.unreadCount }) }}
              </p>
            </div>
          </div>
        </div>
        
        <button 
          v-if="notifStore.unreadCount > 0"
          @click="markAllAsRead"
          class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-lg transition shadow-sm hover:shadow"
        >
          {{ t('notification.header.mark_all_read') }}
        </button>
      </div>
    </header>

    <main class="px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto w-full">
      
      <div v-if="notifStore.loading" class="space-y-4">
        <div v-for="i in 5" :key="i" class="h-20 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 animate-pulse"></div>
      </div>

      <div v-else-if="notifStore.list.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center text-4xl mb-4 opacity-50">
          📭
        </div>
        <p class="text-lg font-medium text-gray-600 dark:text-gray-400 mb-2">{{ t('notification.empty.title') }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-500">{{ t('notification.empty.desc') }}</p>
      </div>

      <div v-else class="space-y-3">
        <div 
          v-for="item in notifStore.list" 
          :key="item.id"
          @click="handleNotifClick(item)"
          class="group relative p-4 rounded-xl transition-all cursor-pointer active:scale-[0.99] duration-200"
          :class="[getCardBg(item.is_read, item.type), getCardBorder(item.is_read)]"
        >
          <div v-if="!item.is_read" class="absolute top-4 right-4 w-2.5 h-2.5 bg-blue-500 rounded-full ring-2 ring-white dark:ring-gray-800 shadow-sm"></div>

          <div class="flex gap-4">
            <div class="mt-0.5 w-12 h-12 rounded-xl flex items-center justify-center text-lg shadow-sm"
                 :class="getIconColor(item.type)">
               {{ getNotifIcon(item.type) }}
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="text-sm font-bold leading-tight" 
                      :class="[getTitleColor(item.type), {'opacity-90': item.is_read}]">
                    {{ item.title }}
                  </h4>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed line-clamp-2">
                    {{ item.message }}
                  </p>
                </div>
              </div>
              
              <div class="flex justify-between items-center mt-3">
                <div class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ formatDate(item.created_at) }}
                </div>
                
                <span v-if="item.action_url" class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 group-hover:bg-blue-50 group-hover:text-blue-600 dark:group-hover:bg-blue-900/30 dark:group-hover:text-blue-400 transition">
                  {{ t('notification.card.view_detail') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>

    <transition name="fade">
      <div v-if="showModal" 
           @click.self="closeModal"
           class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        
        <transition name="slide-up">
          <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md max-h-[85vh] overflow-hidden shadow-2xl border border-gray-200 dark:border-gray-700">
            
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg"
                       :class="getIconColor(selectedNotif?.type)">
                    {{ getNotifIcon(selectedNotif?.type) }}
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-800 dark:text-white">{{ t('notification.modal.title') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('notification.modal.subtitle') }}</p>
                  </div>
                </div>
                <button @click="closeModal" 
                        class="w-8 h-8 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition">
                  <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="p-6 overflow-y-auto max-h-[calc(85vh-180px)]">
              <h2 class="text-lg font-bold mb-2" :class="getTitleColor(selectedNotif?.type)">
                {{ selectedNotif?.title }}
              </h2>
              
              <div class="flex items-center gap-2 mb-6 text-sm text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ selectedNotif ? formatFullDate(selectedNotif.created_at) : '' }}</span>
                <span class="mx-2">•</span>
                <span v-if="selectedNotif?.is_read" class="text-green-600 dark:text-green-400 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ t('notification.modal.read') }}
                </span>
                <span v-else class="text-blue-600 dark:text-blue-400 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                  </svg>
                  {{ t('notification.modal.unread') }}
                </span>
              </div>
              
              <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 mb-6">
                <div class="whitespace-pre-wrap text-gray-700 dark:text-gray-300 leading-relaxed">
                  {{ selectedNotif?.message }}
                </div>
              </div>
              
              <div v-if="selectedNotif?.metadata" class="text-sm space-y-3">
                <h4 class="font-medium text-gray-600 dark:text-gray-400 mb-2">{{ t('notification.modal.additional_info') }}:</h4>
                <div v-for="(value, key) in selectedNotif.metadata" :key="key" 
                     class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                  <span class="text-gray-500 dark:text-gray-400 capitalize">{{ key.replace('_', ' ') }}:</span>
                  <span class="font-medium text-gray-800 dark:text-white">{{ value }}</span>
                </div>
              </div>
            </div>

            <div class="sticky bottom-0 bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
              <div class="flex gap-3">
                <button @click="closeModal" 
                        class="flex-1 py-3 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition font-medium">
                  {{ t('notification.modal.close') }}
                </button>
                
                <button v-if="selectedNotif?.action_url" 
                        @click="handleAction"
                        class="flex-1 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 transition font-medium shadow-sm hover:shadow">
                  {{ selectedNotif.action_label || t('notification.modal.view_action') }}
                </button>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </transition>

  </div>
</template>

<style scoped>
/* Animasi untuk modal */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(20px);
  opacity: 0;
}

/* Custom scrollbar untuk modal body */
.modal-body-scroll::-webkit-scrollbar {
  width: 6px;
}

.modal-body-scroll::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.modal-body-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.modal-body-scroll::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Dark mode scrollbar */
.dark .modal-body-scroll::-webkit-scrollbar-track {
  background: #1f2937;
}

.dark .modal-body-scroll::-webkit-scrollbar-thumb {
  background: #4b5563;
}

.dark .modal-body-scroll::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}

/* Line clamp for message preview */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Hover effects for notification cards */
.group:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Active state for notification cards */
.group:active {
  transform: scale(0.99);
}
</style>