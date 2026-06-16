<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';
import { useI18n } from 'vue-i18n'; // [BARU] Import i18n

const { t } = useI18n(); // [BARU] Init
const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const toast = inject('toast');
const swal = inject('swal');

// --- STATE ---
const recipient = ref(null); 
const isLoadingRecipient = ref(true);
const isSubmitting = ref(false);

const amount = ref('');
const note = ref('');
const pin = ref('');
const showPinModal = ref(false);
const toUsername = ref('');
const toAvatar_url = ref('');

// --- 1. FETCH DATA PENERIMA ---
const fetchRecipientData = async () => {
    const targetId = route.query.toId || route.query.to;
    toUsername.value = route.query.toUsername;
    toAvatar_url.value = route.query.toAvatar_url;

    if (!targetId) {
        toast.fire({ icon: 'error', title: t('transfer_amount.error.invalid_target') }); // [UPDATED]
        router.replace('/wallet');
        return;
    }

    if (parseInt(targetId) === auth.user?.id) {
        await swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: t('transfer_amount.error.self_transfer'), // [UPDATED]
            confirmButtonColor: '#0d9488'
        });
        router.replace('/wallet');
        return;
    }

    try {
        isLoadingRecipient.value = true;
        const res = await api.get(`/users/public/${targetId}`);
        
        if (res.data.status === 'success') {
            recipient.value = res.data.data;
        }
    } catch (err) {
        console.error(err);
        await swal.fire({
            icon: 'error',
            title: t('transfer_amount.error.user_not_found'), // [UPDATED]
            text: t('transfer_amount.error.user_not_found_desc'), // [UPDATED]
            confirmButtonColor: '#d33'
        });
        router.replace('/wallet');
    } finally {
        isLoadingRecipient.value = false;
    }
};

// --- HELPER FORMAT RUPIAH ---
const formatRupiah = (value) => {
    if (!value) return '';
    return new Intl.NumberFormat('id-ID').format(value);
};

const handleInputAmount = (e) => {
    const rawValue = e.target.value.replace(/\D/g, '');
    if (rawValue.length > 9) return; 
    amount.value = rawValue;
};

// --- VALIDASI ---
const minAmount = 10000;

const isValid = computed(() => {
    if (!recipient.value) return false;
    const val = parseInt(amount.value || 0);
    const currentBalance = parseFloat(auth.user?.balance || 0);
    return val >= minAmount && val <= currentBalance;
});

const errorMessage = computed(() => {
    const val = parseInt(amount.value || 0);
    const currentBalance = parseFloat(auth.user?.balance || 0);
    
    if (val > currentBalance) return t('transfer_amount.error.insufficient_balance'); // [UPDATED]
    if (val > 0 && val < minAmount) return t('transfer_amount.error.min_amount', { amount: formatRupiah(minAmount) }); // [UPDATED]
    return '';
});

// --- ACTIONS ---
const confirmTransfer = () => {
    if (!isValid.value) return;
    showPinModal.value = true; 
};

const submitTransfer = async () => {
    if (pin.value.length < 6) {
        toast.fire({ icon: 'warning', title: t('transfer_amount.error.pin_length') }); // [UPDATED]
        return;
    }

    isSubmitting.value = true;
    showPinModal.value = false; 

    try {
        const payload = {
            recipient_id: recipient.value.id,
            amount: parseInt(amount.value),
            note: note.value,
            pin: pin.value
        };
          
        const res = await api.post('/wallet/transfer', payload);

        if (res.data.status === 'success') {
            await auth.fetchProfile(); 
            
            await swal.fire({
                icon: 'success',
                title: t('transfer_amount.success.title'), // [UPDATED]
                text: t('transfer_amount.success.msg', { amount: formatRupiah(amount.value), name: recipient.value.username }), // [UPDATED]
                confirmButtonText: t('transfer_amount.success.btn_done'), // [UPDATED]
                confirmButtonColor: '#0d9488'
            });
            
            router.replace('/wallet');
        }
    } catch (err) {
        console.error(err);
        await swal.fire({
            icon: 'error',
            title: t('transfer_amount.error.title_failed'), // [UPDATED]
            text: err.response?.data?.message || t('transfer_amount.error.system_error'), // [UPDATED]
            confirmButtonColor: '#d33'
        });
        
        if (err.response?.data?.message?.toLowerCase().includes('pin')) {
             pin.value = '';
             showPinModal.value = true; 
        }
    } finally {
        isSubmitting.value = false;
        if (!showPinModal.value) pin.value = '';
    }
};

