<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthApi } from '@/api/auth'

const route = useRoute()
const router = useRouter()
const authApi = useAuthApi()

const form = reactive({
  email: route.query.email ?? '',
  token: route.query.token ?? '',
  password: '',
  password_confirmation: '',
})
const errors = reactive({})
const done = ref(false)

onMounted(() => {
  if (!form.email || !form.token) {
    router.replace('/verifikasi?status=invalid')
  }
})

function clearErrors() {
  Object.keys(errors).forEach((k) => delete errors[k])
}

async function submit() {
  clearErrors()

  const { ok, error } = await authApi.setPassword({ ...form })

  if (ok) {
    done.value = true
    setTimeout(() => router.push('/login'), 2500)
  } else if (error?.errors) {
    Object.entries(error.errors).forEach(([k, v]) => (errors[k] = Array.isArray(v) ? v[0] : v))
  } else {
    errors._global = error?.message ?? 'Gagal menyimpan password. Coba lagi.'
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <h1>ASPERDA</h1>
        <p>Buat password untuk akun Anda</p>
      </div>

      <!-- Sukses -->
      <div v-if="done" class="body success-body">
        <div class="icon-ok">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <h2>Password berhasil dibuat!</h2>
        <p>Anda akan diarahkan ke halaman login…</p>
      </div>

      <!-- Form -->
      <form v-else class="auth-form" novalidate @submit.prevent="submit">
        <div class="info-box">
          Akun: <strong>{{ form.email }}</strong>
        </div>

        <div class="field">
          <label for="password">Password Baru</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Minimal 8 karakter"
            autocomplete="new-password"
            :class="{ invalid: errors.password }"
          />
          <small v-if="errors.password" class="err">{{ errors.password }}</small>
        </div>

        <div class="field">
          <label for="password_confirmation">Konfirmasi Password</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            placeholder="Ulangi password"
            autocomplete="new-password"
            :class="{ invalid: errors.password_confirmation }"
          />
          <small v-if="errors.password_confirmation" class="err">{{ errors.password_confirmation }}</small>
        </div>

        <p v-if="errors._global" class="err global">{{ errors._global }}</p>
        <p v-if="errors.token" class="err global">{{ errors.token }}</p>

        <button type="submit" :disabled="authApi.loading.value" :aria-busy="authApi.loading.value">
          <svg v-if="authApi.loading.value" class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="18" height="18" aria-hidden="true">
            <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
          {{ authApi.loading.value ? 'Menyimpan…' : 'Simpan Password' }}
        </button>
      </form>
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
  max-width: 420px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.08);
  overflow: hidden;
}
.auth-header {
  background: #1a3a5c;
  padding: 24px 32px;
  text-align: center;
}
.auth-header h1 { margin: 0 0 4px; color: #fff; font-size: 1.35rem; letter-spacing: 0.1em; }
.auth-header p { margin: 0; color: rgba(255,255,255,0.65); font-size: 0.875rem; }
.auth-form {
  padding: 28px 32px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.info-box {
  background: #f0f4f8;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.875rem;
  color: #374151;
}
.field { display: flex; flex-direction: column; gap: 0.35rem; }
label { font-size: 0.875rem; font-weight: 600; color: #1a3a5c; }
input {
  padding: 0.75rem 0.9rem;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  font-family: inherit;
  background: #fff;
  transition: border-color 200ms ease, box-shadow 200ms ease;
}
input:focus { outline: none; border-color: #1a3a5c; box-shadow: 0 0 0 3px rgba(26,58,92,0.12); }
input.invalid { border-color: #c03a2b; box-shadow: 0 0 0 3px rgba(192,58,43,0.1); }
.err { color: #c03a2b; font-size: 0.8rem; }
.err.global { margin: 0; }
button {
  padding: 0.875rem;
  background: #1a3a5c;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: filter 200ms ease;
}
button:hover:not(:disabled) { filter: brightness(1.1); }
button:disabled { opacity: 0.6; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 0.8s linear infinite; }
.success-body {
  padding: 40px 32px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.75rem;
}
.icon-ok {
  width: 64px; height: 64px;
  background: rgba(22,163,74,0.1);
  color: #16a34a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
}
.success-body h2 { margin: 0; font-size: 1.2rem; }
.success-body p { margin: 0; color: #6b7280; font-size: 0.9rem; }
</style>
