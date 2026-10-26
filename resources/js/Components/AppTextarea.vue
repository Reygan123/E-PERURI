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
    <div class="flex w-full flex-col gap-1">
        <label
            v-if="label"
            :for="id"
            class="font-body text-sm font-medium text-ptkp-body"
        >
            {{ label }}
            <span
                v-if="required"
                class="ml-0.5 text-ptkp-error"
                aria-hidden="true"
                >*</span
            >
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
                'shadow-2xs w-full resize-y rounded-lg border bg-white px-3.5 py-2 font-body text-sm text-ptkp-body outline-none transition-all duration-150',
                hasError
                    ? 'border-ptkp-error focus:border-ptkp-error focus:ring-2 focus:ring-ptkp-error/20'
                    : 'border-slate-200 focus:border-ptkp-navy focus:ring-2 focus:ring-ptkp-navy/15',
                disabled
                    ? 'cursor-not-allowed bg-slate-50 opacity-80 shadow-none'
                    : '',
            ]"
            @input="onInput"
        ></textarea>

        <p
            v-if="error"
            class="m-0 font-body text-xs text-ptkp-error"
            role="alert"
        >
            {{ error }}
        </p>
        <p v-else-if="hint" class="m-0 font-body text-xs text-slate-500">
            {{ hint }}
        </p>
    </div>
</template>
