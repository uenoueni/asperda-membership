import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

// https://vite.dev/config/
export default defineConfig({
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5173,
  },
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'ASPERDA — Registrasi Keanggotaan',
        short_name: 'ASPERDA',
        start_url: '/',
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#1a3a5c',
        icons: [
          { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
          { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
        ],
      },
      workbox: {
        navigateFallback: '/index.html',
        runtimeCaching: [
          {
            // API calls: network-first
            urlPattern: /\/api\/v1\//,
            handler: 'NetworkFirst',
            options: { cacheName: 'api-cache', networkTimeoutSeconds: 10 },
          },
          {
            // Static assets: cache-first
            urlPattern: /\.(js|css|png|svg|ico)$/,
            handler: 'CacheFirst',
            options: { cacheName: 'static-cache' },
          },
        ],
      },
    }),
  ],
})
