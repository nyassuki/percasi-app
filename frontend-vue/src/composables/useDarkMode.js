import { ref, onMounted, watch } from 'vue';

// State global (di luar fungsi agar reaktif antar komponen)
const isDark = ref(false);

export function useDarkMode() {
    
    // Fungsi untuk update class di tag <html>
    const updateDOM = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    };

    // Fungsi Toggle
    const toggleTheme = () => {
        isDark.value = !isDark.value;
        updateDOM();
    };

    // Saat dimuat pertama kali
    onMounted(() => {
        const savedTheme = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
            isDark.value = true;
        } else {
            isDark.value = false;
        }
        updateDOM();
    });

    return { isDark, toggleTheme };
}