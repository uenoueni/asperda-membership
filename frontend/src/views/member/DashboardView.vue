<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMemberApi } from '@/api/member'
import { useAuthStore } from '@/stores/auth'
import { MemberStatus } from '@/constants/enums'

const router    = useRouter()
const auth      = useAuthStore()
const memberApi = useMemberApi()

const member  = ref(null)
const loading = ref(true)

const status = computed(() => member.value?.status)

const statusLabel = computed(() => ({
  [MemberStatus.PENDING_VERIFICATION]: 'Belum Aktif',
  [MemberStatus.WAITING_SURVEY]:       'Menunggu Verifikasi',
  [MemberStatus.ACTIVE]:               'Aktif',
  [MemberStatus.REJECTED]:             'Ditolak',
  [MemberStatus.EXPIRED]:              'Kadaluarsa',
}[status.value] ?? status.value))

const formattedAmount = (amount) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount)

onMounted(async () => {
  const { ok, data } = await memberApi.show()
  loading.value = false

  if (!ok) return

  // Profil belum lengkap → arahkan ke form
  if (!data.primary_branch) {
    router.replace('/lengkapi-profil')
    return
  }

  member.value = data
})

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="page">
    <!-- Navbar -->
    <nav class="navbar">
      <span class="brand">ASPERDA</span>
      <div class="nav-right">
        <span class="user-name">{{ auth.user?.name }}</span>
        <button class="btn-logout" @click="logout">Keluar</button>
      </div>
    </nav>

    <main class="container">
      <!-- Loading -->
      <div v-if="loading" class="state-center">
        <svg class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="32" height="32">
          <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="#1a3a5c" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
        <p>Memuat data keanggotaan…</p>
      </div>

      <template v-else-if="member">
        <!-- Status Header Card -->
        <div class="status-card" :class="status">
          <div class="status-left">
            <div class="status-badge">{{ statusLabel }}</div>
            <h2>{{ member.rental_name }}</h2>
            <p class="membership-no">{{ member.membership_no ?? '—' }}</p>
          </div>
          <div class="status-icon-wrap">
            <!-- Active -->
            <svg v-if="status === MemberStatus.ACTIVE" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <!-- Waiting Survey -->
            <svg v-else-if="status === MemberStatus.WAITING_SURVEY" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <!-- Pending / Other -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
        </div>

        <!-- CTA: Perlu bayar -->
        <div v-if="status === MemberStatus.PENDING_VERIFICATION" class="info-card cta">
          <div class="info-body">
            <h3>Selesaikan Pembayaran</h3>
            <p>
              Data Anda sudah tersimpan. Lanjutkan dengan membayar biaya pendaftaran untuk mengaktifkan proses verifikasi keanggotaan.
            </p>
          </div>
          <button class="btn-primary" @click="router.push('/review')">Bayar Sekarang →</button>
        </div>

        <!-- Info: Menunggu Survey -->
        <div v-else-if="status === MemberStatus.WAITING_SURVEY" class="info-card survey">
          <h3>Sedang Diverifikasi</h3>
          <p>
            Pembayaran Anda diterima. Tim petugas ASPERDA akan melakukan survei ke lokasi usaha Anda.
            Anda akan mendapat notifikasi email setelah proses selesai.
          </p>
          <div class="survey-timeline">
            <div class="timeline-item done">
              <span class="dot"></span>
              <span>Pendaftaran dikirim</span>
            </div>
            <div class="timeline-item done">
              <span class="dot"></span>
              <span>Pembayaran diterima</span>
            </div>
            <div class="timeline-item active">
              <span class="dot"></span>
              <span>Survei lapangan oleh DPC</span>
            </div>
            <div class="timeline-item">
              <span class="dot"></span>
              <span>Verifikasi DPD</span>
            </div>
            <div class="timeline-item">
              <span class="dot"></span>
              <span>Persetujuan DPP</span>
            </div>
            <div class="timeline-item">
              <span class="dot"></span>
              <span>Keanggotaan Aktif</span>
            </div>
          </div>
        </div>

        <!-- Info: Aktif -->
        <template v-else-if="status === MemberStatus.ACTIVE">
          <!-- Keanggotaan Info -->
          <div class="grid-2">
            <div class="info-card">
              <div class="card-label">Berlaku Hingga</div>
              <div class="card-value">{{ member.expires_at ?? '—' }}</div>
            </div>
            <div class="info-card">
              <div class="card-label">Periode</div>
              <div class="card-value">{{ member.period_year }}</div>
            </div>
          </div>

          <!-- Sertifikat -->
          <div v-if="member.latest_certificate" class="info-card cert">
            <div class="info-body">
              <div class="card-label">Sertifikat Keanggotaan</div>
              <div class="card-value sm">{{ member.latest_certificate.cert_number }}</div>
              <div class="card-sub">Berlaku s.d. {{ member.latest_certificate.valid_until }}</div>
            </div>
            <a :href="member.latest_certificate.download_url" class="btn-outline">Unduh PDF</a>
          </div>
        </template>

        <!-- Data Cabang -->
        <div class="info-card">
          <div class="section-title">Data Cabang Utama</div>
          <div class="rows">
            <div class="row"><span class="lbl">Nama Cabang</span><span class="val">{{ member.primary_branch.branch_name }}</span></div>
            <div class="row"><span class="lbl">Wilayah</span><span class="val">{{ member.primary_branch.city_name }}, {{ member.primary_branch.province_name }}</span></div>
            <div class="row"><span class="lbl">Alamat</span><span class="val">{{ member.primary_branch.address }}</span></div>
            <div class="row"><span class="lbl">Jumlah Unit</span><span class="val">{{ member.primary_branch.unit_count }} unit</span></div>
          </div>
        </div>

        <!-- Pembayaran Terakhir -->
        <div v-if="member.latest_payment" class="info-card">
          <div class="section-title">Pembayaran Terakhir</div>
          <div class="rows">
            <div class="row"><span class="lbl">Status</span>
              <span class="badge" :class="member.latest_payment.status">{{ member.latest_payment.status }}</span>
            </div>
            <div class="row"><span class="lbl">Nominal</span><span class="val">{{ formattedAmount(member.latest_payment.amount) }}</span></div>
            <div class="row" v-if="member.latest_payment.paid_at"><span class="lbl">Dibayar</span><span class="val">{{ new Date(member.latest_payment.paid_at).toLocaleDateString('id-ID') }}</span></div>
          </div>
        </div>
      </template>
    </main>
  </div>
