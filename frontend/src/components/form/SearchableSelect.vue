<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue:  { type: String, default: '' },
  options:     { type: Array,  default: () => [] }, // [{ code, name }]
  placeholder: { type: String, default: 'Pilih…' },
  disabled:    { type: Boolean, default: false },
  loading:     { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const isOpen    = ref(false)
const query     = ref('')
const activeIdx = ref(-1)
const rootRef   = ref(null)
const inputRef  = ref(null)
const listRef   = ref(null)

const selectedOption = computed(() =>
  props.options.find((o) => o.code === props.modelValue) ?? null
)

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return props.options
  return props.options.filter((o) => o.name.toLowerCase().includes(q))
})

function openDropdown() {
  if (props.disabled || props.loading) return
  isOpen.value    = true
  query.value     = ''
  activeIdx.value = props.modelValue
    ? filtered.value.findIndex((o) => o.code === props.modelValue)
    : -1
  nextTick(() => scrollActive())
}

function closeDropdown() {
  isOpen.value    = false
  query.value     = ''
  activeIdx.value = -1
}

function selectOption(option) {
  emit('update:modelValue', option.code)
  closeDropdown()
}

function clearSelection(e) {
  e.stopPropagation()
  emit('update:modelValue', '')
  query.value = ''
  nextTick(() => inputRef.value?.focus())
}

function onInputClick() {
  if (!isOpen.value) openDropdown()
}

function onInputFocus() {
  if (!isOpen.value) openDropdown()
}

function onInput(e) {
  query.value     = e.target.value
  activeIdx.value = -1
  if (!isOpen.value) isOpen.value = true
}

function onKeydown(e) {
  if (props.disabled) return

  if (!isOpen.value) {
    if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(e.key)) {
      e.preventDefault()
      openDropdown()
    }
    return
  }

  switch (e.key) {
    case 'Escape':
      e.preventDefault()
      closeDropdown()
      inputRef.value?.blur()
      break
    case 'ArrowDown':
      e.preventDefault()
      activeIdx.value = Math.min(activeIdx.value + 1, filtered.value.length - 1)
      scrollActive()
      break
    case 'ArrowUp':
      e.preventDefault()
      activeIdx.value = Math.max(activeIdx.value - 1, 0)
      scrollActive()
      break
    case 'Enter':
      e.preventDefault()
      if (activeIdx.value >= 0 && filtered.value[activeIdx.value]) {
        selectOption(filtered.value[activeIdx.value])
      }
      break
    case 'Tab':
      closeDropdown()
      break
  }
}

function scrollActive() {
  nextTick(() => {
    if (activeIdx.value >= 0) {
      listRef.value?.children[activeIdx.value]?.scrollIntoView({ block: 'nearest' })
    }
  })
}

watch(filtered, () => { activeIdx.value = -1 })

