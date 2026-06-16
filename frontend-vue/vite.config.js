import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import mkcert from 'vite-plugin-mkcert'
import path from 'path'

const BackendRL = "http://localhost:3000";
export default defineConfig({
  plugins: [
    vue(),
    mkcert(),
    {
      name: 'configure-response-headers',
      configureServer(server) {
        server.middlewares.use((_req, res, next) => {
          res.setHeader('Cross-Origin-Opener-Policy', 'same-origin-allow-popups')
          res.setHeader('Cross-Origin-Embedder-Policy', 'unsafe-none')
          res.setHeader('Cross-Origin-Resource-Policy', 'cross-origin')
          
          if (_req.url.startsWith('/api')) {
            res.setHeader('Access-Control-Allow-Origin', '*')
          }
          next()
        })
      }
    }
  ],
  
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },

  server: {
    host: '0.0.0.0', // Lebih eksplisit daripada 'true'
    allowedHosts: ['demo.catur.cloud', 'mobile.catur.cloud'],
    https: true,
    port: 5173,
    
    // HMR Fix: Agar koneksi websocket Vite internal tidak error saat akses via domain
    hmr: {
      host: 'localhost',
      protocol: 'wss', 
      clientPort:443
    },

    headers: {
      'Cross-Origin-Opener-Policy': 'same-origin-allow-popups',
      'Cross-Origin-Embedder-Policy': 'unsafe-none',
      'Cross-Origin-Resource-Policy': 'cross-origin'
    },

    proxy: {
      '/api': {
        target:BackendRL,
        changeOrigin: true,
        secure: false,
        configure: (proxy, _options) => {
          proxy.on('proxyRes', (proxyRes, req, res) => {
            // Paksa header agar sinkron dengan COOP/COEP
            proxyRes.headers['cross-origin-opener-policy'] = 'same-origin-allow-popups'
            proxyRes.headers['cross-origin-embedder-policy'] = 'unsafe-none'
            proxyRes.headers['access-control-allow-origin'] = req.headers.origin || '*'
            proxyRes.headers['access-control-allow-credentials'] = 'true'
          })
        }
      },
      
      '/socket.io': {
        target: BackendRL,  
        changeOrigin: true,
        ws: true, // Wajib true untuk Socket.io
        secure: false,
      },

      '/public': {
        target: BackendRL,
        changeOrigin: true,
        secure: false,
      }
    } 
  }
})
