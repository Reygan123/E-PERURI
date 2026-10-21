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
  <div class="app-form-group">
    <label v-if="label" :for="id" class="app-label">
      {{ label }}
      <span v-if="required" class="required-mark" aria-hidden="true">*</span>
    </label>

    <div class="select-wrapper">
      <select
        :id="id"
        :value="modelValue"
        :required="required"
        :disabled="disabled"
        :class="['app-select', { 'has-error': hasError }]"
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
      <div class="select-chevron" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path
            fill-rule="evenodd"
            d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
            clip-rule="evenodd"
          />
        </svg>
      </div>
    </div>

    <p v-if="error" class="error-message" role="alert">
      {{ error }}
    </p>
    <p v-else-if="hint" class="hint-message">
      {{ hint }}
    </p>
  </div>
</template>

<style scoped>
.app-form-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  width: 100%;
}

.app-label {
  font-family: var(--font-body);
  font-size: var(--text-sm);
  font-weight: 500;
  color: var(--color-text-body);
}

.required-mark {
  color: var(--color-error);
  margin-left: 2px;
}

.select-wrapper {
  position: relative;
  width: 100%;
}

.app-select {
  width: 100%;
  padding: var(--space-2) var(--space-6) var(--space-2) var(--space-3);
  font-family: var(--font-body);
  font-size: var(--text-sm);
  color: var(--color-text-body);
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  outline: none;
  appearance: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
  box-sizing: border-box;
  cursor: pointer;
}

.app-select:focus {
  border-color: var(--color-navy);
  box-shadow: 0 0 0 1px var(--color-navy);
}

.app-select.has-error {
  border-color: var(--color-error);
}
.app-select.has-error:focus {
  box-shadow: 0 0 0 1px var(--color-error);
}

.app-select:disabled {
  background-color: var(--color-mist);
  cursor: not-allowed;
  opacity: 0.8;
}

.select-chevron {
  position: absolute;
  right: var(--space-3);
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  width: 16px;
  height: 16px;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
}
.select-chevron svg {
  width: 16px;
  height: 16px;
}

.error-message {
  font-family: var(--font-body);
  font-size: var(--text-xs);
  color: var(--color-error);
  margin: 0;
}

.hint-message {
  font-family: var(--font-body);
  font-size: var(--text-xs);
  color: #64748b;
  margin: 0;
}
</style>
