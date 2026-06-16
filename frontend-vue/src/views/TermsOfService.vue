<template>
  <div class="legal-page terms-of-service">
    <LegalNavigation />
    
    <main class="legal-content">
      <div class="container">
        <header class="page-header">
          <h1 class="page-title">
            <i class="title-icon terms-icon"></i>
            Syarat Layanan
          </h1>
          <p class="page-subtitle">Terakhir diperbarui: {{ lastUpdated }}</p>
          <div class="acceptance-banner" v-if="!termsAccepted">
            <i class="banner-icon"></i>
            <div>
              <h3>Harap Baca dan Setujui Syarat Layanan</h3>
              <p>Untuk menggunakan platform kami, Anda harus menyetujui Syarat Layanan berikut</p>
            </div>
            <button class="accept-btn" @click="acceptTerms">
              Saya Setuju
            </button>
          </div>
          <div class="accepted-banner" v-else>
            <i class="accepted-icon"></i>
            <p>Anda telah menyetujui Syarat Layanan pada {{ acceptanceDate }}</p>
            <button class="revoke-btn" @click="revokeAcceptance">
              Batalkan Persetujuan
            </button>
          </div>
        </header>

        <div class="content-wrapper">
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
            
            <div class="quick-summary">
              <h4>Ringkasan Cepat</h4>
              <ul>
                <li><i class="summary-icon age-icon"></i> Minimal 18 tahun</li>
                <li><i class="summary-icon kyc-icon"></i> KYC wajib</li>
                <li><i class="summary-icon conduct-icon"></i> Perilaku sportif</li>
                <li><i class="summary-icon fee-icon"></i> Biaya transaksi berlaku</li>
              </ul>
            </div>
          </aside>

          <article class="main-content">
            <!-- Bagian 1: Definisi -->
            <section id="definitions" class="content-section">
              <h2 class="section-title">
                <span class="section-number">1.</span>
                Definisi
              </h2>
              <div class="definitions-grid">
                <div class="definition-card">
                  <h3>Platform</h3>
                  <p>Sistem catur online termasuk semua fitur dan layanannya</p>
                </div>
                <div class="definition-card">
                  <h3>Pengguna</h3>
                  <p>Individu yang terdaftar dan menggunakan Platform</p>
                </div>
                <div class="definition-card">
                  <h3>Saldo</h3>
                  <p>Dana yang tersedia di akun Pengguna untuk transaksi</p>
                </div>
                <div class="definition-card">
                  <h3>Merchant</h3>
                  <p>Mitra usaha yang menyediakan barang/jasa di Platform</p>
                </div>
              </div>
            </section>

            <!-- Bagian 2: Persyaratan Akun -->
            <section id="account-requirements" class="content-section">
              <h2 class="section-title">
                <span class="section-number">2.</span>
                Persyaratan Akun
              </h2>
              
              <div class="requirement-card">
                <div class="requirement-header">
                  <i class="req-icon age-icon"></i>
                  <h3>Usia Minimum</h3>
                  <span class="req-badge mandatory">Wajib</span>
                </div>
                <p>Pengguna harus berusia minimal <strong>18 tahun</strong> atau sesuai usia dewasa di yurisdiksi masing-masing.</p>
              </div>

              <div class="requirement-card">
                <div class="requirement-header">
                  <i class="req-icon kyc-icon"></i>
                  <h3>Verifikasi KYC</h3>
                  <span class="req-badge mandatory">Wajib</span>
                </div>
                <p>Proses KYC lengkap meliputi:</p>
                <ul class="kyc-steps">
                  <li>
                    <span class="step-number">1</span>
                    <div>
                      <strong>Upload KTP</strong>
                      <p>Foto jelas KTP asli dengan data terbaca</p>
                    </div>
                  </li>
                  <li>
                    <span class="step-number">2</span>
                    <div>
                      <strong>Selfie Verifikasi</strong>
                      <p>Foto selfie memegang KTP dan kertas bertuliskan tanggal</p>
                    </div>
                  </li>
                  <li>
                    <span class="step-number">3</span>
                    <div>
                      <strong>Verifikasi Alamat</strong>
                      <p>Konfirmasi alamat lengkap dengan kode pos</p>
                    </div>
                  </li>
                  <li>
                    <span class="step-number">4</span>
                    <div>
                      <strong>Verifikasi Kontak</strong>
                      <p>Konfirmasi nomor telepon dan email</p>
                    </div>
                  </li>
                </ul>
                <p class="processing-time">
                  <i class="time-icon"></i>
                  Proses verifikasi memakan waktu 1-3 hari kerja
                </p>
              </div>

              <div class="requirement-card">
                <div class="requirement-header">
                  <i class="req-icon account-icon"></i>
                  <h3>Keamanan Akun</h3>
                  <span class="req-badge mandatory">Wajib</span>
                </div>
                <p>Pengguna bertanggung jawab penuh atas:</p>
                <ul>
                  <li>Kerahasiaan password dan PIN transaksi</li>
                  <li>Segala aktivitas yang terjadi di akun mereka</li>
                  <li>Keamanan perangkat yang digunakan untuk mengakses Platform</li>
                </ul>
              </div>
            </section>

            <!-- Bagian 3: Transaksi Keuangan -->
            <section id="financial-transactions" class="content-section">
              <h2 class="section-title">
                <span class="section-number">3.</span>
                Transaksi Keuangan
              </h2>

              <div class="transaction-types">
                <div class="transaction-type">
                  <h3><i class="transaction-icon deposit-icon"></i> Deposit</h3>
                  <ul>
                    <li>Minimal deposit: Rp 10.000</li>
                    <li>Maksimal deposit harian: Rp 50.000.000</li>
                    <li>Biaya admin: 0% (ditanggung Platform)</li>
                    <li>Proses: Instan - 5 menit</li>
                  </ul>
                </div>

                <div class="transaction-type">
                  <h3><i class="transaction-icon withdraw-icon"></i> Withdraw</h3>
                  <ul>
                    <li>Minimal withdraw: Rp 50.000</li>
                    <li>Maksimal withdraw harian: Rp 20.000.000</li>
                    <li>Biaya admin: Rp 2.500 per transaksi</li>
                    <li>Proses: 1-3 jam kerja</li>
                  </ul>
                </div>

                <div class="transaction-type">
                  <h3><i class="transaction-icon transfer-icon"></i> Transfer ke Pengguna</h3>
                  <ul>
                    <li>Minimal transfer: Rp 5.000</li>
                    <li>Biaya admin: Rp 1.000 per transaksi</li>
                    <li>Verifikasi: PIN transaksi wajib</li>
                    <li>Proses: Instan</li>
                  </ul>
                </div>

                <div class="transaction-type">
                  <h3><i class="transaction-icon merchant-icon"></i> Pembelian Merchant</h3>
                  <ul>
                    <li>Harga sesuai ketentuan merchant</li>
                    <li>Pajak dan biaya tambahan berlaku</li>
                    <li>Kebijakan refund mengikuti merchant</li>
                    <li>Platform sebagai perantara</li>
                  </ul>
                </div>
              </div>

              <div class="fee-table">
                <h3>Rincian Biaya</h3>
                <table>
                  <thead>
                    <tr>
                      <th>Jenis Transaksi</th>
                      <th>Biaya</th>
                      <th>Minimum</th>
                      <th>Maksimum</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Deposit Bank</td>
                      <td>0%</td>
                      <td>Rp 10.000</td>
                      <td>Rp 50.000.000</td>
                    </tr>
                    <tr>
                      <td>Withdraw Bank</td>
                      <td>Rp 2.500</td>
                      <td>Rp 50.000</td>
                      <td>Rp 20.000.000</td>
                    </tr>
                    <tr>
                      <td>Transfer Pengguna</td>
                      <td>Rp 1.000</td>
                      <td>Rp 5.000</td>
                      <td>Tidak terbatas</td>
                    </tr>
                    <tr>
                      <td>Pembelian Merchant</td>
                      <td>0%*</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                  </tbody>
                </table>
                <p class="table-note">*Biaya merchant mungkin berlaku</p>
              </div>
            </section>

            <!-- Bagian 4: Fitur Gameplay -->
            <section id="gameplay-features" class="content-section">
              <h2 class="section-title">
                <span class="section-number">4.</span>
                Fitur Gameplay
              </h2>

              <div class="feature-grid">
                <div class="feature-card">
                  <div class="feature-icon challenge-icon"></div>
                  <h3>Tantangan Pemain</h3>
                  <p>Pengguna dapat menantang pemain lain dengan ketentuan:</p>
                  <ul>
                    <li>Taruhan minimum: Rp 1.000</li>
                    <li>Kedua pihak harus menyetujui tantangan</li>
                    <li>Sistem rating akan menyesuaikan hasil</li>
                  </ul>
                </div>

                <div class="feature-card">
                  <div class="feature-icon search-icon"></div>
                  <h3>Pencarian Pemain</h3>
                  <p>Fitur pencarian dengan persetujuan:</p>
                  <ul>
                    <li>Pengguna dapat memilih muncul di pencarian</li>
                    <li>Filter berdasarkan rating dan lokasi</li>
                    <li>Privasi diutamakan</li>
                  </ul>
                </div>

                <div class="feature-card">
                  <div class="feature-icon conduct-icon"></div>
                  <h3>Kode Etik Bermain</h3>
                  <p>Perilaku yang diharapkan:</p>
                  <ul>
                    <li>Tidak menggunakan software bantuan</li>
                    <li>Menghormati lawan main</li>
                    <li>Tidak melakukan kecurangan</li>
                  </ul>
                </div>

                <div class="feature-card">
                  <div class="feature-icon dispute-icon"></div>
                  <h3>Penyelesaian Sengketa</h3>
                  <p>Mekanisme penyelesaian masalah:</p>
                  <ul>
                    <li>Laporan pelanggaran melalui sistem</li>
                    <li>Investigasi dalam 48 jam</li>
                    <li>Sanksi sesuai tingkat pelanggaran</li>
                  </ul>
                </div>
              </div>
            </section>

            <!-- Bagian 5: Pembatasan dan Sanksi -->
            <section id="restrictions-sanctions" class="content-section">
              <h2 class="section-title">
                <span class="section-number">5.</span>
                Pembatasan dan Sanksi
              </h2>

              <div class="restrictions">
                <h3>Aktivitas yang Dilarang</h3>
                <div class="violation-grid">
                  <div class="violation-card critical">
                    <h4><i class="violation-icon critical-icon"></i> Pelanggaran Kritis</h4>
                    <ul>
                      <li>Pencucian uang</li>
                      <li>Penipuan identitas</li>
                      <li>Bot atau software ilegal</li>
                    </ul>
                    <span class="sanction">Sanksi: Blokir permanen + laporan hukum</span>
                  </div>

                  <div class="violation-card severe">
                    <h4><i class="violation-icon severe-icon"></i> Pelanggaran Berat</h4>
                    <ul>
                      <li>Penggunaan akun orang lain</li>
                      <li>Kecurangan dalam permainan</li>
                      <li>Penghinaan berat</li>
                    </ul>
                    <span class="sanction">Sanksi: Suspensi 30 hari</span>
                  </div>

                  <div class="violation-card moderate">
                    <h4><i class="violation-icon moderate-icon"></i> Pelanggaran Sedang</h4>
                    <ul>
                      <li>Perilaku tidak sportif</li>
                      <li>Spam challenge</li>
                      <li>Penyalahgunaan fitur</li>
                    </ul>
                    <span class="sanction">Sanksi: Peringatan + suspensi 7 hari</span>
                  </div>

                  <div class="violation-card minor">
                    <h4><i class="violation-icon minor-icon"></i> Pelanggaran Ringan</h4>
                    <ul>
                      <li>Keterlambatan respon challenge</li>
                      <li>Kesalahan tidak disengaja</li>
                    </ul>
                    <span class="sanction">Sanksi: Peringatan</span>
                  </div>
                </div>
              </div>
            </section>

            <!-- Bagian 6: Perubahan Syarat -->
            <section id="terms-changes" class="content-section">
              <h2 class="section-title">
                <span class="section-number">6.</span>
                Perubahan Syarat Layanan
              </h2>
              
              <div class="changes-process">
                <div class="change-step">
                  <div class="step-indicator">1</div>
                  <div>
                    <h3>Notifikasi Perubahan</h3>
                    <p>Pengguna akan mendapat notifikasi 30 hari sebelum perubahan berlaku</p>
                  </div>
                </div>
                
                <div class="change-step">
                  <div class="step-indicator">2</div>
                  <div>
                    <h3>Masa Tenggang</h3>
                    <p>Pengguna memiliki 14 hari untuk memberikan tanggapan atau penolakan</p>
                  </div>
                </div>
                
                <div class="change-step">
                  <div class="step-indicator">3</div>
                  <div>
                    <h3>Persetujuan Ulang</h3>
                    <p>Pengguna harus menyetujui syarat baru untuk terus menggunakan layanan</p>
                  </div>
                </div>
              </div>

              <div class="notice-box">
                <i class="notice-icon"></i>
                <div>
                  <h4>Penting</h4>
                  <p>Dengan terus menggunakan Platform setelah perubahan berlaku, Anda dianggap menyetujui syarat yang baru.</p>
                </div>
              </div>
            </section>

            <!-- Kontak dan Pengaduan -->
            <div class="complaint-section">
              <h3>Pengaduan dan Dukungan</h3>
              <div class="complaint-channels">
                <div class="channel">
                  <i class="channel-icon support-icon"></i>
                  <h4>Dukungan Teknis</h4>
                  <p>support@chessplatform.com</p>
                  <p>+62 21 8765 4321</p>
                </div>
                <div class="channel">
                  <i class="channel-icon complaint-icon"></i>
                  <h4>Pengaduan Pelanggan</h4>
                  <p>complaint@chessplatform.com</p>
                  <p>Jam kerja: 09:00 - 18:00 WIB</p>
                </div>
                <div class="channel">
                  <i class="channel-icon legal-icon"></i>
                  <h4>Bantuan Hukum</h4>
                  <p>legal@chessplatform.com</p>
                  <p>Untuk masalah hukum dan kontrak</p>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import LegalNavigation from '@/components/LegalNavigation.vue'

