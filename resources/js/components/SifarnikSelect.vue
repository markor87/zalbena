<template>
  <v-select
    :model-value="modelValue"
    @update:model-value="value => emit('update:modelValue', value)"
    :options="options"
    :placeholder="placeholder"
    :taggable="taggable"
    :disabled="disabled"
    :create-option="value => value"
    v-bind="extraProps"
    append-to-body
    :calculate-position="positionDropdown"
    class="vue-select-custom"
  />
</template>

<script setup>
import { computed } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
  modelValue: { type: [String, Number, Object], default: null },
  options: { type: Array, default: () => [] },
  reduce: { type: Function, default: null },
  getOptionLabel: { type: Function, default: null },
  placeholder: { type: String, default: 'Изаберите вредност' },
  // Dozvoljava i vrednost koja nije u listi - potvrdjuje se tasterom Enter
  taggable: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue']);

// Prosledjujemo samo ono sto je zadato da ne bismo pregazili podrazumevano ponasanje v-select-a
const extraProps = computed(() => {
  const extra = {};
  if (props.reduce) extra.reduce = props.reduce;
  if (props.getOptionLabel) extra['get-option-label'] = props.getOptionLabel;
  return extra;
});

// Dropdown je zakacen na body pa mu sirinu racunamo rucno - inace se
// duge stavke sifarnika seku na sirinu polja
const positionDropdown = (dropdownList, component, { width, top, left }) => {
  const leftPx = parseFloat(left);
  const available = window.innerWidth - (leftPx - (window.scrollX || 0)) - 16;

  dropdownList.classList.add('vs-dropdown-sifarnik');
  dropdownList.style.top = top;
  dropdownList.style.left = left;
  dropdownList.style.minWidth = width;
  dropdownList.style.maxWidth = `${Math.max(parseFloat(width), available)}px`;
};
</script>

<style scoped>
:deep(.vs__dropdown-toggle) {
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
}

:deep(.vs__dropdown-toggle:hover) {
  border-color: #9333ea;
}

:deep(.vs__dropdown-toggle:focus-within) {
  border-color: transparent;
  box-shadow: 0 0 0 2px #9333ea40;
}

/* Izabrana vrednost ostaje u jednom redu, visak se skracuje sa "..." */
:deep(.vs__selected-options) {
  flex-wrap: nowrap;
  overflow: hidden;
  min-width: 0;
}

:deep(.vs__selected) {
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}
</style>

<!-- Dropdown se preko append-to-body renderuje van komponente pa stil ne sme biti scoped -->
<style>
.vs-dropdown-sifarnik {
  width: max-content;
}

.vs-dropdown-sifarnik .vs__dropdown-option {
  white-space: normal;
  overflow-wrap: anywhere;
}
</style>
