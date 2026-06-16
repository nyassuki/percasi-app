<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 md:p-6 transition-colors duration-300">
    <!-- Modern Gradient Background -->
    <div class="fixed inset-0 bg-gradient-to-br from-white via-blue-50 to-teal-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 -z-10"></div>
    
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden -z-5">
      <div class="absolute top-1/4 -left-20 w-72 h-72 bg-teal-300/10 rounded-full blur-3xl animate-pulse-slow"></div>
      <div class="absolute bottom-1/4 -right-20 w-72 h-72 bg-blue-300/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
      <div class="absolute top-3/4 left-1/3 w-64 h-64 bg-emerald-300/5 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 2s"></div>
    </div>

    <div class="relative w-full max-w-md mx-auto">
      
      <!-- Modern Language Selector -->
      <div class="flex justify-end mb-6">
        <div class="relative group">
          <button @click="showLanguageDropdown = !showLanguageDropdown"
                  class="flex items-center gap-2 px-3 py-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200">
            <img :src="`https://flagcdn.com/w40/${currentFlag}.png`" class="w-5 h-5 rounded object-cover" />
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ locale.toUpperCase() }}</span>
            <svg class="w-3 h-3 text-gray-500 transition-transform duration-200" 
                 :class="{ 'rotate-180': showLanguageDropdown }" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          
          <!-- Language Dropdown -->
          <transition name="fade-down">
                  <div v-if="showLanguageDropdown" 
                       class="absolute right-0 mt-2 w-48 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                    <div class="py-1 max-h-60 overflow-y-auto">
                      <button v-for="lang in languages" 
                              :key="lang.c"
                              @click="changeLanguage(lang.c)"
                              class="flex items-center gap-3 w-full px-3 py-2.5 text-left hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
                              :class="{ 'bg-teal-50 dark:bg-teal-900/30': locale === lang.c }">
                        <img :src="`https://flagcdn.com/w40/${lang.f}.png`" class="w-6 h-6 rounded object-cover" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ lang.n }}</span>
                        <svg v-if="locale === lang.c" class="w-4 h-4 text-teal-500 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                </transition>
        </div>
      </div>

      <!-- App Logo & Welcome -->
      <div class="text-center mb-8 space-y-3">
        <!-- Animated Logo -->
        <div class="relative inline-flex mb-2">
          <div class="absolute inset-0 bg-gradient-to-r from-teal-400 to-emerald-500 rounded-2xl blur-xl opacity-70 animate-pulse"></div>
          <div class="relative w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl shadow-2xl shadow-teal-500/30 flex items-center justify-center overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            <span class="text-2xl md:text-4xl">♟️</span>
          </div>
        </div>
        
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-1">
            {{ settings.appName || 'Percasi' }}
          </h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 font-medium px-4">
            <template v-if="current_user_email">
              {{ t('auth_login.welcome_user', { name: current_user_fullname || current_user_name || current_user_email }) }}
            </template>
            <template v-else>
              {{ stepDescription }}
            </template>
          </p>
        </div>
      </div>

      <!-- Main Card -->
      <div class="relative">
        <!-- Card Glow Effect -->
        <div class="absolute -inset-0.5 bg-gradient-to-r from-teal-400/30 via-blue-400/20 to-emerald-400/30 rounded-2xl blur opacity-70 group-hover:opacity-100 transition-opacity duration-500"></div>
        
        <div class="relative bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-2xl shadow-2xl p-6 md:p-8 border border-gray-100 dark:border-gray-700/50">
          
          <!-- Error Message -->
          <transition name="slide-fade">
            <div v-if="error" class="mb-5 p-3.5 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border-l-4 border-red-500 rounded-r-xl">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0">
                  <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-xs font-medium text-red-700 dark:text-red-400 flex-1 leading-tight">
                  {{ error }}
                </p>
                <button @click="error = ''" class="text-red-500 hover:text-red-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </transition>

          <!-- Biometric Login Section -->
          <div v-if="current_user_email && step === 'login'" class="animate-fade-in">
            <div class="text-center space-y-5">
              <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                {{ t('auth_login.biometric.title') }}
              </h3>
              
              <div class="space-y-3">
                <button 
                  type="button"
                  @click="handleBiometricLogin"
                  :disabled="loading"
                  class="group w-full relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 p-4 hover:border-teal-500 hover:shadow-lg transition-all duration-300 disabled:opacity-50 active:scale-[0.98]"
                >
                  <!-- Shimmer Effect -->
                  <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                  
                  <div class="relative flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center shadow-lg shadow-teal-500/20 group-hover:scale-110 transition-transform duration-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                      </svg>
                    </div>
                    <div class="text-left flex-1">
                      <p class="font-bold text-gray-900 dark:text-white text-sm">
                        {{ loading ? t('auth_login.biometric.processing') : t('auth_login.biometric.btn') }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ current_user_email }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                  </div>
                </button>

                <div class="text-xs text-gray-500 dark:text-gray-400 text-center px-2">
                  {{ t('auth_login.biometric.desc') }}
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2 pt-2">
                <button @click="clearCurrentUser" 
                        class="text-xs font-medium py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors active:scale-[0.98]">
                  {{ t('auth_login.other_account') }}
                </button>
                <router-link to="/register" 
                             class="text-xs font-medium py-3 rounded-lg bg-gradient-to-r from-teal-500 to-emerald-500 text-white shadow-lg shadow-teal-500/30 hover:shadow-xl hover:from-teal-600 hover:to-emerald-600 transition-all flex items-center justify-center gap-1.5 active:scale-[0.98]">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                  </svg>
                  {{ t('auth_login.register_new') }}
                </router-link>
              </div>
            </div>
          </div>

          <!-- Regular Login Form -->
          <div v-else-if="step === 'login'" class="animate-fade-in space-y-5">
            <form @submit.prevent="handleLogin" class="space-y-4">
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1.5 ml-1">
                    {{ t('auth_login.form.email_label') }}
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                      </svg>
                    </div>
                    <input v-model="email" type="email" 
                           class="w-full pl-9 pr-4 py-3 bg-gray-50/50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl text-black  text-sm focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 outline-none transition-all dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                           :placeholder="t('auth_login.form.email_placeholder')"
                           required />
                  </div>
                </div>

                <div>
                  <div class="flex justify-between mb-1.5 ml-1">
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                      {{ t('auth_login.form.password_label') }}
                    </label>
                    <router-link to="/forgot-password" class="text-xs font-medium text-teal-600 dark:text-teal-400 hover:text-teal-700 transition-colors">
                      {{ t('auth_login.form.forgot_password') }}
                    </router-link>
                  </div>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                      </svg>
                    </div>
                    <input v-model="password" type="password"
                           class="w-full pl-9 pr-4 py-3 bg-gray-50/50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-black focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 outline-none transition-all dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                           :placeholder="t('auth_login.form.password_placeholder')"
                           required />
                  </div>
                </div>
              </div>

              <button type="submit" :disabled="loading" 
                      class="w-full bg-gradient-to-r from-gray-900 to-slate-800 dark:from-teal-600 dark:to-emerald-600 text-white font-semibold py-3.5 rounded-xl hover:shadow-lg active:scale-[0.98] transition-all duration-300 disabled:opacity-50 shadow-md">
                <span v-if="loading" class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                  </svg>
                  {{ t('auth_login.form.btn_loading') }}
                </span>
                <span v-else>{{ t('auth_login.form.btn_login') }}</span>
              </button>
            </form>

            <!-- Divider -->
            <div class="relative py-2">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
              </div>
              <div class="relative flex justify-center">
                <span class="px-3 bg-white dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-400">
                  {{ t('auth_login.form.or_divider') }}
                </span>
              </div>
            </div>

            <!-- Google Login -->
            <div class="flex justify-center">
              <GoogleLogin :callback="callbackGoogle" />
            </div>

            <!-- Register Link -->
            <div class="text-center pt-3">
              <p class="text-xs text-gray-600 dark:text-gray-400">
                {{ t('auth_login.register_link.text') }} 
                <router-link to="/register" class="font-semibold text-teal-600 dark:text-teal-400 hover:text-teal-700 transition-colors">
                  {{ t('auth_login.register_link.link') }}
                </router-link>
              </p>
            </div>
          </div>

          <!-- 2FA Setup/Verify Section -->
          <div v-else class="animate-slide-up space-y-5">
            <div v-if="step === 'setup'" class="text-center space-y-4">
              <h3 class="font-bold text-gray-900 dark:text-white text-lg">{{ t('auth_login.2fa.title_setup') }}</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 px-2">{{ t('auth_login.2fa.desc_setup') }}</p>
              
              <!-- QR Code Container -->
              <div class="relative inline-flex p-4 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="absolute inset-0 bg-gradient-to-r from-teal-400/20 to-blue-400/20 rounded-2xl blur-xl opacity-30"></div>
                <div class="relative bg-white p-3 rounded-xl border-4 border-gray-50 dark:border-gray-900">
                  <img v-if="qrCodeUrl" :src="qrCodeUrl" class="w-40 h-40 md:w-48 md:h-48 mx-auto" alt="QR Code" />
                </div>
              </div>

              <!-- Manual Code -->
              <div class="bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between gap-3">
                  <div class="text-left">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                      {{ t('auth_login.2fa.manual_code') }}
                    </p>
                    <p class="font-mono font-bold text-teal-600 dark:text-teal-400 text-sm select-all">
                      {{ manualCode }}
                    </p>
                  </div>
                  <button @click="copyCode" 
                          class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors active:scale-95">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- OTP Form -->
            <form @submit.prevent="handleVerify2FA" class="space-y-5">
              <div class="space-y-3">
                <label class="block text-center text-sm font-semibold text-gray-600 dark:text-gray-400">
                  {{ step === 'setup' ? t('auth_login.2fa.title_input_setup') : t('auth_login.2fa.title_verify') }}
                </label>
                <div class="relative">
                  <input 
                    ref="otpInput" 
                    v-model="otp" 
                    type="text" 
                    inputmode="numeric" 
                    pattern="\d{6}"
                    maxlength="6"
                    class="w-full text-center tracking-[0.5em] text-3xl md:text-4xl font-black bg-gray-50/50 dark:bg-gray-900/50 border-2 border-gray-300 dark:border-gray-600 rounded-xl p-4 md:p-5 text-teal-600 dark:text-teal-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/30 outline-none transition-all placeholder:text-gray-300 dark:placeholder:text-gray-600"
                    placeholder="000000" 
                    required 
                  />
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                  {{ t('auth_login.2fa.otp_hint') }}
                </p>
              </div>

              <button type="submit" :disabled="loading || otp.length < 6" 
                      class="w-full bg-gradient-to-r from-teal-500 to-emerald-500 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-teal-500/20 hover:shadow-xl hover:from-teal-600 hover:to-emerald-600 transition-all active:scale-[0.98] disabled:opacity-50">
                <span v-if="loading" class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                  </svg>
                  {{ t('auth_login.form.btn_loading') }}
                </span>
                <span v-else>{{ step === 'setup' ? t('auth_login.2fa.btn_activate') : t('auth_login.2fa.btn_confirm') }}</span>
              </button>

              <button @click="backToLogin" type="button" 
                      class="w-full text-center text-xs font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ t('auth_login.2fa.back_to_login') }}
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 text-center">
        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-medium">
          © {{ new Date().getFullYear() }} {{ settings.appName }}. {{ t('auth_login.footer') }}
        </p>
      </div>
    </div>

    <!-- Mobile Bottom Safe Area -->
    <div class="h-8 md:h-0"></div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, inject, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { useSettingStore } from '../stores/settings';