</template>

<style scoped>
.page { min-height: 100vh; background: #f4f6f9; }

/* Navbar */
.navbar {
  background: #1a3a5c;
  padding: 0 24px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 10;
}
.brand { color: #fff; font-weight: 800; font-size: 1rem; letter-spacing: 0.1em; }
.nav-right { display: flex; align-items: center; gap: 1rem; }
.user-name { color: rgba(255,255,255,0.8); font-size: 0.875rem; }
.btn-logout {
  background: rgba(255,255,255,0.12);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.2);
  padding: 0.35rem 0.75rem;
  border-radius: 8px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: background 150ms;
}
.btn-logout:hover { background: rgba(255,255,255,0.2); }

.container { max-width: 640px; margin: 0 auto; padding: 24px 16px 48px; display: flex; flex-direction: column; gap: 1rem; }

.state-center { display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 60px 0; color: #6b7280; }
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 0.8s linear infinite; }

/* Status Card */
.status-card {
  border-radius: 16px;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #fff;
  background: #1a3a5c;
}
.status-card.active          { background: linear-gradient(135deg, #1a5c3a, #2d9e5d); }
.status-card.waiting_survey  { background: linear-gradient(135deg, #7c5c1a, #d97706); }
.status-card.rejected,
.status-card.expired         { background: linear-gradient(135deg, #5c1a1a, #c03a2b); }
.status-badge {
  display: inline-block;
  background: rgba(255,255,255,0.2);
  padding: 3px 10px;
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}
.status-left h2 { margin: 0 0 4px; font-size: 1.25rem; }
.membership-no { margin: 0; opacity: 0.8; font-size: 0.875rem; font-family: monospace; }
.status-icon-wrap { opacity: 0.3; }

/* Info Cards */
.info-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.info-card.cta  { border-left: 4px solid #1a3a5c; }
.info-card.survey { border-left: 4px solid #d97706; }
.info-card.cert { flex-direction: row; align-items: center; justify-content: space-between; gap: 1rem; }
.info-body { flex: 1; }
.section-title { font-weight: 700; font-size: 0.78rem; letter-spacing: 0.08em; text-transform: uppercase; color: #6b7280; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.card-label { font-size: 0.78rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
.card-value { font-size: 1.5rem; font-weight: 800; color: #1a3a5c; }
.card-value.sm { font-size: 1rem; font-family: monospace; }
.card-sub { font-size: 0.8rem; color: #6b7280; margin-top: 2px; }
.info-card h3 { margin: 0; font-size: 1rem; color: #1a3a5c; }
.info-card p { margin: 0; font-size: 0.875rem; color: #374151; line-height: 1.6; }

/* Survey Timeline */
.survey-timeline { display: flex; flex-direction: column; gap: 0.6rem; margin-top: 0.5rem; }
.timeline-item { display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem; color: #9ca3af; }
.timeline-item.done  { color: #16a34a; }
.timeline-item.active { color: #d97706; font-weight: 600; }
.dot {
  width: 10px; height: 10px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

/* Rows */
.rows { display: flex; flex-direction: column; gap: 0.5rem; }
.row { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; font-size: 0.875rem; }
.lbl { color: #6b7280; flex-shrink: 0; }
.val { font-weight: 500; color: #111827; text-align: right; }
.badge {
  padding: 2px 8px;
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}
.badge.paid     { background: #dcfce7; color: #16a34a; }
.badge.pending  { background: #fef9c3; color: #92400e; }
.badge.failed,
.badge.expired  { background: #fee2e2; color: #c03a2b; }

/* Grid */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

/* Buttons */
.btn-primary {
  padding: 0.8rem 1.25rem;
  background: #1a3a5c;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: filter 150ms;
  align-self: flex-start;
}
.btn-primary:hover { filter: brightness(1.1); }
.btn-outline {
  padding: 0.6rem 1rem;
  border: 1.5px solid #1a3a5c;
  border-radius: 10px;
  color: #1a3a5c;
  font-size: 0.85rem;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
  transition: background 150ms;
  flex-shrink: 0;
}
.btn-outline:hover { background: rgba(26,58,92,0.05); }

@media (max-width: 480px) {
  .grid-2 { grid-template-columns: 1fr; }
  .info-card.cert { flex-direction: column; align-items: flex-start; }
  .status-icon-wrap { display: none; }
}
</style>
