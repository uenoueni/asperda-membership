# Vue 3 Frontend Conventions

## Project Structure

```
src/
├── api/                    # Satu file per domain, berisi fungsi pemanggil API
│   ├── auth.js
│   ├── member.js
│   ├── survey.js
│   ├── payment.js
│   ├── certificate.js
│   ├── refund.js
│   ├── starterkit.js
│   ├── wilayah.js          # Cascade dropdown data
│   └── admin/
│       ├── members.js
│       ├── sanctions.js
│       └── settings.js
├── composables/            # Reusable logic
│   ├── useApi.js           # HTTP client wrapper (WAJIB digunakan)
│   ├── useAuth.js          # Auth state dan actions
│   ├── useWilayah.js       # Cascade dropdown wilayah
│   └── usePagination.js    # Pagination state helper
├── constants/
│   └── enums.js            # Mirror dari PHP Enum — satu sumber kebenaran
├── router/
│   └── index.js            # Vue Router, dengan navigation guard
├── stores/
│   └── auth.js             # Pinia store untuk auth state
├── views/                  # Halaman (dipanggil oleh router)
│   ├── auth/
│   ├── member/
│   ├── survey/
│   └── admin/
└── components/             # Komponen reusable
    ├── ui/                 # Komponen generik (BaseButton, BaseInput, dll)
    ├── form/               # Komponen form khusus (WilayahSelect, StatusBadge, dll)
    └── layout/             # Layout wrapper
```

---

## useApi Composable (WAJIB — jangan bypass ini)

Semua HTTP request harus melalui `useApi`. Jangan gunakan `fetch` atau `axios` langsung di komponen atau `api/` files.

```js
// src/composables/useApi.js
import { ref } from 'vue'
import router from '@/router'

const BASE_URL = import.meta.env.VITE_API_BASE_URL // contoh: https://daftar.asperda.id/api/v1

export function useApi() {
  const loading = ref(false)
  const error   = ref(null)

  async function request(method, endpoint, body = null, options = {}) {
    loading.value = true
    error.value   = null

    try {
      const config = {
        method,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'include', // Sanctum cookie
        ...options,
      }

      if (body && method !== 'GET') {
        config.body = JSON.stringify(body)
      }

      const res  = await fetch(`${BASE_URL}${endpoint}`, config)
      const json = await res.json()

      if (!res.ok) {
        error.value = json
        return { ok: false, data: null, error: json }
      }

      return { ok: true, data: json.data, meta: json.meta, message: json.message, error: null }

    } catch (e) {
      const networkError = { success: false, message: 'Koneksi gagal. Periksa jaringan Anda.' }
      error.value = networkError
      return { ok: false, data: null, error: networkError }

    } finally {
      loading.value = false
    }
  }

  const get    = (endpoint, params = {}) => {
    const qs = new URLSearchParams(params).toString()
    return request('GET', qs ? `${endpoint}?${qs}` : endpoint)
  }
  const post   = (endpoint, body)  => request('POST',   endpoint, body)
  const put    = (endpoint, body)  => request('PUT',    endpoint, body)
  const patch  = (endpoint, body)  => request('PATCH',  endpoint, body)
  const del    = (endpoint)        => request('DELETE', endpoint)

  return { loading, error, get, post, put, patch, del }
}
```

**Cara pakai di `api/` files:**

```js
// src/api/survey.js
import { useApi } from '@/composables/useApi'

export function useSurveyApi() {
  const api = useApi()

  return {
    loading: api.loading,
    error:   api.error,

    list:    (params) => api.get('/survey', params),
    detail:  (id)     => api.get(`/survey/${id}`),
    accept:  (id)     => api.post(`/survey/${id}/accept`),
    approve: (id, body) => api.post(`/survey/${id}/approve`, body),
    reject:  (id, body) => api.post(`/survey/${id}/reject`, body),
  }
}
```

**Cara pakai di komponen:**

```vue
<script setup>
import { onMounted, ref } from 'vue'
import { useSurveyApi } from '@/api/survey'

const surveyApi = useSurveyApi()
const assignments = ref([])

onMounted(async () => {
  const { ok, data } = await surveyApi.list({ status: 'pending' })
  if (ok) assignments.value = data
})
</script>
```

---

## Cascade Dropdown Wilayah (WAJIB lazy-load)

Dropdown wilayah (provinsi, kota, kecamatan) **selalu** mengambil data dari API saat dibutuhkan, bukan di-preload saat halaman mount.

