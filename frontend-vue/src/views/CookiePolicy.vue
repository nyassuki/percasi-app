<template>
  <div class="legal-page cookie-policy">
    <LegalNavigation />
    
    <main class="legal-content">
      <div class="container">
        <header class="page-header">
          <h1 class="page-title">
            <i class="title-icon cookie-icon"></i>
            Kebijakan Cookie
          </h1>
          <p class="page-subtitle">Terakhir diperbarui: {{ lastUpdated }}</p>
          <p class="page-intro">
            Kebijakan Cookie ini menjelaskan bagaimana kami menggunakan cookie dan teknologi pelacakan serupa 
            untuk meningkatkan pengalaman Anda di Platform Catur Online kami.
          </p>
          
          <div class="cookie-controls">
            <div class="control-buttons">
              <button class="btn-accept-all" @click="acceptAllCookies">
                Terima Semua Cookie
              </button>
              <button class="btn-customize" @click="showCustomization = true">
                Sesuaikan Preferensi
              </button>
              <button class="btn-reject-all" @click="rejectAllCookies">
                Tolak Semua Cookie
              </button>
            </div>
            
            <div class="current-status" v-if="cookiesAccepted !== null">
              <i :class="statusIcon"></i>
              <span>{{ statusMessage }}</span>
            </div>
          </div>
        </header>

        <div class="content-wrapper">
          <!-- Tabel Isi -->
          <aside class="table-of-contents">
            <h3 class="toc-title">Daftar Isi</h3>
            <nav class="toc-nav">
              <ul>
                <li v-for="section in toc" :key="section.id">
                  <a :href="`#${section.id}`" @click.prevent="scrollTo(section.id)">
                    {{ section.title }}
                  </a>
                </li>
              </ul>
            </nav>
            
            <div class="cookie-summary">
              <h4>Ringkasan Cookie</h4>
              <div class="summary-stats">
                <div class="stat">
                  <span class="stat-value">{{ cookieCount.total }}</span>
                  <span class="stat-label">Total Cookie</span>
                </div>
                <div class="stat">
                  <span class="stat-value">{{ cookieCount.essential }}</span>
                  <span class="stat-label">Wajib</span>
                </div>
                <div class="stat">
                  <span class="stat-value">{{ cookieCount.optional }}</span>
                  <span class="stat-label">Opsional</span>
                </div>
              </div>
            </div>
          </aside>

          <!-- Konten Utama -->
          <article class="main-content">
            <!-- Bagian 1: Apa Itu Cookie -->
            <section id="what-are-cookies" class="content-section">
              <h2 class="section-title">
                <span class="section-number">1.</span>
                Apa Itu Cookie?
              </h2>
              
              <div class="cookie-explanation">
                <div class="explanation-card">
                  <div class="explanation-icon definition-icon"></div>
                  <h3>Definisi</h3>
                  <p>Cookie adalah file teks kecil yang disimpan di perangkat Anda ketika Anda mengunjungi situs web.</p>
                </div>
                
                <div class="explanation-card">
                  <div class="explanation-icon function-icon"></div>
                  <h3>Fungsi</h3>
                  <p>Cookie membantu situs web mengingat preferensi Anda dan meningkatkan pengalaman pengguna.</p>
                </div>
                
                <div class="explanation-card">
                  <div class="explanation-icon security-icon"></div>
                  <h3>Keamanan</h3>
                  <p>Cookie tidak mengandung virus dan tidak dapat mengakses informasi pribadi di komputer Anda.</p>
                </div>
              </div>
            </section>

            <!-- Bagian 2: Jenis Cookie yang Kami Gunakan -->
            <section id="cookie-types" class="content-section">
              <h2 class="section-title">
                <span class="section-number">2.</span>
                Jenis Cookie yang Kami Gunakan
              </h2>

              <div class="cookie-categories">
                <!-- Cookie Wajib -->
                <div class="category-card essential">
                  <div class="category-header">
                    <h3><i class="category-icon essential-icon"></i> Cookie Wajib</h3>
                    <span class="category-status required">Wajib</span>
                  </div>
                  <p>Cookie yang diperlukan untuk platform berfungsi dengan baik:</p>
                  <ul>
                    <li>Cookie sesi login</li>
                    <li>Cookie keamanan transaksi</li>
                    <li>Cookie preferensi bahasa</li>
                    <li>Cookie penyimpanan keranjang</li>
                  </ul>
                  <div class="category-control">
                    <label class="toggle-label">
                      <input type="checkbox" checked disabled>
                      <span class="toggle-slider"></span>
                      <span class="toggle-text">Selalu aktif</span>
                    </label>
                    <p class="control-note">Tidak dapat dinonaktifkan</p>
                  </div>
                </div>

                <!-- Cookie Fungsionalitas -->
                <div class="category-card functional">
                  <div class="category-header">
                    <h3><i class="category-icon functional-icon"></i> Cookie Fungsionalitas</h3>
                    <span class="category-status optional">Opsional</span>
                  </div>
                  <p>Cookie yang meningkatkan pengalaman pengguna:</p>
                  <ul>
                    <li>Preferensi tema (gelap/terang)</li>
                    <li>Pengaturan notifikasi</li>
                    <li>Layout papan catur yang disimpan</li>
                    <li>Riwayat pencarian pemain</li>
                  </ul>
                  <div class="category-control">
                    <label class="toggle-label">
                      <input 
                        type="checkbox" 
                        v-model="cookiePreferences.functional"
                        @change="updatePreferences"
                      >
                      <span class="toggle-slider"></span>
                      <span class="toggle-text">{{ cookiePreferences.functional ? 'Aktif' : 'Nonaktif' }}</span>
                    </label>
                  </div>
                </div>

                <!-- Cookie Analitik -->
                <div class="category-card analytics">
                  <div class="category-header">
                    <h3><i class="category-icon analytics-icon"></i> Cookie Analitik</h3>
                    <span class="category-status optional">Opsional</span>
                  </div>
                  <p>Cookie untuk memahami penggunaan platform:</p>
                  <ul>
                    <li>Statistik kunjungan</li>
                    <li>Analisis fitur yang populer</li>
                    <li>Pengukuran performa platform</li>
                    <li>Deteksi masalah teknis</li>
                  </ul>
                  <div class="category-control">
                    <label class="toggle-label">
                      <input 
                        type="checkbox" 
                        v-model="cookiePreferences.analytics"
                        @change="updatePreferences"
                      >
                      <span class="toggle-slider"></span>
                      <span class="toggle-text">{{ cookiePreferences.analytics ? 'Aktif' : 'Nonaktif' }}</span>
                    </label>
                  </div>
                </div>

                <!-- Cookie Pemasaran -->
                <div class="category-card marketing">
                  <div class="category-header">
                    <h3><i class="category-icon marketing-icon"></i> Cookie Pemasaran</h3>
                    <span class="category-status optional">Opsional</span>
                  </div>
                  <p>Cookie untuk menampilkan iklan yang relevan:</p>
                  <ul>
                    <li>Iklan turnamen catur</li>
                    <li>Promosi merchant</li>
                    <li>Rekomendasi pemain</li>
                    <li>Notifikasi event</li>
                  </ul>
                  <div class="category-control">
                    <label class="toggle-label">
                      <input 
                        type="checkbox" 
                        v-model="cookiePreferences.marketing"
                        @change="updatePreferences"
                      >
                      <span class="toggle-slider"></span>
                      <span class="toggle-text">{{ cookiePreferences.marketing ? 'Aktif' : 'Nonaktif' }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </section>

            <!-- Bagian 3: Durasi Penyimpanan Cookie -->
            <section id="cookie-duration" class="content-section">
              <h2 class="section-title">
                <span class="section-number">3.</span>
                Durasi Penyimpanan Cookie
              </h2>

              <div class="duration-chart">
                <div class="chart-bars">
                  <div 
                    v-for="item in cookieDurations" 
                    :key="item.type"
                    class="chart-bar"
                    :style="{ width: item.percentage + '%' }"
                    :class="item.type"
                  >
                    <div class="bar-tooltip">
                      <strong>{{ item.type }}</strong>
                      <p>{{ item.duration }}</p>
                      <p>Contoh: {{ item.example }}</p>
                    </div>
                  </div>
                </div>
                
                <div class="chart-labels">
                  <div class="label">Sesi</div>
                  <div class="label">Pendek</div>
                  <div class="label">Menengah</div>
                  <div class="label">Panjang</div>
                </div>
              </div>

              <div class="duration-table">
                <h3>Rincian Durasi Cookie</h3>
                <table>
                  <thead>
                    <tr>
                      <th>Jenis Cookie</th>
                      <th>Nama Cookie</th>
                      <th>Durasi</th>
                      <th>Tujuan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="cookie in detailedCookies" :key="cookie.name">
                      <td><span :class="'badge ' + cookie.type">{{ cookie.typeLabel }}</span></td>
                      <td><code>{{ cookie.name }}</code></td>
                      <td>{{ cookie.duration }}</td>
                      <td>{{ cookie.purpose }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>

            <!-- Bagian 4: Kontrol Cookie -->
            <section id="cookie-control" class="content-section">
              <h2 class="section-title">
                <span class="section-number">4.</span>
                Kontrol Cookie Anda
              </h2>

              <div class="control-options">
                <div class="control-option">
                  <div class="option-icon browser-icon"></div>
                  <div>
                    <h3>Pengaturan Browser</h3>
                    <p>Anda dapat mengelola cookie melalui pengaturan browser:</p>
                    <ul>
                      <li>Chrome: Settings → Privacy and Security → Cookies</li>
                      <li>Firefox: Options → Privacy & Security → Cookies</li>
                      <li>Safari: Preferences → Privacy → Cookies</li>
                      <li>Edge: Settings → Cookies and site permissions</li>
                    </ul>
                  </div>
                </div>

                <div class="control-option">
                  <div class="option-icon platform-icon"></div>
                  <div>
                    <h3>Pengaturan Platform</h3>
                    <p>Atur preferensi cookie langsung di platform kami:</p>
                    <button class="btn-settings" @click="showCustomization = true">
                      Buka Pengaturan Cookie
                    </button>
                    <p class="note">Pengaturan akan disimpan untuk kunjungan berikutnya</p>
                  </div>
                </div>
              </div>

              <div class="browser-support">
                <h3>Dukungan Browser</h3>
                <p>Platform kami mendukung kontrol cookie di browser berikut:</p>
                <div class="browser-icons">
                  <div class="browser-icon chrome"></div>
                  <div class="browser-icon firefox"></div>
                  <div class="browser-icon safari"></div>
                  <div class="browser-icon edge"></div>
                  <div class="browser-icon opera"></div>
                </div>
              </div>
            </section>

            <!-- Bagian 5: Cookie Pihak Ketiga -->
            <section id="third-party-cookies" class="content-section">
              <h2 class="section-title">
                <span class="section-number">5.</span>
                Cookie Pihak Ketiga
              </h2>

              <div class="third-party-info">
                <div class="warning-box">
                  <i class="warning-icon"></i>
                  <div>
                    <h3>Perhatian</h3>
                    <p>Cookie pihak ketiga memiliki kebijakan privasi mereka sendiri yang berbeda dengan kami.</p>
                  </div>
                </div>

                <div class="third-party-list">
                  <div class="third-party-card">
                    <h3><i class="provider-icon payment-icon"></i> Penyedia Pembayaran</h3>
                    <p>Cookie untuk verifikasi transaksi keuangan</p>
                    <a href="#" class="provider-link">Kebijakan Privasi Penyedia</a>
                  </div>
                  
                  <div class="third-party-card">
                    <h3><i class="provider-icon analytics-icon"></i> Google Analytics</h3>
                    <p>Cookie untuk analisis pengunjung</p>
                    <a href="https://policies.google.com/privacy" target="_blank" class="provider-link">
                      Kebijakan Google
                    </a>
                  </div>
                  
                  <div class="third-party-card">
                    <h3><i class="provider-icon chat-icon"></i> Telegram Widget</h3>
                    <p>Cookie untuk integrasi chat Telegram</p>
                    <a href="https://telegram.org/privacy" target="_blank" class="provider-link">
                      Kebijakan Telegram
                    </a>
                  </div>
                </div>
              </div>
            </section>

            <!-- Informasi Kontak -->
            <div class="contact-section">
              <h3>Pertanyaan tentang Cookie?</h3>
              <p>Hubungi petugas perlindungan data kami:</p>
              <div class="contact-info">
                <p><i class="contact-icon email-icon"></i> dpo@chessplatform.com</p>
                <p><i class="contact-icon phone-icon"></i> +62 21 1234 5678 (ext. 901)</p>
              </div>
            </div>
          </article>
        </div>
      </div>

      <!-- Modal Kustomisasi Cookie -->
      <div class="customization-modal" v-if="showCustomization">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Pengaturan Cookie</h2>
            <button class="modal-close" @click="showCustomization = false">&times;</button>
          </div>
          
          <div class="modal-body">
            <p>Sesuaikan preferensi cookie Anda. Cookie wajib tidak dapat dinonaktifkan.</p>
            
            <div class="modal-settings">
              <div class="setting-item" v-for="(value, key) in cookiePreferences" :key="key">
                <div class="setting-info">
                  <h3>{{ getSettingLabel(key) }}</h3>
                  <p>{{ getSettingDescription(key) }}</p>
                </div>
                <label class="modal-toggle">
                  <input 
                    type="checkbox" 
                    v-model="cookiePreferences[key]"
                    :disabled="key === 'essential'"
                  >
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>
            
            <div class="modal-actions">
              <button class="btn-save" @click="savePreferences">
                Simpan Preferensi
              </button>
              <button class="btn-cancel" @click="showCustomization = false">
                Batal
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import LegalNavigation from '@/components/LegalNavigation.vue'

export default {
  name: 'CookiePolicy',
  components: {
    LegalNavigation
  },
  setup() {
    const lastUpdated = ref('15 November 2023')
    const showCustomization = ref(false)
    const cookiesAccepted = ref(null)
    
    const cookiePreferences = ref({
      essential: true,
      functional: true,
      analytics: true,
      marketing: false
    })
    
    const cookieCount = ref({
      total: 24,
      essential: 8,
      optional: 16
    })
    
    const toc = ref([
      { id: 'what-are-cookies', title: 'Apa Itu Cookie?' },
      { id: 'cookie-types', title: 'Jenis Cookie' },
      { id: 'cookie-duration', title: 'Durasi Penyimpanan' },
      { id: 'cookie-control', title: 'Kontrol Cookie' },
      { id: 'third-party-cookies', title: 'Cookie Pihak Ketiga' }
    ])
    
    const cookieDurations = ref([
      { type: 'session', duration: 'Sesi browser', percentage: 25, example: 'Cookie login' },
      { type: 'short', duration: '7 hari', percentage: 20, example: 'Preferensi tema' },
      { type: 'medium', duration: '30 hari', percentage: 35, example: 'Analitik' },
      { type: 'long', duration: '1 tahun', percentage: 20, example: 'Cookie pemasaran' }
    ])
    
    const detailedCookies = ref([
      { type: 'essential', typeLabel: 'Wajib', name: 'auth_token', duration: 'Sesi', purpose: 'Autentikasi pengguna' },
      { type: 'essential', typeLabel: 'Wajib', name: 'session_id', duration: 'Sesi', purpose: 'Identifikasi sesi' },
      { type: 'functional', typeLabel: 'Fungsional', name: 'theme_pref', duration: '30 hari', purpose: 'Preferensi tema' },
      { type: 'functional', typeLabel: 'Fungsional', name: 'notification', duration: '7 hari', purpose: 'Pengaturan notifikasi' },
      { type: 'analytics', typeLabel: 'Analitik', name: '_ga', duration: '2 tahun', purpose: 'Analisis Google' },
      { type: 'analytics', typeLabel: 'Analitik', name: '_gid', duration: '24 jam', purpose: 'Analisis pengunjung' },
      { type: 'marketing', typeLabel: 'Pemasaran', name: 'fbp', duration: '3 bulan', purpose: 'Iklan Facebook' }
    ])
    
    onMounted(() => {
      const saved = localStorage.getItem('cookiePreferences')
      const accepted = localStorage.getItem('cookiesAccepted')
      
      if (saved) {
        cookiePreferences.value = JSON.parse(saved)
      }
      
      if (accepted !== null) {
        cookiesAccepted.value = accepted === 'true'
      }
    })
    
    const scrollTo = (id) => {
      const element = document.getElementById(id)
      if (element) {
        element.scrollIntoView({ behavior: 'smooth' })
      }
    }
    
    const acceptAllCookies = () => {
      cookiePreferences.value = {
        essential: true,
        functional: true,
        analytics: true,
        marketing: true
      }
      
      savePreferences()
      cookiesAccepted.value = true
      showCustomization.value = false
      
      alert('Semua cookie telah diterima. Terima kasih!')
    }
    
    const rejectAllCookies = () => {
      cookiePreferences.value = {
        essential: true,
        functional: false,
        analytics: false,
        marketing: false
      }
      
      savePreferences()
      cookiesAccepted.value = false
      showCustomization.value = false
      
      alert('Semua cookie opsional telah ditolak. Cookie wajib tetap aktif.')
    }
    
    const updatePreferences = () => {
      localStorage.setItem('cookiePreferences', JSON.stringify(cookiePreferences.value))
      
      // Cek apakah ada cookie yang diterima
      const hasAccepted = cookiePreferences.value.functional || 
                         cookiePreferences.value.analytics || 
                         cookiePreferences.value.marketing
      
      cookiesAccepted.value = hasAccepted
      localStorage.setItem('cookiesAccepted', hasAccepted.toString())
    }
    
    const savePreferences = () => {
      updatePreferences()
      showCustomization.value = false
      alert('Preferensi cookie telah disimpan.')
    }
    
    const getSettingLabel = (key) => {
      const labels = {
        essential: 'Cookie Wajib',
        functional: 'Cookie Fungsionalitas',
        analytics: 'Cookie Analitik',
        marketing: 'Cookie Pemasaran'
      }
      return labels[key] || key
    }
    
    const getSettingDescription = (key) => {
      const descriptions = {
        essential: 'Diperlukan untuk platform berfungsi',
        functional: 'Meningkatkan pengalaman pengguna',
        analytics: 'Memahami penggunaan platform',
        marketing: 'Menampilkan iklan yang relevan'
      }
      return descriptions[key] || ''
    }
    
    const statusIcon = computed(() => {
      if (cookiesAccepted.value === null) return 'status-icon pending'
      return cookiesAccepted.value ? 'status-icon accepted' : 'status-icon rejected'
    })
    
    const statusMessage = computed(() => {
      if (cookiesAccepted.value === null) return 'Belum ada keputusan'
      return cookiesAccepted.value ? 'Cookie diterima' : 'Cookie ditolak'
    })
    
    return {
      lastUpdated,
      toc,
      showCustomization,
      cookiesAccepted,
      cookiePreferences,
      cookieCount,
      cookieDurations,
      detailedCookies,
      scrollTo,
      acceptAllCookies,
      rejectAllCookies,
      updatePreferences,
      savePreferences,
      getSettingLabel,
      getSettingDescription,
      statusIcon,
      statusMessage
    }
  }
}
</script>

<style scoped>
.cookie-policy {
  background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
}

.cookie-controls {
  margin-top: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
}

.control-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.btn-accept-all,
.btn-customize,
.btn-reject-all {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 25px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-accept-all {
  background: #4caf50;
  color: white;
}

.btn-accept-all:hover {
  background: #388e3c;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.btn-customize {
  background: #2196f3;
  color: white;
}

.btn-customize:hover {
  background: #1976d2;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
}

.btn-reject-all {
  background: #f44336;
  color: white;
}

.btn-reject-all:hover {
  background: #d32f2f;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
}

.current-status {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 8px;
  background: #f5f5f5;
}

.status-icon {
  width: 16px;
  height: 16px;
  border-radius: 50%;
}

.status-icon.pending {
  background: #ff9800;
}

.status-icon.accepted {
  background: #4caf50;
}

.status-icon.rejected {
  background: #f44336;
}

.cookie-summary {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
}

.summary-stats {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
}

.stat {
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: bold;
  color: #2c3e50;
}

.stat-label {
  display: block;
  font-size: 0.875rem;
  color: #7f8c8d;
}

.cookie-explanation {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.explanation-card {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.explanation-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 1rem;
  background-size: contain;
}

.definition-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/%3E%3C/svg%3E");
}

.function-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E");
}

.security-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z'/%3E%3C/svg%3E");
}

.cookie-categories {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.category-card {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
  border-top: 4px solid;
}

.category-card.essential {
  border-color: #4caf50;
}

.category-card.functional {
  border-color: #2196f3;
}

.category-card.analytics {
  border-color: #ff9800;
}

.category-card.marketing {
  border-color: #e91e63;
}

.category-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.category-header h3 {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #2c3e50;
}

.category-icon {
  width: 24px;
  height: 24px;
  background-size: contain;
}

.essential-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234caf50'%3E%3Cpath d='M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z'/%3E%3C/svg%3E");
}

.functional-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232196f3'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E");
}