import { GoogleLogin } from 'vue3-google-login';
import { startAuthentication } from '@simplewebauthn/browser';
import api from '../services/api';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const swal = inject('swal');
const toast = inject('toast');

const auth = useAuthStore();
const router = useRouter();
const settings = useSettingStore();

// State variables
const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);
const step = ref('login');
const otp = ref('');
const tempToken = ref(null);
const qrCodeUrl = ref('');
const manualCode = ref('');
const otpInput = ref(null);
const current_user_email = ref(null);
const current_user_fullname = ref(null);
const current_user_name = ref(null);
const showLanguageDropdown = ref(false);

// Computed properties
const stepDescription = computed(() => {
  if (step.value === 'setup') return t('auth_login.2fa.desc_setup');
  if (step.value === 'verify') return t('auth_login.2fa.desc_verify');
  return `${t('auth_login.default_welcome')} ${settings.appName || 'Percasi'}`;
});
const languages = [
  {c:'id', f:'id', n:'Indonesia'}, 
  {c:'en', f:'gb', n:'English'}, 
  {c:'cn', f:'cn', n:'Chinese'}, 
  {c:'jp', f:'jp', n:'Japanese'}, 
  {c:'ru', f:'ru', n:'Russian'}, 
  {c:'th', f:'th', n:'Thai'}, 
  {c:'ph', f:'ph', n:'Filipino'}, 
  {c:'vt', f:'vn', n:'Vietnamese'}
];
const currentFlag = computed(() => {
  const lang = languages.find(l => l.c === locale.value);
  return lang ? lang.f : 'gb';
});
const changeLanguage = (langCode) => {
  locale.value = langCode;
  showLanguageDropdown.value = false;
  localStorage.setItem('user-locale', langCode);
};
// Methods
const getCurrentUser = () => {
  current_user_email.value = localStorage.getItem('current_user_email') || null;
  current_user_fullname.value = localStorage.getItem('current_user_fullname') || null;
  current_user_name.value = localStorage.getItem('current_user_name') || null;
};