export default {
  name: 'TermsOfService',
  components: {
    LegalNavigation
  },
  setup() {
    const lastUpdated = ref('15 November 2023')
    const termsAccepted = ref(false)
    const acceptanceDate = ref('')
    
    const toc = ref([
      { id: 'definitions', title: 'Definisi' },
      { id: 'account-requirements', title: 'Persyaratan Akun' },
      { id: 'financial-transactions', title: 'Transaksi Keuangan' },
      { id: 'gameplay-features', title: 'Fitur Gameplay' },
      { id: 'restrictions-sanctions', title: 'Pembatasan dan Sanksi' },
      { id: 'terms-changes', title: 'Perubahan Syarat' }
    ])
    
    onMounted(() => {
      const accepted = localStorage.getItem('termsAccepted')
      const date = localStorage.getItem('termsAcceptanceDate')
      
      if (accepted === 'true') {
        termsAccepted.value = true
        acceptanceDate.value = date || 'Tanggal tidak tersedia'
      }
    })
    
    const scrollTo = (id) => {
      const element = document.getElementById(id)
      if (element) {
        element.scrollIntoView({ behavior: 'smooth' })
      }
    }
    
    const acceptTerms = () => {
      const now = new Date().toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
      
      termsAccepted.value = true
      acceptanceDate.value = now
      
      localStorage.setItem('termsAccepted', 'true')
      localStorage.setItem('termsAcceptanceDate', now)
      
      alert('Terima kasih! Anda telah menyetujui Syarat Layanan kami.')
    }
    
    const revokeAcceptance = () => {
      if (confirm('Membatalkan persetujuan akan menonaktifkan akun Anda. Lanjutkan?')) {
        termsAccepted.value = false
        localStorage.removeItem('termsAccepted')
        localStorage.removeItem('termsAcceptanceDate')
        alert('Persetujuan telah dibatalkan. Akun Anda akan dinonaktifkan.')
      }
    }
    
    return {
      lastUpdated,
      toc,
      termsAccepted,
      acceptanceDate,
      scrollTo,
      acceptTerms,
      revokeAcceptance
    }
  }
}
</script>