function handleOutsideClick(e) {
  if (rootRef.value && !rootRef.value.contains(e.target)) closeDropdown()
}
onMounted(()       => document.addEventListener('mousedown', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', handleOutsideClick))
</script>

<template>
  <div
    ref="rootRef"
    class="ss-root"
    :class="{ 'is-open': isOpen, 'is-disabled': disabled || loading }"
  >
    <!-- Trigger / Input -->
    <div class="ss-control">
      <span v-if="loading" class="ss-spinner" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
      </span>
      <span v-else-if="isOpen" class="ss-search-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </span>

      <input
        ref="inputRef"
        class="ss-input"
        :value="isOpen ? query : (selectedOption?.name ?? '')"
        :placeholder="loading ? 'Memuat…' : placeholder"
        :disabled="disabled || loading"
        :readonly="!isOpen && !query"
        autocomplete="off"
        spellcheck="false"
        @click="onInputClick"
        @focus="onInputFocus"
        @input="onInput"
        @keydown="onKeydown"
      />

      <button
        v-if="modelValue && !disabled && !loading"
        type="button"
        class="ss-clear"
        tabindex="-1"
        aria-label="Hapus pilihan"
        @mousedown.prevent="clearSelection"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>

      <span class="ss-chevron" :class="{ 'is-open': isOpen }" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
      </span>
    </div>

    <!-- Dropdown -->
    <div v-if="isOpen" class="ss-dropdown">
      <ul ref="listRef" class="ss-list" role="listbox">
        <li
          v-if="filtered.length === 0"
          class="ss-empty"
          role="option"
          aria-selected="false"
        >
          Tidak ada hasil untuk "{{ query }}"
        </li>
        <li
          v-for="(opt, i) in filtered"
          :key="opt.code"
          class="ss-option"
          :class="{
            'is-active':   i === activeIdx,
            'is-selected': opt.code === modelValue,
          }"
          role="option"
          :aria-selected="opt.code === modelValue"
          @mousedown.prevent="selectOption(opt)"
          @mousemove="activeIdx = i"
        >
          <svg
            v-if="opt.code === modelValue"
            class="ss-check"
            xmlns="http://www.w3.org/2000/svg"
            width="13"
            height="13"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
          ><polyline points="20 6 9 17 4 12"/></svg>
          <span v-else class="ss-check-placeholder"/>
          {{ opt.name }}
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.ss-root {
  position: relative;
  width: 100%;
  font-family: 'Inter', system-ui, sans-serif;
}

/* Control */
.ss-control {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0 0.875rem;
  border: 1px solid var(--color-outline-variant);
  border-radius: var(--radius);
  background: var(--color-surface-container-lowest, #fff);
  transition: border-color 180ms ease, box-shadow 180ms ease;
  cursor: text;
}

.ss-root.is-open .ss-control {
  border-color: var(--color-secondary);
  box-shadow: 0 0 0 3px rgba(0, 96, 172, 0.15);
}

.ss-root.is-disabled .ss-control {
  background: var(--color-surface-container-low);
  cursor: not-allowed;
}

/* Input */
.ss-input {
  flex: 1;
  min-width: 0;
  padding: 0.6875rem 0;
  border: none;
  outline: none;
  background: transparent;
  font-size: 0.9375rem;
  font-family: inherit;
  color: var(--color-on-surface);
  cursor: inherit;
}

.ss-input::placeholder {
  color: var(--color-outline);
}

.ss-input:disabled {
  color: var(--color-outline);
}

/* Icons */
.ss-spinner svg { animation: ss-spin 0.8s linear infinite; }
@keyframes ss-spin { to { transform: rotate(360deg); } }

.ss-search-icon,
.ss-spinner {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  color: var(--color-outline);
}

.ss-chevron {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  color: var(--color-outline);
  transition: transform 180ms ease;
  pointer-events: none;
}

.ss-chevron.is-open {
  transform: rotate(180deg);
}

/* Clear button */
.ss-clear {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: var(--radius-full);
  border: none;
  background: var(--color-outline-variant);
  color: var(--color-on-surface-variant);
  cursor: pointer;
  padding: 0;
  transition: background 150ms ease;
}

.ss-clear:hover {
  background: var(--color-outline);
  color: #fff;
}

/* Dropdown */
.ss-dropdown {
  position: absolute;
  z-index: 100;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: var(--color-surface-container-lowest, #fff);
  border: 1px solid var(--color-outline-variant);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
  animation: ss-open 120ms ease;
}

@keyframes ss-open {
  from { opacity: 0; transform: translateY(-4px); }
  to   { opacity: 1; transform: translateY(0); }
}

.ss-list {
  max-height: 220px;
  overflow-y: auto;
  margin: 0;
  padding: 0.25rem;
  list-style: none;
  overscroll-behavior: contain;
}

/* Scrollbar */
.ss-list::-webkit-scrollbar { width: 4px; }
.ss-list::-webkit-scrollbar-track  { background: transparent; }
.ss-list::-webkit-scrollbar-thumb  { background: var(--color-outline-variant); border-radius: var(--radius-full); }

/* Options */
.ss-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.625rem;
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  color: var(--color-on-surface);
  cursor: pointer;
  user-select: none;
  transition: background 100ms ease;
}

.ss-option.is-active {
  background: var(--color-surface-container-low);
}

.ss-option.is-selected {
  color: var(--color-secondary);
  font-weight: 600;
}

.ss-check {
  flex-shrink: 0;
  color: var(--color-secondary);
}

.ss-check-placeholder {
  flex-shrink: 0;
  width: 13px;
}

.ss-empty {
  padding: 0.75rem 0.625rem;
  font-size: 0.8125rem;
  color: var(--color-outline);
  text-align: center;
  list-style: none;
}
</style>
