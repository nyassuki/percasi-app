import {
    createRouter,
    createWebHistory
} from 'vue-router';
import {
    useAuthStore
} from '../stores/auth';

// Layouts
import MainLayout from '../layouts/MainLayout.vue';

// Views (Eager loaded for critical paths)
import Login from '../views/Login.vue';
import ForgotPassword from '../views/ForgotPassword.vue';
import {
    useLoadingStore
} from '../stores/loading';
import MatchHistory from '../views/MatchHistory.vue';
import Lobby from '../views/Lobby.vue';
import ReceiveMoney from '../views/ReceiveMoney.vue';
import TransferAmount from '../views/TransferAmount.vue';
import TournamentLobby from '../views/TournamentLobby.vue'; 
import PaymentQris from  '../views/qris.vue'
import news from  '../views/NewsDetail.vue'
import LiveLobby from '../views/LiveLobby.vue'
import MirrorView from '../views/MirrorView.vue'
import settings from '../views/settings.vue'
import hWallet from '../views/help/Wallet.vue'
import LanguageSwitcher from '../components/LanguageSwitcher.vue'
import PrivacyPolicy from '../views/PrivacyPolicy.vue'
import TermsOfService from '../views/TermsOfService.vue'
import CookiePolicy from '../views/CookiePolicy.vue'

const router = createRouter({
    history: createWebHistory(
        import.meta.env.BASE_URL),
    routes: [
        // --- PUBLIC / GUEST ROUTES ---
        {
            path: '/login',
            name: 'login',
            component: Login,
            meta: {
                requiresGuest: true
            }
        }, {
            path: '/forgot-password',
            name: 'forgot-password',
            component: ForgotPassword,
            meta: {
                requiresGuest: true
            }
        }, {
            path: '/register',
            name: 'register',
            component: () =>
                import ('../views/Register.vue'),
            meta: {
                requiresGuest: true
            } // Jika Anda punya middleware guest
        },
        // --- PROTECTED ROUTES (WITH LAYOUT) ---
        {
            path: '/',
            component: MainLayout,
            meta: {
                requiresAuth: true
            },
            children: [{
                path: '',
                redirect: {
                    name: 'dashboard'
                } // Redirect root ke dashboard
            }, {
                path: 'dashboard',
                name: 'dashboard',
                component: () =>
                    import ('../views/Dashboard.vue')
            }, {
                path: 'game/:id',
                name: 'game',
                component: () =>
                    import ('../views/GameArena.vue')
            }, {
                path: 'wallet',
                name: 'wallet',
                component: () =>
                    import ('../views/Wallet.vue')
            }, {
                path: 'tournaments',
                name: 'tournaments',
                component: () =>
                    import ('../views/Tournaments.vue')
            }, {
                path: 'profile',
                name: 'profile',
                component: () =>
                    import ('../views/Profile.vue')
            }, {
                path: 'kyc',
                name: 'kyc',
                component: () =>
                    import ('../views/Kyc.vue')
            }, {
                path: 'bot',
                name: 'bot',
                component: () =>
                    import ('../views/BotArena.vue')
            }, {
                path: 'transactions',
                name: 'transaction-history',
                component: () =>
                    import ('../views/TransactionHistory.vue')
            }, {
                path: 'inbox',
                name: 'inbox',
                component: () =>
                    import ('../views/Inbox.vue')
            }, {
                path: 'market',
                name: 'marketplace',
                component: () =>
                    import ('../views/market.vue')
            }, {
                path: 'market/train',
                name: 'train-booking', 
                component: () =>
                    import ('../views/TrainBooking.vue')
            }, {
                path: 'market/flight',
                name: 'flight-booking', 
                component: () =>
                    import ('../views/FlightBooking.vue')
            }, {
                path: 'scan',
                name: 'qr-scanner', 
                component: () =>
                    import ('../views/ScanQr.vue')
            }, {
                path: '/play/qr',
                name: 'qr-match',
                component: () =>
                    import ('../views/QrMatch.vue'),
                meta: {
                    requiresAuth: true
                }
            }, {
                path: '/matches/history',
                name: 'MatchHistory',
                component: MatchHistory,
                meta: {
                    requiresAuth: true
                }
            }, {
                path: '/lobby',
                name: 'Loby arena',
                component: Lobby,
                meta: {
                    requiresAuth: true
                }
            }, {
                path: '/wallet/receive',
                name: 'Wallet receive',
                component: ReceiveMoney,
                meta: {
                    requiresAuth: true
                }
            }, {
                path: '/wallet/transfer/amount',
                name: 'TransferAmount',
                component: TransferAmount,
                meta: {
                    requiresAuth: true
                }
            }, {
                path: '/tournaments/:id/lobby',
                name: 'tournament-lobby',
                component: TournamentLobby,
                meta: {
                    requiresAuth: true
                }
            }, {
                path: '/payment/qris',
                name: 'qris-payment',
                component: PaymentQris,
                meta: {
                    requiresAuth: true
                }
            }, {
                path: '/news',
                name: 'news-detail',
                component: news,
                meta: {
                    requiresAuth: true
                }
            }, {
                path: '/switch-lang',
                name: 'language-switcher',
                component: LanguageSwitcher,
                meta: {
                    requiresAuth: true
                }
            },{
            
            path: '/mirror/:id', 
            name: 'MirrorView',
            component: MirrorView,
            props: true 
          },{
            
            path: '/settings', 
            name: 'settings',
            component: settings,
            props: true 
          },{
            
            path: '/help/wallet', 
            name: 'hWallet',
            component: hWallet,
            props: true 
          },{
            path: '/help/wallet', 
            name: 'hWallet',
            component: hWallet,
            props: true 
          },{
            path: '/privacy-policy',
            name: 'PrivacyPolicy',
            component: PrivacyPolicy,
            meta: {
              title: 'Kebijakan Privasi - Platform Catur Online'
            }
          },
          {
            path: '/terms-of-service',
            name: 'TermsOfService',
            component: TermsOfService,
            meta: {
              title: 'Syarat Layanan - Platform Catur Online'
            }
          },
          {
            path: '/cookie-policy',
            name: 'CookiePolicy',
            component: CookiePolicy,
            meta: {
              title: 'Kebijakan Cookie - Platform Catur Online'
            }
          }]
        },
    ]
});

// --- NAVIGATION GUARD ---
router.beforeEach((to, from, next) => {
    const loadingStore = useLoadingStore();
    loadingStore.start();

    const auth = useAuthStore();
    const isAuthenticated = auth.isAuthenticated; // Pastikan getter ini ada di store Anda

    // 1. Cek apakah halaman butuh login
    if (to.meta.requiresAuth && !isAuthenticated) {
        return next({
            name: 'login'
        });
    }

    // 2. Cek apakah halaman khusus tamu (Login/Forgot Pass), tapi user sudah login
    if (to.meta.requiresGuest && isAuthenticated) {
        return next({
            name: 'dashboard'
        });
    }

    // 3. Lanjut
    next();
});
router.onError((error) => {
    console.error('Router error:', error);
    
    const authStore = useAuthStore();
    
    // Handle auth errors during navigation
    if (error.message?.includes('Session expired') || 
        error.message?.includes('401')) {
        authStore.handleTokenExpired();
    }
});
router.afterEach(() => {
    const loadingStore = useLoadingStore();
    loadingStore.finish();
});
export default router;