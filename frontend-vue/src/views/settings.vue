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
            <h1 class="text-lg font-bold text-gray-800 dark:text-white uppercase tracking-tighter">{{ t('settings.header.title') }}</h1>
            <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-0.5">{{ t('settings.header.subtitle') }}</p>
          </div>
          
          <div class="flex items-center gap-2">
            <button @click="showHelpModal = true" class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 active:scale-95 touch-manipulation">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
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
          <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] ml-2">{{ t('settings.security.title') }}</h2>
          
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div class="flex items-start gap-4">
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', formData.is_single_login === 1 ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400']">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-width="2"/></svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 dark:text-white text-base">{{ t('settings.security.single_login.title') }}</h3>
                  <p class="text-xs text-gray-500 leading-relaxed mt-1">{{ t('settings.security.single_login.desc') }}</p>
                </div>
              </div>
              <ToggleSwitch :value="formData.is_single_login === 1" @toggle="handleToggle('is_single_login', $event ? 1 : 0)" />
            </div>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div class="flex items-start gap-4">
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', formData.is_2fa_active === 'YES' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400']">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-width="2"/></svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 dark:text-white text-base">{{ t('settings.security.2fa.title') }}</h3>
                  <p class="text-xs text-gray-500 leading-relaxed mt-1">{{ t('settings.security.2fa.desc') }}</p>
                </div>
              </div>
              <ToggleSwitch :value="formData.is_2fa_active === 'YES'" @toggle="handle2FAToggle($event)" />
            </div>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="flex items-center justify-between">
              <div class="flex items-start gap-4">
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', formData.bio_login_active === 'YES' ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400']">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" stroke-width="2"/></svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 dark:text-white text-base">{{ t('settings.security.biometric.title') }}</h3>
                  <p class="text-xs text-gray-500 leading-relaxed mt-1">{{ t('settings.security.biometric.desc') }}</p>
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
                      <p class="text-xs font-bold text-amber-800 dark:text-amber-300">{{ t('settings.security.register_biometric.unsupported.title') }}</p>
                      <p class="text-xs text-amber-600 dark:text-amber-400/80 mt-1 leading-relaxed">
                        {{ t('settings.security.register_biometric.unsupported.desc') }}
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
                          <p class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-widest">{{ t('settings.security.register_biometric.title') }}</p>
                          <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ t('settings.security.register_biometric.subtitle') }}</p>
                          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ t('settings.security.register_biometric.desc') }}</p>
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
                          {{ t('settings.security.register_biometric.btn_processing') }}
                        </span>

                        <span v-else-if="isBiometricRegistered" class="flex items-center gap-2">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          {{ t('settings.security.register_biometric.btn_registered') }}
                        </span>

                        <span v-else>{{ t('settings.security.register_biometric.btn_register') }}</span>

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
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ t('settings.security.register_biometric.tips.title') }}</p>
                        <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                          <li class="flex items-center gap-2">• {{ t('settings.security.register_biometric.tips.1') }}</li>
                          <li class="flex items-center gap-2">• {{ t('settings.security.register_biometric.tips.2') }}</li>
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
          <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] ml-2">{{ t('settings.privacy.title') }}</h2>
          
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div class="flex items-start gap-4">
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', formData.open_match === 'YES' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400']">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-width="2"/></svg>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800 dark:text-white text-base">{{ t('settings.privacy.open_match.title') }}</h3>
                  <p class="text-xs text-gray-500 leading-relaxed mt-1">{{ t('settings.privacy.open_match.desc') }}</p>
                </div>
              </div>
              <ToggleSwitch :value="formData.open_match === 'YES'" @toggle="handleToggle('open_match', $event ? 'YES' : 'NO')" />
            </div>
          </div>
        </div>

        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-3xl p-6 border border-blue-100 dark:border-blue-800/30 hover:shadow-md transition-shadow">
          <div class="flex gap-4 items-start">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800/30 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h4 class="font-bold text-blue-900 dark:text-blue-300 text-sm mb-2">{{ t('settings.tips.title') }}</h4>
              <ul class="text-xs text-blue-700 dark:text-blue-400/80 space-y-1">
                <li class="flex items-start gap-2">• {{ t('settings.tips.1') }}</li>
                <li class="flex items-start gap-2">• {{ t('settings.tips.2') }}</li>
                <li class="flex items-start gap-2">• {{ t('settings.tips.3') }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </main>

    <transition name="modal">
      <div v-if="show2FAModal" class="fixed inset-0 z-[100] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="close2FAModal"></div>
        
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden animate-modal-up border border-gray-100 dark:border-slate-700">
          <div class="p-8">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ t('settings.modal_2fa.title') }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ t('settings.modal_2fa.desc') }}</p>
            </div>

            <div class="bg-gray-50 dark:bg-slate-900 rounded-2xl p-6 mb-6 border border-gray-200 dark:border-slate-700 text-center">
              <div v-if="twoFASetupData.qr_code" class="inline-block bg-white p-2 rounded-xl shadow-sm">
                 <img :src="twoFASetupData.qr_code" class="w-40 h-40 mx-auto" alt="QR Code" />
              </div>
              <div v-else class="h-40 flex items-center justify-center">
                 <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                 <span class="ml-2 text-sm text-gray-500">{{ t('settings.modal_2fa.loading_qr') }}</span>
              </div>
              
              <div class="mt-4 pt-4 border-t border-gray-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">{{ t('settings.modal_2fa.manual_code') }}</p>
                <code class="bg-white dark:bg-slate-800 px-3 py-2 rounded-lg text-sm font-mono text-gray-800 dark:text-gray-200 select-all border border-gray-200 dark:border-slate-700 block">
                  {{ twoFASetupData.secret || 'Loading...' }}
                </code>
              </div>
            </div>

            <form @submit.prevent="verifyAndActivate2FA">
              <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2">{{ t('settings.modal_2fa.verify_otp') }}</label>
                <input v-model="twoFAVerifyCode" type="text" maxlength="6" :placeholder="t('settings.modal_2fa.placeholder_otp')" class="w-full text-center text-black text-2xl tracking-[0.5em] font-bold py-4 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all shadow-sm" />
              </div>

              <div class="flex gap-3">
                <button type="button" @click="close2FAModal" class="flex-1 py-3.5 rounded-xl bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors">{{ t('settings.modal_2fa.btn_cancel') }}</button>
                <button type="submit" :disabled="loading2FA || twoFAVerifyCode.length < 6" class="flex-1 py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                   <span v-if="loading2FA" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                   <span>{{ t('settings.modal_2fa.btn_activate') }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <transition name="modal">
      <div v-if="showConfirmDialog" class="fixed inset-0 z-[100] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="cancelConfirm"></div>
        
        <div class="relative w-full max-w-sm bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden animate-modal-up border border-gray-100 dark:border-slate-700">
          <div class="p-6">
            <div class="flex justify-center mb-4">
              <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center', 
                            confirmConfig.type === 'warning' ? 'bg-amber-100 dark:bg-amber-900/30' : 
                            confirmConfig.type === 'danger' ? 'bg-red-100 dark:bg-red-900/30' :
                            'bg-blue-100 dark:bg-blue-900/30']">
                <svg v-if="confirmConfig.type === 'warning'" class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <svg v-else-if="confirmConfig.type === 'danger'" class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg v-else class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">{{ confirmConfig.title }}</h3>
            
            <p class="text-sm text-gray-600 dark:text-gray-300 text-center mb-6 leading-relaxed">{{ confirmConfig.message }}</p>

            <div v-if="confirmConfig.details" class="bg-gray-50 dark:bg-slate-900/50 p-4 rounded-xl mb-6">
              <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ confirmConfig.details }}</p>
            </div>

            <div class="flex gap-3">
              <button @click="cancelConfirm" 
                      :class="['flex-1 py-3.5 rounded-xl font-bold text-sm transition-all active:scale-95',
                              confirmConfig.type === 'danger' ? 
                              'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-600' :
                              'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-600']">
                {{ confirmConfig.cancelText }}
              </button>
              <button @click="confirmAction" 
                      :class="['flex-1 py-3.5 rounded-xl font-bold text-sm transition-all active:scale-95 shadow-lg',
                              confirmConfig.type === 'danger' ? 
                              'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white' :
                              confirmConfig.type === 'warning' ?
                              'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white' :
                              'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white']">
                {{ confirmConfig.confirmText }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <transition name="modal">
      <div v-if="showHelpModal" class="fixed inset-0 z-[100] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showHelpModal = false"></div>
        
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden animate-modal-up border border-gray-100 dark:border-slate-700">
          <div class="px-8 pt-8 pb-6 border-b border-gray-100 dark:border-slate-700/50">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ t('settings.help.title') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ t('settings.help.subtitle') }}</p>
              </div>
            </div>
          </div>

          <div class="p-8 max-h-[60vh] overflow-y-auto">
            <div class="space-y-6">
              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">1</span>
                  </div>
                  <h4 class="font-bold text-gray-800 dark:text-white">{{ t('settings.security.single_login.title') }}</h4>
                </div>
                <div class="ml-13">
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ t('settings.help.single_login_desc') }}
                  </p>
                  <ul class="text-xs text-gray-500 dark:text-gray-400 mt-2 space-y-1 ml-4">
                    <li class="list-disc">{{ t('settings.help.benefits.1') }}</li>
                    <li class="list-disc">{{ t('settings.help.benefits.2') }}</li>
                    <li class="list-disc">{{ t('settings.help.benefits.3') }}</li>
                  </ul>
                </div>
              </div>

              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-blue-600 dark:text-blue-400 font-bold">2</span>
                  </div>
                  <h4 class="font-bold text-gray-800 dark:text-white">{{ t('settings.security.2fa.title') }}</h4>
                </div>
                <div class="ml-13">
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ t('settings.help.2fa_desc') }}
                  </p>
                  <div class="bg-blue-50 dark:bg-blue-900/10 p-4 rounded-xl mt-3 border border-blue-100 dark:border-blue-800/30">
                    <p class="text-xs font-bold text-blue-700 dark:text-blue-300 mb-2">{{ t('settings.help.setup_steps') }}</p>
                    <ol class="text-xs text-blue-600 dark:text-blue-400 space-y-2">
                      <li class="flex items-start gap-2">{{ t('settings.help.step_1') }}</li>
                      <li class="flex items-start gap-2">{{ t('settings.help.step_2') }}</li>
                      <li class="flex items-start gap-2">{{ t('settings.help.step_3') }}</li>
                      <li class="flex items-start gap-2">{{ t('settings.help.step_4') }}</li>
                    </ol>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-purple-600 dark:text-purple-400 font-bold">3</span>
                  </div>
                  <h4 class="font-bold text-gray-800 dark:text-white">{{ t('settings.security.biometric.title') }}</h4>
                </div>
                <div class="ml-13">
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ t('settings.help.biometric_desc') }}
                  </p>
                  <div class="bg-purple-50 dark:bg-purple-900/10 p-4 rounded-xl mt-3 border border-purple-100 dark:border-purple-800/30">
                    <p class="text-xs font-bold text-purple-700 dark:text-purple-300 mb-2">{{ t('settings.help.requirements') }}</p>
                    <ul class="text-xs text-purple-600 dark:text-purple-400 space-y-1">
                      <li class="flex items-start gap-2">• {{ t('settings.help.req_1') }}</li>
                      <li class="flex items-start gap-2">• {{ t('settings.help.req_2') }}</li>
                      <li class="flex items-start gap-2">• {{ t('settings.help.req_3') }}</li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-amber-600 dark:text-amber-400 font-bold">4</span>
                  </div>
                  <h4 class="font-bold text-gray-800 dark:text-white">{{ t('settings.privacy.open_match.title') }}</h4>
                </div>
                <div class="ml-13">
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ t('settings.help.challenge_desc') }}
                  </p>
                  <ul class="text-xs text-gray-500 dark:text-gray-400 mt-2 space-y-1 ml-4">
                    <li class="list-disc">{{ t('settings.help.challenge_active') }}</li>
                    <li class="list-disc">{{ t('settings.help.challenge_inactive') }}</li>
                  </ul>
                </div>
              </div>

              <div class="bg-slate-50 dark:bg-slate-900/30 p-5 rounded-2xl border border-slate-200 dark:border-slate-700">
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Butuh bantuan lebih lanjut?</p>
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-gray-800 dark:text-white">{{ t('settings.help.email_support') }}</p>
                    <a href="mailto:admin@percasi.id" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">admin@percasi.id</a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="px-8 py-6 border-t border-gray-100 dark:border-slate-700/50">
            <button @click="showHelpModal = false" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm shadow-lg shadow-blue-500/30 transition-all active:scale-95">
              {{ t('settings.help.btn_understand') }}
            </button>
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
import { ref, reactive, onMounted, computed, h } from 'vue'
import { useRouter } from 'vue-router'
import { startRegistration } from '@simplewebauthn/browser'
import api from '../services/api'
import { useI18n } from 'vue-i18n' // [BARU]

