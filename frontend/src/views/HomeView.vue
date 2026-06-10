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
        <a href="#top" class="brand">
          ASPERDA<span class="brand-dot">.</span>
        </a>
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
      <div class="hero-bg-shapes" aria-hidden="true">
        <div class="glow-orb red-glow"></div>
        <div class="glow-orb navy-glow"></div>
      </div>
      <div class="wrap hero-inner">
        <span class="eyebrow" data-reveal>
          <span class="eyebrow-line"></span>
          Asosiasi Pengusaha Rental Kendaraan Indonesia
          <span class="eyebrow-line"></span>
        </span>
        <h1 data-reveal style="--delay: 100ms">
          Bergabung dengan <span class="highlight-navy">ASPERDA</span>
        </h1>
        <p class="lead" data-reveal style="--delay: 200ms">
          Rumah besar bagi pengusaha rental mobil daerah untuk berkembang, berkolaborasi,
          serta memperoleh perlindungan dan pembinaan usaha yang profesional dan berdaya saing.
        </p>
        <div class="cta" data-reveal style="--delay: 300ms">
          <a href="#daftar" class="btn-primary">Daftar Anggota</a>
          <a href="#keanggotaan" class="btn-line">Pelajari Keanggotaan</a>
        </div>
      </div>

      <!-- Overlapping Stats Bar -->
      <div class="hero-stats-bar" data-reveal style="--delay: 450ms">
        <div class="hero-stats-inner">
          <div v-for="s in stats" :key="s.label" class="hero-stat">
            <strong>{{ s.value }}</strong>
            <span>{{ s.label }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Tujuan Section (Two Column) -->
    <section id="keanggotaan" class="wrap section tujuan-section">
      <div class="tujuan-intro" data-reveal>
        <span class="section-tag">Tujuan Utama</span>
        <h2 class="section-title">Membangun Ekosistem Rental yang Solid &amp; Terpercaya</h2>
        <p class="sub text-large">
          Keanggotaan ASPERDA dirancang untuk memfasilitasi pertumbuhan bisnis yang sehat, aman, dan berstandar nasional bagi seluruh pengusaha rental mobil di Indonesia.
        </p>
      </div>
      <div class="tujuan-content" data-reveal style="--delay: 150ms">
        <div class="purpose-list">
          <div v-for="(t, i) in tujuan" :key="i" class="purpose-item">
            <div class="purpose-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <p class="purpose-text">{{ t }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Jenis Keanggotaan -->
    <section class="band bg-soft-navy">
      <div class="wrap section">
        <div class="section-header centered" data-reveal>
          <span class="section-tag text-red">Kategori Anggota</span>
          <h2 class="section-title centered">Pilihan Jenis Keanggotaan</h2>
          <p class="sub centered">
            Kami merangkul berbagai pelaku ekosistem industri transportasi dan rental untuk berkolaborasi secara sinergis.
          </p>
        </div>
        <div class="cards">
          <article
            v-for="(j, i) in jenis"
            :key="i"
            class="card"
            :class="{ 'card-featured': j.title === 'Anggota Kehormatan' }"
            data-reveal
            :style="`--delay: ${(i + 1) * 100}ms`"
          >
            <div class="card-accent-bar"></div>
            <div class="card-icon" v-html="iconSvg(j.icon)"></div>
            <h3>{{ j.title }}</h3>
            <p>{{ j.desc }}</p>
          </article>
        </div>
      </div>
    </section>

    <!-- Manfaat Keanggotaan -->
    <section id="manfaat" class="wrap section manfaat-section">
      <div class="section-header" data-reveal>
        <span class="section-tag">Benefit Eksklusif</span>
        <h2 class="section-title">Manfaat Menjadi Anggota</h2>
        <p class="sub">
          Dapatkan berbagai kemudahan dan nilai tambah yang menunjang kredibilitas serta keberlanjutan bisnis rental Anda.
        </p>
      </div>
      <div class="benefits">
        <div
          v-for="(m, i) in manfaat"
          :key="i"
          class="benefit-card"
          data-reveal
          :style="`--delay: ${i * 60}ms`"
        >
          <div class="benefit-icon-wrapper" v-html="iconSvg(m.icon, 20)"></div>
          <span class="benefit-text">{{ m.text }}</span>
        </div>
      </div>
    </section>

    <!-- Hak & Kewajiban (Side by Side Cards) -->
    <section class="band bg-gradient-light">
      <div class="wrap section two-col">
        <!-- Hak Card -->
        <div class="hak-kewajiban-card card-navy" data-reveal>
          <div class="hk-header">
            <div class="hk-badge">Hak Anggota</div>
            <h3>Perlindungan &amp; Dukungan Penuh</h3>
          </div>
          <div class="hk-body">
            <ul class="hk-list">
              <li v-for="(h, i) in hak" :key="i">
                <span class="hk-bullet-navy"></span>
                <span>{{ h }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Kewajiban Card -->
        <div class="hak-kewajiban-card card-red" data-reveal style="--delay: 150ms">
          <div class="hk-header">
            <div class="hk-badge">Kewajiban Anggota</div>
            <h3>Integritas &amp; Standar Profesional</h3>
          </div>
          <div class="hk-body">
            <ul class="hk-list">
              <li v-for="(k, i) in kewajiban" :key="i">
                <span class="hk-bullet-red"></span>
                <span>{{ k }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Proses Pendaftaran (Timeline) -->
    <section id="proses" class="wrap section proses-section">
      <div class="section-header centered" data-reveal>
        <span class="section-tag">Alur Gabung</span>
        <h2 class="section-title centered">Tahapan Proses Pendaftaran</h2>
        <p class="sub centered">
          Langkah mudah untuk menjadi anggota resmi ASPERDA dan terverifikasi secara nasional.
        </p>
      </div>
      <div class="timeline-wrap" data-reveal style="--delay: 100ms">
        <ol class="timeline">
          <li v-for="(p, i) in proses" :key="i" class="tl-item">
            <div class="tl-num-wrap">
              <div class="tl-num">{{ i + 1 }}</div>
              <div class="tl-dot"></div>
            </div>
            <div class="tl-content">
              <p class="tl-text">{{ p }}</p>
            </div>
          </li>
        </ol>
      </div>
    </section>

    <!-- Daftar (Registration Portal) -->
    <section id="daftar" class="band bg-soft-navy border-top-red">
      <div class="wrap section register-container">
        <div class="register-intro" data-reveal>
          <span class="section-tag">Formulir Digital</span>
          <h2 class="section-title">Pendaftaran Mandiri Anggota Baru</h2>
          <p class="sub">
            Mulai langkah sukses Anda hari ini. Isi informasi dasar usaha Anda secara lengkap dan benar. Setelah mengirimkan formulir, silakan periksa email Anda untuk panduan verifikasi dokumen operasional.
          </p>
          <div class="trust-banner">
            <div class="trust-banner-title">Bergabung Bersama Kami:</div>
            <div class="trust-strip">
              <div v-for="s in stats" :key="s.label" class="trust-item">
                <strong>{{ s.value }}</strong>
                <span>{{ s.label }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="register-card-wrapper" data-reveal style="--delay: 150ms">
          <div class="register-card-header">
            <div class="card-accent-stripe"></div>
            <h3>Formulir Anggota ASPERDA</h3>
            <p>Silakan lengkapi kolom pendaftaran di bawah ini.</p>
          </div>
          <div class="register-card-body">
            <MemberRegisterForm />
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="wrap foot-inner">
        <div class="foot-brand-section">
          <strong class="footer-brand">ASPERDA<span class="brand-dot">.</span></strong>
          <p class="footer-desc">Asosiasi Pengusaha Rental Kendaraan Indonesia</p>
          <p class="footer-tagline">Mewadahi, membina, dan memajukan rental kendaraan daerah untuk Indonesia yang lebih terhubung.</p>
        </div>
        <div class="foot-contact">
          <h4>Hubungi Kami</h4>
          <p class="contact-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="contact-icon"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Graha Mampang Lt. 1, Jakarta Selatan, 12790
          </p>
          <p class="contact-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="contact-icon"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            Hotline (WA): 0859-2373-1419
          </p>
          <p class="contact-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="contact-icon"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            info@asperdaindonesia.com
          </p>
        </div>
      </div>
      <div class="copy">
        <div class="wrap copy-inner">
          <span>© 2026 Asperda Indonesia. Seluruh hak cipta dilindungi undang-undang.</span>
          <div class="copy-links">
            <a href="#">Kebijakan Privasi</a>
            <span class="separator">|</span>
            <a href="#">Syarat &amp; Ketentuan</a>
          </div>
        </div>
      </div>
    </footer>

  </div>
</template>

<style scoped>
/* ─── Layout helpers ─── */
.wrap {
  width: 100%;
  max-width: var(--max);
  margin: 0 auto;
  padding: 0 1.5rem;
}
.section {
  padding: 6.5rem 1.5rem;
}
.band {
  background: #ffffff;
  border-top: 1px solid var(--line);
  border-bottom: 1px solid var(--line);
}
.bg-soft-navy {
  background: #f8fafc; /* very clean white-grey with a hint of slate */
}
.bg-gradient-light {
  background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
}
.border-top-red {
  border-top: 4px solid var(--accent);
}
.sub {
  color: var(--muted);
  max-width: 60ch;
  font-size: 1.05rem;
  line-height: 1.6;
}
.sub.centered {
  margin-left: auto;
  margin-right: auto;
  text-align: center;
}
.sub.text-large {
  font-size: 1.15rem;
  line-height: 1.65;
}

/* ─── Section Headings / tags ─── */
.section-header {
  margin-bottom: 3.5rem;
}
.section-header.centered {
  text-align: center;
}
.section-tag {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--primary); /* Navy tag */
  margin-bottom: 0.75rem;
  position: relative;
  padding-left: 1rem;
}
.section-tag::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 6px;
  height: 6px;
  background: var(--accent); /* Red dot */
  border-radius: 50%;
}
.section-tag.text-red {
  color: var(--accent);
}
.section-tag.text-red::before {
  background: var(--primary);
}

.section-title {
  font-size: clamp(2rem, 3.5vw, 2.75rem);
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-weight: 700;
  color: var(--primary); /* Navy */
  line-height: 1.2;
  margin-top: 0.25rem;
  margin-bottom: 1.25rem;
}
.section-title.centered {
  text-align: center;
}

/* ─── Scroll Reveal Animations ─── */
[data-reveal] {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.75s var(--ease), transform 0.75s var(--ease);
  transition-delay: var(--delay, 0ms);
}
[data-reveal].revealed {
  opacity: 1;
  transform: none;
}

/* ─── Navigation Header ─── */
.nav {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(255, 255, 255, 0.88);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(29, 45, 68, 0.08);
  transition: background 0.3s;
}
.nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 72px;
}
.brand {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: var(--primary);
  font-size: 1.5rem;
  display: flex;
  align-items: center;
}
.brand-dot {
  color: var(--accent); /* Red dot at the end */
  font-weight: 900;
}
.links {
  display: flex;
  align-items: center;
  gap: 2rem;
}
.links a {
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  color: var(--primary);
  font-size: 0.9rem;
  letter-spacing: 0.02em;
  padding: 0.5rem 0;
  position: relative;
  transition: color 0.3s var(--ease);
  cursor: pointer;
}
.links a::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--accent); /* Red indicator */
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.3s var(--ease);
}
.links a:hover {
  color: var(--accent);
}
.links a:hover::after {
  transform: scaleX(1);
  transform-origin: left;
}
.btn-ghost {
  padding: 0.5rem 1.35rem !important;
  border: 1.5px solid var(--accent);
  border-radius: 6px;
  color: var(--accent) !important;
  font-weight: 600 !important;
  background: transparent;
  transition: all 0.3s var(--ease) !important;
}
.btn-ghost:hover {
  background: var(--accent) !important;
  color: #fff !important;
}
.btn-ghost::after {
  display: none !important;
}

