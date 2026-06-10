<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePaymentApi } from '@/api/payment'

const router     = useRouter()
const paymentApi = usePaymentApi()

const state      = ref('loading')  // loading | ready | pending | success | error
const errorMsg   = ref('')
const paymentId  = ref(null)
const snapToken  = ref(null)

const MIDTRANS_CLIENT_KEY = import.meta.env.VITE_MIDTRANS_CLIENT_KEY
const SNAP_JS_URL = import.meta.env.VITE_MIDTRANS_IS_PRODUCTION === 'true'
  ? 'https://app.midtrans.com/snap/snap.js'
  : 'https://app.sandbox.midtrans.com/snap/snap.js'

let snapScript = null

function loadSnapJs() {
  return new Promise((resolve, reject) => {
    if (window.snap) { resolve(); return }

    snapScript = document.createElement('script')
    snapScript.src = SNAP_JS_URL
    snapScript.setAttribute('data-client-key', MIDTRANS_CLIENT_KEY)
    snapScript.onload  = resolve
    snapScript.onerror = () => reject(new Error('Gagal memuat Midtrans Snap.js'))
    document.head.appendChild(snapScript)
  })
}

async function pollStatus(id, maxTries = 10) {
  for (let i = 0; i < maxTries; i++) {
    await new Promise(r => setTimeout(r, 2000))
    const { ok, data } = await paymentApi.getStatus(id)
    if (ok && data.status === 'paid') return true
  }
  return false
}

async function openSnap() {
  state.value = 'loading'
  try {
    await loadSnapJs()

    window.snap.pay(snapToken.value, {
      onSuccess: async () => {
        state.value = 'loading'
        const paid = await pollStatus(paymentId.value)
        state.value = paid ? 'success' : 'pending'
        if (paid) {
          setTimeout(() => router.push('/dashboard'), 1500)
        }
      },
      onPending: () => {
        state.value = 'pending'
      },
      onError: (result) => {
        errorMsg.value = result?.status_message ?? 'Pembayaran gagal. Silakan coba lagi.'
        state.value = 'error'
      },
      onClose: () => {
        // User tutup popup tanpa selesai bayar
        state.value = 'ready'
      },
    })
    state.value = 'ready'
  } catch (e) {
    errorMsg.value = e.message
    state.value = 'error'
  }
}

onMounted(async () => {
  const { ok, data, error } = await paymentApi.create({ type: 'registration' })
  if (!ok) {
    errorMsg.value = error?.message ?? 'Gagal menginisiasi pembayaran.'
    state.value = 'error'
    return
  }
  paymentId.value = data.payment_id
  snapToken.value = data.snap_token
  await openSnap()
})

onUnmounted(() => {
  if (snapScript && snapScript.parentNode) {
    snapScript.parentNode.removeChild(snapScript)
  }
})
</script>

<template>
  <div class="page">
    <div class="card">
      <div class="card-header">
        <h1>ASPERDA</h1>
        <p>Pembayaran Pendaftaran</p>
      </div>

      <div class="body">
        <!-- Loading / Memuat Snap -->
        <template v-if="state === 'loading'">
          <div class="status-icon">
            <svg class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="36" height="36">
              <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="#1a3a5c" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
          </div>
          <h2>Memuat halaman pembayaran…</h2>
          <p>Mohon tunggu sebentar.</p>
        </template>

        <!-- Siap Bayar (popup snap sudah terbuka tapi user close) -->
        <template v-else-if="state === 'ready'">
          <div class="status-icon neutral">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#1a3a5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
          </div>
          <h2>Selesaikan Pembayaran</h2>
          <p>Klik tombol di bawah untuk membuka halaman pembayaran Midtrans.</p>
          <button class="btn-primary" @click="openSnap">Buka Halaman Pembayaran</button>
          <button class="btn-ghost" @click="router.push('/review')">Kembali ke Ringkasan</button>
        </template>

        <!-- Menunggu Konfirmasi -->
        <template v-else-if="state === 'pending'">
          <div class="status-icon warn">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <h2>Pembayaran Diproses</h2>
          <p>
            Pembayaran Anda sedang diproses. Jika sudah membayar via transfer bank atau virtual account,
            konfirmasi akan otomatis masuk dalam beberapa menit.
          </p>
          <button class="btn-primary" @click="router.push('/dashboard')">Ke Dashboard</button>
        </template>

        <!-- Berhasil -->
        <template v-else-if="state === 'success'">
          <div class="status-icon ok">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
          </div>
          <h2>Pembayaran Berhasil!</h2>
          <p>Terima kasih. Pendaftaran Anda sedang dalam proses verifikasi oleh petugas.</p>
          <p class="muted">Mengalihkan ke dashboard…</p>
        </template>

        <!-- Error -->
        <template v-else-if="state === 'error'">
          <div class="status-icon danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#c03a2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </div>
          <h2>Pembayaran Gagal</h2>
          <p>{{ errorMsg }}</p>
          <button class="btn-primary" @click="router.push('/review')">Coba Lagi</button>
          <button class="btn-ghost" @click="router.push('/dashboard')">Ke Dashboard</button>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f6f9;
  padding: 2rem 1.5rem;
}
.card {
  width: 100%;
  max-width: 480px;
  background: #fff;
  border: 1px solid var(--line, #e5e7eb);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.08);
  overflow: hidden;
}
.card-header {
  background: #1a3a5c;
  padding: 24px 32px;
  text-align: center;
}
.card-header h1 { margin: 0 0 4px; color: #fff; font-size: 1.35rem; letter-spacing: 0.1em; }
.card-header p  { margin: 0; color: rgba(255,255,255,0.7); font-size: 0.875rem; }
.body {
  padding: 36px 32px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.75rem;
}
.status-icon {
  width: 72px; height: 72px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
  background: #f3f4f6;
}
.status-icon.neutral { background: rgba(26,58,92,0.06); }
.status-icon.ok      { background: rgba(22,163,74,0.08); }
.status-icon.warn    { background: rgba(217,119,6,0.08); }
.status-icon.danger  { background: rgba(192,58,43,0.08); }
h2 { margin: 0; font-size: 1.2rem; color: #111827; }
p  { margin: 0; font-size: 0.9rem; color: #374151; line-height: 1.55; }
.muted { color: #9ca3af; font-size: 0.82rem; }
.btn-primary, .btn-ghost {
  width: 100%;
  padding: 0.875rem;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: filter 200ms ease, background 200ms ease;
  margin-top: 0.25rem;
}
.btn-primary { background: #1a3a5c; color: #fff; }
.btn-primary:hover { filter: brightness(1.1); }
.btn-ghost { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-ghost:hover { background: #f3f4f6; }
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 0.8s linear infinite; }

@media (max-width: 480px) {
  .body { padding: 28px 16px; }
}
</style>