const { t } = useI18n(); // [BARU]
const router = useRouter()

// --- ICONS ---
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

// --- TOGGLE SWITCH ---
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
const loading = ref(false)
const saving = ref(false)
const registerLoading = ref(false)
const isBiometricSupported = ref(false)
const isBiometricRegistered = ref(false)
const toastMessage = ref('')
const toastType = ref('success')
const activeTab = ref('security')
const originalData = ref({})
const users = ref({})
const temp_token = ref('')
const showHelpModal = ref(false)

// 2FA State
const show2FAModal = ref(false)
const loading2FA = ref(false)
const twoFASetupData = reactive({ qr_code: '', secret: '' }) 
const twoFAVerifyCode = ref('')

// Confirm Dialog State
const showConfirmDialog = ref(false)
const confirmConfig = reactive({
  title: '',
  message: '',
  details: '',
  type: 'warning', // 'warning', 'danger', 'info'
  cancelText: 'Batal',
  confirmText: 'Ya, Lanjutkan',
  onConfirm: null,
  onCancel: null
})

// Form Data
const formData = reactive({
  is_single_login: 0,
  is_2fa_active: 'NO',
  bio_login_active: 'NO',
  open_match: 'NO'
})

// [UPDATED] Computed agar label tab berubah sesuai bahasa
const tabs = computed(() => [
  { id: 'security', label: t('settings.tabs.security'), icon: SecurityIcon },
  { id: 'privacy', label: t('settings.tabs.privacy'), icon: PrivacyIcon }
])