/* ─── Hero Section (Modern Light Theme) ─── */
.hero {
  background: #ffffff;
  position: relative;
  overflow: hidden;
  padding: 6.5rem 1.5rem 8.5rem;
}
.hero-bg-shapes {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}
/* Beautiful modern tech lines and glowing orbs */
.hero-bg-shapes::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(29, 45, 68, 0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(29, 45, 68, 0.02) 1px, transparent 1px);
  background-size: 50px 50px;
  background-position: center top;
}
.glow-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.08;
}
.red-glow {
  top: -10%;
  right: 15%;
  width: 400px;
  height: 400px;
  background: var(--accent);
}
.navy-glow {
  bottom: -20%;
  left: 10%;
  width: 500px;
  height: 500px;
  background: var(--primary);
}

.hero-inner {
  max-width: 900px;
  margin: 0 auto;
  text-align: center;
  position: relative;
  z-index: 2;
}
.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.75rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--accent);
  font-weight: 600;
  margin-bottom: 1.75rem;
}
.eyebrow-line {
  display: block;
  width: 32px;
  height: 1.5px;
  background: var(--accent);
}
.hero h1 {
  color: var(--primary);
  font-size: clamp(2.5rem, 5.5vw, 4.25rem);
  margin-bottom: 1.25rem;
  font-weight: 700;
  line-height: 1.15;
}
.highlight-navy {
  color: var(--primary);
  position: relative;
}
.highlight-navy::after {
  content: '';
  position: absolute;
  bottom: 4px;
  left: 0;
  width: 100%;
  height: 4px;
  background: rgba(192, 58, 43, 0.15); /* Red marker line under ASPERDA */
  z-index: -1;
}
.lead {
  color: var(--muted);
  max-width: 65ch;
  margin: 0 auto 2.75rem;
  font-size: 1.15rem;
  line-height: 1.7;
}
.cta {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}
.btn-primary {
  background: var(--accent);
  color: #ffffff;
  padding: 1rem 2.25rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.01em;
  box-shadow: 0 4px 16px rgba(192, 58, 43, 0.22);
  transition: all 0.3s var(--ease);
}
.btn-primary:hover {
  background: var(--primary-dark);
  box-shadow: 0 8px 24px rgba(29, 45, 68, 0.25);
  transform: translateY(-2px);
}
.btn-primary:active {
  transform: translateY(0);
}
.btn-line {
  border: 2px solid var(--primary);
  color: var(--primary);
  padding: 1rem 2.25rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.95rem;
  background: transparent;
  transition: all 0.3s var(--ease);
}
.btn-line:hover {
  background: rgba(29, 45, 68, 0.05);
  border-color: var(--primary-dark);
  transform: translateY(-2px);
}

