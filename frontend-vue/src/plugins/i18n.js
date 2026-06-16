import { createI18n } from 'vue-i18n'

// Import the JSON files
import en from '../locales/en.json'
import id from '../locales/id.json'
import cn from '../locales/cn.json'
import jp from '../locales/jp.json'
import ru from '../locales/ru.json'
import th from '../locales/th.json'
import ph from '../locales/ph.json'
import vt from '../locales/vt.json'

// Check for saved language in localStorage, default to 'id'
const savedLocale = localStorage.getItem('user-locale') || 'id'

const i18n = createI18n({
  legacy: false, // Set to false to use Composition API
  locale: savedLocale, // Default language
  fallbackLocale: 'en', // Fallback language if translation not found
  globalInjection: true, // Allow usage of $t in all templates
  messages: {
    en,
    id,
    cn,
    jp,
    ru,
    th,
    ph,
    vt
  }
})

export default i18n