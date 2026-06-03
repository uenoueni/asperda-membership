<script setup>
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const form = reactive({ email: '', password: '' })
const errors = reactive({})
const submitting = ref(false)

function clearErrors() {
  Object.keys(errors).forEach((k) => delete errors[k])
}

async function submit() {
  clearErrors()
  submitting.value = true

  const { ok, error } = await auth.login(form.email, form.password)

  submitting.value = false

  if (ok) {
    const redirect = route.query.redirect || redirectForRole(auth.role)
    router.push(redirect)
  } else if (error?.errors) {
    Object.entries(error.errors).forEach(([k, v]) => (errors[k] = Array.isArray(v) ? v[0] : v))
  } else {
    errors._global = error?.message ?? 'Login gagal. Periksa email dan password Anda.'
  }
}

function redirectForRole(role) {
  if (['dpc', 'dpd', 'dpp', 'super_admin'].includes(role)) return '/survey'
  return '/dashboard'
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <h1>ASPERDA</h1>
        <p>Masuk ke akun keanggotaan Anda</p>
      </div>

      <form class="auth-form" novalidate @submit.prevent="submit">
        <div class="field">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="nama@email.com"
            autocomplete="email"
            :class="{ invalid: errors.email }"
          />
          <small v-if="errors.email" class="err">{{ errors.email }}</small>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Password Anda"
            autocomplete="current-password"
            :class="{ invalid: errors.password }"
          />
          <small v-if="errors.password" class="err">{{ errors.password }}</small>
        </div>

        <p v-if="errors._global" class="err global">{{ errors._global }}</p>

        <button type="submit" :disabled="submitting" :aria-busy="submitting">
          <svg v-if="submitting" class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="18" height="18" aria-hidden="true">
            <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
          {{ submitting ? 'Masuk…' : 'Masuk' }}
        </button>
      </form>

      <div class="auth-footer">
        <p>Belum punya akun? <a href="/#daftar">Daftar di sini</a></p>
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
  max-width: 420px;
  background: #fff;
  border: 1px solid var(--line, #e5e7eb);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.08);
  overflow: hidden;
}
.auth-header {
  background: #1a3a5c;
  padding: 28px 32px;
  text-align: center;
}
.auth-header h1 {
  margin: 0 0 4px;
  color: #fff;
  font-size: 1.5rem;
  letter-spacing: 0.1em;
}
.auth-header p {
  margin: 0;
  color: rgba(255,255,255,0.65);
  font-size: 0.875rem;
}
.auth-form {
  padding: 28px 32px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
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
.auth-footer {
  padding: 16px 32px 24px;
  text-align: center;
  border-top: 1px solid #e5e7eb;
}
.auth-footer p { margin: 0; font-size: 0.875rem; color: #6b7280; }
.auth-footer a { color: #1a3a5c; font-weight: 600; }
</style>