.analytics-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ff9800'%3E%3Cpath d='M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z'/%3E%3C/svg%3E");
}

.marketing-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23e91e63'%3E%3Cpath d='M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z'/%3E%3C/svg%3E");
}

.category-status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: bold;
}

.category-status.required {
  background: #c8e6c9;
  color: #2e7d32;
}

.category-status.optional {
  background: #bbdefb;
  color: #1565c0;
}

.category-card ul {
  list-style: none;
  padding-left: 0;
  margin: 1rem 0;
}

.category-card li {
  padding: 0.25rem 0;
  color: #34495e;
  position: relative;
  padding-left: 1.5rem;
}

.category-card li:before {
  content: '•';
  position: absolute;
  left: 0;
  color: inherit;
  font-weight: bold;
}

.category-control {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #e0e0e0;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.control-note {
  font-size: 0.875rem;
  color: #7f8c8d;
  margin-top: 0.5rem;
  font-style: italic;
}

.duration-chart {
  margin: 2rem 0;
}

.chart-bars {
  display: flex;
  height: 40px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chart-bar {
  height: 100%;
  position: relative;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.chart-bar:hover {
  transform: scaleY(1.1);
}

.chart-bar.session {
  background: #4caf50;
}

.chart-bar.short {
  background: #2196f3;
}

.chart-bar.medium {
  background: #ff9800;
}

.chart-bar.long {
  background: #e91e63;
}

.bar-tooltip {
  position: absolute;
  top: -120px;
  left: 50%;
  transform: translateX(-50%);
  background: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  min-width: 200px;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 100;
}

.chart-bar:hover .bar-tooltip {
  opacity: 1;
  visibility: visible;
  top: -100px;
}

.chart-labels {
  display: flex;
  justify-content: space-between;
  margin-top: 0.5rem;
}

.label {
  font-size: 0.875rem;
  color: #7f8c8d;
}

.duration-table {
  margin-top: 2rem;
  overflow-x: auto;
}

.duration-table table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.duration-table th {
  background: #3498db;
  color: white;
  padding: 1rem;
  text-align: left;
}

.duration-table td {
  padding: 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: bold;
}

.badge.essential {
  background: #c8e6c9;
  color: #2e7d32;
}

.badge.functional {
  background: #bbdefb;
  color: #1565c0;
}

.badge.analytics {
  background: #ffe0b2;
  color: #e65100;
}

.badge.marketing {
  background: #f8bbd0;
  color: #880e4f;
}

code {
  background: #f5f5f5;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-family: monospace;
}

.control-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin: 1.5rem 0;
}

.control-option {
  display: flex;
  gap: 1rem;
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.option-icon {
  width: 60px;
  height: 60px;
  min-width: 60px;
  background-size: contain;
}

.browser-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232196f3'%3E%3Cpath d='M19 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 14H5V8h14v10z'/%3E%3C/svg%3E");
}

.platform-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232196f3'%3E%3Cpath d='M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z'/%3E%3C/svg%3E");
}

