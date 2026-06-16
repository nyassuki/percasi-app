<template>
  <div class="legal-page privacy-policy">
    <LegalNavigation />
    
    <main class="legal-content">
      <div class="container">
        <header class="page-header">
          <h1 class="page-title">
            <i class="title-icon privacy-icon"></i>
            Kebijakan Privasi
          </h1>
          <p class="page-subtitle">Terakhir diperbarui: {{ lastUpdated }}</p>
          <p class="page-intro">
            Kebijakan Privasi ini menjelaskan bagaimana Platform Catur Online kami mengumpulkan, menggunakan, 
            dan melindungi informasi pribadi Anda sesuai dengan undang-undang perlindungan data yang berlaku.
          </p>
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
            <div class="consent-toggle">
              <label class="toggle-label">
                <input 
                  type="checkbox" 
                  v-model="consentGiven"
                  @change="updateConsent"
                >
                <span class="toggle-slider"></span>
                <span class="toggle-text">Setuju dengan Kebijakan Privasi</span>
              </label>
              <p v-if="consentGiven" class="consent-status active">
                <i class="status-icon"></i> Persetujuan tercatat
              </p>
            </div>
          </aside>

          <!-- Konten Utama -->
          <article class="main-content">
            <section id="data-collection" class="content-section">
              <h2 class="section-title">
                <span class="section-number">1.</span>
                Data yang Kami Kumpulkan
              </h2>
              <div class="data-categories">
                <div class="data-category">
                  <h3><i class="category-icon ktp-icon"></i> Data Identitas</h3>
                  <ul>
                    <li>Foto KTP dan data yang tertera</li>
                    <li>Foto selfie untuk verifikasi KYC</li>
                    <li>Nama lengkap sesuai identitas</li>
                    <li>Tanggal lahir</li>
                    <li>Alamat lengkap dan kode pos</li>
                  </ul>
                </div>
                <div class="data-category">
                  <h3><i class="category-icon contact-icon"></i> Data Kontak</h3>
                  <ul>
                    <li>Nomor telepon terverifikasi</li>
                    <li>Alamat email</li>
                    <li>Akun Telegram (opsional)</li>
                  </ul>
                </div>
                <div class="data-category">
                  <h3><i class="category-icon financial-icon"></i> Data Finansial</h3>
                  <ul>
                    <li>Rekening bank untuk deposit/withdraw</li>
                    <li>Riwayat transaksi keuangan</li>
                    <li>Data pembelian pada merchant</li>
                  </ul>
                </div>
                <div class="data-category">
                  <h3><i class="category-icon gameplay-icon"></i> Data Gameplay</h3>
                  <ul>
                    <li>Statistik permainan catur</li>
                    <li>Riwayat pertandingan</li>
                    <li>Preferensi pencarian dan duel</li>
                  </ul>
                </div>
              </div>
            </section>

            <section id="data-usage" class="content-section">
              <h2 class="section-title">
                <span class="section-number">2.</span>
                Penggunaan Data
              </h2>
              <div class="usage-grid">
                <div class="usage-card">
                  <div class="usage-icon verification-icon"></div>
                  <h3>Verifikasi KYC</h3>
                  <p>Memverifikasi identitas Anda sesuai regulasi keuangan</p>
                </div>
                <div class="usage-card">
                  <div class="usage-icon security-icon"></div>
                  <h3>Keamanan Akun</h3>
                  <p>Melindungi akun Anda dari akses tidak sah</p>
                </div>
                <div class="usage-card">
                  <div class="usage-icon transaction-icon"></div>
                  <h3>Transaksi Keuangan</h3>
                  <p>Memproses deposit, withdraw, dan transfer dana</p>
                </div>
                <div class="usage-card">
                  <div class="usage-icon gameplay-icon"></div>
                  <h3>Pengalaman Bermain</h3>
                  <p>Memfasilitasi tantangan dan pencarian pemain</p>
                </div>
              </div>
            </section>

            <section id="data-sharing" class="content-section">
              <h2 class="section-title">
                <span class="section-number">3.</span>
                Berbagi Data
              </h2>
              <div class="sharing-info">
                <p>Kami hanya membagikan data Anda dengan:</p>
                <ul>
                  <li><strong>Penyedia Pembayaran:</strong> Untuk verifikasi transaksi keuangan</li>
                  <li><strong>Merchant Mitra:</strong> Hanya data yang diperlukan untuk pembelian</li>
                  <li><strong>Otoritas Hukum:</strong> Jika diperlukan oleh hukum yang berlaku</li>
                </ul>
                <div class="warning-note">
                  <i class="warning-icon"></i>
                  <p>Kami TIDAK menjual atau menyewakan data pribadi Anda kepada pihak ketiga untuk tujuan pemasaran.</p>
                </div>
              </div>
            </section>

            <section id="user-rights" class="content-section">
              <h2 class="section-title">
                <span class="section-number">4.</span>
                Hak Pengguna
              </h2>
              <div class="rights-grid">
                <div class="right-card">
                  <h3><i class="right-icon access-icon"></i> Hak Akses</h3>
                  <p>Anda dapat meminta akses ke data pribadi yang kami simpan</p>
                  <button class="btn-right" @click="requestDataAccess">
                    Ajukan Permintaan
                  </button>
                </div>
                <div class="right-card">
                  <h3><i class="right-icon correction-icon"></i> Hak Koreksi</h3>
                  <p>Anda dapat memperbaiki data yang tidak akurat</p>
                  <button class="btn-right" @click="updateProfile">
                    Perbarui Profil
                  </button>
                </div>
                <div class="right-card">
                  <h3><i class="right-icon deletion-icon"></i> Hak Penghapusan</h3>
                  <p>Anda dapat meminta penghapusan data pribadi</p>
                  <button class="btn-right" @click="requestDataDeletion">
                    Ajukan Penghapusan
                  </button>
                </div>
                <div class="right-card">
                  <h3><i class="right-icon consent-icon"></i> Hak Penarikan Persetujuan</h3>
                  <p>Anda dapat menarik persetujuan kapan saja</p>
                  <button class="btn-right" @click="withdrawConsent">
                    Tarik Persetujuan
                  </button>
                </div>
              </div>
            </section>

            <section id="data-security" class="content-section">
              <h2 class="section-title">
                <span class="section-number">5.</span>
                Keamanan Data
              </h2>
              <div class="security-features">
                <div class="security-feature">
                  <i class="feature-icon encryption-icon"></i>
                  <div>
                    <h3>Enkripsi End-to-End</h3>
                    <p>Data Anda dienkripsi menggunakan standar AES-256</p>
                  </div>
                </div>
                <div class="security-feature">
                  <i class="feature-icon backup-icon"></i>
                  <div>
                    <h3>Backup Rutin</h3>
                    <p>Data dicadangkan secara teratur di server terpisah</p>
                  </div>
                </div>
                <div class="security-feature">
                  <i class="feature-icon audit-icon"></i>
                  <div>
                    <h3>Audit Berkala</h3>
                    <p>Sistem kami diaudit secara berkala oleh pihak ketiga</p>
                  </div>
                </div>
              </div>
            </section>

            <div class="contact-section">
              <h3>Pertanyaan atau Keluhan?</h3>
              <p>Hubungi Petugas Perlindungan Data kami:</p>
              <div class="contact-info">
                <p><i class="contact-icon email-icon"></i> dpo@chessplatform.com</p>
                <p><i class="contact-icon phone-icon"></i> +62 21 1234 5678</p>
              </div>
            </div>
          </article>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref } from 'vue'