onMounted(() => {
    fetchRecipientData();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950 flex flex-col transition-colors duration-300 font-sans">
    
    <div class="fixed top-0 left-0 right-0 z-40 bg-teal-700 dark:bg-slate-900 shadow-md">
        <div class="px-6 py-4 flex items-center gap-4 text-white">
            <button @click="router.back()" class="bg-white/20 p-2 rounded-full hover:bg-white/30 transition backdrop-blur-md active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <h1 class="text-xl font-bold tracking-wide">{{ t('transfer_amount.header.title') }}</h1>
        </div>
    </div>

    <div class="flex-1 pt-[72px]">
        
        <div class="bg-teal-700 dark:bg-slate-900 px-6 pb-14 pt-4 rounded-b-[40px] shadow-lg relative z-10 transition-colors duration-300">
            
            <div v-if="isLoadingRecipient" class="flex flex-col items-center animate-pulse">
                <div class="w-20 h-20 bg-white/20 rounded-full mb-3"></div>
                <div class="h-6 w-32 bg-white/20 rounded mb-2"></div>
                <div class="h-4 w-20 bg-white/10 rounded"></div>
            </div>

            <div v-else-if="recipient" class="flex flex-col items-center text-white animate-fade-in">
                <div class="relative w-20 h-20 mb-3">
                    <img 
                        :src="recipient.avatar_url ? `${auth.imageBaseUrl || ''}/${toAvatar_url}` : `https://ui-avatars.com/api/?name=${recipient.username}&background=random&color=fff`" 
                        class="w-full h-full rounded-full object-cover border-4 border-white/20 shadow-inner bg-gray-200"
                    />
                    <div v-if="recipient.kyc_status === 'verified'" class="absolute bottom-0 right-0 bg-blue-500 text-white p-1 rounded-full border-2 border-teal-700" :title="t('transfer_amount.recipient.verified')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </div>
                </div>
                <p class="text-teal-200 text-xs font-bold uppercase tracking-widest mb-1">{{ t('transfer_amount.recipient.to') }}</p>
                <h2 class="text-2xl font-bold tracking-tight">{{ toUsername }}</h2>
                <p class="text-xs opacity-70 font-mono mt-1 bg-black/20 px-2 py-0.5 rounded text-teal-100">{{ t('transfer_amount.recipient.id') }}: {{ recipient.id }}</p>
            </div>
        </div>

        <div class="px-6 -mt-10 relative z-20 pb-10">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl p-6 border border-gray-100 dark:border-slate-700 transition-colors duration-300">
                
                <div class="mb-6">
                    <label class="block text-gray-500 dark:text-gray-400 text-xs font-bold uppercase mb-2 ml-1">{{ t('transfer_amount.form.amount_label') }}</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-lg group-focus-within:text-teal-500 transition-colors">Rp</span>
                        <input 
                            type="text" 
                            inputmode="numeric"
                            :value="formatRupiah(amount)"
                            @input="handleInputAmount"
                            :disabled="isLoadingRecipient"
                            class="w-full bg-gray-50 dark:bg-slate-900 border-2 border-transparent focus:border-teal-500 rounded-2xl py-4 pl-12 pr-4 text-2xl font-bold text-gray-800 dark:text-white outline-none transition placeholder-gray-300 dark:placeholder-gray-700 disabled:opacity-50"
                            placeholder="0"
                            autofocus
                        />
                    </div>
                    <div class="flex justify-between mt-2 px-1 h-5 items-center">
                        <span class="text-xs text-red-500 font-bold animate-pulse">{{ errorMessage }}</span>
                        <span class="text-xs text-gray-400 font-medium">{{ t('transfer_amount.form.balance') }}: Rp {{ formatRupiah(auth.user?.balance) }}</span>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-gray-500 dark:text-gray-400 text-xs font-bold uppercase mb-2 ml-1">{{ t('transfer_amount.form.note_label') }}</label>
                    <div class="relative">
                        <textarea 
                            v-model="note"
                            rows="2"
                            :disabled="isLoadingRecipient"
                            class="w-full bg-gray-50 dark:bg-slate-900 rounded-xl p-4 text-sm text-gray-700 dark:text-white outline-none focus:ring-2 ring-teal-500/50 transition resize-none disabled:opacity-50 border border-transparent focus:border-teal-500 placeholder-gray-400"
                            :placeholder="t('transfer_amount.form.note_placeholder')"
                        ></textarea>
                    </div>
                </div>

                <button 
                    @click="confirmTransfer"
                    :disabled="!isValid || isSubmitting || isLoadingRecipient"
                    class="w-full bg-gradient-to-r from-teal-500 to-teal-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-teal-500/30 transition transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex justify-center items-center gap-2"
                >
                    <span v-if="isSubmitting" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span>{{ isSubmitting ? t('transfer_amount.form.btn_processing') : t('transfer_amount.form.btn_continue') }}</span>
                </button>

            </div>
        </div>
    </div>

    <Transition name="modal">
        <div v-if="showPinModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showPinModal = false"></div>
            
            <div class="bg-white dark:bg-slate-800 w-full max-w-xs rounded-3xl p-8 text-center shadow-2xl relative z-10 transform transition-all border border-white/10">
                <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 text-teal-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    🔒
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">{{ t('transfer_amount.pin_modal.title') }}</h3>
                <p class="text-gray-500 dark:text-gray-400 text-xs mb-6">{{ t('transfer_amount.pin_modal.desc') }}</p>
                
                <div class="relative mb-8">
                    <input 
                        type="password" 
                        v-model="pin" 
                        maxlength="6"
                        inputmode="numeric"
                        class="w-full text-center text-3xl tracking-[0.5em] font-bold py-3 border-b-2 border-teal-500 bg-transparent text-gray-800 dark:text-white outline-none focus:border-teal-400 transition placeholder-gray-300 dark:placeholder-gray-700"
                        :placeholder="t('transfer_amount.pin_modal.placeholder')"
                        autofocus
                    />
                </div>

                <div class="flex gap-3">
                    <button @click="showPinModal = false" class="flex-1 py-3 text-gray-500 dark:text-gray-300 font-bold hover:bg-gray-100 dark:hover:bg-slate-700 rounded-xl transition">{{ t('transfer_amount.pin_modal.btn_cancel') }}</button>
                    <button 
                        @click="submitTransfer" 
                        :disabled="pin.length < 6"
                        class="flex-1 py-3 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-500 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ t('transfer_amount.pin_modal.btn_send') }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>

  </div>
</template>

<style scoped>
/* Animations */
.animate-fade-in { animation: fadeIn 0.6s ease-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Modal Transition Vue */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .bg-white,
.modal-leave-active .bg-white {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-enter-from .bg-white,
.modal-leave-to .bg-white {
    transform: scale(0.9) translateY(20px);
}
</style>