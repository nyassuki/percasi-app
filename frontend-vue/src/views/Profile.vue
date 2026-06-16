<script setup>
import { ref, onMounted, reactive, watch, inject } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const swal = inject('swal');
const toast = inject('toast');

const auth = useAuthStore();
const router = useRouter();
const loading = ref(false);
const isInitializing = ref(true); 
const activeTab = ref('general'); 

// --- STATE PASSWORD VISIBILITY ---
const showCurrentPass = ref(false);
const showNewPass = ref(false);
const showConfirmPass = ref(false);
const iSpinset=1;
// --- STATE FORM ---
const form = reactive({
  full_name: '',
  phone_number: '',
  address_line: '',
  postal_code: '',
  country_id: null,
  province_id: null,
  regency_id: null,
  district_id: null,
  subdistrict_id: null,
  avatar: null,
  is_wallet_pinset:'',
  // Financial
  bank_code: null,            
  account_number: '',        
  account_holder_name: '', 
  wallet_pin: '', 

  // Security (Password Update)
  current_password: '',
  new_password: '',
  confirm_password: ''
});

// --- STATE DATA MASTER ---
const master = reactive({
  countries: [],
  provinces: [],
  regencies: [],
  districts: [],
  subdistricts: [],
  banks: [] 
});

const avatarPreview = ref(null);

// --- 1. LOAD DATA MASTER ---
const fetchCountries = async () => {
  try {
    const res = await api.get('/master/countries');
    master.countries = res.data.data;
  } catch (e) { console.error("Err Countries", e); }
};

const fetchBanks = async () => {
  try {
    const res = await api.get('/master/banks'); 
    master.banks = res.data.data;
  } catch (e) {
    master.banks = [
        { code: 'BCA', name: 'BCA' }, { code: 'BRI', name: 'BRI' }, 
        { code: 'MANDIRI', name: 'Mandiri' }, { code: 'BNI', name: 'BNI' },
        { code: 'JAGO', name: 'Bank Jago' }, { code: 'SEABANK', name: 'Seabank' }
    ];
  }
};

// --- 2. LOAD USER BANK ACCOUNT ---
const fetchUserBankAccount = async () => {
  try {
    const res = await api.get('/finance/bank-accounts');
    const data = res.data.data;
    
    let account = null;
    if (Array.isArray(data)) {
        if (data.length > 0) account = data[0];
    } else {
        account = data;
    }

    if (account) {
        form.bank_code = account.bank_code;
        form.account_number = account.account_number;
        form.account_holder_name = account.account_holder_name || form.full_name;
    } else {
        form.account_holder_name = form.full_name;
    }
  } catch (e) {
    form.account_holder_name = form.full_name;
  }
};

// --- LOGIKA VERIFIKASI ---

const handleEmailVerification = async () => {
  const status = auth.user?.is_email_verified;
  if (status === 'NO') {
    try {
      await api.post('/verification/request/email', {email:auth.user?.email});
      toast.fire({ icon: 'info', title: t('profile.toast.email_sent') }); // [UPDATED]
    } catch (e) { console.error("Email verification request failed", e); }
  }
};

const requestPhoneVerification = async () => {
  loading.value = true;
  try {
    await api.post('/verification/request/phone', { phone: form.phone_number });
    
    // Munculkan Popup OTP [UPDATED]
    const { value: otp } = await swal.fire({
      title: t('profile.modal.phone_title'),
      text: t('profile.modal.phone_desc'),
      input: 'text',
      inputAttributes: { maxlength: 6, autofocus: 'true', inputmode: 'numeric' },
      showCancelButton: true,
      confirmButtonText: t('profile.modal.btn_verify'),
      confirmButtonColor: '#0d9488',
      cancelButtonText: t('profile.modal.btn_cancel'),
      preConfirm: (value) => {
        if (!value || value.length !== 6) {
          swal.showValidationMessage(t('profile.modal.validation'));
        }
        return value;
      }
    });

    if (otp) {
      const res = await api.post('/verification/match/phone', { otp_code: otp,phone: form.phone_number });
      if (res.data.success || res.status === 200) {
        toast.fire({ icon: 'success', title: t('profile.toast.phone_verified') }); // [UPDATED]
        await auth.fetchProfile(); // Refresh status
      }
    }
  } catch (err) {
    const serverMsg = err.response?.data?.message;
    const displayMsg = serverMsg ? (t(serverMsg) ? t(serverMsg) : serverMsg) : t('profile.toast.error');
    swal.fire(t('wallet.toast.error_title'), displayMsg, 'error');
  } finally {
    loading.value = false;
  }
};