<style scoped>
.terms-of-service {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.acceptance-banner {
  background: linear-gradient(135deg, #ffd54f 0%, #ffb300 100%);
  border-radius: 10px;
  padding: 1.5rem;
  margin-top: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  box-shadow: 0 4px 15px rgba(255, 179, 0, 0.3);
}

.banner-icon {
  width: 40px;
  height: 40px;
  min-width: 40px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffffff'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/%3E%3C/svg%3E");
  background-size: contain;
}

.acceptance-banner h3 {
  color: #5d4037;
  margin-bottom: 0.25rem;
}

.acceptance-banner p {
  color: #6d4c41;
  margin: 0;
}

.accept-btn {
  background: #2e7d32;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 25px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.accept-btn:hover {
  background: #1b5e20;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(46, 125, 50, 0.3);
}

.accepted-banner {
  background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
  border-radius: 10px;
  padding: 1.5rem;
  margin-top: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.accepted-icon {
  width: 40px;
  height: 40px;
  min-width: 40px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232e7d32'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/%3E%3C/svg%3E");
  background-size: contain;
}

.accepted-banner p {
  color: #2e7d32;
  font-weight: 500;
  margin: 0;
}

.revoke-btn {
  background: #ef5350;
  color: white;
  border: none;
  padding: 0.5rem 1.5rem;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.revoke-btn:hover {
  background: #d32f2f;
  transform: translateY(-2px);
}

.quick-summary {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
}

.quick-summary h4 {
  color: #2c3e50;
  margin-bottom: 1rem;
}

.quick-summary ul {
  list-style: none;
  padding: 0;
}

.quick-summary li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0;
  color: #34495e;
}

.summary-icon {
  width: 20px;
  height: 20px;
  background-size: contain;
}

.age-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
}

.kyc-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z'/%3E%3C/svg%3E");
}

.conduct-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/%3E%3C/svg%3E");
}

