<script setup>
import { ref, onMounted, nextTick } from 'vue'
import MemberRegisterForm from '@/components/form/MemberRegisterForm.vue'

const menuOpen = ref(false)

const icons = {
  users: `<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
  award: `<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>`,
  network: `<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>`,
  shield: `<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>`,
  book: `<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>`,
  globe: `<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>`,
  briefcase: `<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>`,
  help: `<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>`,
  rss: `<path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/>`,
}

const iconSvg = (name, size = 24) => {
  const inner = icons[name] ?? ''
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${inner}</svg>`
}

const tujuan = [
  'Mewadahi pengusaha rental mobil daerah dalam organisasi yang solid dan profesional',
  'Meningkatkan standar pelayanan dan etika usaha',
  'Memberikan perlindungan dan advokasi terhadap kepentingan anggota',
  'Mendorong pertumbuhan usaha yang sehat dan berkelanjutan',
  'Memperkuat posisi tawar pengusaha rental dalam kebijakan publik',
]

const jenis = [
  { title: 'Anggota Biasa', desc: 'Pengusaha rental mobil daerah yang memenuhi syarat administratif dan operasional.', icon: 'users' },
  { title: 'Anggota Kehormatan', desc: 'Tokoh, pembina, atau pihak yang berkontribusi terhadap pengembangan organisasi.', icon: 'award' },
  { title: 'Anggota Mitra', desc: 'Pihak pendukung seperti asuransi, leasing, atau penyedia layanan industri rental.', icon: 'network' },
]

const manfaat = [
  { icon: 'shield', text: 'Penguatan legalitas dan kredibilitas usaha' },
  { icon: 'book', text: 'Akses pelatihan dan peningkatan kompetensi' },
  { icon: 'globe', text: 'Perluasan jaringan bisnis nasional' },
  { icon: 'briefcase', text: 'Informasi peluang kerja sama dan kemitraan' },
  { icon: 'help', text: 'Dukungan dalam menghadapi permasalahan usaha' },
  { icon: 'rss', text: 'Publikasi usaha melalui media organisasi' },
]

const hak = [
  'Mendapat perlindungan dan advokasi organisasi',
  'Mengikuti program pelatihan dan kegiatan resmi',
  'Mendapatkan informasi regulasi dan kebijakan terbaru',
  'Menggunakan atribut dan identitas resmi anggota',
  'Memilih dan dipilih dalam kepengurusan (sesuai ketentuan)',
  'Akses jaringan nasional anggota ASPERDA',
]

const kewajiban = [
  'Mematuhi Anggaran Dasar dan Anggaran Rumah Tangga',
  'Menjaga nama baik organisasi',
  'Menjalankan usaha secara profesional dan etis',
  'Membayar iuran keanggotaan sesuai ketentuan',
  'Mendukung program dan kegiatan organisasi',
]

const proses = [
  'Mengisi formulir pendaftaran resmi',
  'Melengkapi dokumen usaha',
  'Verifikasi oleh pengurus',
  'Pembayaran iuran keanggotaan',
  'Penerbitan sertifikat / kartu anggota',
]

const stats = [
  { value: '500+', label: 'Anggota Aktif' },
  { value: '34', label: 'Provinsi' },
  { value: '2015', label: 'Berdiri Sejak' },
]

onMounted(async () => {
  await nextTick()
  const els = document.querySelectorAll('[data-reveal]')
  if (!window.IntersectionObserver) {
    els.forEach((el) => el.classList.add('revealed'))
    return
  }
  const io = new IntersectionObserver(
    (entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('revealed'); io.unobserve(e.target) } }),
    { threshold: 0.1 }
  )
  els.forEach((el) => io.observe(el))
})
</script>