// --- REGION FETCHERS ---
const fetchProvinces = async (countryId) => {
  if (!countryId) { master.provinces = []; return; }
  const res = await api.get(`/master/provinces/${countryId}`);
  master.provinces = res.data.data;
};
const fetchRegencies = async (provinceId) => {
  if (!provinceId) { master.regencies = []; return; }
  const res = await api.get(`/master/regencies/${provinceId}`);
  master.regencies = res.data.data;
};
const fetchDistricts = async (regencyId) => {
  if (!regencyId) { master.districts = []; return; }
  const res = await api.get(`/master/districts/${regencyId}`);
  master.districts = res.data.data;
};
const fetchSubdistricts = async (districtId) => {
  if (!districtId) { master.subdistricts = []; return; }
  const res = await api.get(`/master/subdistricts/${districtId}`);
  master.subdistricts = res.data.data;
};

// --- WATCHERS ---
const resetBelow = (level) => {
    if (level === 'country') { form.province_id = null; master.provinces = []; }
    if (['province', 'country'].includes(level)) { form.regency_id = null; master.regencies = []; }
    if (['regency', 'province', 'country'].includes(level)) { form.district_id = null; master.districts = []; }
    if (['district', 'regency', 'province', 'country'].includes(level)) { form.subdistrict_id = null; master.subdistricts = []; }
}

watch(() => form.country_id, (newVal) => { if (isInitializing.value) return; resetBelow('country'); if (newVal) fetchProvinces(newVal); });
watch(() => form.province_id, (newVal) => { if (isInitializing.value) return; resetBelow('province'); if (newVal) fetchRegencies(newVal); });
watch(() => form.regency_id, (newVal) => { if (isInitializing.value) return; resetBelow('regency'); if (newVal) fetchDistricts(newVal); });
watch(() => form.district_id, (newVal) => { if (isInitializing.value) return; resetBelow('district'); if (newVal) fetchSubdistricts(newVal); });
watch(() => form.subdistrict_id, () => {
  const selected = master.subdistricts.find(s => s.id === form.subdistrict_id);
  if (selected && selected.postal_code) form.postal_code = selected.postal_code;
});

watch(() => form.full_name, (newVal) => {
  if (!isInitializing.value) {
     form.account_holder_name = newVal;
  }
});

// --- INITIAL LOAD ---
onMounted(async () => {
  loading.value = true;
  isInitializing.value = true;
  try {
    await Promise.all([auth.fetchProfile(), fetchCountries(), fetchBanks()]);
    
    const user = auth.user;
    //console.log(user);

    form.full_name = user.full_name || '';
    form.phone_number = user.phone_number || '';
    form.address_line = user.address_line || '';
    form.postal_code = user.postal_code || '';
    if (user.avatar_url) avatarPreview.value = `${user.avatar_url}`;

    form.country_id = user.country_id;
    if (user.country_id) { await fetchProvinces(user.country_id); form.province_id = user.province_id; }
    if (user.province_id) { await fetchRegencies(user.province_id); form.regency_id = user.regency_id; }
    if (user.regency_id) { await fetchDistricts(user.regency_id); form.district_id = user.district_id; }
    if (user.district_id) { await fetchSubdistricts(user.district_id); form.subdistrict_id = user.subdistrict_id; }

    await fetchUserBankAccount();
    //await handleEmailVerification();

  } catch (err) { 
    toast.fire({ icon: 'error', title: t('profile.toast.error') }); // [UPDATED]
  } 
  finally { loading.value = false; isInitializing.value = false; }
});