.fee-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z'/%3E%3C/svg%3E");
}

.definitions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.definition-card {
  background: #f1f8e9;
  padding: 1.5rem;
  border-radius: 10px;
  border-left: 4px solid #7cb342;
}

.definition-card h3 {
  color: #33691e;
  margin-bottom: 0.5rem;
}

.requirement-card {
  background: #fff;
  border-radius: 10px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
  border: 1px solid #e0e0e0;
}

.requirement-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.req-icon {
  width: 32px;
  height: 32px;
  background-size: contain;
}

.age-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f57c00'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E");
}

.kyc-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f57c00'%3E%3Cpath d='M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z'/%3E%3C/svg%3E");
}

.account-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f57c00'%3E%3Cpath d='M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z'/%3E%3C/svg%3E");
}

.req-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: bold;
  margin-left: auto;
}

.req-badge.mandatory {
  background: #ffcdd2;
  color: #c62828;
}

.kyc-steps {
  list-style: none;
  padding: 0;
  margin: 1.5rem 0;
}

.kyc-steps li {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  margin-bottom: 1rem;
  background: #f5f5f5;
  border-radius: 8px;
  border-left: 4px solid #2196f3;
}

.step-number {
  background: #2196f3;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  flex-shrink: 0;
}

.processing-time {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #2196f3;
  font-weight: 500;
  margin-top: 1rem;
}