<template>
  <div class="page">

    <!-- Nav -->
    <header class="nav">
      <div class="wrap nav-inner">
        <a href="#top" class="brand">ASPERDA</a>
        <nav class="links" aria-label="Navigasi utama">
          <a href="#keanggotaan">Keanggotaan</a>
          <a href="#manfaat">Manfaat</a>
          <a href="#proses">Proses</a>
          <a href="#daftar" class="btn-ghost">Daftar</a>
        </nav>
        <button
          class="hamburger"
          :class="{ open: menuOpen }"
          :aria-expanded="menuOpen"
          aria-controls="mobile-nav"
          aria-label="Buka menu navigasi"
          @click="menuOpen = !menuOpen"
        >
          <span></span><span></span><span></span>
        </button>
      </div>
      <nav v-if="menuOpen" id="mobile-nav" class="mobile-menu" aria-label="Menu mobile">
        <a href="#keanggotaan" @click="menuOpen = false">Keanggotaan</a>
        <a href="#manfaat" @click="menuOpen = false">Manfaat</a>
        <a href="#proses" @click="menuOpen = false">Proses</a>
        <a href="#daftar" class="mobile-cta" @click="menuOpen = false">Daftar Anggota</a>
      </nav>
    </header>

    <!-- Hero -->
    <section id="top" class="hero">
      <div class="hero-bg-pattern" aria-hidden="true"></div>
      <div class="hero-glow" aria-hidden="true"></div>
      <div class="wrap hero-inner">
        <span class="eyebrow">
          <span class="eyebrow-rule" aria-hidden="true"></span>
          Asosiasi Pengusaha Rental Kendaraan Indonesia
          <span class="eyebrow-rule" aria-hidden="true"></span>
        </span>
        <h1>Bergabung dengan ASPERDA</h1>
        <p class="lead">
          Rumah besar bagi pengusaha rental mobil daerah untuk berkembang, berkolaborasi,
          serta memperoleh perlindungan dan pembinaan usaha yang profesional dan berdaya saing.
        </p>
        <div class="cta">
          <a href="#daftar" class="btn-primary">Daftar Anggota</a>
          <a href="#keanggotaan" class="btn-line">Pelajari Keanggotaan</a>
        </div>
      </div>
      <div class="hero-stats-bar">
        <div class="wrap hero-stats-inner">
          <div v-for="s in stats" :key="s.label" class="hero-stat">
            <strong>{{ s.value }}</strong>
            <span>{{ s.label }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Tujuan -->
    <section id="keanggotaan" class="wrap section">
      <h2 class="section-title" data-reveal>Tujuan Keanggotaan</h2>
      <p class="sub" data-reveal style="--delay:80ms">
        Keanggotaan ASPERDA dibangun untuk memajukan ekosistem usaha rental yang tertib dan profesional.
      </p>
      <ul class="checklist" data-reveal style="--delay:160ms">
        <li v-for="(t, i) in tujuan" :key="i">{{ t }}</li>
      </ul>
    </section>

    <!-- Jenis -->
    <section class="band">
      <div class="wrap section">
        <h2 class="section-title centered" data-reveal>Jenis Keanggotaan</h2>
        <div class="cards">
          <article
            v-for="(j, i) in jenis"
            :key="i"
            class="card"
            data-reveal
            :style="`--delay: ${(i + 1) * 90}ms`"
          >
            <div class="card-icon" v-html="iconSvg(j.icon)"></div>
            <h3>{{ j.title }}</h3>
            <p>{{ j.desc }}</p>
          </article>
        </div>
      </div>
    </section>

    <!-- Manfaat -->
    <section id="manfaat" class="wrap section">
      <h2 class="section-title" data-reveal>Manfaat Keanggotaan</h2>
      <div class="benefits">
        <div
          v-for="(m, i) in manfaat"
          :key="i"
          class="benefit"
          data-reveal
          :style="`--delay: ${i * 55}ms`"
        >
          <div class="benefit-icon" v-html="iconSvg(m.icon, 18)"></div>
          <span>{{ m.text }}</span>
        </div>
      </div>
    </section>

    <!-- Hak & Kewajiban -->
    <section class="band">
      <div class="wrap section two-col">
        <div data-reveal>
          <h2 class="section-title">Hak Anggota</h2>
          <ul class="checklist">
            <li v-for="(h, i) in hak" :key="i">{{ h }}</li>
          </ul>
        </div>
        <div data-reveal style="--delay:110ms">
          <h2 class="section-title">Kewajiban Anggota</h2>
          <ul class="checklist">
            <li v-for="(k, i) in kewajiban" :key="i">{{ k }}</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Proses -->
    <section id="proses" class="wrap section">
      <h2 class="section-title centered" data-reveal>Proses Pendaftaran</h2>
      <div class="timeline-wrap" data-reveal style="--delay:100ms">
        <ol class="timeline">
          <li v-for="(p, i) in proses" :key="i" class="tl-item">
            <div class="tl-num">{{ i + 1 }}</div>
            <p class="tl-text">{{ p }}</p>
          </li>
        </ol>
      </div>
    </section>

    <!-- Daftar -->
    <section id="daftar" class="band">
      <div class="wrap section register">
        <div class="register-intro" data-reveal>
          <h2 class="section-title">Formulir Pendaftaran Anggota</h2>
          <p class="sub">
            Lengkapi data dasar berikut untuk memulai. Setelah pendaftaran, Anda akan menerima
            email verifikasi untuk melanjutkan ke kelengkapan data dan pembayaran.
          </p>
          <div class="trust-strip">
            <div v-for="s in stats" :key="s.label" class="trust-item">
              <strong>{{ s.value }}</strong>
              <span>{{ s.label }}</span>
            </div>
          </div>
        </div>
        <div class="register-card" data-reveal style="--delay:130ms">
          <MemberRegisterForm />
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="wrap foot-inner">
        <div>
          <strong class="brand">ASPERDA</strong>
          <p>Asosiasi Pengusaha Rental Kendaraan Indonesia</p>
        </div>
        <div class="foot-contact">
          <p>Graha Mampang Lt. 1, Jakarta Selatan, 12790</p>
          <p>Hotline (WhatsApp): 0859-2373-1419</p>
          <p>info@asperdaindonesia.com</p>
        </div>
      </div>
      <div class="copy">© 2025 Asperda Indonesia. All rights reserved.</div>
    </footer>

  </div>
</template>

<style scoped>
/* ─── Layout helpers ─── */
.wrap { width: 100%; max-width: var(--max); margin: 0 auto; padding: 0 1.25rem; }
.section { padding: 4.5rem 1.25rem; }
.band { background: var(--bg-soft); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
.sub { color: var(--muted); max-width: 60ch; }

/* ─── Section titles ─── */
.section-title {
  position: relative;
  padding-bottom: 0.875rem;
  margin-bottom: 1.25rem;
}
.section-title::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0;
  width: 40px; height: 3px;
  border-radius: 2px;
  background: var(--accent);
}
.section-title.centered { text-align: center; }
.section-title.centered::after { left: 50%; transform: translateX(-50%); }

/* ─── Scroll reveal ─── */
[data-reveal] {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.55s var(--ease), transform 0.55s var(--ease);
  transition-delay: var(--delay, 0ms);
}
[data-reveal].revealed { opacity: 1; transform: none; }

/* ─── Nav ─── */
.nav {
  position: sticky; top: 0; z-index: 100;
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-bottom: 1px solid var(--line);
}
.nav-inner { display: flex; align-items: center; justify-content: space-between; height: 64px; }
.brand {
  font-family: 'Cormorant Garamond', serif;
  font-weight: 600;
  letter-spacing: 0.1em;
  color: var(--primary);
  font-size: 1.35rem;
}
.links { display: flex; align-items: center; gap: 1.5rem; }
.links a {
  font-weight: 600;
  color: var(--ink);
  font-size: 0.95rem;
  padding: 0.25rem 0;
  position: relative;
  transition: color var(--dur) var(--ease);
  cursor: pointer;
}
.links a::after {
  content: '';
  position: absolute;
  bottom: -2px; left: 0; right: 0;
  height: 2px;
  background: var(--accent);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform var(--dur) var(--ease);
}
.links a:hover { color: var(--primary); }
.links a:hover::after { transform: scaleX(1); }
.btn-ghost {
  padding: 0.45rem 1.1rem;
  border: 1.5px solid var(--accent);
  border-radius: 999px;
  color: var(--accent) !important;
  font-weight: 600;
  transition: background var(--dur) var(--ease), color var(--dur) var(--ease);
}
.btn-ghost:hover { background: var(--accent); color: #fff !important; }
.btn-ghost::after { display: none !important; }

/* ─── Hamburger ─── */
.hamburger {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  flex-direction: column;
  gap: 5px;
  border-radius: 8px;
}
.hamburger span {
  display: block;
  width: 22px; height: 2px;
  background: var(--primary);
  border-radius: 2px;
  transition: transform 0.22s var(--ease), opacity 0.22s var(--ease);
}
.hamburger.open span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.hamburger.open span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }
.mobile-menu {
  border-top: 1px solid var(--line);
  background: #fff;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}
.mobile-menu a {
  display: block;
  padding: 0.9rem 1.5rem;
  font-weight: 600;
  color: var(--ink);
  border-bottom: 1px solid var(--line);
  transition: background var(--dur) var(--ease);
  font-size: 1rem;
}
.mobile-menu a:last-child { border-bottom: none; }
.mobile-menu a:hover { background: var(--bg-soft); }
.mobile-menu .mobile-cta { color: var(--accent); background: rgba(192, 58, 43, 0.04); }

/* ─── Hero ─── */
.hero {
  background: linear-gradient(155deg, var(--primary-mid) 0%, var(--primary) 50%, var(--primary-dark) 100%);
  color: #fff;
  position: relative;
  overflow: hidden;
}
.hero-bg-pattern {
  position: absolute; inset: 0;
  background-image: radial-gradient(circle, rgba(255, 255, 255, 0.09) 1px, transparent 1px);
  background-size: 30px 30px;
  pointer-events: none;
}
.hero-glow {
  position: absolute;
  top: -30%; right: -8%;
  width: 55%; aspect-ratio: 1;
  background: radial-gradient(ellipse at center, rgba(192, 58, 43, 0.18) 0%, transparent 70%);
  pointer-events: none;
}
.hero-inner {
  padding: 5.5rem 1.25rem 3.5rem;
  text-align: center;
  position: relative;
  animation: fadeUp 0.65s 0.2s var(--ease) both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: none; }
}
.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 0.875rem;
  font-size: 0.72rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--accent);
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  margin-bottom: 1.25rem;
}
.eyebrow-rule { display: block; width: 28px; height: 1px; background: currentColor; opacity: 0.65; }
.hero h1 { color: #fff; font-size: clamp(2.75rem, 6vw, 4.25rem); margin-bottom: 0.875rem; font-weight: 600; }
.lead { color: rgba(255, 255, 255, 0.8); max-width: 60ch; margin: 0 auto 2.25rem; font-size: 1.1rem; }
.cta { display: flex; gap: 0.875rem; justify-content: center; flex-wrap: wrap; }
.btn-primary {
  background: var(--accent);
  color: #ffffff;
  padding: 0.9rem 1.85rem;
  border-radius: var(--radius);
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.02em;
  box-shadow: 0 4px 16px rgba(192, 58, 43, 0.38);
  transition: filter var(--dur) var(--ease), transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease);
}
.btn-primary:hover { filter: brightness(1.08); transform: translateY(-2px); box-shadow: 0 8px 28px rgba(192, 58, 43, 0.5); }
.btn-primary:active { transform: translateY(0); }
.btn-line {
  border: 1.5px solid rgba(255, 255, 255, 0.45);
  color: #fff;
  padding: 0.9rem 1.85rem;
  border-radius: var(--radius);
  font-weight: 500;
  font-size: 0.95rem;
  transition: background var(--dur) var(--ease), border-color var(--dur) var(--ease);
}
.btn-line:hover { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.75); }