// --- SUBMIT (UPDATED) ---
const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) { toast.fire({ icon: 'error', title: t('profile.toast.file_size') }); return; } // [UPDATED]
    form.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const saveProfile = async () => {
  // VALIDASI MANUAL SEBELUM REQUEST [UPDATED]
  if (form.new_password || form.confirm_password) {
      if (!form.current_password) {
          toast.fire({ icon: 'warning', title: t('profile.toast.pass_required') });
          return;
      }
      if (form.new_password !== form.confirm_password) {
          toast.fire({ icon: 'error', title: t('profile.toast.pass_mismatch') });
          return;
      }
      if (form.new_password.length < 6) {
          toast.fire({ icon: 'error', title: t('profile.toast.pass_min') });
          return;
      }
  }

  loading.value = true;
  try {
    const promises = [];

    // 1. UPDATE PROFIL UMUM
    const profileFormData = new FormData();
    profileFormData.append('full_name', form.full_name);
    profileFormData.append('phone_number', form.phone_number);
    profileFormData.append('address_line', form.address_line);
    profileFormData.append('postal_code', form.postal_code);
    if (form.country_id) profileFormData.append('country_id', form.country_id);
    if (form.province_id) profileFormData.append('province_id', form.province_id);
    if (form.regency_id) profileFormData.append('regency_id', form.regency_id);
    if (form.district_id) profileFormData.append('district_id', form.district_id);
    if (form.subdistrict_id) profileFormData.append('subdistrict_id', form.subdistrict_id);
    if (form.avatar) profileFormData.append('avatar', form.avatar);
    
    promises.push(api.put('/users/profile', profileFormData, { headers: { 'Content-Type': 'multipart/form-data' } }));

    // 2. UPDATE BANK
    if (form.bank_code && form.account_number) {
        const bankData = {
            bank_code: form.bank_code,
            account_number: form.account_number,
            account_holder_name: form.account_holder_name || form.full_name
        };
        promises.push(api.post('/finance/bank-accounts', bankData));
    }

    // 3. UPDATE PIN WALLET [UPDATED]
    if (form.old_wallet_pin || form.new_wallet_pin_1 || form.new_wallet_pin_2) {
        if(auth.user?.is_wallet_pinset=="YES") {
            if (form.old_wallet_pin.length !== 6) throw new Error(t('profile.toast.pin_length'));
        }
        if (form.new_wallet_pin_1.length !== 6) throw new Error(t('profile.toast.pin_length'));
        if (form.new_wallet_pin_2.length !== 6) throw new Error(t('profile.toast.pin_length'));
        if (form.new_wallet_pin_1 !== form.new_wallet_pin_2) throw new Error(t('profile.toast.pin_mismatch'));

        promises.push(api.post('/wallet/pin', { pin: form.new_wallet_pin_1, old_pin:form.old_wallet_pin, ispinset:auth.user.is_wallet_pinset }));
    }

    // 4. UPDATE PASSWORD (NEW)
    if (form.new_password) {
        promises.push(api.post('/auth/change-password', {
            oldPassword: form.current_password,
            newPassword: form.new_password
        }));
    }

    // --- EKSEKUSI SEMUA REQUEST ---
    const results = await Promise.all(promises);
    
    // Ambil hasil update profile (index 0)
    const profileRes = results[0]; 

    auth.user = profileRes.data.data;
    localStorage.setItem('user', JSON.stringify(auth.user));
    
    // Clear Sensitive Fields
    form.old_wallet_pin = '';
    form.new_wallet_pin_1 = '';
    form.new_wallet_pin_2 = '';

    form.current_password = '';
    form.new_password = '';
    form.confirm_password = '';
    
    toast.fire({ icon: 'success', title: t('profile.toast.success') }); // [UPDATED]
  } catch (err) { 
      console.error(err);
      let msg = err.message;
      if(err.response?.data?.message) msg = err.response.data.message;
      toast.fire({ icon: 'error', title: msg || t('profile.toast.error') }); // [UPDATED]
  } 
  finally { loading.value = false; }
};
</script>