.time-icon {
  width: 20px;
  height: 20px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232196f3'%3E%3Cpath d='M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z'/%3E%3C/svg%3E");
  background-size: contain;
}

.transaction-types {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin: 1.5rem 0;
}

.transaction-type {
  background: #fff;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.transaction-type h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.transaction-icon {
  width: 24px;
  height: 24px;
  background-size: contain;
}

.deposit-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234caf50'%3E%3Cpath d='M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z'/%3E%3C/svg%3E");
}

.withdraw-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f44336'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E");
}

.transfer-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232196f3'%3E%3Cpath d='M5 15h14V9H5v6zm2-4h10v2H7v-2zm4-10h2v3h-2zm9.46 4.01L19.04 3.6l-1.79 1.79 1.41 1.41zM11 20h2v3h-2zm6.24-1.29l1.79 1.8 1.42-1.42-1.8-1.79zM4.96 3.595l1.788 1.79L5.342 6.8 3.554 5.01zM3.55 19.08l1.41 1.42 1.79-1.8-1.41-1.41z'/%3E%3C/svg%3E");
}

.merchant-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%239c27b0'%3E%3Cpath d='M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z'/%3E%3C/svg%3E");
}

.transaction-type ul {
  list-style: none;
  padding-left: 0;
}

.transaction-type li {
  padding: 0.25rem 0;
  color: #34495e;
  position: relative;
  padding-left: 1.5rem;
}

