import { defineStore } from 'pinia';
import api from '../services/api';
import socket from '../services/socket';
import { jwtDecode } from 'jwt-decode';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    /**
     * Cek Expired Token
     */
    checkTokenExpiry() {
      if (!this.token) return false;
      try {
        const decoded = jwtDecode(this.token);
        const currentTime = Date.now() / 1000;
        if (decoded.exp < currentTime) {
          console.warn("Token expired detected via decode.");
          this.handleForceLogout();
          return true;
        }
        return false;
      } catch (error) {
        console.error("Invalid Token format:", error);
        this.handleForceLogout();
        return true;
      }
    },

    /**
     * LOGIN MANUAL
     * Dimodifikasi untuk mengecek status 2FA sebelum setSession
     */
    async login(email, password) {
      try {
        const res = await api.post('/auth/login', { email, password });
        
        // Ambil data dari response (sesuaikan struktur response backend Anda)
        // Misal: res.data.data berisi { user: {...}, token: '...', temp_token: '...' }
        const responseData = res.data.data || res.data; 

        // LOGIKA BARU: Cek apakah User mengaktifkan 2FA
        if (responseData.user && responseData.user.twoFa_actiive === 'YES') {
            // JANGAN set session dulu.
            // Kembalikan data ke Component (Login.vue) agar pindah ke Step 2 (Input OTP)
            return responseData; 
        }

        // Jika 2FA TIDAK aktif, langsung simpan session & token
        this._setSession(responseData);
        return responseData;

      } catch (error) {
        throw error.response?.data || { message: error.message };
      }
    },

    async loginBiometic(email,authResponse) {
      try {
        const res = await api.post('/auth/biometric/verify-login', { email, authResponse });
        
        // Ambil data dari response (sesuaikan struktur response backend Anda)
        // Misal: res.data.data berisi { user: {...}, token: '...', temp_token: '...' }
        const responseData = res.data.data || res.data; 

        // LOGIKA BARU: Cek apakah User mengaktifkan 2FA
        if (responseData.user && responseData.user.twoFa_actiive === 'YES') {
            // JANGAN set session dulu.
            // Kembalikan data ke Component (Login.vue) agar pindah ke Step 2 (Input OTP)
            return responseData; 
        }

        // Jika 2FA TIDAK aktif, langsung simpan session & token
        this._setSession(responseData);
        return responseData;

      } catch (error) {
        throw error.response?.data || { message: error.message };
      }
    },
    
    /**
     * LOGIN GOOGLE
     * Juga dimodifikasi untuk support 2FA
     */
    async loginWithGoogle(googleIdToken) {
      try {
        const res = await api.post('/auth/google', { idToken: googleIdToken });
        const responseData = res.data.data;

        // Cek 2FA untuk user Google juga
        if (responseData.user && responseData.user.twoFa_actiive === 'YES') {
            return responseData;
        }
        this._setSession(responseData);
        return responseData;
      } catch (error) {
        throw error.response?.data || { message: 'Google Login Error' };
      }
    },

    /**
     * [BARU] VERIFIKASI OTP (Langkah 2)
     * Dipanggil dari Login.vue saat user memasukkan kode OTP
     */
    async verify2FA(otp, tempToken) {
      try {
        // 1. PASTIKAN URL diawali /api agar melewati proxy Vite
        const response = await api.post('/auth/2fa/verify', {
          otp: otp,
          temp_token: tempToken
        });
        
        this.token = response.data.token;
        this._setSession(response.data);
        return response.data;
      } catch (error) {
        // 2. PERBAIKAN: Gunakan optional chaining agar tidak error 'undefined'
        // Jika error.response tidak ada, ambil error.message atau teks manual
        const errorMessage = error.response?.data?.message || error.message || "Gagal terhubung ke server";
        
        console.error("Detail Error:", error);
        throw new Error(errorMessage); 
      }
    },

    async fetchProfile() {
      if (!this.isAuthenticated) return;
      if (this.checkTokenExpiry()) return;

      try {
        const res = await api.get('/users/profile'); 
        // Merge data user terbaru dengan yang di state
        this.user = { ...this.user, ...res.data.data }; 
        localStorage.setItem('user', JSON.stringify(this.user));
      } catch (err) {
        console.error("Gagal refresh profile", err);
        if (err.response?.status === 401) {
          this.handleForceLogout();
        }
      }
    },

    /**
     * [TAMBAHAN BARU] REFRESH TOKEN
     * Digunakan oleh axios interceptor atau dipanggil manual jika token expired
     */
    async refreshToken() {
      try {
        // Asumsi endpoint backend adalah /auth/refresh-token
        // Biasanya mengirim refresh token via HTTPOnly cookie atau body (tergantung backend)
        const res = await api.post('/auth/refresh-token');
        
        const responseData = res.data.data || res.data;

        if (responseData && responseData.token) {
          // Update State
          this.token = responseData.token;
          localStorage.setItem('token', this.token);

          // Update socket auth agar koneksi realtime tidak terputus/ditolak
          if (socket) {
            socket.auth = { token: this.token };
            // Jika socket dalam keadaan disconnect, kita bisa mencoba reconnect (opsional)
            // if (!socket.connected) socket.connect();
          }

          return this.token;
        } else {
          throw new Error("Token tidak ditemukan dalam response refresh");
        }
      } catch (error) {
        console.error("Gagal melakukan refresh token:", error);
        // Jika refresh token gagal (misal expired juga), paksa logout
        this.handleForceLogout();
        throw error;
      }
    },
    
    _setSession(data) {
        // Pastikan backend mengirim 'token' di tahap ini
        if (data.token) {
            this.token = data.token;
            localStorage.setItem('token', this.token);
        }
        
        if (data.user) {
            this.user = data.user;
            localStorage.setItem('user', JSON.stringify(this.user));
        }
        
        // Opsional: Connect socket
        if (socket && !socket.connected && this.token) {
             socket.auth = { token: this.token }; // Update auth socket
             socket.connect();
        }
    },
    
    async logout() {
      try {
        if (this.token) {
            await api.post('/auth/logout');
        }
      } catch (error) {
        console.warn("Logout API failed, clearing local data anyway.");
      } finally {
        this.handleForceLogout();
      }
    },

    handleForceLogout() {
        if (socket.connected) {
            socket.disconnect();
        }
        
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        
        // Redirect ke login (opsional, sesuaikan dengan router Anda)
        // window.location.href = '/login'; 
    }
  }
});