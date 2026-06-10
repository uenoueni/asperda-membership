<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useMemberApi } from '@/api/member'
import WilayahSelect from '@/components/form/WilayahSelect.vue'

const router    = useRouter()
const memberApi = useMemberApi()

const currentStep = ref(1)

const steps = [
  { num: 1, label: 'Data Cabang' },
  { num: 2, label: 'Rekening Bank' },
  { num: 3, label: 'Dokumen' },
]

const form = reactive({
  branch_name:       '',
  province_code:     '',
  city_code:         '',
  district_code:     '',
  address:           '',
  unit_count:        '',
  bank_name:         '',
  bank_account_no:   '',
  bank_account_name: '',
})
const documentFile = ref(null)
const dragOver     = ref(false)
const errors       = reactive({})

function clearErrors() {
  Object.keys(errors).forEach((k) => delete errors[k])
}

function onFileChange(e) {
  documentFile.value = e.target.files[0] ?? null
}

function onDrop(e) {
  dragOver.value = false
  const file = e.dataTransfer.files[0]
  if (file) documentFile.value = file
}

function removeFile() {
  documentFile.value = null
}

function goNext() {
  if (currentStep.value < steps.length) currentStep.value++
}

function goBack() {
  if (currentStep.value > 1) currentStep.value--
}

async function submit() {
  clearErrors()

  const fd = new FormData()
  fd.append('branch_name',       form.branch_name)
  fd.append('province_code',     form.province_code)
  fd.append('city_code',         form.city_code)
  if (form.district_code) fd.append('district_code', form.district_code)
  fd.append('address',           form.address)
  fd.append('unit_count',        form.unit_count)
  fd.append('bank_name',         form.bank_name)
  fd.append('bank_account_no',   form.bank_account_no)
  fd.append('bank_account_name', form.bank_account_name)
  if (documentFile.value) fd.append('document', documentFile.value)

  const { ok, error } = await memberApi.completeProfile(fd)

  if (ok) {
    router.push('/review')
  } else if (error?.errors) {
    Object.entries(error.errors).forEach(([k, v]) => (errors[k] = Array.isArray(v) ? v[0] : v))
    // navigate to the step that contains the first error
    const step1Fields = ['branch_name', 'province_code', 'city_code', 'address', 'unit_count']
    const step2Fields = ['bank_name', 'bank_account_no', 'bank_account_name']
    const errKeys     = Object.keys(error.errors)
    if (errKeys.some((k) => step1Fields.includes(k))) currentStep.value = 1
    else if (errKeys.some((k) => step2Fields.includes(k))) currentStep.value = 2
    else currentStep.value = 3
  } else {
    errors._global = error?.message ?? 'Gagal menyimpan profil. Silakan coba lagi.'
  }
}
</script>