/* ─── Hero Floating Stats Card ─── */
.hero-stats-bar {
  max-width: 900px;
  width: calc(100% - 3rem);
  background: #ffffff;
  margin: 4.5rem auto 0;
  border: 1px solid var(--line);
  border-radius: 12px;
  box-shadow: 0 12px 36px rgba(29, 45, 68, 0.08);
  position: relative;
  z-index: 10;
}
.hero-stats-inner {
  display: flex;
  justify-content: space-around;
  padding: 1.75rem 2rem;
  flex-wrap: wrap;
}
.hero-stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  flex: 1;
  text-align: center;
  min-width: 150px;
  padding: 0.5rem;
  position: relative;
}
.hero-stat:not(:last-child)::after {
  content: '';
  position: absolute;
  right: 0;
  top: 20%;
  height: 60%;
  width: 1px;
  background: var(--line);
}
.hero-stat strong {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 2.25rem;
  font-weight: 700;
  color: var(--primary);
  line-height: 1.1;
}
.hero-stat span {
  font-size: 0.72rem;
  font-family: 'Inter', sans-serif;
  letter-spacing: 0.08em;
  color: var(--muted);
  text-transform: uppercase;
  font-weight: 600;
}

/* ─── Tujuan Section (Two Column, editorial layout) ─── */
.tujuan-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4.5rem;
  align-items: center;
  border-top: 1px solid var(--line);
}
.tujuan-intro {
  display: flex;
  flex-direction: column;
}
.tujuan-content {
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 2.5rem;
  box-shadow: var(--shadow-sm);
  position: relative;
  overflow: hidden;
}
.tujuan-content::before {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 4px; height: 100%;
  background: var(--primary); /* Left navy bar */
}
.purpose-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
.purpose-item {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}
.purpose-icon {
  color: var(--accent); /* Red check */
  margin-top: 3px;
  flex-shrink: 0;
}
.purpose-text {
  color: var(--ink);
  font-size: 0.975rem;
  font-weight: 500;
  line-height: 1.5;
  margin: 0;
}