// --- COMPUTED ---
const hasChanges = computed(() => {
  const fields = ['is_single_login', 'bio_login_active', 'open_match']
  if(formData['bio_login_active'] === "NO") {
    localStorage.removeItem('current_user_email');
    localStorage.removeItem('current_user_fullname');
    localStorage.removeItem('current_user_username');
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

// Elegant Confirm Dialog
const showConfirm = (config) => {
  Object.assign(confirmConfig, config)
  showConfirmDialog.value = true
}

const confirmAction = () => {
  if (confirmConfig.onConfirm) {
    confirmConfig.onConfirm()
  }
  showConfirmDialog.value = false
}

const cancelConfirm = () => {
  if (confirmConfig.onCancel) {
    confirmConfig.onCancel()
  }
  showConfirmDialog.value = false
}

// Toggle Handlers
const handleToggle = (field, val) => {
  formData[field] = val
  saveSettings()
}

const handle2FAToggle = async (val) => {
  if (val) {
    // Enable -> Show Modal
    await startSetup2FA()
  } else {
    // Show confirm dialog for disabling 2FA
    showConfirm({
      title: t('settings.confirm.disable_2fa_title'),
      message: t('settings.confirm.disable_2fa_msg'),
      details: t('settings.confirm.disable_2fa_detail'),
      type: 'warning',
      cancelText: t('settings.confirm.btn_cancel'),
      confirmText: t('settings.confirm.btn_yes_disable'),
      onConfirm: () => disable2FA(),
      onCancel: () => {
        // Reset toggle if canceled
        formData.is_2fa_active = 'YES'
      }
    })
  }
}

const handleBiometricToggle = (val) => {
  formData.bio_login_active = val ? 'YES' : 'NO'
  
  if (val) {
    checkBiometricSupport()
    handleRegisterBiometric()
    showToast(t('settings.toast.bio_activate_info'), 'info') // [UPDATED]
  } else {
    // Show confirm dialog for disabling biometric
    showConfirm({
      title: t('settings.confirm.disable_bio_title'),
      message: t('settings.confirm.disable_bio_msg'),
      details: t('settings.confirm.disable_bio_detail'),
      type: 'warning',
      cancelText: t('settings.confirm.btn_cancel'),
      confirmText: t('settings.confirm.btn_yes_turn_off'),
      onConfirm: () => {
        saveSettings()
        localStorage.removeItem('current_user_email')
        localStorage.removeItem('current_user_fullname')
        localStorage.removeItem('current_user_username')
        formData.bio_login_active = 'NO'
        isBiometricRegistered.value = false
      },
      onCancel: () => {
        formData.bio_login_active = 'YES'
      }
    })
  }
}

// 2FA Methods
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
    showToast(t('settings.toast.qr_fail'), 'error')
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
    showToast(t('settings.toast.2fa_success'), 'success')
    close2FAModal()
  } catch (e) {
    showToast(e.response?.data?.message || t('settings.toast.otp_invalid'), 'error')
  } finally {
    loading2FA.value = false
  }
}

const disable2FA = async () => {
  try {
    await api.post('/auth/2fa/disable')
    formData.is_2fa_active = 'NO'
    showToast(t('settings.toast.2fa_disabled'), 'success')
  } catch (e) {
    showToast(t('settings.toast.2fa_disable_fail'), 'error')
    formData.is_2fa_active = 'YES'
  }
}

const close2FAModal = () => {
  show2FAModal.value = false
  // Reset toggle if canceled
  if (formData.is_2fa_active !== 'YES') {
    formData.is_2fa_active = 'NO'
  }
}

// Biometric Methods
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
    showToast(t('settings.toast.bio_unsupported'), 'error')
    if (originalData.value.bio_login_active === 'NO') {
      formData.bio_login_active = 'NO'
    }
    return
  }

  registerLoading.value = true
  try {
    const response = await api.post('/auth/biometric/register-options')
    const options = response.data
    const regResponse = await startRegistration(options)
    
    localStorage.setItem('current_user_email', users.value.email)
    localStorage.setItem('current_user_fullname', users.value.full_name)
    localStorage.setItem('current_user_name', users.value.username)

    const verifyRes = await api.post('/auth/biometric/verify-registration', regResponse)
    if (verifyRes.data.success) {
      saveSettings()
      isBiometricRegistered.value = true
      showToast(t('settings.toast.bio_success'), 'success')
    } else {
      if (originalData.value.bio_login_active === 'NO') {
        formData.bio_login_active = 'NO'
      }
      throw new Error(verifyRes.data.message || t('settings.toast.bio_verify_fail'))
    }
  } catch (err) {
    if (err.name === 'NotAllowedError') {
      showToast(t('settings.toast.bio_cancelled'), 'error')
      if (originalData.value.bio_login_active === 'NO') {
        formData.bio_login_active = 'NO'
      }
    } else {
      const msg = err.response?.data?.message || err.message || t('settings.toast.bio_fail')
      showToast(msg, 'error')
      formData.bio_login_active = originalData.value.bio_login_active || 'NO'
    }
  } finally {
    registerLoading.value = false
  }
}