<template>
  <div class="page">
    <div class="card">

      <!-- ── Header ── -->
      <div class="card-header">
        <div class="brand">
          <span class="brand-logo">A</span>
          <span class="brand-name">ASPERDA</span>
        </div>
        <h1 class="card-title">Lengkapi Data Keanggotaan</h1>
        <p class="card-subtitle">Isi semua informasi di bawah untuk menyelesaikan pendaftaran.</p>
      </div>

      <!-- ── Stepper ── -->
      <div class="stepper" aria-label="Langkah pendaftaran">
        <div
          v-for="(step, i) in steps"
          :key="step.num"
          class="stepper-item"
          :class="{
            'is-done':   currentStep > step.num,
            'is-active': currentStep === step.num,
          }"
        >
          <div class="step-bubble">
            <svg v-if="currentStep > step.num" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            <span v-else>{{ step.num }}</span>
          </div>
          <span class="step-label">{{ step.label }}</span>
          <div v-if="i < steps.length - 1" class="step-connector" :class="{ 'is-done': currentStep > step.num }"/>
        </div>
      </div>

      <!-- ── Form ── -->
      <form class="form" novalidate @submit.prevent="submit">

        <!-- Step 1 — Data Cabang -->
        <transition name="fade" mode="out-in">
          <div v-if="currentStep === 1" key="step1" class="step-body">
            <div class="section-heading">
              <span class="section-number">01</span>
              <h2>Data Cabang</h2>
            </div>

            <div class="field">
              <label for="branch_name">Nama Cabang / Kantor <span class="req">*</span></label>
              <input
                id="branch_name"
                v-model="form.branch_name"
                type="text"
                placeholder="mis. Kantor Pusat Jakarta"
                :class="{ invalid: errors.branch_name }"
                autocomplete="organization"
              />
              <span v-if="errors.branch_name" class="field-error">{{ errors.branch_name }}</span>
            </div>

            <div class="field-group">
              <WilayahSelect
                @update:provinsi="form.province_code = $event"
                @update:kota="form.city_code = $event"
                @update:kecamatan="form.district_code = $event"
              />
              <span v-if="errors.province_code" class="field-error">{{ errors.province_code }}</span>
              <span v-if="errors.city_code"     class="field-error">{{ errors.city_code }}</span>
            </div>

            <div class="field">
              <label for="address">Alamat Lengkap <span class="req">*</span></label>
              <textarea
                id="address"
                v-model="form.address"
                rows="3"
                placeholder="Jl. Sudirman No. 10, RT 01/RW 02, Kelurahan…"
                :class="{ invalid: errors.address }"
              />
              <span v-if="errors.address" class="field-error">{{ errors.address }}</span>
            </div>

            <div class="field field--half">
              <label for="unit_count">Jumlah Unit Kendaraan <span class="req">*</span></label>
              <input
                id="unit_count"
                v-model.number="form.unit_count"
                type="number"
                min="1"
                placeholder="mis. 5"
                :class="{ invalid: errors.unit_count }"
              />
              <span v-if="errors.unit_count" class="field-error">{{ errors.unit_count }}</span>
            </div>

            <div class="step-actions">
              <button type="button" class="btn-primary" @click="goNext">
                Lanjut
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </button>
            </div>
          </div>
        </transition>

        <!-- Step 2 — Rekening Bank -->
        <transition name="fade" mode="out-in">
          <div v-if="currentStep === 2" key="step2" class="step-body">
            <div class="section-heading">
              <span class="section-number">02</span>
              <h2>Informasi Rekening Bank</h2>
            </div>

            <div class="field">
              <label for="bank_name">Nama Bank <span class="req">*</span></label>
              <input
                id="bank_name"
                v-model="form.bank_name"
                type="text"
                placeholder="mis. BCA, Mandiri, BRI"
                :class="{ invalid: errors.bank_name }"
              />
              <span v-if="errors.bank_name" class="field-error">{{ errors.bank_name }}</span>
            </div>

            <div class="field">
              <label for="bank_account_no">Nomor Rekening <span class="req">*</span></label>
              <input
                id="bank_account_no"
                v-model="form.bank_account_no"
                type="text"
                placeholder="Nomor rekening"
                :class="{ invalid: errors.bank_account_no }"
                inputmode="numeric"
              />
              <span v-if="errors.bank_account_no" class="field-error">{{ errors.bank_account_no }}</span>
            </div>

            <div class="field">
              <label for="bank_account_name">Nama Pemilik Rekening <span class="req">*</span></label>
              <input
                id="bank_account_name"
                v-model="form.bank_account_name"
                type="text"
                placeholder="Nama sesuai buku rekening"
                :class="{ invalid: errors.bank_account_name }"
              />
              <span v-if="errors.bank_account_name" class="field-error">{{ errors.bank_account_name }}</span>
              <span class="field-hint">Rekening ini digunakan untuk proses refund jika pendaftaran tidak disetujui.</span>
            </div>

            <div class="step-actions step-actions--split">
              <button type="button" class="btn-secondary" @click="goBack">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Kembali
              </button>
              <button type="button" class="btn-primary" @click="goNext">
                Lanjut
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </button>
            </div>
          </div>
        </transition>

        <!-- Step 3 — Dokumen -->
        <transition name="fade" mode="out-in">
          <div v-if="currentStep === 3" key="step3" class="step-body">
            <div class="section-heading">
              <span class="section-number">03</span>
              <h2>Dokumen Pendukung</h2>
            </div>

            <p class="step-desc">
              Unggah dokumen pendukung seperti akta pendirian, SIUP, atau dokumen resmi lainnya.
              Dokumen bersifat opsional namun mempercepat proses verifikasi.
            </p>

            <!-- Dropzone -->
            <div
              class="dropzone"
              :class="{ 'is-over': dragOver, 'has-file': documentFile, invalid: errors.document }"
              @dragover.prevent="dragOver = true"
              @dragleave.prevent="dragOver = false"
              @drop.prevent="onDrop"
            >
              <template v-if="!documentFile">
                <div class="dropzone-icon" aria-hidden="true">
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <p class="dropzone-primary">Seret &amp; lepas file di sini</p>
                <p class="dropzone-secondary">atau</p>
                <label for="document" class="btn-browse">Pilih File</label>
                <input
                  id="document"
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  class="visually-hidden"
                  @change="onFileChange"
                />
                <p class="dropzone-hint">PDF, JPG, PNG — maks. 2 MB</p>
              </template>

              <template v-else>
                <div class="file-preview">
                  <div class="file-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  </div>
                  <div class="file-info">
                    <span class="file-name">{{ documentFile.name }}</span>
                    <span class="file-size">{{ (documentFile.size / 1024).toFixed(1) }} KB</span>
                  </div>
                  <button type="button" class="file-remove" aria-label="Hapus file" @click="removeFile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </button>
                </div>
              </template>
            </div>
            <span v-if="errors.document" class="field-error">{{ errors.document }}</span>

            <p v-if="errors._global" class="error-global">{{ errors._global }}</p>

            <div class="step-actions step-actions--split">
              <button type="button" class="btn-secondary" @click="goBack">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Kembali
              </button>
              <button
                type="submit"
                class="btn-primary"
                :disabled="memberApi.loading.value"
                :aria-busy="memberApi.loading.value"
              >
                <svg v-if="memberApi.loading.value" class="spinner" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="16" height="16" aria-hidden="true">
                  <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                {{ memberApi.loading.value ? 'Menyimpan…' : 'Simpan &amp; Selesai' }}
              </button>
            </div>
          </div>
        </transition>

      </form>
    </div>
  </div>