.control-option ul {
  list-style: none;
  padding-left: 0;
  margin: 1rem 0;
}

.control-option li {
  padding: 0.25rem 0;
  color: #34495e;
  font-size: 0.875rem;
}

.btn-settings {
  background: #2196f3;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 1rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-settings:hover {
  background: #1976d2;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(33, 150, 243, 0.3);
}

.note {
  font-size: 0.875rem;
  color: #7f8c8d;
  margin-top: 0.5rem;
}

.browser-support {
  margin-top: 2rem;
  text-align: center;
}

.browser-icons {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.browser-icon {
  width: 40px;
  height: 40px;
  background-size: contain;
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

.browser-icon:hover {
  opacity: 1;
}

.chrome {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234CAF50'%3E%3Cpath d='M12 3C7.03 3 3 7.03 3 12s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zm4 9c0 2.21-1.79 4-4 4s-4-1.79-4-4 1.79-4 4-4 4 1.79 4 4z'/%3E%3C/svg%3E");
}

.firefox {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23FF9800'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z'/%3E%3C/svg%3E");
}

.safari {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232196F3'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z'/%3E%3C/svg%3E");
}

.edge {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%237CB342'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z'/%3E%3C/svg%3E");
}

.opera {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23FF5722'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z'/%3E%3C/svg%3E");
}

.third-party-info {
  margin-top: 1.5rem;
}

.warning-box {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 10px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.warning-icon {
  width: 32px;
  height: 32px;
  min-width: 32px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f39c12'%3E%3Cpath d='M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'/%3E%3C/svg%3E");
  background-size: contain;
}

.third-party-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.third-party-card {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.third-party-card h3 {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.provider-icon {
  width: 24px;
  height: 24px;
  background-size: contain;
}

.payment-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234caf50'%3E%3Cpath d='M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z'/%3E%3C/svg%3E");
}

.chat-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232196f3'%3E%3Cpath d='M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z'/%3E%3C/svg%3E");
}

.provider-link {
  display: inline-block;
  margin-top: 1rem;
  color: #2196f3;
  text-decoration: none;
  font-weight: 500;
}

.provider-link:hover {
  text-decoration: underline;
}

.customization-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 15px;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e0e0e0;
}

.modal-header h2 {
  margin: 0;
  color: #2c3e50;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #7f8c8d;
  line-height: 1;
}

.modal-body {
  padding: 1.5rem;
}

.modal-settings {
  margin: 1.5rem 0;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  margin-bottom: 1rem;
  background: #f5f5f5;
  border-radius: 8px;
}

.modal-toggle {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 25px;
}

.modal-toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.modal-toggle .toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 25px;
}

.modal-toggle .toggle-slider:before {
  position: absolute;
  content: "";
  height: 21px;
  width: 21px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

.modal-toggle input:checked + .toggle-slider {
  background-color: #4caf50;
}

.modal-toggle input:checked + .toggle-slider:before {
  transform: translateX(25px);
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-save {
  background: #4caf50;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 600;
}

.btn-save:hover {
  background: #388e3c;
}

.btn-cancel {
  background: #f5f5f5;
  color: #333;
  border: 1px solid #ddd;
  padding: 0.75rem 2rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 600;
}

.btn-cancel:hover {
  background: #e0e0e0;
}

@media (max-width: 768px) {
  .control-buttons {
    flex-direction: column;
  }
  
  .control-options {
    grid-template-columns: 1fr;
  }
  
  .cookie-categories,
  .cookie-explanation {
    grid-template-columns: 1fr;
  }
  
  .modal-content {
    max-height: 80vh;
  }
}
</style>