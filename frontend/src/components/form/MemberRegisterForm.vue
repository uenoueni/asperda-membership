<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthApi } from '@/api/auth'

const router = useRouter()
const authApi = useAuthApi()

// Field data dasar sesuai PRD F-08.
const fields = [
  { key: 'name', label: 'Nama Lengkap', type: 'text', placeholder: 'Nama sesuai KTP', col: 1 },
  { key: 'rental_name', label: 'Nama Usaha Rental', type: 'text', placeholder: 'mis. CV Rental Jaya', col: 1 },
  { key: 'email', label: 'Email', type: 'email', placeholder: 'nama@email.com', col: 1 },
  { key: 'phone', label: 'Nomor Telepon', type: 'tel', placeholder: '08xxxxxxxxxx', col: 1 },
]

const form = reactive(Object.fromEntries(fields.map((f) => [f.key, ''])))
const errors = reactive({})

function clearErrors() {
  Object.keys(errors).forEach((k) => delete errors[k])
}

function validate() {
  clearErrors()
  if (!form.name.trim()) errors.name = 'Nama lengkap wajib diisi.'
  if (!form.rental_name.trim()) errors.rental_name = 'Nama usaha rental wajib diisi.'
  if (!form.email.trim()) errors.email = 'Email wajib diisi.'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errors.email = 'Format email tidak valid.'
  if (!form.phone.trim()) errors.phone = 'Nomor telepon wajib diisi.'
  else if (!/^(\+62|08)\d{7,13}$/.test(form.phone.replace(/[\s-]/g, '')))
    errors.phone = 'Gunakan format Indonesia (08… atau +62…).'
  return Object.keys(errors).length === 0
}

async function submit() {
  if (!validate()) return

  const { ok, error } = await authApi.register({ ...form })

  if (ok) {
    router.push({ path: '/verifikasi', query: { email: form.email } })
  } else if (error?.errors) {
    // Map error per-field dari envelope { errors: { field: [pesan] } }
    Object.entries(error.errors).forEach(([k, v]) => (errors[k] = Array.isArray(v) ? v[0] : v))
  } else {
    errors._global = error?.message ?? 'Gagal mengirim pendaftaran. Silakan coba lagi.'
  }
}
</script>

<template>
  <form class="reg-form" novalidate @submit.prevent="submit">
    <div class="grid">
      <div v-for="f in fields" :key="f.key" class="field" :class="{ wide: f.col === 1 }">
        <label :for="f.key">{{ f.label }}</label>
        <input
          :id="f.key"
          v-model="form[f.key]"
          :type="f.type"
          :placeholder="f.placeholder"
          :class="{ invalid: errors[f.key] }"
        />
        <small v-if="errors[f.key]" class="err">{{ errors[f.key] }}</small>
      </div>
    </div>

    <p v-if="errors._global" class="err global">{{ errors._global }}</p>

    <button type="submit" :disabled="authApi.loading.value" :aria-busy="authApi.loading.value">
      <svg
        v-if="authApi.loading.value"
        class="spinner"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        width="18"
        height="18"
        aria-hidden="true"
      >
        <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
      {{ authApi.loading.value ? 'Mengirim…' : 'Daftar Sekarang' }}
    </button>
    <small class="note">Setelah verifikasi email, Anda akan melengkapi data cabang dan informasi rekening bank.</small>
  </form>
</template>

<style scoped>
.reg-form { display: flex; flex-direction: column; gap: 1.1rem; }
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field.wide { grid-column: 1 / -1; }
label { font-size: 0.875rem; font-weight: 600; color: var(--primary); }
input {
  padding: 0.75rem 0.9rem;
  border: 1.5px solid var(--line);
  border-radius: 12px;
  font-size: 1rem;
  background: #fff;
  font-family: inherit;
  transition: border-color var(--dur, 200ms) var(--ease, ease), box-shadow var(--dur, 200ms) var(--ease, ease);
}
input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.12); }
input.invalid { border-color: var(--danger); box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1); }
.err { color: var(--danger); font-size: 0.8rem; }
.err.global { margin: 0; }
.success { color: var(--ok); font-weight: 600; margin: 0; }
button {
  margin-top: 0.25rem;
  padding: 0.9rem 1.5rem;
  background: var(--accent);
  color: #1a1206;
  font-size: 1rem;
  font-weight: 700;
  font-family: 'Lexend', system-ui, sans-serif;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: filter var(--dur, 200ms) var(--ease, ease), transform var(--dur, 200ms) var(--ease, ease), box-shadow var(--dur, 200ms) var(--ease, ease);
  box-shadow: 0 2px 8px rgba(200, 161, 74, 0.25);
}
button:hover:not(:disabled) { filter: brightness(1.08); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(200, 161, 74, 0.35); }
button:active:not(:disabled) { transform: translateY(0); }
button:disabled { opacity: 0.6; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 0.8s linear infinite; flex-shrink: 0; }
.note { color: var(--muted); font-size: 0.78rem; }

@media (max-width: 560px) {
  .grid { grid-template-columns: 1fr; }
}
</style>
