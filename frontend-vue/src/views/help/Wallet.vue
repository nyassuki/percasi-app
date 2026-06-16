<template>
  <div class="help-wallet-page min-h-screen bg-gradient-to-b from-gray-50 to-white text-gray-800">
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-2">
            <button @click="$router.back()" class="text-blue-600 hover:text-blue-700 transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <h1 class="text-xl lg:text-2xl font-bold text-gray-900">{{ t('help_wallet.header') }}</h1>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="toggleDarkMode" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition">
              <svg v-if="darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
              </svg>
              <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-4 py-8">
      <section class="mb-12 animate-fade-in-up">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-8 text-white shadow-xl">
          <div class="max-w-3xl">
            <h2 class="text-3xl lg:text-4xl font-bold mb-4">{{ t('help_wallet.hero.title') }}</h2>
            <p class="text-lg text-blue-100 mb-6">
              {{ t('help_wallet.hero.desc') }}
            </p>
            <div class="flex flex-wrap gap-4">
              <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                <span>{{ t('help_wallet.hero.f1') }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                <span>{{ t('help_wallet.hero.f2') }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                <span>{{ t('help_wallet.hero.f3') }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="mb-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <button 
            v-for="(section, index) in sections" 
            :key="index"
            @click="scrollToSection(section.id)"
            :class="[
              'p-4 rounded-xl border-2 transition-all duration-300 transform hover:-translate-y-1 text-left',
              activeSection === section.id 
                ? 'border-blue-500 bg-blue-50 shadow-md' 
                : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50 bg-white'
            ]"
          >
            <div class="flex items-center space-x-3">
              <div :class="['p-2 rounded-lg shrink-0', section.color]">
                <span class="text-xl">{{ section.icon }}</span>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">{{ section.title }}</h3>
                <p class="text-xs text-gray-600 line-clamp-1">{{ section.description }}</p>
              </div>
            </div>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-12">
          
          <section id="what-is-wallet" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 scroll-mt-24">
            <div class="flex items-center mb-6">
              <div class="p-3 bg-blue-100 rounded-xl mr-4">
                <span class="text-2xl">💰</span>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ t('help_wallet.content.what_is.title') }}</h2>
                <p class="text-gray-600">{{ t('help_wallet.content.what_is.subtitle') }}</p>
              </div>
            </div>
            
            <div class="space-y-6">
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl">
                <h3 class="font-semibold text-lg mb-3 text-gray-900">{{ t('help_wallet.content.what_is.section_title') }}</h3>
                <p class="text-gray-700 mb-4">
                  {{ t('help_wallet.content.what_is.desc') }}
                </p>
                <ul class="space-y-3">
                  <li v-for="n in 4" :key="n" class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ t(`help_wallet.content.what_is.list.${n}`) }}</span>
                  </li>
                </ul>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                  <div class="text-3xl mb-4">🔐</div>
                  <h4 class="font-bold text-gray-900 mb-2">{{ t('help_wallet.content.what_is.card1.title') }}</h4>
                  <p class="text-gray-600 text-sm">
                    {{ t('help_wallet.content.what_is.card1.desc') }}
                  </p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                  <div class="text-3xl mb-4">⚡</div>
                  <h4 class="font-bold text-gray-900 mb-2">{{ t('help_wallet.content.what_is.card2.title') }}</h4>
                  <p class="text-gray-600 text-sm">
                    {{ t('help_wallet.content.what_is.card2.desc') }}
                  </p>
                </div>
              </div>
            </div>
          </section>

          <section id="wallet-security" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 scroll-mt-24">
            <div class="flex items-center mb-6">
              <div class="p-3 bg-red-100 rounded-xl mr-4">
                <span class="text-2xl">🛡️</span>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ t('help_wallet.content.security.title') }}</h2>
                <p class="text-gray-600">{{ t('help_wallet.content.security.subtitle') }}</p>
              </div>
            </div>

            <div class="space-y-8">
              <div class="alert bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <span class="text-xl">⚠️</span>
                  </div>
                  <div class="ml-3">
                    <h3 class="font-semibold text-yellow-800">{{ t('help_wallet.content.security.alert_title') }}</h3>
                    <p class="text-yellow-700 mt-1 text-sm">
                      {{ t('help_wallet.content.security.alert_desc') }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="(tip, index) in securityTips" :key="index" 
                     class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all">
                  <div class="flex items-start mb-4">
                    <div :class="['p-3 rounded-lg shrink-0', tip.color]">
                      <span class="text-xl">{{ tip.icon }}</span>
                    </div>
                    <div class="ml-4">
                      <h4 class="font-bold text-gray-900">{{ tip.title }}</h4>
                      <p class="text-sm text-gray-600 mt-1">{{ tip.description }}</p>
                    </div>
                  </div>
                  <ul class="space-y-2">
                    <li v-for="(point, idx) in tip.points" :key="idx" 
                        class="text-sm text-gray-700 flex items-start">
                      <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      {{ point }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </section>

          <section id="practical-tips" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 scroll-mt-24">
            <div class="flex items-center mb-6">
              <div class="p-3 bg-green-100 rounded-xl mr-4">
                <span class="text-2xl">💡</span>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ t('help_wallet.content.practical.title') }}</h2>
                <p class="text-gray-600">{{ t('help_wallet.content.practical.subtitle') }}</p>
              </div>
            </div>

            <div class="space-y-6">
              <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl">
                <h3 class="font-bold text-lg text-gray-900 mb-4">{{ t('help_wallet.content.practical.checklist_title') }}</h3>
                <div class="space-y-4">
                  <div v-for="(check, index) in dailyChecks" :key="index" 
                       class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                    <div class="flex items-center">
                      <div :class="['w-8 h-8 rounded-full flex items-center justify-center mr-3 shrink-0', check.checked ? 'bg-green-100' : 'bg-gray-100']">
                        <span v-if="check.checked" class="text-green-600">✓</span>
                        <span v-else class="text-gray-400">{{ index + 1 }}</span>
                      </div>
                      <span :class="['font-medium text-sm md:text-base', check.checked ? 'text-green-700' : 'text-gray-700']">
                        {{ check.text }}
                      </span>
                    </div>
                    <button @click="toggleCheck(index)" 
                            class="text-xs md:text-sm px-3 py-1 rounded-full whitespace-nowrap ml-2"
                            :class="check.checked ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                      {{ check.checked ? t('help_wallet.content.practical.btn_done') : t('help_wallet.content.practical.btn_mark') }}
                    </button>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 p-6 rounded-xl border border-blue-100">
                  <h4 class="font-bold text-gray-900 mb-3">{{ t('help_wallet.content.practical.do_title') }}</h4>
                  <ul class="space-y-2">
                    <li v-for="(doItem, index) in dos" :key="index" class="flex items-start text-sm">
                      <svg class="w-5 h-5 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      {{ doItem }}
                    </li>
                  </ul>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-pink-50 p-6 rounded-xl border border-red-100">
                  <h4 class="font-bold text-gray-900 mb-3">{{ t('help_wallet.content.practical.dont_title') }}</h4>
                  <ul class="space-y-2">
                    <li v-for="(dontItem, index) in donts" :key="index" class="flex items-start text-sm">
                      <svg class="w-5 h-5 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                      {{ dontItem }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </section>

          <section id="faq" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 scroll-mt-24">
            <div class="flex items-center mb-6">
              <div class="p-3 bg-purple-100 rounded-xl mr-4">
                <span class="text-2xl">❓</span>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ t('help_wallet.content.faq.title') }}</h2>
                <p class="text-gray-600">{{ t('help_wallet.content.faq.subtitle') }}</p>
              </div>
            </div>

            <div class="space-y-4">
              <div v-for="(faq, index) in faqs" :key="index" 
                   class="border border-gray-200 rounded-xl overflow-hidden">
                <button 
                  @click="toggleFaq(index)"
                  class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition"
                >
                  <span class="font-semibold text-gray-900">{{ faq.question }}</span>
                  <svg :class="['w-5 h-5 text-gray-500 transition-transform', faq.open ? 'transform rotate-180' : '']" 
                       fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                <div v-if="faq.open" class="px-6 pb-6 pt-2 border-t border-gray-100">
                  <p class="text-gray-700">{{ faq.answer }}</p>
                  <div v-if="faq.tips" class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm font-medium text-blue-800">💡 {{ faq.tips }}</p>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="space-y-8">
          <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
            <h3 class="font-bold text-lg mb-4">{{ t('help_wallet.sidebar.stats_title') }}</h3>
            <div class="space-y-4">
              <div class="bg-white/20 rounded-lg p-4">
                <div class="text-sm opacity-90">{{ t('help_wallet.sidebar.stats_1') }}</div>
                <div class="text-2xl font-bold">99.8%</div>
              </div>
              <div class="bg-white/20 rounded-lg p-4">
                <div class="text-sm opacity-90">{{ t('help_wallet.sidebar.stats_2') }}</div>
                <div class="text-2xl font-bold">100%</div>
              </div>
              <div class="bg-white/20 rounded-lg p-4">
                <div class="text-sm opacity-90">{{ t('help_wallet.sidebar.stats_3') }}</div>
                <div class="text-2xl font-bold">&lt; 2 h</div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
            <h3 class="font-bold text-gray-900 mb-4">{{ t('help_wallet.sidebar.checklist_title') }}</h3>
            <div class="space-y-3">
              <div v-for="(item, index) in checklist" :key="index" class="flex items-center">
                <input type="checkbox" :id="'check-' + index" v-model="item.checked" 
                       class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                <label :for="'check-' + index" class="ml-3 text-sm text-gray-700 cursor-pointer select-none">{{ item.text }}</label>
              </div>
            </div>
            <div class="mt-6 pt-6 border-t border-gray-200">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-600">{{ t('help_wallet.sidebar.progress') }}</span>
                <span class="text-sm font-semibold">{{ completedChecks }}/{{ checklist.length }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                     :style="{ width: `${(completedChecks / checklist.length) * 100}%` }"></div>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-2xl p-6 border border-red-100">
            <h3 class="font-bold text-gray-900 mb-4">{{ t('help_wallet.sidebar.emergency_title') }}</h3>
            <div class="space-y-4">
              <div class="p-4 bg-white rounded-lg border border-red-200">
                <div class="flex items-center mb-2">
                  <span class="text-xl mr-3">📧</span>
                  <div>
                    <p class="font-semibold text-gray-900">{{ t('help_wallet.sidebar.email_support') }}</p>
                    <p class="text-sm text-gray-600 break-all">support@catur.cloud</p>
                  </div>
                </div>
              </div>
              <div class="p-4 bg-white rounded-lg border border-red-200">
                <div class="flex items-center mb-2">
                  <span class="text-xl mr-3">🆘</span>
                  <div>
                    <p class="font-semibold text-gray-900">{{ t('help_wallet.sidebar.security_report') }}</p>
                    <p class="text-sm text-gray-600 break-all">security@catur.cloud</p>
                  </div>
                </div>
              </div>
              <button @click="showEmergencyModal = true"
                      class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center">
                <span class="mr-2">🚨</span> {{ t('help_wallet.sidebar.btn_report') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer class="bg-gray-900 text-white mt-16">
      <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="mb-6 md:mb-0 text-center md:text-left">
            <h3 class="text-xl font-bold mb-2">{{ t('help_wallet.footer.title') }}</h3>
            <p class="text-gray-400 text-sm">{{ t('help_wallet.footer.subtitle') }}</p>
          </div>
          <div class="flex flex-wrap justify-center space-x-4 md:space-x-6 text-sm">
            <a href="#" class="text-gray-400 hover:text-white transition">{{ t('help_wallet.footer.privacy') }}</a>
            <a href="#" class="text-gray-400 hover:text-white transition">{{ t('help_wallet.footer.terms') }}</a>
            <a href="#" class="text-gray-400 hover:text-white transition">{{ t('help_wallet.footer.cookies') }}</a>
          </div>
        </div>
        <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-500 text-sm">
          <p>{{ t('help_wallet.footer.copyright') }}</p>
          <p class="mt-2">{{ t('help_wallet.footer.updated') }} {{ formattedDate }}</p>
        </div>
      </div>
    </footer>

    <div v-if="showEmergencyModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 animate-fade-in-up">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900">{{ t('help_wallet.modal.title') }}</h3>
          <button @click="showEmergencyModal = false" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="space-y-4">
          <div class="p-4 bg-red-50 rounded-lg border border-red-200">
            <p class="text-sm text-red-700 font-medium">
              {{ t('help_wallet.modal.warning') }}
            </p>
            <ul class="mt-2 text-sm text-red-600 list-disc pl-5 space-y-1">
              <li>{{ t('help_wallet.modal.w1') }}</li>
              <li>{{ t('help_wallet.modal.w2') }}</li>
              <li>{{ t('help_wallet.modal.w3') }}</li>
              <li>{{ t('help_wallet.modal.w4') }}</li>
            </ul>
          </div>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('help_wallet.modal.type_label') }}</label>
              <select v-model="emergencyType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="hack">{{ t('help_wallet.modal.type_hack') }}</option>
                <option value="transaction">{{ t('help_wallet.modal.type_trans') }}</option>
                <option value="phishing">{{ t('help_wallet.modal.type_phish') }}</option>
                <option value="other">{{ t('help_wallet.modal.type_other') }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('help_wallet.modal.desc_label') }}</label>
              <textarea v-model="emergencyDescription" 
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="t('help_wallet.modal.desc_ph')"></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('help_wallet.modal.email_label') }}</label>
              <input type="email" v-model="emergencyEmail"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="email@example.com">
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4">
            <button @click="showEmergencyModal = false"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
              {{ t('help_wallet.modal.btn_cancel') }}
            </button>
            <button @click="submitEmergencyReport"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold">
              {{ t('help_wallet.modal.btn_submit') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const darkMode = ref(false)
const showEmergencyModal = ref(false)
const emergencyType = ref('hack')
const emergencyDescription = ref('')
const emergencyEmail = ref('')
const activeSection = ref('what-is-wallet')

const formattedDate = computed(() => {
  return new Date().toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const sections = computed(() => [
  {
    id: 'what-is-wallet',
    title: t('help_wallet.nav.what_is'),
    description: t('help_wallet.nav.what_is_desc'),
    icon: '💰',
    color: 'bg-blue-100 text-blue-600'
  },
  {
    id: 'wallet-security',
    title: t('help_wallet.nav.security'),
    description: t('help_wallet.nav.security_desc'),
    icon: '🛡️',
    color: 'bg-red-100 text-red-600'
  },
  {
    id: 'practical-tips',
    title: t('help_wallet.nav.tips'),
    description: t('help_wallet.nav.tips_desc'),
    icon: '💡',
    color: 'bg-green-100 text-green-600'
  },
  {
    id: 'faq',
    title: t('help_wallet.nav.faq'),
    description: t('help_wallet.nav.faq_desc'),
    icon: '❓',
    color: 'bg-purple-100 text-purple-600'
  }
])

const securityTips = computed(() => [
  {
    icon: '🔑',
    title: t('help_wallet.content.security.tips.pass_title'),
    description: t('help_wallet.content.security.tips.pass_desc'),
    color: 'bg-blue-100 text-blue-600',
    points: [
      t('help_wallet.content.security.tips.pass_p1'),
      t('help_wallet.content.security.tips.pass_p2'),
      t('help_wallet.content.security.tips.pass_p3'),
      t('help_wallet.content.security.tips.pass_p4')
    ]
  },
  {
    icon: '📱',
    title: t('help_wallet.content.security.tips.2fa_title'),
    description: t('help_wallet.content.security.tips.2fa_desc'),
    color: 'bg-green-100 text-green-600',
    points: [
      t('help_wallet.content.security.tips.2fa_p1'),
      t('help_wallet.content.security.tips.2fa_p2'),
      t('help_wallet.content.security.tips.2fa_p3'),
      t('help_wallet.content.security.tips.2fa_p4')
    ]
  },
  {
    icon: '📧',
    title: t('help_wallet.content.security.tips.phish_title'),
    description: t('help_wallet.content.security.tips.phish_desc'),
    color: 'bg-red-100 text-red-600',
    points: [
      t('help_wallet.content.security.tips.phish_p1'),
      t('help_wallet.content.security.tips.phish_p2'),
      t('help_wallet.content.security.tips.phish_p3'),
      t('help_wallet.content.security.tips.phish_p4')
    ]
  },
  {
    icon: '💾',
    title: t('help_wallet.content.security.tips.backup_title'),
    description: t('help_wallet.content.security.tips.backup_desc'),
    color: 'bg-purple-100 text-purple-600',
    points: [
      t('help_wallet.content.security.tips.backup_p1'),
      t('help_wallet.content.security.tips.backup_p2'),
      t('help_wallet.content.security.tips.backup_p3'),
      t('help_wallet.content.security.tips.backup_p4')
    ]
  }
])

const dailyChecks = computed(() => [
  { text: t('help_wallet.content.practical.checks.1'), checked: false },
  { text: t('help_wallet.content.practical.checks.2'), checked: false },
  { text: t('help_wallet.content.practical.checks.3'), checked: false },
  { text: t('help_wallet.content.practical.checks.4'), checked: false }
])

// Use refs for state that changes based on user interaction (checkboxes)
// but initialize content from computed for translation. 
// A simpler way for checklists is to map translation keys to a reactive state.
const checklistState = ref([
  { id: 'check_1', checked: true },
  { id: 'check_2', checked: false },
  { id: 'check_3', checked: true },
  { id: 'check_4', checked: false },
  { id: 'check_5', checked: true },
  { id: 'check_6', checked: true }
])

const checklist = computed(() => {
  return checklistState.value.map(item => ({
    ...item,
    text: t(`help_wallet.sidebar.${item.id}`)
  }))
})

// Since dailyChecks also needs toggle state, we can use a similar approach
// or just make the whole array ref and watch locale changes (more complex).
// Here I'll use a hybrid approach: ref for state, computed for text.
const dailyChecksState = ref([false, false, false, false])
const dailyChecksComputed = computed(() => {
  return dailyChecksState.value.map((checked, index) => ({
    checked,
    text: t(`help_wallet.content.practical.checks.${index + 1}`)
  }))
})

const dos = computed(() => [
  t('help_wallet.content.practical.do_list.1'),
  t('help_wallet.content.practical.do_list.2'),
  t('help_wallet.content.practical.do_list.3'),
  t('help_wallet.content.practical.do_list.4'),
  t('help_wallet.content.practical.do_list.5')
])

const donts = computed(() => [
  t('help_wallet.content.practical.dont_list.1'),
  t('help_wallet.content.practical.dont_list.2'),
  t('help_wallet.content.practical.dont_list.3'),
  t('help_wallet.content.practical.dont_list.4'),
  t('help_wallet.content.practical.dont_list.5')
])

const faqsState = ref([false, false, false, false])
const faqs = computed(() => {
  return faqsState.value.map((isOpen, index) => ({
    open: isOpen,
    question: t(`help_wallet.content.faq.q${index + 1}`),
    answer: t(`help_wallet.content.faq.a${index + 1}`),
    tips: t(`help_wallet.content.faq.t${index + 1}`)
  }))
})

const completedChecks = computed(() => {
  return checklist.value.filter(item => item.checked).length
})

function toggleDarkMode() {
  darkMode.value = !darkMode.value
  if (darkMode.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

function toggleCheck(index) {
  // Directly mutating the state ref
  dailyChecksState.value[index] = !dailyChecksState.value[index]
}

function toggleFaq(index) {
  faqsState.value[index] = !faqsState.value[index]
}

function scrollToSection(sectionId) {
  activeSection.value = sectionId
  const element = document.getElementById(sectionId)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function submitEmergencyReport() {
  alert(`${t('help_wallet.modal.alert_sent')}\n\nType: ${emergencyType.value}\nEmail: ${emergencyEmail.value}`)
  showEmergencyModal.value = false
  emergencyDescription.value = ''
  emergencyEmail.value = ''
}

onMounted(() => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        activeSection.value = entry.target.id
      }
    })
  }, { threshold: 0.5 })

  // Delay observation slightly to ensure DOM is ready
  setTimeout(() => {
    sections.value.forEach(section => {
      const element = document.getElementById(section.id)
      if (element) observer.observe(element)
    })
  }, 100)
})
</script>

<style scoped>
.help-wallet-page {
  scroll-behavior: smooth;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Dark mode support */
.dark .help-wallet-page {
  @apply bg-gray-900 text-gray-100;
}

.dark header {
  @apply bg-gray-800/90 border-gray-700;
}

.dark .bg-white {
  @apply bg-gray-800 border-gray-700;
}

.dark .text-gray-900 {
  @apply text-gray-100;
}

.dark .text-gray-700 {
  @apply text-gray-300;
}

.dark .text-gray-600 {
  @apply text-gray-400;
}

.dark .border-gray-200 {
  @apply border-gray-700;
}

.dark .bg-gray-50 {
  @apply bg-gray-800;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .container {
    @apply px-4;
  }
  
  section {
    @apply p-6;
  }
  
  .grid-cols-4 {
    @apply grid-cols-2;
  }
}

@media (max-width: 480px) {
  .grid-cols-2 {
    @apply grid-cols-1;
  }
  
  .grid-cols-4 {
    @apply grid-cols-1;
  }
}
</style>