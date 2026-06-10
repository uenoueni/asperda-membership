<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMemberApi } from '@/api/member'
import { MemberStatus } from '@/constants/enums'

const router    = useRouter()
const memberApi = useMemberApi()

const member  = ref(null)
const loading = ref(true)
const error   = ref('')

const biayaDaftar = ref(500000)

const formattedBiaya = computed(() =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })
    .format(biayaDaftar.value)
)

onMounted(async () => {
  const { ok, data, error: err } = await memberApi.show()
  loading.value = false
  if (ok) {
    // Jika profil belum lengkap, kembali ke form
    if (!data.primary_branch) {
      router.replace('/lengkapi-profil')
      return
    }
    // Jika sudah punya pembayaran yang lunas, ke dashboard
    const paid = data.latest_payment?.status === 'paid'
    if (paid) {
      router.replace('/dashboard')
      return
    }
    member.value = data
  } else {
    error.value = err?.message ?? 'Gagal memuat data. Silakan muat ulang halaman.'
  }
})

function toPayment() {
  router.push('/bayar')
}
</script>

<template>
  <div class="page">
    <div class="card">
      <div class="card-header">
        <h1>ASPERDA</h1>
        <p>Ringkasan Pendaftaran</p>
      </div>

      <div v-if="loading" class="loading-state">
        <svg class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="28" height="28">
          <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="#1a3a5c" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
        <p>Memuat data…</p>
      </div>

      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="router.go(0)">Muat ulang</button>
      </div>

      <template v-else-if="member">
        <div class="body">
          <!-- Data Diri -->
          <section class="section">
            <div class="section-title">Data Diri</div>
            <div class="rows">
              <div class="row"><span class="label">Nama</span><span class="val">{{ member.user.name }}</span></div>
              <div class="row"><span class="label">Email</span><span class="val">{{ member.user.email }}</span></div>
              <div class="row"><span class="label">No. Telepon</span><span class="val">{{ member.user.phone }}</span></div>
              <div class="row"><span class="label">Nama Usaha</span><span class="val">{{ member.rental_name }}</span></div>
            </div>
          </section>

          <!-- Data Cabang -->
          <section class="section">
            <div class="section-title">Data Cabang</div>
            <div class="rows">
              <div class="row"><span class="label">Nama Cabang</span><span class="val">{{ member.primary_branch.branch_name }}</span></div>
              <div class="row"><span class="label">Provinsi</span><span class="val">{{ member.primary_branch.province_name }}</span></div>
              <div class="row"><span class="label">Kota / Kab</span><span class="val">{{ member.primary_branch.city_name }}</span></div>
              <div v-if="member.primary_branch.district_name" class="row">
                <span class="label">Kecamatan</span><span class="val">{{ member.primary_branch.district_name }}</span>
              </div>
              <div class="row"><span class="label">Alamat</span><span class="val">{{ member.primary_branch.address }}</span></div>
              <div class="row"><span class="label">Jumlah Unit</span><span class="val">{{ member.primary_branch.unit_count }} unit</span></div>
            </div>
          </section>

          <!-- Rekening Bank -->
          <section class="section">
            <div class="section-title">Rekening untuk Refund</div>
            <div class="rows">
              <div class="row"><span class="label">Bank</span><span class="val">{{ member.user.bank_name }}</span></div>
              <div class="row"><span class="label">No. Rekening</span><span class="val">{{ member.user.bank_account_no }}</span></div>
              <div class="row"><span class="label">Nama Pemilik</span><span class="val">{{ member.user.bank_account_name }}</span></div>
            </div>
          </section>

          <!-- Tagihan -->
          <section class="section billing">
            <div class="section-title">Biaya Pendaftaran</div>
            <div class="billing-amount">
              <span>Biaya Registrasi</span>
              <span class="amount">{{ formattedBiaya }}</span>
            </div>
            <p class="billing-note">
              Pembayaran diproses melalui Midtrans (transfer bank, virtual account, QRIS, dll).
              Keanggotaan aktif setelah verifikasi lapangan oleh petugas.
            </p>
          </section>
        </div>

        <div class="footer">
          <button class="btn-secondary" @click="router.push('/lengkapi-profil')">
            Edit Data
          </button>
          <button class="btn-primary" @click="toPayment">
            Bayar Sekarang →
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 100vh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  background: #f4f6f9;
  padding: 2rem 1.5rem;
}
.card {
  width: 100%;
  max-width: 560px;
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
.loading-state, .error-state {
  padding: 48px 32px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  color: #6b7280;
}
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 0.8s linear infinite; }
.body { padding: 24px 32px 0; display: flex; flex-direction: column; gap: 1.25rem; }
.section { display: flex; flex-direction: column; gap: 0.5rem; }
.section-title {
  font-weight: 700;
  font-size: 0.78rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6b7280;
  padding-bottom: 0.35rem;
  border-bottom: 1px solid #e5e7eb;
}
.rows { display: flex; flex-direction: column; gap: 0.4rem; }
.row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 1rem;
  font-size: 0.9rem;
}
.label { color: #6b7280; flex-shrink: 0; }
.val { font-weight: 500; color: #111827; text-align: right; }
.billing { background: #f8fafc; border-radius: 12px; padding: 1rem; }
.billing-amount {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1rem;
  margin-top: 0.5rem;
}
.amount { font-size: 1.5rem; font-weight: 800; color: #1a3a5c; }
.billing-note { margin: 0.75rem 0 0; font-size: 0.8rem; color: #6b7280; line-height: 1.55; }
.footer {
  display: flex;
  gap: 0.75rem;
  padding: 24px 32px;
}
.btn-primary, .btn-secondary {
  flex: 1;
  padding: 0.875rem;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: filter 200ms ease;
}
.btn-primary { background: #1a3a5c; color: #fff; }
.btn-primary:hover { filter: brightness(1.1); }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }

@media (max-width: 480px) {
  .body, .footer { padding-left: 16px; padding-right: 16px; }
  .footer { flex-direction: column-reverse; }
}
</style>
