// src/services/firebase.js
import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";

const firebaseConfig = {
  apiKey: "AIzaSy...",
  authDomain: "project-id.firebaseapp.com",
  projectId: "project-id",
  storageBucket: "project-id.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:web:abcdef"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// Key pair yang digenerate di tab "Cloud Messaging"
const VAPID_KEY = "BKoXy..."; 

export const requestForToken = async () => {
  try {
    const currentToken = await getToken(messaging, { vapidKey: VAPID_KEY });
    if (currentToken) {
      console.log('FCM Token:', currentToken);
      // PENTING: Kirim token ini ke Database Backend Anda via API
      // await api.post('/users/fcm-token', { token: currentToken });
      return currentToken;
    } else {
      console.warn('Tidak ada token registrasi. Minta izin dulu.');
      return null;
    }
  } catch (err) {
    console.error('Gagal mengambil token:', err);
    return null;
  }
};

export const onMessageListener = () =>
  new Promise((resolve) => {
    onMessage(messaging, (payload) => {
      resolve(payload);
    });
  });