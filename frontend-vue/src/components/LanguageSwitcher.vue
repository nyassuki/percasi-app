<template>
  <div class="lang-switcher">
    <label>{{ t('button.change_lang') }}: </label>
    <select v-model="locale" @change="switchLanguage">
      <option value="id">🇮🇩 Indonesia</option>
      <option value="en">🇺🇸 English</option>
    </select>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

// Destructure locale untuk membaca/mengubah bahasa aktif
const { t, locale } = useI18n()

const switchLanguage = (event) => {
  const newLang = event.target.value
  
  // 1. Ubah bahasa di state i18n
  locale.value = newLang
  
  // 2. Simpan ke LocalStorage (agar persist saat refresh)
  localStorage.setItem('user-locale', newLang)
  
  // 3. (Opsional) Set atribut html lang untuk SEO/Aksesibilitas
  document.querySelector('html').setAttribute('lang', newLang)
}
</script>

<style scoped>
.lang-switcher select {
  padding: 5px;
  border-radius: 4px;
  border: 1px solid #ccc;
}
</style>