<template>

  <div class="min-h-screen bg-gradient-to-b from-teal-700 to-teal-600 dark:from-slate-900 dark:to-slate-800 flex flex-col transition-colors duration-300">
    
    <header class="sticky top-0 z-50 bg-gradient-to-r from-teal-700 to-teal-600 dark:from-slate-900 dark:to-slate-800 shadow-xl border-b border-teal-600/30 dark:border-slate-700">
      <div class="px-4 sm:px-6 lg:px-8 pt-7 pb-4">
        <div class="flex items-center gap-3 text-white mb-2 max-w-7xl mx-auto w-full">
          <button @click="router.back()" 
                  class="bg-white/15 hover:bg-white/25 p-2.5 rounded-xl transition-all duration-200 backdrop-blur-sm active:scale-95 shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <div class="flex-1">
            <h1 class="text-xl font-bold tracking-tight">{{ t('profile.header.title') }}</h1>
            <p class="text-teal-100/90 text-sm mt-0.5">{{ t('profile.header.subtitle') }}</p>
          </div>
        </div>
      </div>
    </header>

    <main class="flex-1 bg-gradient-to-b from-white to-gray-50 dark:from-slate-900 dark:to-slate-950 rounded-t-[40px] pt-8 px-4 sm:px-6 lg:px-8 pb-28 shadow-2xl min-h-[80vh] relative z-0 -mt-6 transition-colors duration-300 overflow-y-auto">
      <div class="max-w-7xl mx-auto w-full">
        
        <div class="flex flex-col items-center pb-6 mb-6 border-b border-gray-100 dark:border-slate-700/50">
          <div class="relative group">
            <div class="w-28 h-28 rounded-full border-4 border-white dark:border-slate-800 shadow-2xl overflow-hidden bg-gradient-to-br from-teal-100 to-teal-200 dark:from-slate-700 dark:to-slate-800">
              <img 
                :src="avatarPreview || `https://ui-avatars.com/api/?name=${auth.user?.username}&background=random&color=fff&size=256`" 
                class="w-full h-full object-cover"
              />
            </div>
            <label v-if="activeTab === 'general'" 
                   class="absolute bottom-1 right-1 bg-gradient-to-r from-teal-500 to-emerald-500 text-white p-2.5 rounded-full cursor-pointer shadow-xl hover:shadow-2xl transition-all hover:scale-110 border-3 border-white dark:border-slate-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              </svg>
              <input type="file" class="hidden" @change="onFileChange" accept="image/*" />
            </label>
          </div>
          <p class="mt-4 text-xl font-bold text-gray-800 dark:text-white">{{ auth.user?.username }}</p>
          <div class="flex items-center gap-1.5 mt-1">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ auth.user?.email }}</span>
            
            <div v-if="auth.user?.is_email_verified === 'VERIFIED'" class="flex items-center gap-1 bg-green-100 dark:bg-green-900/30 px-2 py-0.5 rounded-full">
              <svg class="h-3 w-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-[10px] font-bold text-green-600 uppercase">{{ t('profile.verified') }}</span>
            </div>
            
            <button v-else @click="handleEmailVerification" class="text-[10px] font-bold text-amber-600 underline hover:text-amber-700">
              {{ t('profile.verify_now') }}
            </button>
          </div>
          <div class="mt-3 flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
            <span class="text-xs text-teal-600 dark:text-teal-400 font-medium">{{ t('profile.account_active') }}</span>
          </div>
        </div>

        <div class="mb-8">
          <div class="flex bg-gray-100 dark:bg-slate-800/50 rounded-2xl overflow-hidden p-1 border border-gray-200 dark:border-slate-700">
            <button 
              type="button"
              @click="activeTab = 'general'"
              :class="activeTab === 'general' 
                ? 'bg-gradient-to-r from-teal-500 to-emerald-500 text-white shadow-lg' 
                : 'text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-slate-700'"
              class="flex-1 py-3 rounded-xl font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              {{ t('profile.tabs.general') }}
            </button>
            <button 
              type="button"
              @click="activeTab = 'financial'"
              :class="activeTab === 'financial' 
                ? 'bg-gradient-to-r from-teal-500 to-emerald-500 text-white shadow-lg' 
                : 'text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-slate-700'"
              class="flex-1 py-3 rounded-xl font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {{ t('profile.tabs.financial') }}
            </button>
            <button 
              type="button"
              @click="activeTab = 'security'"
              :class="activeTab === 'security' 
                ? 'bg-gradient-to-r from-teal-500 to-emerald-500 text-white shadow-lg' 
                : 'text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-slate-700'"
              class="flex-1 py-3 rounded-xl font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              {{ t('profile.tabs.security') }}
            </button>
          </div>
        </div>

        <form @submit.prevent="saveProfile" class="w-full">
          
          <div v-show="activeTab === 'general'" class="space-y-6 animate-fade-in">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-700 p-5 lg:p-6">
              <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-teal-500 to-teal-600 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                {{ t('profile.general.title') }}
              </h3>
              
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                <div class="lg:col-span-1">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.general.full_name') }}</label>
                  <input v-model="form.full_name" type="text" 
                         class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all" 
                         :placeholder="t('profile.general.full_name_ph')" />
                </div>
                <div class="lg:col-span-1">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.general.phone') }}</label>
                  <div class="relative flex items-center">
                    <input 
                      v-model="form.phone_number" 
                      type="tel" 
                      :readonly="auth.user?.is_phone_verified === 'VERIFIED'"
                      class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all"
                      :class="auth.user?.is_phone_verified === 'VERIFIED' ? 'pr-24 border-green-200 bg-green-50/30' : 'pr-28'"
                      placeholder="0812..." 
                    />
                    
                    <div class="absolute right-2">
                      <div v-if="auth.user?.is_phone_verified === 'VERIFIED'" 
                           class="flex items-center gap-1 bg-green-500 text-white px-3 py-1.5 rounded-lg shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-[10px] font-black uppercase tracking-tighter">{{ t('profile.verified') }}</span>
                      </div>

                      <button v-else 
                              type="button" 
                              @click="requestPhoneVerification"
                              class="bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tight shadow-md active:scale-95 transition-all">
                        {{ t('profile.verify_now') }}
                      </button>
                    </div>
                  </div>
                  <p v-if="auth.user?.is_phone_verified === 'VERIFIED'" class="text-[10px] text-green-600 font-medium mt-1.5 ml-1 italic">
                    {{ t('profile.general.phone_verified_note') }}
                  </p>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-700 p-5 lg:p-6">
              <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-teal-500 to-teal-600 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                {{ t('profile.general.address') }}
              </h3>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-4 mb-4">
                <div class="lg:col-span-1">
                  <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">{{ t('profile.general.country') }}</label>
                  <select v-model="form.country_id" 
                          class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none transition-all">
                    <option :value="null" class="text-gray-400">{{ t('profile.general.select.country') }}</option>
                    <option v-for="c in master.countries" :key="c.id" :value="c.id">{{ c.countryName }}</option>
                  </select>
                </div>
                <div class="lg:col-span-1">
                  <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">{{ t('profile.general.province') }}</label>
                  <select v-model="form.province_id" :disabled="!form.country_id || master.provinces.length === 0" 
                          class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl bg-white dark:bg-slate-800 text-gray-900 dark:text-white disabled:bg-gray-100 dark:disabled:bg-slate-900 disabled:text-gray-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none transition-all">
                    <option :value="null" class="text-gray-400">{{ t('profile.general.select.province') }}</option>
                    <option v-for="p in master.provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                  </select>
                </div>
                <div class="lg:col-span-1">
                  <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">{{ t('profile.general.regency') }}</label>
                  <select v-model="form.regency_id" :disabled="!form.province_id || master.regencies.length === 0" 
                          class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl bg-white dark:bg-slate-800 text-gray-900 dark:text-white disabled:bg-gray-100 dark:disabled:bg-slate-900 disabled:text-gray-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none transition-all">
                    <option :value="null" class="text-gray-400">{{ t('profile.general.select.regency') }}</option>
                    <option v-for="r in master.regencies" :key="r.id" :value="r.id">{{ r.name }}</option>
                  </select>
                </div>
                <div class="lg:col-span-1">
                  <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">{{ t('profile.general.district') }}</label>
                  <select v-model="form.district_id" :disabled="!form.regency_id || master.districts.length === 0" 
                          class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl bg-white dark:bg-slate-800 text-gray-900 dark:text-white disabled:bg-gray-100 dark:disabled:bg-slate-900 disabled:text-gray-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none transition-all">
                    <option :value="null" class="text-gray-400">{{ t('profile.general.select.district') }}</option>
                    <option v-for="d in master.districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                  </select>
                </div>
                <div class="lg:col-span-1">
                  <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">{{ t('profile.general.subdistrict') }}</label>
                  <select v-model="form.subdistrict_id" :disabled="!form.district_id || master.subdistricts.length === 0" 
                          class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl bg-white dark:bg-slate-800 text-gray-900 dark:text-white disabled:bg-gray-100 dark:disabled:bg-slate-900 disabled:text-gray-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none transition-all">
                    <option :value="null" class="text-gray-400">{{ t('profile.general.select.subdistrict') }}</option>
                    <option v-for="s in master.subdistricts" :key="s.id" :value="s.id">{{ s.name }}</option>
                  </select>
                </div>
                <div class="lg:col-span-1">
                  <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">{{ t('profile.general.postal_code') }}</label>
                  <input v-model="form.postal_code" type="text" 
                         class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all" />
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.general.address') }}</label>
                <textarea v-model="form.address_line" 
                          class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all" 
                          rows="3" :placeholder="t('profile.general.address_ph')"></textarea>
              </div>
            </div>
          </div>

          <div v-show="activeTab === 'financial'" class="space-y-6 animate-fade-in">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-700 p-5 lg:p-6">
              <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-teal-500 to-teal-600 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                </div>
                {{ t('profile.financial.title') }}
              </h3>
              
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                <div class="lg:col-span-1">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.financial.bank_name') }}</label>
                  <select v-model="form.bank_code" 
                          class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none transition-all">
                    <option :value="null" class="text-gray-400">{{ t('profile.financial.select_bank') }}</option>
                    <option v-for="b in master.banks" :key="b.bank_code" :value="b.bank_code">{{ b.bank_name }}</option>
                  </select>
                </div>
                
                <div class="lg:col-span-1">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.financial.account_number') }}</label>
                  <input v-model="form.account_number" type="number" 
                         class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all" 
                         placeholder="Contoh: 1234567890" />
                </div>
                
                <div class="lg:col-span-2">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.financial.account_holder') }}</label>
                  <input v-model="form.account_holder_name" type="text" 
                         class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all" 
                         :placeholder="t('profile.financial.account_holder_ph')" readonly />
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ t('profile.financial.account_holder_note') }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-700 p-5 lg:p-6">
              <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-teal-500 to-teal-600 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                {{ t('profile.financial.wallet_security') }}
              </h3>
              
              <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 rounded-xl p-4 mb-4">
                <div class="flex items-start gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="text-sm text-amber-700 dark:text-amber-400">
                    {{ t('profile.financial.wallet_note') }}
                  </p>
                </div>
              </div>

              <div>
              <div v-if="auth.user?.is_wallet_pinset == 'YES'">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.financial.old_pin') }}<br> {{ t('profile.financial.old_pin_note') }}</label>
                <input 
                  v-model="form.old_wallet_pin" 
                  type="password" 
                  maxlength="6" 
                  inputmode="numeric"
                  @input="form.old_wallet_pin = form.old_wallet_pin.replace(/\D/g, '')"
                  class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white tracking-widest text-center text-lg transition-all" 
                  placeholder="••••••" 
                />
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2"></p>
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.financial.new_pin') }}</label>
              <input 
                v-model="form.new_wallet_pin_1" 
                type="password" 
                maxlength="6" 
                inputmode="numeric"
                @input="form.new_wallet_pin_1 = form.new_wallet_pin_1.replace(/\D/g, '')"
                class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white tracking-widest text-center text-lg transition-all" 
                placeholder="••••••" 
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2"></p>
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.financial.confirm_pin') }}</label>
              <input 
                v-model="form.new_wallet_pin_2" 
                type="password" 
                maxlength="6" 
                inputmode="numeric"
                @input="form.new_wallet_pin_2 = form.new_wallet_pin_2.replace(/\D/g, '')"
                class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white tracking-widest text-center text-lg transition-all" 
                placeholder="••••••" 
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ t('profile.financial.pin_rule') }}</p>
            </div>
            </div>
          </div>

          <div v-show="activeTab === 'security'" class="space-y-6 animate-fade-in">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-700 p-5 lg:p-6">
              <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-teal-500 to-teal-600 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                {{ t('profile.security.title') }}
              </h3>
              
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/30 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="text-sm text-blue-700 dark:text-blue-400">
                    {{ t('profile.security.note') }}
                  </p>
                </div>
              </div>
              
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                <div class="lg:col-span-1">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.security.current_pass') }}</label>
                  <div class="relative">
                    <input 
                      v-model="form.current_password" 
                      :type="showCurrentPass ? 'text' : 'password'" 
                      class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 pr-12 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all" 
                      :placeholder="t('profile.security.current_pass_ph')" 
                    />
                    <button 
                      type="button" 
                      @click="showCurrentPass = !showCurrentPass" 
                      class="absolute right-3 top-3.5 text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                    >
                      <svg v-if="!showCurrentPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <div class="lg:col-span-1">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.security.new_pass') }}</label>
                  <div class="relative">
                    <input 
                      v-model="form.new_password" 
                      :type="showNewPass ? 'text' : 'password'" 
                      class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 pr-12 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all" 
                      :placeholder="t('profile.security.new_pass_ph')" 
                    />
                    <button 
                      type="button" 
                      @click="showNewPass = !showNewPass" 
                      class="absolute right-3 top-3.5 text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                    >
                      <svg v-if="!showNewPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <div class="lg:col-span-1">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.security.confirm_pass') }}</label>
                  <div class="relative">
                    <input 
                      v-model="form.confirm_password" 
                      :type="showConfirmPass ? 'text' : 'password'" 
                      class="w-full border-2 border-gray-200 dark:border-slate-700 p-3.5 pr-12 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-900 outline-none bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-all" 
                      :placeholder="t('profile.security.confirm_pass_ph')" 
                    />
                    <button 
                      type="button" 
                      @click="showConfirmPass = !showConfirmPass" 
                      class="absolute right-3 top-3.5 text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                    >
                      <svg v-if="!showConfirmPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-8 pb-10">
            <button 
              type="submit" 
              :disabled="loading" 
              class="w-full max-w-2xl mx-auto block bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed active:scale-[0.98] transform relative overflow-hidden group"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-teal-500 to-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              
              <span class="relative flex items-center justify-center gap-3 text-lg">
                <template v-if="loading">
                  <span class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                  {{ t('profile.button.saving') }}
                </template>
                <template v-else>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                  </svg>
                  {{ t('profile.button.save') }}
                </template>
              </span>
            </button>
          </div>

        </form>
      </div>
    </main>

  </div>
</template>

<style scoped>
/* Animations */
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

.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}

.dark ::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

.dark ::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Smooth transitions */
* {
  transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
}

/* Custom number input */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}

/* Custom select styling */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 1.25em 1.25em;
  padding-right: 2.5rem;
}

.dark select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
}

/* Prevent iOS zoom */
@media (max-width: 768px) {
  input[type="number"],
  input[type="tel"],
  input[type="password"],
  select {
    font-size: 16px !important;
  }
}
</style>