const clearCurrentUser = () => {
  localStorage.removeItem('current_user_email');
  localStorage.removeItem('current_user_fullname');
  localStorage.removeItem('current_user_name');
  current_user_email.value = null;
  current_user_fullname.value = null;
  current_user_name.value = null;
  email.value = '';
  password.value = '';
  error.value = '';
  showLanguageDropdown.value = false;
};

const handleBiometricLogin = async () => {
  const emailToUse = current_user_email.value || email.value;
  const hostname = window.location.hostname;

  if (!emailToUse) {
    error.value = t('auth_login.error.email_required');
    return;
  }
  
  error.value = '';
  loading.value = true;

  try {
    const res = await api.post('/auth/biometric/login-options', { 
      email: emailToUse 
    });
    const options = res.data;

    // Domain consistency check
    if (
      options.rpId !== hostname &&
      !hostname.endsWith('.' + options.rpId)
    ) {
      throw new Error(
        `Domain mismatch: rpId '${options.rpId}' doesn't match hostname '${hostname}'`
      );
    }

    if (options.allowCredentials.length === 0) {
      throw new Error('No biometric credentials found for this user');
    }

    // Start biometric authentication
    const authResponse = await startAuthentication(options);
    const response = await auth.loginBiometic(emailToUse, authResponse);
    handleAuthResponse(response);
  } catch (err) {
    console.error('Biometric login failed:', err);
    
    if (err.name === 'NotAllowedError') {
      error.value = t('auth_login.error.biometric_cancelled');
    } else if (err.message.includes('Domain mismatch')) {
      error.value = err.message;
    } else {
      error.value = err.response?.data?.message || err.message || t('auth_login.error.biometric_failed');
    }
    
    toast.fire({ icon: 'error', title: error.value });
  } finally {
    loading.value = false;
  }
};