// Data Methods
const fetchSettings = async () => {
  try {
    loading.value = true
    const res = await api.get('/users/profile')
    const d = res.data.data
    users.value = d
    Object.assign(formData, {
      is_single_login: Number(d.is_single_login) || 0,
      is_2fa_active: d.is_2fa_active || 'NO',
      bio_login_active: d.bio_login_active || 'NO',
      open_match: d.open_match || 'NO'
    })
    originalData.value = { ...formData }
    isBiometricRegistered.value = d.bio_login_active === 'YES'
  } catch (e) {
    showToast(t('settings.toast.load_fail'), 'error')
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  if (!hasChanges.value || saving.value) return
  try {
    saving.value = true
    const fd = new FormData()
    Object.keys(formData).forEach(k => {
      if(k !== 'is_2fa_active') fd.append(k, formData[k])
    })
    await api.put('/users/profile', fd, { 
      headers: { 'Content-Type': 'multipart/form-data' } 
    })
    originalData.value = { ...formData }
    showToast(t('settings.toast.save_success'), 'success')
  } catch (e) {
    showToast(t('settings.toast.save_fail'), 'error')
  } finally {
    saving.value = false
  }
}

const goBack = () => {
  if (hasChanges.value) {
    showConfirm({
      title: t('settings.confirm.unsaved_title'),
      message: t('settings.confirm.unsaved_msg'),
      details: t('settings.confirm.unsaved_detail'),
      type: 'warning',
      cancelText: t('settings.confirm.btn_stay'),
      confirmText: t('settings.confirm.btn_leave'),
      onConfirm: () => router.back(),
      onCancel: () => {}
    })
  } else {
    router.back()
  }
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
/* Animations */
.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

.animate-modal-up {
  animation: slideUpModal 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

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

/* Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .animate-modal-up,
.modal-leave-active .animate-modal-up {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-enter-from .animate-modal-up,
.modal-leave-to .animate-modal-up {
  transform: translateY(40px) scale(0.95);
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

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .dark\:from-slate-950 {
    --tw-gradient-from: #020617 var(--tw-gradient-from-position);
  }
}

/* Mobile optimizations */
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

/* Scrollbar for help modal */
.max-h-\[60vh\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[60vh\]::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.max-h-\[60vh\]::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.dark .max-h-\[60vh\]::-webkit-scrollbar-thumb {
  background: #475569;
}

.dark .max-h-\[60vh\]::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

/* Ensure proper button sizes for touch */
button {
  min-height: 44px;
  min-width: 44px;
}
</style>