</template>

<style scoped>
/* ── Layout ── */
.page {
  min-height: 100vh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  background: var(--color-surface);
  padding: 2.5rem 1rem;
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--color-on-surface);
}

.card {
  width: 100%;
  max-width: 800px;
  background: #ffffff;
  border-radius: var(--radius-lg);
  box-shadow: 0 4px 6px rgba(2, 36, 72, 0.04), 0 20px 40px rgba(2, 36, 72, 0.10);
  overflow: hidden;
}

/* ── Header ── */
.card-header {
  background: var(--color-primary-container);
  padding: 2rem 2.5rem;
  text-align: center;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.brand-logo {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Manrope', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  color: #ffffff;
  line-height: 36px;
  text-align: center;
}

.brand-name {
  font-family: 'Manrope', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  letter-spacing: 0.15em;
  color: rgba(255, 255, 255, 0.9);
}

.card-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 0.375rem;
  line-height: 1.25;
}

.card-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.65);
  margin: 0;
}

/* ── Stepper ── */
.stepper {
  display: flex;
  align-items: flex-start;
  padding: 1.5rem 2.5rem;
  background: var(--color-surface-container-low);
  border-bottom: 1px solid var(--color-outline-variant);
  gap: 0;
}

.stepper-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  position: relative;
  gap: 0.5rem;
}

.step-bubble {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-full);
  border: 2px solid var(--color-outline-variant);
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-outline);
  transition: all 200ms ease;
  position: relative;
  z-index: 1;
}

.stepper-item.is-active .step-bubble {
  border-color: var(--color-secondary);
  background: var(--color-secondary);
  color: #ffffff;
  box-shadow: 0 0 0 4px rgba(0, 96, 172, 0.15);
}

.stepper-item.is-done .step-bubble {
  border-color: var(--color-secondary);
  background: var(--color-secondary);
  color: #ffffff;
}

.step-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-outline);
  text-align: center;
  white-space: nowrap;
  letter-spacing: 0.02em;
  transition: color 200ms ease;
}

.stepper-item.is-active .step-label,
.stepper-item.is-done  .step-label {
  color: var(--color-secondary);
}

.step-connector {
  position: absolute;
  top: 15px;
  left: calc(50% + 20px);
  right: calc(-50% + 20px);
  height: 2px;
  background: var(--color-outline-variant);
  transition: background 200ms ease;
}

.step-connector.is-done {
  background: var(--color-secondary);
}

/* ── Form ── */
.form {
  padding: 2rem 2.5rem;
}

.step-body {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.section-heading {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--color-outline-variant);
}

.section-number {
  font-family: 'Manrope', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-secondary);
  letter-spacing: 0.06em;
}

.section-heading h2 {
  font-family: 'Manrope', sans-serif;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--color-primary);
  margin: 0;
}

.step-desc {
  font-size: 0.875rem;
  color: var(--color-on-surface-variant);
  line-height: 1.6;
  margin: 0;
}

/* ── Fields ── */
.field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.field--half {
  max-width: 280px;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-on-surface-variant);
  letter-spacing: 0.03em;
}

.req {
  color: var(--color-error);
  margin-left: 2px;
}

input[type="text"],
input[type="number"],
textarea {
  padding: 0.6875rem 0.875rem;
  border: 1px solid var(--color-outline-variant);
  border-radius: var(--radius);
  font-size: 0.9375rem;
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--color-on-surface);
  background: #ffffff;
  transition: border-color 180ms ease, box-shadow 180ms ease;
  resize: vertical;
  line-height: 1.5;
}