```js
// src/composables/useWilayah.js
import { ref, watch } from 'vue'
import { useApi } from '@/composables/useApi'

export function useWilayah() {
  const api = useApi()

  const provinsiList   = ref([])
  const kotaList       = ref([])
  const kecamatanList  = ref([])

  const selectedProvinsi   = ref(null)
  const selectedKota       = ref(null)
  const selectedKecamatan  = ref(null)

  const loadingProvinsi   = ref(false)
  const loadingKota       = ref(false)
  const loadingKecamatan  = ref(false)

  // Provinsi: lazy — dipanggil saat dropdown dibuka pertama kali
  async function fetchProvinsi() {
    if (provinsiList.value.length) return  // sudah ada, skip
    loadingProvinsi.value = true
    const { ok, data } = await api.get('/wilayah/provinsi')
    if (ok) provinsiList.value = data
    loadingProvinsi.value = false
  }

  // Kota: fetch ulang setiap provinsi berubah
  watch(selectedProvinsi, async (code) => {
    kotaList.value       = []
    kecamatanList.value  = []
    selectedKota.value       = null
    selectedKecamatan.value  = null

    if (!code) return
    loadingKota.value = true
    const { ok, data } = await api.get(`/wilayah/kota/${code}`)
    if (ok) kotaList.value = data
    loadingKota.value = false
  })

  // Kecamatan: fetch ulang setiap kota berubah
  watch(selectedKota, async (code) => {
    kecamatanList.value     = []
    selectedKecamatan.value = null

    if (!code) return
    loadingKecamatan.value = true
    const { ok, data } = await api.get(`/wilayah/kecamatan/${code}`)
    if (ok) kecamatanList.value = data
    loadingKecamatan.value = false
  })

  // Untuk edit form: set nilai awal dari data yang sudah ada
  async function prefill(provinceCode, cityCode, districtCode = null) {
    await fetchProvinsi()
    selectedProvinsi.value = provinceCode
    // watch akan auto-fetch kota
    await nextTick()
    selectedKota.value = cityCode
    if (districtCode) {
      await nextTick()
      selectedKecamatan.value = districtCode
    }
  }

  return {
    provinsiList, kotaList, kecamatanList,
    selectedProvinsi, selectedKota, selectedKecamatan,
    loadingProvinsi, loadingKota, loadingKecamatan,
    fetchProvinsi, prefill,
  }
}
```

**Komponen WilayahSelect:**

```vue
<!-- src/components/form/WilayahSelect.vue -->
<script setup>
import { useWilayah } from '@/composables/useWilayah'

const {
  provinsiList, kotaList, kecamatanList,
  selectedProvinsi, selectedKota, selectedKecamatan,
  loadingProvinsi, loadingKota, loadingKecamatan,
  fetchProvinsi,
} = useWilayah()

const emit = defineEmits(['update:provinsi', 'update:kota', 'update:kecamatan'])

watch(selectedProvinsi, (v) => emit('update:provinsi', v))
watch(selectedKota,     (v) => emit('update:kota', v))
watch(selectedKecamatan,(v) => emit('update:kecamatan', v))
</script>

<template>
  <div class="wilayah-select">
    <!-- Provinsi: fetch saat dropdown dibuka (lazy) -->
    <select
      v-model="selectedProvinsi"
      @focus="fetchProvinsi"
      :disabled="loadingProvinsi"
    >
      <option value="">
        {{ loadingProvinsi ? 'Memuat...' : 'Pilih Provinsi' }}
      </option>
      <option v-for="p in provinsiList" :key="p.code" :value="p.code">
        {{ p.name }}
      </option>
    </select>

    <!-- Kota: diaktifkan setelah provinsi dipilih -->
    <select
      v-model="selectedKota"
      :disabled="!selectedProvinsi || loadingKota"
    >
      <option value="">
        {{ loadingKota ? 'Memuat...' : 'Pilih Kota/Kabupaten' }}
      </option>
      <option v-for="k in kotaList" :key="k.code" :value="k.code">
        {{ k.name }}
      </option>
    </select>

    <!-- Kecamatan: opsional -->
    <select
      v-model="selectedKecamatan"
      :disabled="!selectedKota || loadingKecamatan"
    >
      <option value="">
        {{ loadingKecamatan ? 'Memuat...' : 'Pilih Kecamatan (opsional)' }}
      </option>
      <option v-for="k in kecamatanList" :key="k.code" :value="k.code">
        {{ k.name }}
      </option>
    </select>
  </div>
</template>
```

**Aturan dropdown lainnya:** Semua dropdown yang datanya dari API (bukan list statis < 20 item) mengikuti pola yang sama: disabled saat loading, tidak fetch saat page mount, fetch saat user interaksi pertama.

---

## Enums di Frontend

