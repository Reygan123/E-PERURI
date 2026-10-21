<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  modelValue?: string | number | null;
  id?: string;
  label?: string;
  type?: string;
  placeholder?: string;
  error?: string;
  hint?: string;
  required?: boolean;
  disabled?: boolean;
  readonly?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  id: () => `input-${Math.random().toString(36).substring(2, 9)}`,
  label: '',
  type: 'text',
  placeholder: '',
  error: '',
  hint: '',
  required: false,
  disabled: false,
  readonly: false,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const onInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
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

    <div class="input-wrapper">
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :class="['app-input', { 'has-error': hasError }]"
        @input="onInput"
      />
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

.input-wrapper {
  position: relative;
  width: 100%;
}

.app-input {
  width: 100%;
  padding: var(--space-2) var(--space-3);
  font-family: var(--font-body);
  font-size: var(--text-sm);
  color: var(--color-text-body);
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  outline: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
  box-sizing: border-box;
}

.app-input::placeholder {
  color: #94a3b8;
}

.app-input:focus {
  border-color: var(--color-navy);
  box-shadow: 0 0 0 1px var(--color-navy);
}

.app-input.has-error {
  border-color: var(--color-error);
}
.app-input.has-error:focus {
  box-shadow: 0 0 0 1px var(--color-error);
}

.app-input:disabled {
  background-color: var(--color-mist);
  cursor: not-allowed;
  opacity: 0.8;
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