input::placeholder,
textarea::placeholder {
  color: var(--color-outline);
}

input:focus,
textarea:focus {
  outline: none;
  border-color: var(--color-secondary);
  box-shadow: 0 0 0 3px rgba(0, 96, 172, 0.15);
}

input.invalid,
textarea.invalid {
  border-color: var(--color-error);
  box-shadow: 0 0 0 3px rgba(186, 26, 26, 0.1);
}

.field-error {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--color-error);
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.field-hint {
  font-size: 0.75rem;
  color: var(--color-outline);
  line-height: 1.5;
}

/* ── Dropzone ── */
.dropzone {
  border: 2px dashed var(--color-outline-variant);
  border-radius: var(--radius);
  background: var(--color-surface-container-low);
  padding: 2rem 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  text-align: center;
  transition: border-color 180ms ease, background 180ms ease;
  cursor: default;
}

.dropzone.is-over {
  border-color: var(--color-secondary);
  background: rgba(0, 96, 172, 0.04);
}

.dropzone.invalid {
  border-color: var(--color-error);
}

.dropzone.has-file {
  padding: 1rem 1.5rem;
  border-style: solid;
  border-color: var(--color-secondary);
  background: rgba(0, 96, 172, 0.04);
}

.dropzone-icon {
  color: var(--color-outline);
  margin-bottom: 0.25rem;
}

.dropzone-primary {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-on-surface);
  margin: 0;
}

.dropzone-secondary {
  font-size: 0.8125rem;
  color: var(--color-outline);
  margin: 0;
}

.dropzone-hint {
  font-size: 0.75rem;
  color: var(--color-outline);
  margin: 0;
}

.btn-browse {
  display: inline-flex;
  align-items: center;
  padding: 0.4375rem 1rem;
  border: 1px solid var(--color-outline-variant);
  border-radius: var(--radius);
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-primary);
  background: #ffffff;
  cursor: pointer;
  transition: border-color 180ms ease, box-shadow 180ms ease;
}

.btn-browse:hover {
  border-color: var(--color-secondary);
  box-shadow: 0 0 0 3px rgba(0, 96, 172, 0.1);
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* File preview */
.file-preview {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  width: 100%;
}

.file-icon {
  flex-shrink: 0;
  color: var(--color-secondary);
}

.file-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
  text-align: left;
}

.file-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-on-surface);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-size {
  font-size: 0.75rem;
  color: var(--color-outline);
}

.file-remove {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  border-radius: var(--radius-full);
  border: none;
  background: var(--color-error-container);
  color: var(--color-error);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: filter 180ms ease;
}

.file-remove:hover {
  filter: brightness(0.92);
}

/* ── Error global ── */
.error-global {
  font-size: 0.875rem;
  color: var(--color-error);
  background: var(--color-error-container);
  border-radius: var(--radius);
  padding: 0.75rem 1rem;
  margin: 0;
}

/* ── Step Actions ── */
.step-actions {
  display: flex;
  justify-content: flex-end;
  padding-top: 0.5rem;
}

.step-actions--split {
  justify-content: space-between;
}

.btn-primary,
.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6875rem 1.5rem;
  border-radius: var(--radius);
  font-size: 0.9375rem;
  font-weight: 600;
  font-family: 'Inter', system-ui, sans-serif;
  cursor: pointer;
  transition: filter 180ms ease, box-shadow 180ms ease;
  border: none;
}

.btn-primary {
  background: var(--color-primary-container);
  color: var(--color-on-primary);
}

.btn-primary:hover:not(:disabled) {
  filter: brightness(1.12);
}

.btn-primary:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.btn-secondary {
  background: transparent;
  color: var(--color-primary);
  border: 1px solid var(--color-outline-variant);
}

.btn-secondary:hover {
  border-color: var(--color-secondary);
  box-shadow: 0 0 0 3px rgba(0, 96, 172, 0.1);
}

@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 0.8s linear infinite; }

/* ── Transition ── */
.fade-enter-active,
.fade-leave-active { transition: opacity 180ms ease, transform 180ms ease; }
.fade-enter-from   { opacity: 0; transform: translateX(10px); }
.fade-leave-to     { opacity: 0; transform: translateX(-10px); }

/* ── Responsive ── */
@media (max-width: 640px) {
  .card-header { padding: 1.5rem 1.25rem; }
  .stepper     { padding: 1.25rem 1rem; }
  .form        { padding: 1.5rem 1.25rem; }
  .step-label  { display: none; }
  .field--half { max-width: 100%; }
  .card-title  { font-size: 1.25rem; }
}
</style>
