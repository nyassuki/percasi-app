<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-gray-100 dark:from-slate-950 dark:to-gray-900 transition-colors duration-300">
    
    <header class="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-gray-200/50 dark:border-slate-800/50 shadow-sm">
      <div class="w-full px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
          <button @click="goBack" class="p-3 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition-shadow active:scale-95 touch-manipulation">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          
          <div class="text-center">
            <h1 class="text-lg font-bold text-gray-800 dark:text-white uppercase tracking-tighter">Pengaturan</h1>
            <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-0.5"> Account Center</p>
          </div>
          
          <div class="flex items-center gap-2">
            <button @click="showHelp" class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 active:scale-95 touch-manipulation">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
            <div class="relative">
              <button @click="saveSettings" :disabled="saving || !hasChanges"
                :class="['px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all duration-300 active:scale-95 touch-manipulation',
                  saving || !hasChanges ? 'bg-gray-100 dark:bg-slate-800 text-gray-400 border border-gray-200 dark:border-slate-700' : 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg']">
                {{ saving ? 'Saving...' : 'Simpan' }}
              </button>
              <div v-if="hasChanges && !saving" class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 rounded-full animate-pulse border-2 border-white dark:border-slate-900"></div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="pt-28 pb-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto space-y-6">
        
        <div class="bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm p-1.5 rounded-[2rem] border border-gray-200/50 dark:border-slate-700/50 flex shadow-sm">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
            :class="['flex-1 py-4 px-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center justify-center gap-2 active:scale-95 touch-manipulation',
              activeTab === tab.id ? 'bg-white dark:bg-slate-700 text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-800 dark:hover:text-white']">
            <component :is="tab.icon" /> {{ tab.label }}
          </button>
        </div>

        <div v-show="activeTab === 'security'" class="space-y-4 animate-fade-in">
          <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] ml-2">Keamanan & Akses</h2>
          
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="flex items-start gap-4">
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', formData.is_single_login === 1 ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400']">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-width="2"/></svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 dark:text-white text-base">Single Login</h3>
                  <p class="text-xs text-gray-500 leading-relaxed mt-1">Login perangkat baru akan otomatis mengeluarkan sesi lama.</p>
                </div>
              </div>
              <ToggleSwitch :value="formData.is_single_login === 1" @toggle="handleToggle('is_single_login', $event ? 1 : 0)" />
            </div>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="flex items-start gap-4">
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', formData.is_2fa_active === 'YES' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400']">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-width="2"/></svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 dark:text-white text-base">Autentikasi 2 Faktor</h3>
                  <p class="text-xs text-gray-500 leading-relaxed mt-1">Gunakan kode dari Google Authenticator untuk login.</p>
                </div>
              </div>
              <ToggleSwitch :value="formData.is_2fa_active === 'YES'" @toggle="handle2FAToggle($event)" />
            </div>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between">
              <div class="flex items-start gap-4">
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', formData.bio_login_active === 'YES' ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400']">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" stroke-width="2"/></svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 dark:text-white text-base">Login Biometrik</h3>
                  <p class="text-xs text-gray-500 leading-relaxed mt-1">Gunakan sidik jari atau wajah untuk akses cepat.</p>
                </div>
              </div>
              <ToggleSwitch :value="formData.bio_login_active === 'YES'" @toggle="handleBiometricToggle($event)" />
            </div>

            <transition name="slide-down">
              <div v-if="formData.bio_login_active === 'YES'" class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700/50">
                
                <div v-if="!isBiometricSupported" class="bg-amber-50 dark:bg-amber-900/20 p-5 rounded-2xl border border-amber-200 dark:border-amber-800/30 mb-4">
                  <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-800/30 rounded-xl flex items-center justify-center flex-shrink-0">
                      <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                      </svg>
                    </div>
                    <div>
                      <p class="text-xs font-bold text-amber-800 dark:text-amber-300">Perangkat Tidak Mendukung</p>
                      <p class="text-xs text-amber-600 dark:text-amber-400/80 mt-1 leading-relaxed">
                        Browser/device Anda belum mendukung fitur biometrik.
                      </p>
                    </div>
                  </div>
                </div>

                <div v-else class="space-y-4">
                  <div class="bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900/10 dark:to-violet-900/10 p-5 rounded-2xl border border-purple-100 dark:border-purple-800/30">
                    <div class="flex items-center justify-between gap-4">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center shadow-sm border border-purple-200 dark:border-purple-700/50">
                          <span class="text-xl">👆</span>
                        </div>
                        <div>
                          <p class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-widest">Touch ID Ready</p>
                          <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Siap Didaftarkan</p>
                          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tap untuk registrasi sidik jari</p>
                        </div>
                      </div>
                      <button @click="handleRegisterBiometric" :disabled="registerLoading"
                        :class="['px-5 py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all shadow-lg active:scale-95 touch-manipulation min-h-[44px] min-w-[120px] flex items-center justify-center',
                          registerLoading ? 'bg-gray-300 dark:bg-slate-700 text-gray-500' : 'bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white']">
                        <span v-if="registerLoading" class="flex items-center gap-2">
                          <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                          </svg>
                          Processing...
                        </span>
                        <span v-else>Daftar Sekarang</span>
                      </button>
                    </div>
                  </div>

                  <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl">
                    <div class="flex items-start gap-3">
                      <div class="w-8 h-8 bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tips Pendaftaran Biometrik:</p>
                        <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                          <li class="flex items-center gap-2">• Pastikan sidik jari/wajah sudah terdaftar di sistem operasi</li>
                          <li class="flex items-center gap-2">• Gunakan jari yang sama dengan yang didaftarkan di perangkat</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </transition>
          </div>
        </div>

        <div v-show="activeTab === 'privacy'" class="space-y-4 animate-fade-in">
          <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] ml-2">Koneksi & Visibilitas</h2>
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="flex items-start gap-4">
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', formData.open_match === 'YES' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400']">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-width="2"/></svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 dark:text-white text-base">Tantangan Terbuka</h3>
                  <p class="text-xs text-gray-500 leading-relaxed mt-1">Izinkan atlet lain menemukan dan menantang Anda bermain.</p>
                </div>
              </div>
              <ToggleSwitch :value="formData.open_match === 'YES'" @toggle="handleToggle('open_match', $event ? 'YES' : 'NO')" />
            </div>
          </div>
        </div>

        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-3xl p-6 border border-blue-100 dark:border-blue-800/30">
          <div class="flex gap-4 items-start">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800/30 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h4 class="font-bold text-blue-900 dark:text-blue-300 text-sm mb-2">Tips Keamanan Akun</h4>
              <ul class="text-xs text-blue-700 dark:text-blue-400/80 space-y-1">
                <li class="flex items-start gap-2">• Single Login & 2FA melindungi rating dan wallet turnamen Anda</li>
                <li class="flex items-start gap-2">• Biometrik mempercepat login tanpa mengingat password</li>
                <li class="flex items-start gap-2">• Pastikan selalu logout dari perangkat publik</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </main>
    
    <transition name="fade">
      <div v-if="show2FAModal" class="fixed inset-0 z-[100] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="close2FAModal"></div>
        
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden animate-slide-up border border-gray-100 dark:border-slate-700">
          <div class="p-8">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Setup Authenticator</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Scan QR Code ini menggunakan aplikasi Google Authenticator atau Authy.</p>
            </div>

            <div class="bg-gray-50 dark:bg-slate-900 rounded-2xl p-6 mb-6 border border-gray-200 dark:border-slate-700 text-center">
              <div v-if="twoFASetupData.qr_code" class="inline-block bg-white p-2 rounded-xl shadow-sm">
                 <img :src="twoFASetupData.qr_code" class="w-40 h-40 mx-auto" alt="QR Code" />
              </div>
              <div v-else class="h-40 flex items-center justify-center">
                 <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
              </div>
              
              <div class="mt-4 pt-4 border-t border-gray-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Kode Manual</p>
                <code class="bg-white dark:bg-slate-800 px-3 py-2 rounded-lg text-sm font-mono text-gray-800 dark:text-gray-200 select-all border border-gray-200 dark:border-slate-700 block">
                  {{ twoFASetupData.secret || 'Loading...' }}
                </code>
              </div>
            </div>

            <form @submit.prevent="verifyAndActivate2FA">
              <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2">Verifikasi OTP</label>
                <input v-model="twoFAVerifyCode" type="text" maxlength="6" placeholder="000000" class="w-full text-center text-black text-2xl tracking-[0.5em] font-bold py-4 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all shadow-sm" />
              </div>

              <div class="flex gap-3">
                <button type="button" @click="close2FAModal" class="flex-1 py-3.5 rounded-xl bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors">Batal</button>
                <button type="submit" :disabled="loading2FA || twoFAVerifyCode.length < 6" class="flex-1 py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                   <span v-if="loading2FA" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                   <span>Aktifkan</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <transition name="slide-up">
      <div v-if="toastMessage" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 z-[100] min-w-[300px]">
        <div :class="['px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border backdrop-blur-md', toastType === 'success' ? 'bg-emerald-600/90 border-emerald-500 text-white' : 'bg-rose-600/90 border-rose-500 text-white']">
          <div :class="['w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0', toastType === 'success' ? 'bg-emerald-500/20' : 'bg-rose-500/20']">
            <svg v-if="toastType === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <span class="text-sm font-bold">{{ toastMessage }}</span>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, h, inject } from 'vue'