```js
// src/constants/enums.js
// Mirror dari app/Enums/ di Laravel — update keduanya jika ada perubahan

export const MemberStatus = Object.freeze({
  PENDING_VERIFICATION: 'pending_verification',
  WAITING_SURVEY:       'waiting_survey',
  ACTIVE:               'active',
  REJECTED:             'rejected',
  EXPIRED:              'expired',
})

export const SurveyLevel = Object.freeze({
  DPC: 'dpc',
  DPD: 'dpd',
  DPP: 'dpp',
})

export const SurveyStatus = Object.freeze({
  PENDING:   'pending',
  ACCEPTED:  'accepted',
  APPROVED:  'approved',
  REJECTED:  'rejected',
  ESCALATED: 'escalated',
})

export const PaymentStatus = Object.freeze({
  PENDING:  'pending',
  PAID:     'paid',
  FAILED:   'failed',
  EXPIRED:  'expired',
  REFUNDED: 'refunded',
})

export const RefundStatus = Object.freeze({
  QUEUED:     'queued',
  PROCESSING: 'processing',
  COMPLETED:  'completed',
  CANCELLED:  'cancelled',
})

export const StarterkitDistributionStatus = Object.freeze({
  PENDING:     'pending',
  DISTRIBUTED: 'distributed',
  CONFIRMED:   'confirmed',
})

export const UserRole = Object.freeze({
  SUPER_ADMIN: 'super_admin',
  DPP:         'dpp',
  DPD:         'dpd',
  DPC:         'dpc',
  MEMBER:      'member',
})
```

Jangan hardcode string `'pending'`, `'active'`, dll di template. Selalu import dari `enums.js`.

---

## Routing & Navigation Guards

```js
// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // Public
  { path: '/daftar',        component: () => import('@/views/auth/RegisterView.vue') },
  { path: '/verifikasi',    component: () => import('@/views/auth/VerifyEmailView.vue') },
  { path: '/set-password',  component: () => import('@/views/auth/SetPasswordView.vue') },
  { path: '/login',         component: () => import('@/views/auth/LoginView.vue') },
  { path: '/verify/:cert',  component: () => import('@/views/public/CertVerifyView.vue') },

  // Member
  {
    path: '/dashboard',
    component: () => import('@/views/member/DashboardView.vue'),
    meta: { requiresAuth: true, roles: ['member'] },
  },

  // Petugas
  {
    path: '/survey',
    component: () => import('@/views/survey/SurveyListView.vue'),
    meta: { requiresAuth: true, roles: ['dpc', 'dpd', 'dpp', 'super_admin'] },
  },

  // Admin
  {
    path: '/admin',
    component: () => import('@/views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, roles: ['dpc', 'dpd', 'dpp', 'super_admin'] },
    children: [
      { path: 'members', component: () => import('@/views/admin/MemberListView.vue') },
      { path: 'members/:id', component: () => import('@/views/admin/MemberDetailView.vue') },
      { path: 'refunds', component: () => import('@/views/admin/RefundListView.vue') },
      { path: 'sanctions', component: () => import('@/views/admin/SanctionListView.vue') },
      { path: 'starterkit', component: () => import('@/views/admin/StarterkitView.vue') },
      // Super admin only
      { path: 'settings', component: () => import('@/views/admin/SettingsView.vue'), meta: { roles: ['super_admin'] } },
      { path: 'users', component: () => import('@/views/admin/UserManagementView.vue'), meta: { roles: ['super_admin'] } },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  if (to.meta.roles && !to.meta.roles.includes(auth.user?.role)) {
    return { path: '/dashboard' }  // redirect ke halaman yang accessible
  }
})

export default router
```

---

## Pinia Auth Store

```js
// src/stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApi } from '@/composables/useApi'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const api  = useApi()

  const isAuthenticated = computed(() => !!user.value)
  const role            = computed(() => user.value?.role ?? null)

  async function fetchUser() {
    const { ok, data } = await api.get('/auth/me')
    if (ok) user.value = data
    return ok
  }

  async function logout() {
    await api.post('/auth/logout')
    user.value = null
  }

  return { user, isAuthenticated, role, fetchUser, logout }
})
```

---

## Pagination

```js
// src/composables/usePagination.js
import { ref, reactive } from 'vue'

export function usePagination(fetchFn) {
  const items = ref([])
  const meta  = reactive({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const loading = ref(false)

  async function load(params = {}) {
    loading.value = true
    const { ok, data, meta: m } = await fetchFn({ page: meta.current_page, ...params })
    if (ok) {
      items.value = data
      Object.assign(meta, m)
    }
    loading.value = false
  }

  function goTo(page) {
    meta.current_page = page
    load()
  }

  return { items, meta, loading, load, goTo }
}
```

---

## PWA Setup

```js
// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
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
        theme_color: '#1a3a5c',  // sesuaikan dengan brand ASPERDA
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
```
