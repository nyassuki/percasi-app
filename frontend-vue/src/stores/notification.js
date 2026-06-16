import { defineStore } from 'pinia';
import api from '../services/api';

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        list: [],           // Menyimpan detail notifikasi (judul, pesan, tanggal)
        unreadCount: 0,     // Menyimpan jumlah angka merah
        loading: false
    }),

    actions: {
        // 1. Load List Notifikasi (Biasanya saat dropdown dibuka)
        async fetchNotifications() {
            this.loading = true;
            try {
                const res = await api.get('/notifications');
                // Sesuaikan dengan struktur response backend Anda
                // Contoh asumsi: { status: 'success', data: [...], unread_count: 5 }
                if (res.data.status === 'success') {
                    this.list = res.data.data;
                    
                    // Opsional: Sinkronkan count juga jika backend mengirimnya
                    if (res.data.unread !== undefined) {
                        this.unreadCount = res.data.unread;
                    }
                }
            } catch (err) {
                console.error("Gagal load notif", err);
            } finally {
                this.loading = false;
            }
        },

        // 2. Load Hanya Jumlah (Untuk Lonceng Navbar saat pertama load)
        async fetchUnreadCount() {
            try {
                const res = await api.get('/notifications/unread-count');
                this.unreadCount = res.data.count || 0;
            } catch (err) {
                console.error("Gagal ambil unread count", err);
            }
        },

        // 3. Menandai Sudah Dibaca
        async markAsRead(id = null) {
            try {
                // Panggil API
                // Endpoint ini harus bisa handle { id: null } untuk "Mark All Read"
                await api.get(`/notifications/read/${id}`);

                // Update State Lokal (Agar UI responsif tanpa refresh)
                if (id) {
                    // CASE: Baca 1 Notifikasi
                    const item = this.list.find(n => n.id === id);
                    if (item && item.is_read == 0) {
                        item.is_read = 1;
                        this.decrementCount();
                    }
                } else {
                    // CASE: Mark All Read
                    this.list.forEach(n => n.is_read = 1);
                    this.unreadCount = 0;
                }
            } catch (err) {
                console.error("Gagal mark read", err);
                // Opsional: Tampilkan toast error disini
            }
        },

        // --- ACTION KHUSUS REALTIME / SOCKET ---

        /**
         * Dipanggil dari App.vue saat socket 'app_notification' masuk.
         * @param {Object} data - Payload notifikasi dari socket
         */
        handleNewNotification(data) {
            // 1. Tambah Counter Lonceng
            this.unreadCount++;

            // 2. Tambah ke List (Top) jika list sedang ada isinya
            // Kita buat object dummy agar langsung muncul di list tanpa refresh
            const newNotif = {
                id: Date.now(), // ID sementara (atau backend kirim ID asli)
                title: data.title,
                message: data.message,
                type: data.type,
                is_read: 0,
                created_at: new Date().toISOString(), // Waktu sekarang
                ...data // Merge data lain jika ada
            };

            // Masukkan ke urutan paling atas (unshift)
            this.list.unshift(newNotif);
            
            // Batasi list agar tidak kepanjangan di memori (misal max 50)
            if (this.list.length > 50) {
                this.list.pop();
            }
        },

        // Helper untuk mengurangi count (biar gak minus)
        decrementCount() {
            if (this.unreadCount > 0) {
                this.unreadCount--;
            }
        },

        // Reset saat Logout
        resetState() {
            this.list = [];
            this.unreadCount = 0;
            this.loading = false;
        }
    }
});