const handleAuthResponse = async (response) => {
  const payload = response.data || response;
  
  if (payload.mode === 'verify') {
    step.value = 'verify';
    tempToken.value = payload.data?.temp_token || payload.temp_token;
    await focusOtp();
    return;
  }

  if (payload.mode === 'setup') {
    step.value = 'setup';
    qrCodeUrl.value = payload.data?.qr_code || payload.qr_code;
    manualCode.value = payload.data?.manual_code || payload.manual_code;
    tempToken.value = payload.data?.temp_token || payload.temp_token;
    await focusOtp();
    return;
  }

  if (payload.token) {
    router.push('/dashboard');
  }
};

const focusOtp = async () => {
  await nextTick();
  if (otpInput.value) {
    otpInput.value.focus();
    otpInput.value.select();
  }
};

const handleLogin = async () => {
  error.value = '';
  loading.value = true;
  try {
    const response = await auth.login(email.value, password.value);
    await handleAuthResponse(response);
  } catch (err) {
    error.value = err.response?.data?.message || err.message || t('auth_login.error.login_failed');
    toast.fire({ icon: 'error', title: error.value });
  } finally {
    loading.value = false;
  }
};

const callbackGoogle = async (response) => {
  error.value = '';
  try {
    const res = await auth.loginWithGoogle(response.credential);
    await handleAuthResponse(res);
  } catch (err) {
    error.value = err.response?.data?.message || t('auth_login.error.google_failed');
    toast.fire({ icon: 'error', title: error.value });
  }
};

