import { io } from "socket.io-client";
import { reactive } from "vue";

 
export const socketState = reactive({
  connected: false,
  fooEvents: [],
  barEvents: []
});

const socket = io( import.meta.env.VITE_BASE_URL, {
  autoConnect: false, 
  reconnection: true,             // Aktifkan auto-reconnect bawaan (untuk network error)
  reconnectionAttempts: Infinity, // Coba terus tanpa batas (atau ganti angka misal 50)
  reconnectionDelay: 2000,        // Tunggu 2 detik sebelum coba lagi
  reconnectionDelayMax: 5000,     // Maksimal tunggu 5 detik
  timeout: 20000,                 // Waktu tunggu sebelum anggap RTO
  
  // Callback Auth: Memastikan Token selalu FRESH saat reconnect
  auth: (cb) => {
    const token = localStorage.getItem('token');
    cb({ token });
  }
});

// --- LISTENERS ---

socket.on("connect", () => {
  socketState.connected = true;
  console.log("🟢 Socket Connected:", socket.id);
});

socket.on("disconnect", (reason) => {
  socketState.connected = false;
  console.warn(`🔴 Socket Disconnected: ${reason}`);
  
  // [FIX RECONNECT] 
  // Jika server memutus koneksi secara paksa (misal server restart atau token expired sementara),
  // socket.io TIDAK akan reconnect otomatis. Kita harus paksa connect manual.
  if (reason === "io server disconnect") {
    const token = localStorage.getItem('token');
    if (token) {
        // Coba connect lagi setelah 2 detik
        setTimeout(() => {
            console.log("🔄 Trying to reconnect manually...");
            socket.connect();
        }, 2000);
    }
  }
});

socket.on("connect_error", (err) => {
  console.error("⚠️ Socket Connection Error:", err.message);
  socketState.connected = false;
  
  // Opsional: Jika error karena "Authentication error", mungkin token expired
  // Anda bisa redirect ke login di sini jika perlu.
});

// --- NETWORK LISTENER (Browser Level) ---
// Fitur Tambahan: Paksa reconnect saat browser mendeteksi internet kembali nyala
if (typeof window !== "undefined") {
  window.addEventListener("online", () => {
    console.log("🌐 Network Online detected, forcing socket reconnect...");
    connectSocket();
  });
}

// --- HELPERS ---

export const connectSocket = () => {
  // Hanya connect jika belum connect DAN ada token
  if (!socket.connected) {
    const token = localStorage.getItem('token');
    if (token) {
        socket.connect();
    } else {
        console.warn("Socket connect skipped: No token found.");
    }
  }
};

export const disconnectSocket = () => {
  if (socket.connected) {
    socket.disconnect();
  }
};

export const state = socketState;
export default socket;