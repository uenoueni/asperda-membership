<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthApi } from '@/api/auth'

const route = useRoute()
const authApi = useAuthApi()

const email = route.query.email ?? ''
const status = route.query.status ?? ''

const resendDone = ref(false)
const resendError = ref('')

async function resend() {
  resendError.value = ''
  const { ok, error } = await authApi.resendVerification(email)
  if (ok) {
    resendDone.value = true
  } else {
    resendError.value = error?.message ?? 'Gagal mengirim ulang email.'
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <h1>ASPERDA</h1>
      </div>

      <div class="body">
        <!-- Link invalid / expired -->
        <template v-if="status === 'invalid'">
          <div class="icon warn">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <h2>Link tidak valid atau kadaluarsa</h2>
          <p>Link verifikasi berlaku 24 jam. Minta link baru di bawah ini.</p>
        </template>

        <!-- Sukses daftar, cek email -->
        <template v-else>
          <div class="icon ok">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
            </svg>
          </div>
          <h2>Cek email Anda</h2>
          <p>Kami mengirim link verifikasi ke <strong>{{ email }}</strong>. Klik link tersebut untuk melanjutkan pendaftaran.</p>
          <p class="muted">Link berlaku 24 jam. Periksa folder <em>Spam</em> jika tidak muncul di inbox.</p>
        </template>

        <!-- Resend -->
        <template v-if="!resendDone">
          <p v-if="resendError" class="err">{{ resendError }}</p>
          <button
            v-if="email"
            :disabled="authApi.loading.value"
            @click="resend"
          >
            <svg v-if="authApi.loading.value" class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="16" height="16" aria-hidden="true">
              <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
            {{ authApi.loading.value ? 'Mengirim…' : 'Kirim ulang email verifikasi' }}
          </button>
        </template>
        <p v-else class="success">Email verifikasi telah dikirim ulang. Silakan cek inbox Anda.</p>

        <a href="/login" class="back">Kembali ke halaman login</a>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f6f9;
  padding: 1.5rem;
}
.auth-card {
  width: 100%;
  max-width: 440px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.08);
  overflow: hidden;
}
.auth-header {
  background: #1a3a5c;
  padding: 20px 32px;
  text-align: center;
}
.auth-header h1 { margin: 0; color: #fff; font-size: 1.35rem; letter-spacing: 0.1em; }
.body {
  padding: 32px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.75rem;
}
.icon {
  width: 64px; height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
}
.icon.ok { background: rgba(26,58,92,0.08); color: #1a3a5c; }
.icon.warn { background: rgba(192,58,43,0.08); color: #c03a2b; }
h2 { margin: 0; font-size: 1.2rem; }
p { margin: 0; font-size: 0.9rem; color: #374151; line-height: 1.55; }
.muted { color: #9ca3af; font-size: 0.82rem; }
.err { color: #c03a2b; font-size: 0.85rem; }
.success { color: #16a34a; font-weight: 600; font-size: 0.9rem; }
button {
  width: 100%;
  padding: 0.8rem;
  background: #1a3a5c;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: filter 200ms ease;
  margin-top: 0.5rem;
}
button:hover:not(:disabled) { filter: brightness(1.1); }
button:disabled { opacity: 0.6; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 0.8s linear infinite; }
.back { font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem; }
.back:hover { color: #1a3a5c; }
</style>