import LegalNavigation from '@/components/LegalNavigation.vue'

export default {
  name: 'PrivacyPolicy',
  components: {
    LegalNavigation
  },
  setup() {
    const lastUpdated = ref('15 November 2023')
    const consentGiven = ref(false)
    
    const toc = ref([
      { id: 'data-collection', title: 'Data yang Kami Kumpulkan' },
      { id: 'data-usage', title: 'Penggunaan Data' },
      { id: 'data-sharing', title: 'Berbagi Data' },
      { id: 'user-rights', title: 'Hak Pengguna' },
      { id: 'data-security', title: 'Keamanan Data' }
    ])
    
    const scrollTo = (id) => {
      const element = document.getElementById(id)
      if (element) {
        element.scrollIntoView({ behavior: 'smooth' })
      }
    }
    
    const updateConsent = () => {
      // Simulasi penyimpanan persetujuan
      localStorage.setItem('privacyConsent', consentGiven.value)
      console.log('Privacy consent updated:', consentGiven.value)
    }
    
    const requestDataAccess = () => {
      alert('Permintaan akses data telah dikirim ke tim kami. Kami akan menghubungi Anda dalam 7 hari kerja.')
    }
    
    const updateProfile = () => {
      // Redirect ke halaman profil
      window.location.href = '/profile/edit'
    }
    
    const requestDataDeletion = () => {
      alert('Permintaan penghapusan data telah dicatat. Proses ini akan memakan waktu 30 hari.')
    }
    
    const withdrawConsent = () => {
      consentGiven.value = false
      updateConsent()
      alert('Persetujuan telah ditarik. Beberapa fitur mungkin tidak tersedia.')
    }
    
    // Load consent status
    consentGiven.value = localStorage.getItem('privacyConsent') === 'true'
    
    return {
      lastUpdated,
      toc,
      consentGiven,
      scrollTo,
      updateConsent,
      requestDataAccess,
      updateProfile,
      requestDataDeletion,
      withdrawConsent
    }
  }
}
</script>