/* hero stats bar */
.hero-stats-bar {
  background: rgba(0, 0, 0, 0.22);
  backdrop-filter: blur(6px);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  position: relative;
  animation: fadeUp 0.65s 0.7s var(--ease) both;
}
.hero-stats-inner {
  display: flex;
  justify-content: center;
  gap: 3.5rem;
  padding: 1.35rem 1.25rem;
}
.hero-stat { display: flex; flex-direction: column; align-items: center; gap: 0.15rem; }
.hero-stat strong {
  font-family: 'Cormorant Garamond', serif;
  font-size: 2rem;
  font-weight: 600;
  color: var(--accent);
  line-height: 1;
}
.hero-stat span { font-size: 0.75rem; letter-spacing: 0.07em; color: rgba(255, 255, 255, 0.65); text-transform: uppercase; }

/* ─── Checklist ─── */
.checklist { display: flex; flex-direction: column; margin-top: 0.5rem; }
.checklist li {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.55rem 0;
  color: var(--ink);
  border-bottom: 1px solid var(--line);
  line-height: 1.5;
}
.checklist li:last-child { border-bottom: none; }
.checklist li::before {
  content: '';
  width: 22px; height: 22px;
  flex-shrink: 0;
  margin-top: 1px;
  border-radius: 50%;
  background:
    url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg>")
    center / 12px no-repeat var(--primary);
}