const handleVerify2FA = async () => {
  if (otp.value.length < 6) return;
  error.value = '';
  loading.value = true;
  try {
    const res = await auth.verify2FA(otp.value, tempToken.value);
    if (res.status || res.token) {
      router.push('/dashboard');
    }
  } catch (err) {
    otp.value = ''; 
    error.value = err.response?.data?.message || t('auth_login.error.otp_invalid');
    toast.fire({ icon: 'error', title: error.value });
  } finally {
    loading.value = false;
  }
};

const backToLogin = () => {
  step.value = 'login';
  otp.value = '';
  error.value = '';
  tempToken.value = null;
  showLanguageDropdown.value = false;
};

const copyCode = () => {
  navigator.clipboard.writeText(manualCode.value);
  toast.fire({ icon: 'success', title: t('auth_login.2fa.copy_success') });
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (showLanguageDropdown.value && !event.target.closest('.relative.group')) {
    showLanguageDropdown.value = false;
  }
};

// Lifecycle
onMounted(async () => {
  await settings.fetchSettings();
  getCurrentUser();
  const savedLocale = localStorage.getItem('user-locale');
    if (savedLocale) {
        locale.value = savedLocale;
    }
  document.addEventListener('click', handleClickOutside);
});

// Cleanup
import { onUnmounted } from 'vue';
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Custom Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideFade {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulseSlow {
  0%, 100% {
    opacity: 0.3;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes fadeDown {
  from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.animate-fade-in {
  animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.animate-slide-up {
  animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.animate-pulse-slow {
  animation: pulseSlow 3s ease-in-out infinite;
}

.slide-fade-enter-active {
  animation: slideFade 0.2s ease-out;
}

.slide-fade-leave-active {
  animation: slideFade 0.2s ease-out reverse;
}

.fade-down-enter-active {
  animation: fadeDown 0.2s ease-out;
}

.fade-down-leave-active {
  animation: fadeDown 0.2s ease-out reverse;
}

/* Smooth transitions */
* {
  transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}

/* Custom scrollbar for language dropdown */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: rgba(100, 116, 139, 0.3);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(100, 116, 139, 0.5);
}

/* OTP input styling */
input[type="text"]::-webkit-outer-spin-button,
input[type="text"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="text"] {
  -moz-appearance: textfield;
}

/* Better touch targets for mobile */
@media (max-width: 768px) {
  button, a, input, .selectable {
    min-height: 44px;
    min-width: 44px;
  }
  
  .text-3xl {
    font-size: 2rem;
  }
  
  .p-6 {
    padding: 1.25rem;
  }
  
  .w-40.h-40 {
    width: 10rem;
    height: 10rem;
  }
}

/* Safe area for iPhone X and newer */
@supports (padding: max(0px)) {
  .min-h-screen {
    padding-left: max(1rem, env(safe-area-inset-left));
    padding-right: max(1rem, env(safe-area-inset-right));
    padding-bottom: max(1rem, env(safe-area-inset-bottom));
  }
}

/* Hover effects optimization */
@media (hover: hover) {
  button:hover, a:hover {
    transform: translateY(-1px);
  }
  
  button:active, a:active {
    transform: translateY(0);
  }
}

/* Reduced motion preferences */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .backdrop-blur-xl {
    backdrop-filter: blur(12px);
  }
}

/* Selection styling */
::selection {
  background-color: rgba(20, 184, 166, 0.3);
  color: inherit;
}

/* Focus visible for accessibility */
:focus-visible {
  outline: 2px solid #0d9488;
  outline-offset: 2px;
}

/* Loading state optimization */
button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Responsive typography */
@media (max-width: 640px) {
  h1 {
    font-size: 1.75rem;
  }
  
  .text-lg {
    font-size: 1.125rem;
  }
  
  .text-sm {
    font-size: 0.875rem;
  }
  
  .text-xs {
    font-size: 0.75rem;
  }
}
</style>