<style scoped>
.legal-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.legal-content {
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
  padding: 2rem;
  background: white;
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.page-title {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.title-icon {
  width: 40px;
  height: 40px;
  background-size: contain;
}

.page-subtitle {
  color: #7f8c8d;
  font-size: 1rem;
  margin-bottom: 1rem;
}

.page-intro {
  color: #34495e;
  font-size: 1.1rem;
  line-height: 1.6;
  max-width: 800px;
  margin: 0 auto;
}

.content-wrapper {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 2rem;
  margin-top: 2rem;
}

.table-of-contents {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
  height: fit-content;
  position: sticky;
  top: 2rem;
}

.toc-title {
  color: #2c3e50;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #3498db;
}

.toc-nav ul {
  list-style: none;
  padding: 0;
}

.toc-nav li {
  margin-bottom: 0.75rem;
}

.toc-nav a {
  color: #34495e;
  text-decoration: none;
  padding: 0.5rem 1rem;
  display: block;
  border-radius: 5px;
  transition: all 0.3s ease;
}

.toc-nav a:hover {
  background: #3498db;
  color: white;
  transform: translateX(5px);
}

.consent-toggle {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #ecf0f1;
}

.toggle-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  gap: 0.5rem;
}

.toggle-label input {
  display: none;
}

.toggle-slider {
  width: 50px;
  height: 25px;
  background: #e74c3c;
  border-radius: 25px;
  position: relative;
  transition: all 0.3s ease;
}

.toggle-slider:before {
  content: '';
  position: absolute;
  width: 21px;
  height: 21px;
  border-radius: 50%;
  background: white;
  top: 2px;
  left: 2px;
  transition: all 0.3s ease;
}

.toggle-label input:checked + .toggle-slider {
  background: #2ecc71;
}

.toggle-label input:checked + .toggle-slider:before {
  transform: translateX(25px);
}

.toggle-text {
  color: #2c3e50;
  font-weight: 600;
}

.consent-status {
  margin-top: 0.5rem;
  padding: 0.5rem;
  border-radius: 5px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.consent-status.active {
  background: #d5f4e6;
  color: #27ae60;
}

.status-icon {
  width: 16px;
  height: 16px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2327ae60'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E");
}

.main-content {
  background: white;
  padding: 2.5rem;
  border-radius: 15px;
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
}

.content-section {
  margin-bottom: 3rem;
  scroll-margin-top: 2rem;
}

.section-title {
  color: #2c3e50;
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-number {
  color: #3498db;
  font-weight: bold;
}

.data-categories {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.data-category {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 10px;
  border-left: 4px solid #3498db;
}

.data-category h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.category-icon {
  width: 24px;
  height: 24px;
  background-size: contain;
}

.ktp-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
}

.contact-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z'/%3E%3C/svg%3E");
}

.financial-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z'/%3E%3C/svg%3E");
}

.gameplay-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M21 6H3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-10 7H8v3H6v-3H3v-2h3V8h2v3h3v2zm4.5 2c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4-3c-.83 0-1.5-.67-1.5-1.5S18.67 9 19.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z'/%3E%3C/svg%3E");
}