/* ─── Jenis Keanggotaan Cards ─── */
.cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.75rem;
  margin-top: 3.5rem;
}
.card {
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 2.5rem 2rem;
  box-shadow: var(--shadow-sm);
  transition: all 0.4s var(--ease);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
.card-accent-bar {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: var(--primary); /* Default Navy top bar */
}
.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 10px;
  background: rgba(29, 45, 68, 0.05); /* Navy tint */
  display: grid;
  place-items: center;
  margin-bottom: 1.5rem;
  color: var(--primary);
  transition: all 0.3s var(--ease);
}
.card-icon :deep(svg) {
  display: block;
}
.card h3 {
  font-size: 1.25rem;
  margin-bottom: 0.75rem;
  color: var(--primary);
  font-family: 'Inter', sans-serif;
  font-weight: 600;
}
.card p {
  color: var(--muted);
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.55;
}

/* Featured card (middle card - Keanggotaan Kehormatan) */
.card-featured {
  transform: scale(1.02);
  border-color: rgba(192, 58, 43, 0.2);
  box-shadow: 0 10px 30px rgba(192, 58, 43, 0.08);
}
.card-featured .card-accent-bar {
  background: var(--accent); /* Red top bar for featured card */
}
.card-featured .card-icon {
  background: rgba(192, 58, 43, 0.07); /* Red tint */
  color: var(--accent);
}

