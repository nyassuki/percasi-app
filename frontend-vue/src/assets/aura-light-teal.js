import { createApp } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';

const MyTealPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '#f0fdfa',
            100: '#ccfbf1',
            200: '#99f6e4',
            300: '#5eead4',
            400: '#2dd4bf',
            500: '#14b8a6',
            600: '#0d9488',
            700: '#0f766e',
            800: '#115e59',
            900: '#134e4a',
            950: '#042f2e'
        }
    }
});

const app = createApp(App);
app.use(PrimeVue, {
    theme: {
        preset: MyTealPreset,
        options: {
            darkModeSelector: 'system',
        }
    }
});