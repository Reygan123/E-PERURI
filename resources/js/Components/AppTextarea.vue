<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  modelValue?: string | null;
  id?: string;
  label?: string;
  rows?: number;
  placeholder?: string;
  error?: string;
  hint?: string;
  required?: boolean;
  disabled?: boolean;
  readonly?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  id: () => `textarea-${Math.random().toString(36).substring(2, 9)}`,
  label: '',
  rows: 3,
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
  const target = event.target as HTMLTextAreaElement;
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

    <textarea
      :id="id"
      :rows="rows"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :readonly="readonly"
      :class="[
        'w-full px-3 py-2 font-body text-sm text-ptkp-body bg-white border rounded-sm outline-none transition-colors duration-150 resize-y',
        hasError
          ? 'border-ptkp-error focus:border-ptkp-error focus:ring-1 focus:ring-ptkp-error'
          : 'border-ptkp-border focus:border-ptkp-navy focus:ring-1 focus:ring-ptkp-navy',
        disabled ? 'bg-ptkp-mist cursor-not-allowed opacity-80' : ''
      ]"
      @input="onInput"
    ></textarea>

    <p v-if="error" class="font-body text-xs text-ptkp-error m-0" role="alert">
      {{ error }}
    </p>
    <p v-else-if="hint" class="font-body text-xs text-slate-500 m-0">
      {{ hint }}
    </p>
  </div>
</template>