/* Hover effects */
.card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 30px rgba(29, 45, 68, 0.12);
  border-color: rgba(29, 45, 68, 0.15);
}
.card-featured:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 16px 36px rgba(192, 58, 43, 0.15);
  border-color: rgba(192, 58, 43, 0.3);
}
.card:hover .card-icon {
  transform: scale(1.1);
  background: var(--primary);
  color: #ffffff;
}
.card-featured:hover .card-icon {
  background: var(--accent);
  color: #ffffff;
}

/* ─── Manfaat Keanggotaan ─── */
.manfaat-section {
  border-bottom: 1px solid var(--line);
}
.benefits {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-top: 3.5rem;
}
.benefit-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.5rem;
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: 8px;
  transition: all 0.3s var(--ease);
}
.benefit-card:hover {
  border-color: var(--accent);
  box-shadow: 0 6px 18px rgba(192, 58, 43, 0.06);
  transform: translateY(-2px);
}
.benefit-icon-wrapper {
  width: 42px;
  height: 42px;
  border-radius: 8px;
  background: rgba(192, 58, 43, 0.06); /* Red tint */
  display: grid;
  place-items: center;
  flex-shrink: 0;
  color: var(--accent);
  transition: all 0.3s;
}
.benefit-card:hover .benefit-icon-wrapper {
  background: var(--accent);
  color: #ffffff;
}
.benefit-icon-wrapper :deep(svg) {
  display: block;
}
.benefit-text {
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  font-size: 0.95rem;
  color: var(--ink);
  line-height: 1.4;
}

/* ─── Hak & Kewajiban (Modern block styling) ─── */
.hk-header {
  padding: 2.25rem 2.25rem 1.5rem;
  border-bottom: 1px solid var(--line);
}
.hk-badge {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  padding: 0.25rem 0.65rem;
  border-radius: 4px;
  margin-bottom: 0.75rem;
}
.hak-kewajiban-card {
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.hak-kewajiban-card h3 {
  font-family: 'Inter', sans-serif;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary);
  margin: 0;
}
.hk-body {
  padding: 2.25rem;
}
.hk-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.hk-list li {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  font-size: 0.95rem;
  color: var(--ink);
  line-height: 1.5;
}

/* Card Navy Variant for Hak */
.card-navy {
  border-top: 4px solid var(--primary);
}
.card-navy .hk-badge {
  background: rgba(29, 45, 68, 0.08);
  color: var(--primary);
}
.hk-bullet-navy {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--primary);
  margin-top: 7px;
  flex-shrink: 0;
}

/* Card Red Variant for Kewajiban */
.card-red {
  border-top: 4px solid var(--accent);
}
.card-red .hk-badge {
  background: rgba(192, 58, 43, 0.08);
  color: var(--accent);
}
.hk-bullet-red {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--accent);
  margin-top: 7px;
  flex-shrink: 0;
}

/* ─── Proses Pendaftaran (Timeline) ─── */
.proses-section {
  border-top: 1px solid var(--line);
  border-bottom: 1px solid var(--line);
}
.timeline-wrap {
  margin-top: 4rem;
}
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
  position: relative;
  padding: 0 1rem;
}
.tl-item:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 22px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: var(--line);
  z-index: 1;
}
.tl-num-wrap {
  position: relative;
  z-index: 2;
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.tl-num {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: #ffffff;
  border: 2px solid var(--primary);
  color: var(--primary);
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 1.1rem;
  display: grid;
  place-items: center;
  transition: all 0.3s;
  box-shadow: 0 4px 10px rgba(29, 45, 68, 0.06);
}
.tl-item:hover .tl-num {
  background: var(--accent);
  border-color: var(--accent);
  color: #ffffff;
  transform: scale(1.1);
  box-shadow: 0 6px 14px rgba(192, 58, 43, 0.25);
}
.tl-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--primary);
  line-height: 1.5;
  margin: 0;
}