/* ─── Cards ─── */
.cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.25rem;
  margin-top: 2rem;
}
.card {
  background: #fff;
  border: 1px solid var(--line);
  border-top: 3px solid var(--accent);
  border-radius: var(--radius);
  padding: 1.85rem 1.75rem;
  box-shadow: var(--shadow-sm);
  transition: transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease);
  cursor: default;
}
.card:hover { transform: translateY(-5px); box-shadow: var(--shadow); }
.card-icon {
  width: 52px; height: 52px;
  border-radius: 14px;
  background: rgba(26, 58, 92, 0.07);
  display: grid;
  place-items: center;
  margin-bottom: 1.1rem;
  color: var(--primary);
  transition: background var(--dur) var(--ease);
}
.card:hover .card-icon { background: rgba(26, 58, 92, 0.13); }
.card-icon :deep(svg) { display: block; }
.card h3 { font-size: 1.1rem; margin-bottom: 0.4rem; }
.card p { color: var(--muted); margin: 0; font-size: 0.95rem; line-height: 1.55; }

/* ─── Benefits ─── */
.benefits {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.25rem 2.5rem;
  margin-top: 2rem;
}
.benefit {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.75rem;
  font-weight: 500;
  border-radius: var(--radius-sm);
  transition: background var(--dur) var(--ease);
}
.benefit:hover { background: var(--bg-soft); }
.benefit-icon {
  width: 38px; height: 38px;
  border-radius: 10px;
  background: rgba(192, 58, 43, 0.09);
  display: grid;
  place-items: center;
  flex-shrink: 0;
  color: var(--accent);
}
.benefit-icon :deep(svg) { display: block; }

