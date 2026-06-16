import { createApp } from 'vue'
import { createPinia } from 'pinia'
import VueSweetAlert2 from './plugins/sweetalert';
import PrimeVue from 'primevue/config';
// 1. IMPORT LIBRARY GOOGLE (PENTING: Jangan sampai hilang)
import vue3GoogleLogin from 'vue3-google-login'

import App from './App.vue'
import router from './router'

// 2. Import CSS Utama (Tailwind)
import './assets/main.css'

// 3. Import CSS Chessground (Disini agar tidak konflik dengan Tailwind)
import 'chessground/assets/chessground.base.css'
import 'chessground/assets/chessground.brown.css'
import 'chessground/assets/chessground.cburnett.css'

import './assets/main.css'


import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';
import i18n from './plugins/i18n' // Import konfigurasi tadi

const app = createApp(App)
app.use(i18n)
app.use(createPinia())
app.use(router)
app.use(VueSweetAlert2);
// 4. Inisialisasi Google Login
// Ganti CLIENT_ID dengan ID asli Anda dari Google Console
app.use(vue3GoogleLogin, {
  clientId: '742613131488-t8b3f5o9vfupbn817ppoapba1ja3pvhd.apps.googleusercontent.com' 
})


const MyTealPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{teal.50}',
            100: '{teal.100}',
            200: '{teal.200}',
            300: '{teal.300}',
            400: '{teal.400}',
            500: '{teal.500}',
            600: '{teal.600}',
            700: '{teal.700}',
            800: '{teal.800}',
            900: '{teal.900}',
            950: '{teal.950}'
        }
    }
});

app.use(PrimeVue, {
    theme: {
        preset: MyTealPreset,
        options: {
            darkModeSelector: false, // atau 'system' jika ingin dark mode
        }
    }
});
app.mount('#app')