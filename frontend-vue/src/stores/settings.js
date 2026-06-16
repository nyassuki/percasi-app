import { defineStore } from 'pinia';
import api from '../services/api';

export const useSettingStore = defineStore('setting', {
  state: () => ({
    appName: 'Loading...', // Default sebelum load
    version: '1.0.0',
    loading: false
  }),
  
  actions: {
    async fetchSettings() {
      this.loading = true;
      try {
        const res = await api.get('/master/settings'); // Request ke backend
         if (res.data.status === 'success') {
          this.appName = res.data.data[0]['app_name'];
           
          // Update Judul Tab Browser otomatis
          document.title = this.appName;
        }
      } catch (err) {
        this.appName = 'Percasi App (Offline)';
      } finally {
        this.loading = false;
      }
    }
  }
});