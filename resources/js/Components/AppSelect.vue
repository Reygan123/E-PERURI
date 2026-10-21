<script setup lang="ts">
import { computed } from 'vue';

interface OptionItem {
  value: string | number;
  label: string;
}

interface Props {
  modelValue?: string | number | null;
  id?: string;
  label?: string;
  options?: Array<OptionItem | string>;
  placeholder?: string;
  error?: string;
  hint?: string;
  required?: boolean;
  disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  id: () => `select-${Math.random().toString(36).substring(2, 9)}`,
  label: '',
  options: () => [],
  placeholder: '-- Pilih opsi --',
  error: '',
  hint: '',
  required: false,
  disabled: false,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const normalizedOptions = computed<OptionItem[]>(() => {
  return props.options.map((opt) => {
    if (typeof opt === 'object' && opt !== null) {
      return opt as OptionItem;
    }
    return { value: opt, label: String(opt) };
  });
});

const onChange = (event: Event) => {
  const target = event.target as HTMLSelectElement;
  emit('update:modelValue', target.value);
};

const hasError = computed(() => Boolean(props.error));
</script>

<template>
  <div class="flex flex-col gap-1 w-full">
    <label v-if="label" :for="id" class="font-body text-sm font-medium text-ptkp-body">
      {{ label }}
      <span v-if="required" class="text-ptkp-error ml-0.5" aria-hidden="true">*</span>
    </label>

    <div class="relative w-full">
      <select
        :id="id"
        :value="modelValue"
        :required="required"
        :disabled="disabled"
        :class="[
          'w-full px-3.5 py-2 pr-9 font-body text-sm text-ptkp-body bg-white border rounded-lg outline-none appearance-none cursor-pointer transition-all duration-150 shadow-2xs',
          hasError
            ? 'border-ptkp-error focus:border-ptkp-error focus:ring-2 focus:ring-ptkp-error/20'
            : 'border-slate-200 focus:border-ptkp-navy focus:ring-2 focus:ring-ptkp-navy/15',
          disabled ? 'bg-slate-50 cursor-not-allowed opacity-80 shadow-none' : ''
        ]"
        @change="onChange"
      >
        <option v-if="placeholder" value="" disabled :selected="!modelValue">
          {{ placeholder }}
        </option>
        <option
          v-for="opt in normalizedOptions"
          :key="opt.value"
          :value="opt.value"
        >
          {{ opt.label }}
        </option>
      </select>

      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500">
        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path
            fill-rule="evenodd"
            d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
            clip-rule="evenodd"
          />
        </svg>
      </div>
    </div>

    <p v-if="error" class="font-body text-xs text-ptkp-error m-0" role="alert">
      {{ error }}
    </p>
    <p v-else-if="hint" class="font-body text-xs text-slate-500 m-0">
      {{ hint }}
    </p>
  </div>
</template>
