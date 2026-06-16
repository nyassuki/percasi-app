import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
});

let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach(prom => {
    if (error) prom.reject(error);
    else prom.resolve(token);
  });
  failedQueue = [];
};

// --- REQUEST INTERCEPTOR ---
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`;
    }
    // Cache busting untuk GET
    if (config.method === 'get') {
      config.params = { ...config.params, _t: Date.now() };
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// --- RESPONSE INTERCEPTOR (CONSOLIDATED) ---
api.interceptors.response.use(
  (response) => {
    const authStore = useAuthStore();
    if (authStore.isAuthenticated) authStore.resetActivityTimer?.();
    return response;
  },
  async (error) => {
    const { config, response } = error;
    const authStore = useAuthStore();

    // 1. LOGIKA RETRY (Network Error & 5xx)
    // Dijalankan lebih dulu sebelum error diproses lebih lanjut
    const shouldRetry = !response || (response.status >= 500) || (response.status === 429);
    
    if (shouldRetry && config && !config._retry) {
      config._retryCount = config._retryCount ?? 0;
      const maxRetries = 3;

      if (config._retryCount < maxRetries) {
        config._retryCount++;
        const delay = 1000 * Math.pow(2, config._retryCount - 1);
        console.warn(`Retrying (${config._retryCount}/${maxRetries}) in ${delay}ms...`);
        
        await new Promise(resolve => setTimeout(resolve, delay));
        return api(config);
      }
    }

    // 2. JIKA TIDAK ADA RESPONSE (Network Error)
    if (!response) {
      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('network-error', {
          detail: { message: 'Koneksi terputus. Periksa internet Anda.' }
        }));
      }
      return Promise.reject(error);
    }

    const { status, data } = response;

    // 3. HANDLE 401 (Unauthorized / Token Expired)
    if (status === 401 && !config._retry) {
      config._retry = true;
      if (!isRefreshing) {
        isRefreshing = true;
        try {
          // Asumsi authStore punya method refreshToken
          await authStore.refreshToken();
          isRefreshing = false;
          processQueue(null, authStore.token);
          config.headers.Authorization = `Bearer ${authStore.token}`;
          return api(config);
        } catch (refreshErr) {
          isRefreshing = false;
          processQueue(refreshErr, null);
          authStore.logout?.(false);
          return Promise.reject(refreshErr);
        }
      }

      return new Promise((resolve, reject) => {
        failedQueue.push({ resolve, reject });
      }).then(token => {
        config.headers.Authorization = `Bearer ${token}`;
        return api(config);
      }).catch(err => Promise.reject(err));
    }

    // 4. HANDLE 403 (Forbidden)
    if (status === 403) {
      window.dispatchEvent(new CustomEvent('access-denied', {
        detail: { message: data?.message || 'Akses ditolak.' }
      }));
    }

    // 5. HANDLE 5xx (Server Error)
    if (status >= 500) {
      // PENTING: Gunakan pesan asli dari backend jika ada
      const serverMsg = data?.message || 'Terjadi kesalahan internal pada server.';
      
      window.dispatchEvent(new CustomEvent('server-error', {
        detail: { status, message: serverMsg }
      }));

      // Timpa pesan error agar mudah dibaca di front-end
      error.message = serverMsg; 
    }

    // 6. KEMBALIKAN ERROR ASLI (Agar catch di Vue bisa membaca)
    // Tambahkan properti bantu untuk memudahkan handling di UI
    error.formattedMessage = data?.message || error.message;
    return Promise.reject(error);
  }
);

export default api;