/* ─── Two col ─── */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; }

/* ─── Timeline ─── */
.timeline-wrap { margin-top: 2.5rem; }
.timeline {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  list-style: none;
  padding: 0; margin: 0;
}
.tl-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 0 0.5rem;
  position: relative;
}
.tl-item:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 22px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: linear-gradient(to right, rgba(26, 58, 92, 0.4), rgba(26, 58, 92, 0.08));
  z-index: 0;
}
.tl-num {
  width: 44px; height: 44px;
  border-radius: 50%;
  background: var(--primary);
  color: #fff;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  font-size: 1rem;
  display: grid;
  place-items: center;
  margin-bottom: 0.875rem;
  box-shadow: 0 4px 14px rgba(29, 45, 68, 0.32);
  position: relative; z-index: 1;
}
.tl-text {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--ink);
  line-height: 1.45;
  margin: 0;
}

/* ─── Register ─── */
.register {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: 3rem;
  align-items: start;
}
.register-card {
  background: #fff;
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 2.25rem;
  box-shadow: var(--shadow);
}

/* ─── Trust strip ─── */
.trust-strip {
  display: flex;
  margin-top: 2rem;
  border: 1px solid var(--line);
  border-radius: var(--radius-sm);
  overflow: hidden;
}
.trust-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.1rem 0.75rem;
  gap: 0.2rem;
  border-right: 1px solid var(--line);
}
.trust-item:last-child { border-right: none; }
.trust-item strong {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.75rem;
  font-weight: 600;
  color: var(--primary);
  line-height: 1;
}
.trust-item span { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }

/* ─── Footer ─── */
.footer { background: var(--primary-dark); color: rgba(200, 215, 235, 0.85); }
.foot-inner { display: flex; justify-content: space-between; gap: 2rem; padding: 3.5rem 1.25rem 2rem; flex-wrap: wrap; }
.footer .brand { font-family: 'Cormorant Garamond', serif; font-weight: 600; color: #fff; font-size: 1.5rem; letter-spacing: 0.1em; display: block; margin-bottom: 0.4rem; }
.footer p { margin: 0.3rem 0; font-size: 0.9rem; }
.foot-contact { text-align: right; }
.copy { border-top: 1px solid rgba(255, 255, 255, 0.08); padding: 1.1rem 1.25rem; text-align: center; font-size: 0.82rem; color: rgba(255, 255, 255, 0.4); }

/* ─── Responsive ─── */
@media (max-width: 860px) {
  .hamburger { display: flex; }
  .links { display: none; }
  .cards { grid-template-columns: 1fr; }
  .benefits { grid-template-columns: 1fr; }
  .two-col { grid-template-columns: 1fr; gap: 2rem; }
  .register { grid-template-columns: 1fr; gap: 2rem; }
  .foot-contact { text-align: left; }
  .hero-stats-inner { gap: 2rem; }
}

@media (max-width: 640px) {
  .section { padding: 3rem 1.25rem; }
  .timeline { grid-template-columns: 1fr 1fr; }
  .tl-item::after { display: none; }
  .trust-strip { flex-direction: column; }
  .trust-item { border-right: none; border-bottom: 1px solid var(--line); }
  .trust-item:last-child { border-bottom: none; }
  .hero-stats-inner { gap: 1.25rem; }
}

@media (max-width: 420px) {
  .timeline { grid-template-columns: 1fr; }
  .tl-item {
    flex-direction: row;
    align-items: center;
    text-align: left;
    gap: 1rem;
    padding: 0.5rem 0;
  }
  .tl-item::after { display: none; }
  .tl-num { flex-shrink: 0; margin-bottom: 0; }
}
</style>
