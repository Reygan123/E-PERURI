<script setup lang="ts">
import { watch, onMounted, onUnmounted, computed } from 'vue';

interface Props {
  show?: boolean;
  title?: string;
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl';
  closeable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  show: false,
  title: '',
  maxWidth: 'md',
  closeable: true,
});

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const close = () => {
  if (props.closeable) {
    emit('close');
  }
};

const handleKeyDown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.show && props.closeable) {
    close();
  }
};

const maxWidthClass = computed(() => {
  switch (props.maxWidth) {
    case 'sm':
      return 'max-w-sm';
    case 'lg':
      return 'max-w-2xl';
    case 'xl':
      return 'max-w-4xl';
    case 'md':
    default:
      return 'max-w-lg';
  }
});

watch(
  () => props.show,
  (val) => {
    if (val) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  }
);

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
  document.body.style.overflow = '';
});
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ptkp-deep/50 backdrop-blur-sm"
        @click="close"
      >
        <div
          :class="[
            'w-full bg-white rounded-xl shadow-raised flex flex-col max-h-[90vh] overflow-hidden',
            maxWidthClass
          ]"
          role="dialog"
          aria-modal="true"
          @click.stop
        >
          <!-- Header -->
          <div
            v-if="title || $slots.header"
            class="flex items-center justify-between px-6 py-4 border-b border-slate-100"
          >
            <slot name="header">
              <h3 class="m-0 font-heading text-lg font-bold text-ptkp-navy">
                {{ title }}
              </h3>
            </slot>
            <button
              v-if="closeable"
              type="button"
              class="text-slate-400 hover:text-ptkp-navy hover:bg-slate-100 rounded-lg transition-colors p-1.5"
              aria-label="Tutup modal"
              @click="close"
            >
              <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path
                  d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
                />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="p-6 overflow-y-auto flex-1">
            <slot />
          </div>

          <!-- Footer -->
          <div
            v-if="$slots.footer"
            class="px-6 py-4 bg-[#fafbfe] border-t border-slate-100 flex items-center justify-end gap-3"
          >
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