import { useRouter } from 'vue-router'
import { startRegistration } from '@simplewebauthn/browser'
import api from '../services/api'

const toast = inject('toast')

// --- ICONS (Tetap Sama) ---
const SecurityIcon = { 
  render: () => h('svg', { 
    class: 'h-4 w-4', 
    fill: 'none', 
    viewBox: '0 0 24 24', 
    stroke: 'currentColor' 
  }, [
    h('path', { 
      d: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 
      'stroke-width': 2, 
      'stroke-linecap': 'round' 
    })
  ]) 
}

const PrivacyIcon = { 
  render: () => h('svg', { 
    class: 'h-4 w-4', 
    fill: 'none', 
    viewBox: '0 0 24 24', 
    stroke: 'currentColor' 
  }, [
    h('path', { 
      d: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 
      'stroke-width': 2, 
      'stroke-linecap': 'round' 
    })
  ]) 
}

// --- TOGGLE SWITCH (Tetap Sama) ---
const ToggleSwitch = (props, { emit }) => {
  return h('button', {
    type: 'button', 
    onClick: () => emit('toggle', !props.value),
    class: ['relative inline-flex h-7 w-12 items-center rounded-full transition-all duration-300 active:scale-95 touch-manipulation', 
            props.value ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-slate-700'],
    style: 'min-height: 44px; min-width: 48px;'
  }, [
    h('span', { 
      class: ['inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform duration-300', 
              props.value ? 'translate-x-6' : 'translate-x-1'] 
    })
  ])
}

// --- STATE ---
const router = useRouter()
const loading = ref(false)
const saving = ref(false)
const registerLoading = ref(false)
const isBiometricSupported = ref(false)
const registrationSuccess = ref(false)
const toastMessage = ref('')
const toastType = ref('success')
const activeTab = ref('security')
const originalData = ref({})
const users = ref({})
const temp_token = ref('')

// NEW STATE: 2FA SETUP
const show2FAModal = ref(false)
const loading2FA = ref(false)
const twoFASetupData = reactive({ qr_code: '', secret: '' }) 
const twoFAVerifyCode = ref('')

const formData = reactive({
  is_single_login: 0,
  is_2fa_active: 'NO',
  bio_login_active: 'NO',
  open_match: 'NO'
})

const tabs = [
  { id: 'security', label: 'Keamanan', icon: SecurityIcon },
  { id: 'privacy', label: 'Privasi', icon: PrivacyIcon }
]

// --- COMPUTED ---
const hasChanges = computed(() => {
  const fields = ['is_single_login', 'bio_login_active', 'open_match'] // Hapus is_2fa_active dari sini
    if(formData['bio_login_active']=="NO") {
      localStorage.removeItem('current_user_email');
      localStorage.removeItem('current_user_fullname');
      localStorage.removeItem('current_user_username');
    } else if(formData['bio_login_active']=="YES") {
      handleRegisterBiometric();
    }
  return fields.some(f => String(formData[f]) !== String(originalData.value[f]))
})

// --- METHODS ---
const showToast = (message, type = 'success') => {
  toastMessage.value = message
  toastType.value = type
  setTimeout(() => {
    toastMessage.value = ''
  }, 3000)
}

const handleToggle = (field, val) => {
  formData[field] = val
}

// NEW 2FA METHODS
const handle2FAToggle = async (val) => {
  if (val) {
    // Enable -> Show Modal
    await startSetup2FA()
  } else {
    // Disable -> API Call
    if(confirm('Apakah Anda yakin ingin menonaktifkan Autentikasi 2 Faktor?')) {
       await disable2FA()
    }
  }
}

const startSetup2FA = async () => {
  loading2FA.value = true
  show2FAModal.value = true
  twoFAVerifyCode.value = ''
  try {
    const res = await api.post('/auth/2fa/setup')
    twoFASetupData.qr_code = res.data.qr_code
    twoFASetupData.secret = res.data.manual_code || res.data.secret
    temp_token.value = res.data.temp_token
  } catch (e) {
    showToast('Gagal memuat QR Code', 'error')
    close2FAModal()
  } finally {
    loading2FA.value = false
  }
}

const verifyAndActivate2FA = async () => {
  loading2FA.value = true
  try {
    await api.post('/auth/2fa/verify', { 
        otp: twoFAVerifyCode.value,
        temp_token: temp_token.value 
    })
    formData.is_2fa_active = 'YES'
    showToast('2FA Berhasil Diaktifkan!', 'success')
    close2FAModal()
  } catch (e) {
    showToast(e.response?.data?.message || 'Kode OTP salah', 'error')
  } finally {
    loading2FA.value = false
  }
}

const disable2FA = async () => {
    try {
        await api.post('/auth/2fa/disable')
        formData.is_2fa_active = 'NO'
        showToast('2FA Dinonaktifkan', 'success')
    } catch (e) {
        showToast('Gagal menonaktifkan 2FA', 'error')
    }
}

const close2FAModal = () => {
  show2FAModal.value = false
  // Reset toggle if canceled
  if (formData.is_2fa_active !== 'YES') {
      formData.is_2fa_active = 'NO'
  }
}

// BIOMETRIC METHODS (ASLI)
const handleBiometricToggle = (val) => {
  formData.bio_login_active = val ? 'YES' : 'NO'
  if (val) {
    checkBiometricSupport()
    showToast('Aktifkan biometrik dengan menekan tombol "Daftar Sekarang"', 'info')
  }
}

const checkBiometricSupport = async () => {
  try {
    if (!window.PublicKeyCredential) {
      isBiometricSupported.value = false
      return
    }
    const isPlatformAuthenticatorAvailable = await window.PublicKeyCredential
      .isUserVerifyingPlatformAuthenticatorAvailable?.()
      .catch(() => false)
    
    isBiometricSupported.value = isPlatformAuthenticatorAvailable
  } catch (error) {
    console.warn('WebAuthn support check failed:', error)
    isBiometricSupported.value = false
  }
}

const handleRegisterBiometric = async () => {
  if (!isBiometricSupported.value) {
    showToast('Perangkat tidak mendukung biometrik', 'error');
    return;
  }
  registerLoading.value = true;
  try {
    const response = await api.post('/auth/biometric/register-options');
    const options = response.data;
    const regResponse = await startRegistration(options);
    
    saveSettings();
    localStorage.setItem('current_user_email', users.value.email);
    localStorage.setItem('current_user_fullname', users.value.full_name);
    localStorage.setItem('current_user_name', users.value.username);

    const verifyRes = await api.post('/auth/biometric/verify-registration', regResponse);
    if (verifyRes.data.success) {
      showToast('Biometrik berhasil didaftarkan!', 'success');
    } else {
      throw new Error(verifyRes.data.message || 'Gagal verifikasi di server');
    }
  } catch (err) {
    if (err.name === 'NotAllowedError') {
      showToast('Pendaftaran dibatalkan user.', 'error');
    } else {
      const msg = err.response?.data?.message || err.message || 'Gagal registrasi biometrik.';
      showToast(msg, 'error');
    }
  } finally {
    registerLoading.value = false;
  }
};

const fetchSettings = async () => {
  try {
    loading.value = true
    const res = await api.get('/users/profile')
    const d = res.data.data
    users.value=d;
    Object.assign(formData, {
      is_single_login: Number(d.is_single_login) || 0,
      is_2fa_active: d.is_2fa_active || 'NO',
      bio_login_active: d.bio_login_active || 'NO',
      open_match: d.open_match || 'NO'
    })
    originalData.value = { ...formData }
  } catch (e) {
    showToast('Gagal memuat profil', 'error')
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  if (!hasChanges.value || saving.value) return
  try {
    saving.value = true
    const fd = new FormData()
    // Skip saving is_2fa_active here, it's handled by its own endpoint
    Object.keys(formData).forEach(k => {
        if(k !== 'is_2fa_active') fd.append(k, formData[k])
    })
    await api.put('/users/profile', fd, { 
      headers: { 'Content-Type': 'multipart/form-data' } 
    })
    originalData.value = { ...formData }
    showToast('Pengaturan berhasil disimpan', 'success')
  } catch (e) {
    showToast('Gagal menyimpan pengaturan', 'error')
  } finally {
    saving.value = false
  }
}

const goBack = () => {
  if (hasChanges.value && !confirm('Ada perubahan yang belum disimpan. Yakin ingin keluar?')) {
    return
  }
  router.back()
}

const showHelp = () => {
  showToast('Hubungi admin@percasi.id untuk bantuan', 'info')
}

// --- LIFECYCLE ---
onMounted(async () => {
  await Promise.all([
    fetchSettings(),
    checkBiometricSupport()
  ])
})
</script>

<style scoped>
/* Animations (Tetap Sama) */
.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

/* Modal Animation */
.animate-slide-up {
  animation: slideUpModal 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUpModal {
  from {
    opacity: 0;
    transform: translateY(40px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  max-height: 500px;
  opacity: 1;
}

.slide-down-enter-from,
.slide-down-leave-to {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translate(-50%, 0);
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translate(-50%, 20px);
  opacity: 0;
}

/* Touch optimization */
.touch-manipulation {
  touch-action: manipulation;
}

.min-tap-target {
  min-height: 44px;
  min-width: 44px;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 4px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Keyframes */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .dark\:from-slate-950 {
    --tw-gradient-from: #020617 var(--tw-gradient-from-position);
  }
}

/* Ensure proper touch feedback on mobile */
@media (max-width: 640px) {
  main {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  .rounded-3xl {
    border-radius: 1.5rem;
  }
  
  button {
    -webkit-tap-highlight-color: transparent;
  }
}
</style>