.transaction-type li:before {
  content: '•';
  position: absolute;
  left: 0;
  color: #3498db;
  font-weight: bold;
}

.fee-table {
  margin-top: 2rem;
  overflow-x: auto;
}

.fee-table table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.fee-table th {
  background: #3498db;
  color: white;
  padding: 1rem;
  text-align: left;
}

.fee-table td {
  padding: 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.fee-table tr:nth-child(even) {
  background: #f5f5f5;
}

.table-note {
  font-size: 0.875rem;
  color: #7f8c8d;
  margin-top: 0.5rem;
  font-style: italic;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.feature-card {
  background: #fff;
  padding: 1.5rem;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
}

.feature-icon {
  width: 50px;
  height: 50px;
  margin: 0 auto 1rem;
  background-size: contain;
}

.challenge-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23e91e63'%3E%3Cpath d='M15 14c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4zm-9-4V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 2c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z'/%3E%3C/svg%3E");
}

.search-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23e91e63'%3E%3Cpath d='M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z'/%3E%3C/svg%3E");
}

.feature-card h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
}

.violation-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.violation-card {
  padding: 1.5rem;
  border-radius: 10px;
  border-top: 4px solid;
}

.violation-card.critical {
  background: #ffebee;
  border-color: #d32f2f;
}

.violation-card.severe {
  background: #fff3e0;
  border-color: #f57c00;
}

.violation-card.moderate {
  background: #fffde7;
  border-color: #fbc02d;
}

.violation-card.minor {
  background: #f1f8e9;
  border-color: #7cb342;
}

.violation-card h4 {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.violation-icon {
  width: 20px;
  height: 20px;
  background-size: contain;
}

.critical-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23d32f2f'%3E%3Cpath d='M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'/%3E%3C/svg%3E");
}

.severe-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f57c00'%3E%3Cpath d='M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'/%3E%3C/svg%3E");
}

.moderate-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23fbc02d'%3E%3Cpath d='M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'/%3E%3C/svg%3E");
}

.minor-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%237cb342'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/%3E%3C/svg%3E");
}

.sanction {
  display: block;
  margin-top: 1rem;
  padding: 0.5rem;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 5px;
  font-weight: bold;
  text-align: center;
}

.changes-process {
  margin: 1.5rem 0;
}

.change-step {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1.5rem;
  background: #f5f5f5;
  border-radius: 10px;
}

.step-indicator {
  background: #3498db;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.notice-box {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 10px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.notice-icon {
  width: 32px;
  height: 32px;
  min-width: 32px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23f39c12'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'/%3E%3C/svg%3E");
  background-size: contain;
}

.notice-box h4 {
  color: #856404;
  margin-bottom: 0.25rem;
}

.notice-box p {
  color: #856404;
  margin: 0;
}

.complaint-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 2px solid #e0e0e0;
}

.complaint-channels {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.channel {
  background: #fff;
  padding: 1.5rem;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.channel-icon {
  width: 40px;
  height: 40px;
  margin: 0 auto 1rem;
  background-size: contain;
}

.support-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232196f3'%3E%3Cpath d='M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 12h-2v-2h2v2zm0-4h-2V6h2v4z'/%3E%3C/svg%3E");
}

.complaint-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23e91e63'%3E%3Cpath d='M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 9h-2V5h2v6zm0 4h-2v-2h2v2z'/%3E%3C/svg%3E");
}

.legal-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%237cb342'%3E%3Cpath d='M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z'/%3E%3C/svg%3E");
}

.channel h4 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.channel p {
  color: #34495e;
  margin: 0.25rem 0;
}

@media (max-width: 768px) {
  .acceptance-banner,
  .accepted-banner {
    flex-direction: column;
    text-align: center;
  }
  
  .definitions-grid,
  .transaction-types,
  .feature-grid,
  .violation-grid,
  .complaint-channels {
    grid-template-columns: 1fr;
  }
  
  .fee-table {
    font-size: 0.875rem;
  }
}
</style>