<script setup>
import { watch, onMounted } from 'vue'
import { useWilayah } from '@/composables/useWilayah'
import SearchableSelect from '@/components/form/SearchableSelect.vue'

const emit = defineEmits(['update:provinsi', 'update:kota', 'update:kecamatan'])

const {
  provinsiList, kotaList, kecamatanList,
  selectedProvinsi, selectedKota, selectedKecamatan,
  loadingProvinsi, loadingKota, loadingKecamatan,
  fetchProvinsi,
} = useWilayah()

watch(selectedProvinsi,  (v) => emit('update:provinsi',  v))
watch(selectedKota,      (v) => emit('update:kota',      v))
watch(selectedKecamatan, (v) => emit('update:kecamatan', v))

// Fetch provinsi list saat komponen mount — guard di dalam fetchProvinsi
// mencegah duplicate request jika sudah ada data
onMounted(fetchProvinsi)
</script>

<template>
  <div class="wilayah-select">

    <div class="field">
      <label>Provinsi <span class="req">*</span></label>
      <SearchableSelect
        v-model="selectedProvinsi"
        :options="provinsiList"
        :loading="loadingProvinsi"
        placeholder="Pilih atau ketik nama provinsi…"
      />
    </div>

    <div class="field">
      <label>Kota / Kabupaten <span class="req">*</span></label>
      <SearchableSelect
        v-model="selectedKota"
        :options="kotaList"
        :loading="loadingKota"
        :disabled="!selectedProvinsi"
        :placeholder="selectedProvinsi ? 'Pilih atau ketik nama kota…' : '— Pilih provinsi dulu —'"
      />
    </div>

    <div class="field">
      <label>Kecamatan <span class="opt">(opsional)</span></label>
      <SearchableSelect
        v-model="selectedKecamatan"
        :options="kecamatanList"
        :loading="loadingKecamatan"
        :disabled="!selectedKota"
        :placeholder="selectedKota ? 'Pilih atau ketik nama kecamatan…' : '— Pilih kota dulu —'"
      />
    </div>

  </div>
</template>

<style scoped>
.wilayah-select {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.field {
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

.req { color: var(--color-error); margin-left: 2px; }
.opt { font-weight: 400; color: var(--color-outline); font-size: 0.8rem; }
</style>