.data-category ul {
  list-style: none;
  padding-left: 0;
}

.data-category li {
  padding: 0.25rem 0;
  color: #34495e;
  position: relative;
  padding-left: 1.5rem;
}

.data-category li:before {
  content: '✓';
  position: absolute;
  left: 0;
  color: #2ecc71;
  font-weight: bold;
}

.usage-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.usage-card {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 10px;
  text-align: center;
  transition: transform 0.3s ease;
}

.usage-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
}

.usage-icon {
  width: 50px;
  height: 50px;
  margin: 0 auto 1rem;
  background-size: contain;
}

.verification-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z'/%3E%3C/svg%3E");
}

.security-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z'/%3E%3C/svg%3E");
}

.transaction-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z'/%3E%3C/svg%3E");
}

.usage-card h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.sharing-info {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 10px;
  border-left: 4px solid #3498db;
}

.sharing-info ul {
  list-style: none;
  padding-left: 1.5rem;
}

.sharing-info li {
  padding: 0.5rem 0;
  color: #34495e;
}

.warning-note {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 8px;
  padding: 1rem;
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.warning-icon {
  width: 24px;
  height: 24px;
  min-width: 24px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f39c12'%3E%3Cpath d='M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'/%3E%3C/svg%3E");
}

.rights-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.right-card {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 10px;
  text-align: center;
  border-top: 4px solid #3498db;
}

.right-card h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.right-icon {
  width: 24px;
  height: 24px;
  background-size: contain;
}

.access-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/%3E%3C/svg%3E");
}

.correction-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z'/%3E%3C/svg%3E");
}

.deletion-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z'/%3E%3C/svg%3E");
}

.consent-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M13 7.5h5v2h-5zm0 7h5v2h-5zM19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM11 6H6v5h5V6zm-1 4H7V7h3v3zm1 3H6v5h5v-5zm-1 4H7v-3h3v3z'/%3E%3C/svg%3E");
}

.btn-right {
  background: #3498db;
  color: white;
  border: none;
  padding: 0.5rem 1.5rem;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 1rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-right:hover {
  background: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
}

.security-features {
  display: grid;
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.security-feature {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 10px;
}

.feature-icon {
  width: 40px;
  height: 40px;
  min-width: 40px;
  background-size: contain;
}

.encryption-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z'/%3E%3C/svg%3E");
}

.backup-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z'/%3E%3C/svg%3E");
}

.audit-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4zm2.5 2.1h-15V5h15v14.1zm0-16.1h-15c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h15c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z'/%3E%3C/svg%3E");
}

.contact-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 2px solid #ecf0f1;
  text-align: center;
}

.contact-section h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
}

.contact-info {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.contact-info p {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #34495e;
}

.contact-icon {
  width: 20px;
  height: 20px;
  background-size: contain;
}

.email-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z'/%3E%3C/svg%3E");
}

.phone-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z'/%3E%3C/svg%3E");
}

@media (max-width: 992px) {
  .content-wrapper {
    grid-template-columns: 1fr;
  }
  
  .table-of-contents {
    position: static;
  }
  
  .page-title {
    font-size: 2rem;
  }
}

@media (max-width: 768px) {
  .main-content {
    padding: 1.5rem;
  }
  
  .data-categories,
  .usage-grid,
  .rights-grid {
    grid-template-columns: 1fr;
  }
  
  .contact-info {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>