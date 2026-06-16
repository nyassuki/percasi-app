<script setup>
import { ref, reactive, inject } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import api from '../services/api'; 
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const router = useRouter();
const swal = inject('swal');
const toast = inject('toast');

const loading = ref(false);
const showPassword = ref(false);

const form = reactive({
  username: '',
  fullName: '',
  email: '',
  phone_number: '',
  password: ''
});

const handleRegister = async () => {
  if (!form.fullName || !form.email || !form.password || !form.username) {
      if(toast) toast.fire({ icon: 'warning', title: t('auth_register.toast.warning_incomplete') }); // [UPDATED]
      return;
  }

  loading.value = true;

  try {
    const response = await api.post('/auth/register', form);

    if (response.data.status === true) {
      if(swal) {
          await swal.fire({
            icon: 'success',
            title: t('auth_register.toast.success_title'), // [UPDATED]
            text: t('auth_register.toast.success_desc'), // [UPDATED]
            confirmButtonColor: '#0d9488',
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
          });
      }
      router.push('/login');
    }

  } catch (error) {
    console.error(error);
    const msg = error.response?.data?.message || t('auth_register.toast.error_desc'); // [UPDATED]
    
    if(swal) {
        swal.fire({ 
            icon: 'error', 
            title: t('auth_register.toast.error_title'), // [UPDATED]
            text: msg,
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
        });
    } else if (toast) {
        toast.fire({ icon: 'error', title: msg });
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-teal-700 dark:bg-slate-900 flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-300">
    
    <div class="absolute top-0 left-0 w-64 h-64 bg-teal-500 dark:bg-teal-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 dark:opacity-10 -translate-x-1/2 -translate-y-1/2 animate-blob"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-purple-500 dark:bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 dark:opacity-10 translate-x-1/2 translate-y-1/2 animate-blob animation-delay-2000"></div>

    <div class="bg-white dark:bg-slate-800/90 backdrop-blur-xl border border-transparent dark:border-slate-700 w-full max-w-md rounded-3xl shadow-2xl p-8 relative z-10 transition-colors duration-300">
      
      <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-gray-800 dark:text-white mb-2 tracking-tight">{{ t('auth_register.title') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">{{ t('auth_register.subtitle') }}</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-5">
        
        <div class="space-y-1">
            <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider ml-1">{{ t('auth_register.form.username_label') }}</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input 
                    v-model="form.username" 
                    type="text" 
                    :placeholder="t('auth_register.form.username_placeholder')"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    required
                />
            </div>
        </div>

        <div class="space-y-1">
            <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider ml-1">{{ t('auth_register.form.fullname_label') }}</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input 
                    v-model="form.fullName" 
                    type="text" 
                    :placeholder="t('auth_register.form.fullname_placeholder')"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    required
                />
            </div>
        </div>

        <div class="space-y-1">
            <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider ml-1">{{ t('auth_register.form.email_label') }}</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input 
                    v-model="form.email" 
                    type="email" 
                    :placeholder="t('auth_register.form.email_placeholder')"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    required
                />
            </div>
        </div>

        <div class="space-y-1">
            <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider ml-1">{{ t('auth_register.form.password_label') }}</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input 
                    v-model="form.password" 
                    :type="showPassword ? 'text' : 'password'" 
                    :placeholder="t('auth_register.form.password_placeholder')"
                    class="w-full pl-10 pr-12 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    required
                />
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition">
                      <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                      </svg>
                </button>
            </div>
        </div>

        <button 
          type="submit" 
          :disabled="loading"
          class="w-full bg-gradient-to-r from-teal-500 to-teal-700 hover:from-teal-400 hover:to-teal-600 text-white font-bold py-3 rounded-xl shadow-lg transform transition hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-4"
        >
           <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
           <span v-else>{{ t('auth_register.form.btn_register') }}</span>
        </button>

      </form>

      <div class="mt-8 text-center">
        <p class="text-gray-600 dark:text-gray-400 text-sm">
          {{ t('auth_register.login_link.text') }} 
          <RouterLink to="/login" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 font-bold hover:underline transition">
            {{ t('auth_register.login_link.link') }}
          </RouterLink>
        </p>
      </div>

    </div>
  </div>
</template>

<style scoped>
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
</style>