/* ─── Register Section (Registration Portal) ─── */
.register-container {
  display: grid;
  grid-template-columns: 1fr 1.1fr;
  gap: 5rem;
  align-items: start;
}
.trust-banner {
  margin-top: 3rem;
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}
.trust-banner-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
  text-align: center;
}
.trust-strip {
  display: flex;
  overflow: hidden;
}
.trust-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem;
  gap: 0.15rem;
  border-right: 1px solid var(--line);
}
.trust-item:last-child {
  border-right: none;
}
.trust-item strong {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--accent); /* Red highlight */
  line-height: 1;
}
.trust-item span {
  font-size: 0.7rem;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.register-card-wrapper {
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: 12px;
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  position: relative;
}
.card-accent-stripe {
  height: 5px;
  background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
}
.register-card-header {
  padding: 2.25rem 2.25rem 1.5rem;
  border-bottom: 1px solid var(--line);
}
.register-card-header h3 {
  font-family: 'Inter', sans-serif;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--primary);
  margin: 0 0 0.25rem;
}
.register-card-header p {
  font-size: 0.85rem;
  color: var(--muted);
  margin: 0;
}
.register-card-body {
  padding: 2.25rem;
}

/* ─── Footer ─── */
.footer {
  background: var(--primary-dark);
  color: rgba(244, 246, 251, 0.8);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.foot-inner {
  display: flex;
  justify-content: space-between;
  gap: 4rem;
  padding: 5rem 1.5rem 3.5rem;
}
.foot-brand-section {
  max-width: 400px;
}
.footer-brand {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-weight: 700;
  color: #ffffff;
  font-size: 1.75rem;
  letter-spacing: 0.05em;
  display: block;
  margin-bottom: 0.75rem;
}
.footer-desc {
  font-size: 0.95rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 0.75rem;
}
.footer-tagline {
  font-size: 0.875rem;
  line-height: 1.6;
  color: rgba(244, 246, 251, 0.6);
  margin: 0;
}
.foot-contact {
  min-width: 280px;
}
.foot-contact h4 {
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  color: #ffffff;
  margin-top: 0;
  margin-bottom: 1.25rem;
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.contact-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9rem;
  margin-bottom: 0.875rem !important;
  color: rgba(244, 246, 251, 0.85);
}
.contact-icon {
  color: var(--accent); /* Red contact icons */
  flex-shrink: 0;
}
.copy {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding: 1.5rem 1.5rem;
  background: rgba(0, 0, 0, 0.15);
}
.copy-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
  color: rgba(244, 246, 251, 0.45);
}
.copy-links {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}
.copy-links a {
  transition: color 0.3s;
  color: rgba(244, 246, 251, 0.45);
}
.copy-links a:hover {
  color: #ffffff;
}
.separator {
  color: rgba(244, 246, 251, 0.2);
}

/* ─── Hamburger Mobile Menu & Media Queries ─── */
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
  background: #ffffff;
  box-shadow: 0 8px 24px rgba(29, 45, 68, 0.08);
}
.mobile-menu a {
  display: block;
  padding: 1rem 1.5rem;
  font-weight: 600;
  color: var(--primary);
  border-bottom: 1px solid var(--line);
  transition: background var(--dur) var(--ease);
  font-size: 0.95rem;
}
.mobile-menu a:last-child { border-bottom: none; }
.mobile-menu a:hover { background: var(--bg-soft); }
.mobile-menu .mobile-cta { color: var(--accent); background: rgba(192, 58, 43, 0.04); }

/* Responsive adjustments */
@media (max-width: 992px) {
  .section { padding: 4.5rem 1.5rem; }
  .tujuan-section { grid-template-columns: 1fr; gap: 3rem; }
  .cards { grid-template-columns: 1fr 1fr; }
  .benefits { grid-template-columns: 1fr 1fr; }
  .register-container { grid-template-columns: 1fr; gap: 3rem; }
}

@media (max-width: 768px) {
  .hamburger { display: flex; }
  .links { display: none; }
  .hero-stats-inner { gap: 1rem; }
  .hero-stat:not(:last-child)::after { display: none; }
  .two-col { grid-template-columns: 1fr; gap: 2rem; }
  .timeline { grid-template-columns: 1fr; gap: 2rem; }
  .tl-item::after { display: none; }
  .tl-item {
    flex-direction: row;
    text-align: left;
    gap: 1.5rem;
    padding: 0;
  }
  .tl-num-wrap {
    margin-bottom: 0;
  }
  .foot-inner { flex-direction: column; gap: 2.5rem; }
  .copy-inner { flex-direction: column; gap: 1rem; text-align: center; }
}

@media (max-width: 576px) {
  .cards { grid-template-columns: 1fr; }
  .benefits { grid-template-columns: 1fr; }
  .hero-stats-bar { margin-top: 3rem; }
}
</style>
