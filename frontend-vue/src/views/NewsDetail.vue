<script setup>
import { ref, onMounted, computed, watchEffect } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Share2, Heart, Bookmark, Clock, User, ChevronLeft, Calendar, Tag, ExternalLink, Send, ArrowUp, Menu, X } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t, locale } = useI18n(); // [BARU] Init
const route = useRoute();
const router = useRouter();

// State
const loading = ref(true);
const news = ref(null);
const relatedNews = ref([]);
const isLiked = ref(false);
const isBookmarked = ref(false);
const likeCount = ref(124);
const showShareOptions = ref(false);
const showMobileMenu = ref(false);
const isScrolled = ref(false);

// Computed
// [UPDATED] Menggunakan t() untuk label menit
const readingTime = computed(() => {
  if (!news.value?.content) return `3 ${t('news_detail.meta.min_read')}`;
  const wordCount = news.value.content.replace(/<[^>]*>/g, '').split(/\s+/).length;
  const minutes = Math.ceil(wordCount / 200);
  return `${minutes} ${t('news_detail.meta.min_read')}`;
});

// [UPDATED] Format tanggal dinamis
const formattedDate = computed(() => {
  if (!news.value?.date) return '';
  const date = new Date(news.value.date);
  const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
  return date.toLocaleDateString(currentLocale, {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
});

// Helper untuk format tanggal di related news
const formatRelatedDate = (dateStr) => {
    const date = new Date(dateStr);
    const currentLocale = locale.value === 'id' ? 'id-ID' : 'en-US';
    return date.toLocaleDateString(currentLocale, { day: 'numeric', month: 'short' });
}

// Methods
const fetchNewsDetail = async () => {
  loading.value = true;
  try {
    // Simulasi data dengan loading delay
    await new Promise(resolve => setTimeout(resolve, 800));
    
    // NOTE: Konten artikel (judul, isi) biasanya datang dari DB dan mungkin tidak diterjemahkan otomatis oleh i18n frontend
    news.value = {
      title: "Strategi Ampuh Meningkatkan Rating ELO dalam Turnamen Catur Online",
      category: "Tips & Strategi",
      author: "Admin Percasi",
      authorRole: "Master FIDE",
      date: "2025-12-21",
      views: "2.5K",
      content: `
        <p class="lead">Bermain catur dalam tekanan turnamen tentu berbeda dengan bermain santai. Untuk meningkatkan rating ELO, konsistensi adalah kunci utama yang harus Anda perhatikan.</p>
        
        <h2>1. Analisis Setiap Kekalahan</h2>
        <p>Jangan langsung memulai permainan baru setelah kalah. Luangkan waktu 10-15 menit untuk menganalisis permainan Anda. Gunakan Engine Stockfish untuk melihat di mana letak kesalahan blunder Anda.</p>
        
        <div class="quote">
          <p>"Catur bukan hanya tentang membuat langkah yang bagus, tetapi tentang menghindari langkah buruk. Catur adalah perjuangan melawan kesalahan diri sendiri."</p>
          <span>— Grandmaster Garry Kasparov</span>
        </div>
        
        <p>Analisis pasca-pertandingan membantu Anda memahami pola kesalahan dan memperbaiki strategi untuk pertandingan berikutnya.</p>
        
        <h2>2. Manajemen Waktu yang Bijak</h2>
        <p>Dalam turnamen dengan kontrol waktu Rapid atau Blitz, manajemen waktu sangat krusial. Alokasikan waktu Anda dengan bijak:</p>
        <ul>
          <li><strong>Opening:</strong> Jangan menghabiskan terlalu banyak waktu untuk pembukaan yang sudah Anda kuasai</li>
          <li><strong>Middle Game:</strong> Fokus pada posisi kritis dan pertimbangan taktis</li>
          <li><strong>Endgame:</strong> Persiapkan waktu lebih untuk akhir permainan yang kompleks</li>
        </ul>
        
        <h2>3. Latihan Rutin dan Terstruktur</h2>
        <p>Konsistensi dalam latihan lebih penting daripada durasi latihan. Ciptakan rutinitas harian:</p>
        <ol>
          <li>30 menit teka-teki taktis</li>
          <li>Analisis 1 partai master setiap hari</li>
          <li>Latihan endgame dasar</li>
          <li>Review pembukaan favorit</li>
        </ol>
        
        <h2>4. Persiapan Mental dan Fisik</h2>
        <p>Kondisi fisik dan mental yang prima sangat memengaruhi performa permainan:</p>
        <ul>
          <li>Tidur yang cukup sebelum turnamen</li>
          <li>Konsumsi makanan bergizi dan cukup hidrasi</li>
          <li>Teknik pernapasan untuk mengelola stres</li>
          <li>Visualisasi positif sebelum pertandingan</li>
        </ul>
      `,
      image: "https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?auto=format&fit=crop&w=1200&h=600&q=80",
      tags: ["Strategi", "ELO", "Turnamen", "Catur Online", "Tips", "Peningkatan Skill"],
      stats: {
        likes: 124,
        shares: 45,
        comments: 28
      }
    };

    relatedNews.value = [
      { 
        id: 1, 
        title: "Update Sistem Pembayaran QRIS Kini Lebih Cepat dan Aman", 
        date: "2025-12-20",
        category: "Update Sistem",
        image: "https://images.unsplash.com/photo-1613243555978-636c48dc653c?auto=format&fit=crop&w=400&h=250&q=80",
        readTime: "2 min"
      },
      { 
        id: 2, 
        title: "Daftar Turnamen Nasional Januari 2026: Hadiah Total Rp 500 Juta", 
        date: "2025-12-19",
        category: "Turnamen",
        image: "https://images.unsplash.com/photo-1524178234883-043d5c3f3cf4?auto=format&fit=crop&w=400&h=250&q=80",
        readTime: "4 min"
      },
      { 
        id: 3, 
        title: "Teknik Opening Terbaru dalam Catur Modern 2025", 
        date: "2025-12-18",
        category: "Strategi",
        image: "https://images.unsplash.com/photo-1579546929662-711aa81148cf?auto=format&fit=crop&w=400&h=250&q=80",
        readTime: "5 min"
      },
      { 
        id: 4, 
        title: "Tips Mengatasi Mental Block Saat Bertanding", 
        date: "2025-12-17",
        category: "Psikologi",
        image: "https://images.unsplash.com/photo-1552058544-f2b08422138a?auto=format&fit=crop&w=400&h=250&q=80",
        readTime: "3 min"
      }
    ];
  } catch (error) {
    console.error("Gagal memuat berita:", error);
  } finally {
    loading.value = false;
  }
};

const toggleLike = () => {
  isLiked.value = !isLiked.value;
  likeCount.value += isLiked.value ? 1 : -1;
  
  if (isLiked.value) {
    const likeBtn = document.querySelector('.like-btn');
    likeBtn.classList.add('animate-like');
    setTimeout(() => {
      likeBtn.classList.remove('animate-like');
    }, 600);
  }
};

const toggleBookmark = () => {
  isBookmarked.value = !isBookmarked.value;
  
  if (isBookmarked.value) {
    const bookmarkBtn = document.querySelector('.bookmark-btn');
    bookmarkBtn.classList.add('animate-bookmark');
    setTimeout(() => {
      bookmarkBtn.classList.remove('animate-bookmark');
    }, 600);
  }
};

const shareArticle = (platform) => {
  const shareUrl = window.location.href;
  const title = news.value?.title || '';
  
  switch(platform) {
    case 'whatsapp':
      window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + shareUrl)}`, '_blank');
      break;
    case 'twitter':
      window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(shareUrl)}`, '_blank');
      break;
    case 'facebook':
      window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`, '_blank');
      break;
    case 'copy':
      navigator.clipboard.writeText(shareUrl);
      showToast(t('news_detail.toast.copy_success')); // [UPDATED]
      break;
  }
  
  showShareOptions.value = false;
};

const showToast = (message) => {
  const toast = document.createElement('div');
  toast.className = 'toast-notification';
  toast.textContent = message;
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.remove();
  }, 3000);
};

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
};

onMounted(() => {
  window.addEventListener('scroll', () => {
    isScrolled.value = window.scrollY > 50;
  });
  fetchNewsDetail();
});

watchEffect(() => {
  if (route.params.id) {
    fetchNewsDetail();
  }
});
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-gray-950 transition-colors duration-300">
    <header 
      class="sticky top-0 z-50 transition-all duration-300"
      :class="isScrolled ? 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl shadow-sm' : 'bg-transparent'"
    >
      <div class="px-4 sm:px-6 py-3">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
          <div class="flex items-center gap-3">
            <button 
              @click="router.back()" 
              class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-md transition-all active:scale-95 touch-manipulation"
              :aria-label="t('common.back')"
            >
              <ChevronLeft class="w-5 h-5 text-gray-600 dark:text-gray-300" />
            </button>
            <div class="hidden sm:block">
              <h1 class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ t('news_detail.header.title') }}</h1>
              <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('news_detail.header.subtitle') }}</p>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <button 
              @click="toggleBookmark"
              class="bookmark-btn relative w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-md transition-all active:scale-95 touch-manipulation flex items-center justify-center"
              :class="{ 'text-emerald-600 dark:text-emerald-400': isBookmarked }"
            >
              <Bookmark :fill="isBookmarked ? 'currentColor' : 'none'" class="w-5 h-5 transition-all" />
            </button>
            
            <button 
              @click="showShareOptions = !showShareOptions"
              class="w-10 h-10 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md hover:shadow-lg transition-all active:scale-95 touch-manipulation flex items-center justify-center"
            >
              <Share2 class="w-5 h-5" />
            </button>
            
            <button 
              @click="showMobileMenu = !showMobileMenu"
              class="sm:hidden w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
            >
              <Menu v-if="!showMobileMenu" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
              <X v-else class="w-5 h-5 text-gray-600 dark:text-gray-300" />
            </button>
          </div>
        </div>
      </div>
      
      <div 
        v-if="showMobileMenu"
        class="sm:hidden fixed inset-x-0 top-16 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 shadow-xl animate-slide-down"
      >
        <div class="px-4 py-3 space-y-2">
          <button class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            <Heart class="w-5 h-5" />
            <span>{{ t('news_detail.menu.liked') }}</span>
          </button>
          <button class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            <Bookmark class="w-5 h-5" />
            <span>{{ t('news_detail.menu.saved') }}</span>
          </button>
          <button class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            <User class="w-5 h-5" />
            <span>{{ t('news_detail.menu.profile') }}</span>
          </button>
        </div>
      </div>
    </header>

    <transition name="fade">
      <div 
        v-if="showShareOptions" 
        @click="showShareOptions = false"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm"
      >
        <div 
          @click.stop
          class="bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-3xl p-6 w-full max-w-md sm:max-w-lg animate-slide-up shadow-2xl"
        >
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ t('news_detail.share.title') }}</h3>
            <button 
              @click="showShareOptions = false" 
              class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
              <X class="w-5 h-5 text-gray-500" />
            </button>
          </div>
          
          <div class="grid grid-cols-4 gap-3 sm:gap-4">
            <button 
              @click="shareArticle('whatsapp')"
              class="flex flex-col items-center p-4 rounded-2xl bg-gradient-to-b from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/10 hover:from-green-100 hover:to-green-200 dark:hover:from-green-900/30 dark:hover:to-green-900/20 transition-all active:scale-95 touch-manipulation"
            >
              <div class="w-12 h-12 sm:w-14 sm:h-14 bg-green-500 rounded-full flex items-center justify-center mb-2 shadow-lg">
                <span class="text-white font-bold text-lg">WA</span>
              </div>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('news_detail.share.whatsapp') }}</span>
            </button>
            
            <button 
              @click="shareArticle('twitter')"
              class="flex flex-col items-center p-4 rounded-2xl bg-gradient-to-b from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10 hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-900/30 dark:hover:to-blue-900/20 transition-all active:scale-95 touch-manipulation"
            >
              <div class="w-12 h-12 sm:w-14 sm:h-14 bg-blue-400 rounded-full flex items-center justify-center mb-2 shadow-lg">
                <span class="text-white font-bold text-lg">𝕏</span>
              </div>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('news_detail.share.twitter') }}</span>
            </button>
            
            <button 
              @click="shareArticle('facebook')"
              class="flex flex-col items-center p-4 rounded-2xl bg-gradient-to-b from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-900/10 hover:from-indigo-100 hover:to-indigo-200 dark:hover:from-indigo-900/30 dark:hover:to-indigo-900/20 transition-all active:scale-95 touch-manipulation"
            >
              <div class="w-12 h-12 sm:w-14 sm:h-14 bg-indigo-600 rounded-full flex items-center justify-center mb-2 shadow-lg">
                <span class="text-white font-bold text-lg">f</span>
              </div>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('news_detail.share.facebook') }}</span>
            </button>
            
            <button 
              @click="shareArticle('copy')"
              class="flex flex-col items-center p-4 rounded-2xl bg-gradient-to-b from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/10 hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-900/30 dark:hover:to-purple-900/20 transition-all active:scale-95 touch-manipulation"
            >
              <div class="w-12 h-12 sm:w-14 sm:h-14 bg-purple-500 rounded-full flex items-center justify-center mb-2 shadow-lg">
                <span class="text-white font-bold text-lg">📋</span>
              </div>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('news_detail.share.copy') }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <div v-if="loading" class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
      <div class="space-y-8">
        <div class="space-y-4">
          <div class="h-4 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700 rounded-full w-32 animate-pulse"></div>
          <div class="h-8 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700 rounded-2xl w-3/4 animate-pulse"></div>
          <div class="h-6 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700 rounded-2xl w-1/2 animate-pulse"></div>
        </div>
        <div class="h-64 sm:h-80 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700 rounded-3xl animate-pulse"></div>
        <div class="space-y-3">
          <div v-for="i in 5" :key="i" class="h-4 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700 rounded-full animate-pulse" :style="`width: ${Math.random() * 60 + 40}%`"></div>
        </div>
      </div>
    </div>

    <main v-else class="max-w-3xl mx-auto px-4 sm:px-6 py-6 pb-20">
      <div class="mb-8 sm:mb-10">
        <div class="flex flex-wrap items-center gap-3 mb-4">
          <span class="px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-bold rounded-full shadow-sm">
            {{ news.category }}
          </span>
          <div class="flex items-center gap-3 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-1.5">
              <Clock class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
              <span>{{ readingTime }}</span>
            </div>
            <div class="w-1 h-1 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
            <div class="flex items-center gap-1.5">
              <Calendar class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
              <span>{{ formattedDate }}</span>
            </div>
            <div class="w-1 h-1 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
            <span>👁️ {{ news.views }} {{ t('news_detail.meta.views') }}</span>
          </div>
        </div>

        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white leading-tight mb-6 sm:mb-8">
          {{ news.title }}
        </h1>

        <div class="flex items-center gap-4 p-4 sm:p-5 bg-gradient-to-r from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl mb-6 sm:mb-8 border border-gray-100 dark:border-gray-800 shadow-sm">
          <div class="relative">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-md">
              <User class="w-7 h-7 sm:w-8 sm:h-8 text-white" />
            </div>
            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center">
              <span class="text-xs text-white font-bold">✓</span>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <h4 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base truncate">{{ news.author }}</h4>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 truncate">{{ news.authorRole }}</p>
            <div class="flex items-center gap-3 mt-1.5">
              <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">● {{ t('news_detail.meta.verified') }}</span>
              <span class="text-xs text-gray-400">•</span>
              <span class="text-xs text-gray-500">{{ t('news_detail.meta.articles_count') }}</span>
            </div>
          </div>
          <button class="hidden sm:flex items-center gap-2 px-4 py-2.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl text-sm font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
            {{ t('news_detail.meta.follow') }}
          </button>
        </div>
      </div>

      <div class="relative rounded-3xl overflow-hidden mb-8 sm:mb-10 shadow-xl">
        <div class="aspect-video w-full overflow-hidden">
          <img 
            :src="news.image" 
            :alt="news.title"
            class="w-full h-full object-cover transition-transform duration-700 hover:scale-105"
            loading="lazy"
          />
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        
        <div class="absolute bottom-4 right-4 flex items-center gap-2">
          <button 
            @click="toggleLike"
            class="like-btn flex items-center gap-2 px-4 py-2.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-full shadow-lg hover:shadow-xl transition-all active:scale-95"
          >
            <Heart 
              :fill="isLiked ? 'currentColor' : 'none'" 
              class="w-5 h-5 transition-all"
              :class="isLiked ? 'text-red-500' : 'text-gray-600 dark:text-gray-400'"
            />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ likeCount }}</span>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl mb-8">
        <div class="flex items-center gap-4">
          <div class="text-center">
            <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">{{ news.stats.likes }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('news_detail.stats.likes') }}</div>
          </div>
          <div class="h-8 w-px bg-gray-200 dark:bg-gray-800"></div>
          <div class="text-center">
            <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">{{ news.stats.comments }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('news_detail.stats.comments') }}</div>
          </div>
          <div class="h-8 w-px bg-gray-200 dark:bg-gray-800"></div>
          <div class="text-center">
            <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">{{ news.stats.shares }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('news_detail.stats.shares') }}</div>
          </div>
        </div>
        <button class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
          <ExternalLink class="w-5 h-5" />
        </button>
      </div>

      <article 
        class="prose prose-base sm:prose-lg dark:prose-invert max-w-none 
               prose-headings:text-gray-900 dark:prose-headings:text-white prose-headings:font-bold
               prose-p:text-gray-600 dark:prose-p:text-gray-400 prose-p:leading-relaxed
               prose-li:text-gray-600 dark:prose-li:text-gray-400
               prose-strong:text-gray-900 dark:prose-strong:text-white
               prose-blockquote:border-emerald-500 prose-blockquote:bg-gradient-to-r prose-blockquote:from-emerald-50 prose-blockquote:to-teal-50
               dark:prose-blockquote:from-emerald-900/10 dark:prose-blockquote:to-teal-900/10
               prose-blockquote:px-6 prose-blockquote:py-5 prose-blockquote:rounded-2xl prose-blockquote:shadow-sm
               prose-blockquote:border-l-4
               prose-img:rounded-2xl prose-img:shadow-lg prose-img:mx-auto
               prose-ul:space-y-2 prose-ol:space-y-2
               prose-a:text-emerald-600 dark:prose-a:text-emerald-400 prose-a:no-underline hover:prose-a:underline
               mb-10 sm:mb-12"
        v-html="news.content"
      >
      </article>

      <div class="mb-10 sm:mb-12">
        <div class="flex items-center gap-2 mb-4">
          <Tag class="w-5 h-5 text-gray-400" />
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ t('news_detail.tags_title') }}</h3>
        </div>
        <div class="flex flex-wrap gap-2.5">
          <button 
            v-for="tag in news.tags" 
            :key="tag"
            class="px-4 py-2 bg-gradient-to-r from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:from-gray-100 hover:to-gray-50 dark:hover:from-gray-800 dark:hover:to-gray-700 transition-all border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 active:scale-95 touch-manipulation shadow-sm"
          >
            #{{ tag }}
          </button>
        </div>
      </div>

      <div class="sticky bottom-20 sm:static mb-8 sm:mb-10">
        <div class="flex items-center gap-3 p-4 bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800">
          <button 
            @click="toggleLike"
            class="like-btn flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors active:scale-95 touch-manipulation"
            :class="{ 'text-red-500 border-red-200 dark:border-red-900/30 bg-red-50 dark:bg-red-900/10': isLiked }"
          >
            <Heart :fill="isLiked ? 'currentColor' : 'none'" class="w-5 h-5" />
            <span class="font-medium">{{ isLiked ? t('news_detail.actions.liked') : t('news_detail.actions.like') }}</span>
          </button>
          
          <button 
            @click="showShareOptions = true"
            class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-medium hover:from-emerald-600 hover:to-teal-700 transition-all active:scale-95 touch-manipulation shadow-sm"
          >
            <Share2 class="w-5 h-5" />
            <span>{{ t('news_detail.actions.share') }}</span>
          </button>
        </div>
      </div>

      <div class="border-t border-gray-100 dark:border-gray-900 pt-10 sm:pt-12">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ t('news_detail.related.title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ t('news_detail.related.subtitle') }}</p>
          </div>
          <button 
            @click="router.push('/news')" 
            class="hidden sm:flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-medium hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors"
          >
            {{ t('news_detail.related.view_all') }}
            <ExternalLink class="w-4 h-4" />
          </button>
        </div>

        <div class="flex overflow-x-auto sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 pb-4 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-hide">
          <article 
            v-for="item in relatedNews" 
            :key="item.id"
            @click="router.push(`/news/${item.id}`)"
            class="group flex-shrink-0 w-[280px] sm:w-auto bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 dark:border-gray-800 hover:border-emerald-200 dark:hover:border-emerald-800 active:scale-[0.98] touch-manipulation"
          >
            <div class="relative aspect-[4/3] overflow-hidden">
              <img 
                :src="item.image" 
                :alt="item.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
              />
              <div class="absolute top-3 left-3">
                <span class="px-3 py-1 bg-emerald-600 text-white text-xs font-bold rounded-full shadow-sm">
                  {{ item.category }}
                </span>
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </div>
            
            <div class="p-4 sm:p-5">
              <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-3">
                <Clock class="w-3.5 h-3.5" />
                <span>{{ item.readTime }}</span>
                <span>•</span>
                <span>{{ formatRelatedDate(item.date) }}</span>
              </div>
              
              <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors leading-tight">
                {{ item.title }}
              </h3>
              
              <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                <button class="text-emerald-600 dark:text-emerald-400 font-medium text-xs sm:text-sm hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors flex items-center gap-1.5">
                  {{ t('news_detail.related.read_more') }}
                  <ChevronLeft class="w-3.5 h-3.5 rotate-180" />
                </button>
                <button 
                  @click.stop
                  class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                >
                  <Bookmark class="w-4 h-4 text-gray-400" />
                </button>
              </div>
            </div>
          </article>
        </div>
        
        <button 
          @click="router.push('/news')" 
          class="sm:hidden w-full mt-6 px-4 py-3 bg-gray-50 dark:bg-gray-900 text-emerald-600 dark:text-emerald-400 font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border border-gray-200 dark:border-gray-800"
        >
          {{ t('news_detail.related.view_all_mobile') }}
        </button>
      </div>

      <div class="mt-10 sm:mt-12 p-6 sm:p-8 bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 rounded-3xl text-center relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
        <div class="relative z-10">
          <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 sm:mb-6 backdrop-blur-sm">
            <Send class="w-8 h-8 sm:w-10 sm:h-10 text-white" />
          </div>
          <h3 class="text-xl sm:text-2xl font-bold text-white mb-2">{{ t('news_detail.newsletter.title') }}</h3>
          <p class="text-emerald-100 text-sm sm:text-base mb-6 sm:mb-8 max-w-md mx-auto">{{ t('news_detail.newsletter.desc') }}</p>
          <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            <input 
              type="email" 
              :placeholder="t('news_detail.newsletter.placeholder')"
              class="flex-1 px-4 py-3 sm:py-4 rounded-xl bg-white/20 backdrop-blur-sm text-white placeholder-emerald-200 border border-emerald-300/50 focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent text-sm sm:text-base"
            />
            <button class="px-6 py-3 sm:py-4 bg-white text-emerald-600 font-bold rounded-xl hover:bg-emerald-50 transition-all active:scale-95 shadow-lg touch-manipulation text-sm sm:text-base">
              {{ t('news_detail.newsletter.subscribe') }}
            </button>
          </div>
          <p class="text-emerald-200 text-xs mt-4">{{ t('news_detail.newsletter.privacy') }}</p>
        </div>
      </div>
    </main>

    <div class="fixed bottom-6 right-4 sm:right-6 z-40 flex flex-col gap-3">
      <button 
        @click="scrollToTop"
        class="p-3 sm:p-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl shadow-2xl hover:shadow-3xl hover:scale-105 transition-all active:scale-95 touch-manipulation"
        :class="{ 'opacity-0 translate-y-4 pointer-events-none': !isScrolled }"
      >
        <ArrowUp class="w-5 h-5 sm:w-6 sm:h-6" />
      </button>
    </div>

    <div class="toast-container"></div>
  </div>
</template>

<style scoped>
/* Animations */
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

@keyframes slide-down {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes like-pulse {
  0% { transform: scale(1); }
  25% { transform: scale(1.2); }
  50% { transform: scale(0.9); }
  75% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

@keyframes bookmark-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.animate-slide-up {
  animation: slide-up 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-slide-down {
  animation: slide-down 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom animations for buttons */
.animate-like {
  animation: like-pulse 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-bookmark {
  animation: bookmark-bounce 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar hide */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Touch optimization */
.touch-manipulation {
  touch-action: manipulation;
}

/* Line clamp utilities */
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Toast notification */
.toast-notification {
  position: fixed;
  bottom: 100px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  z-index: 9999;
  backdrop-filter: blur(10px);
  animation: fade-in 0.3s ease;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

/* Custom prose styles */
.prose .lead {
  font-size: 1.125em;
  line-height: 1.7;
  color: #4b5563;
  font-weight: 500;
  margin-bottom: 2em;
  padding: 1.5em;
  background: linear-gradient(135deg, #f0fdf4 0%, #f0f9ff 100%);
  border-radius: 1em;
  border-left: 4px solid #10b981;
}

.prose .quote {
  @apply relative pl-6 my-8 italic;
}

.prose .quote::before {
  content: '';
  @apply absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-emerald-500 to-teal-600 rounded-full;
}

.prose .quote span {
  @apply block mt-4 text-sm text-gray-500 dark:text-gray-400 not-italic font-medium;
}

.prose ul, .prose ol {
  @apply space-y-3;
}

.prose ul li {
  @apply relative pl-6;
}

.prose ul li::before {
  content: '•';
  @apply absolute left-0 text-emerald-500 font-bold;
}

.prose ol li {
  @apply relative pl-6;
}

.prose ol li::before {
  @apply absolute left-0 font-bold text-emerald-500;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .prose {
    font-size: 1rem;
    line-height: 1.7;
  }
  
  .prose h2 {
    font-size: 1.4rem;
    margin-top: 1.8em;
    margin-bottom: 0.8em;
  }
  
  .prose h3 {
    font-size: 1.2rem;
  }
  
  .prose .lead {
    font-size: 1.1rem;
    padding: 1.2em;
  }
  
  .prose img {
    margin: 1.5em 0;
  }
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .prose .lead {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
    color: #d1d5db;
  }
}

/* Safe area insets for modern mobile devices */
@supports (padding: max(0px)) {
  .px-4 {
    padding-left: max(1rem, env(safe-area-inset-left));
    padding-right: max(1rem, env(safe-area-inset-right));
  }
  
  .pb-20 {
    padding-bottom: max(5rem, env(safe-area-inset-bottom));
  }
  
  .fixed.bottom-6 {
    bottom: max(1.5rem, env(safe-area-inset-bottom));
  }
}

/* Reduce motion preference */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>