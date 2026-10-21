<script setup lang="ts">
import { watch, onMounted, onUnmounted } from 'vue';

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
    <Transition name="modal-fade">
      <div v-if="show" class="app-modal-backdrop" @click="close">
        <div
          :class="['app-modal-dialog', `max-width-${maxWidth}`]"
          role="dialog"
          aria-modal="true"
          @click.stop
        >
          <!-- Header -->
          <div v-if="title || $slots.header" class="modal-header">
            <slot name="header">
              <h3 class="modal-title">{{ title }}</h3>
            </slot>
            <button
              v-if="closeable"
              type="button"
              class="modal-close"
              aria-label="Tutup modal"
              @click="close"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path
                  d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
                />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body">
            <slot />
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="modal-footer">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.app-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(20, 26, 92, 0.45); /* Deep Navy semi-transparan */
  padding: var(--space-4);
  backdrop-filter: blur(2px);
}

.app-modal-dialog {
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md); /* 8px untuk modal */
  box-shadow: var(--shadow-raised);
  width: 100%;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow: hidden;
}

/* Max Width Sizing */
.max-width-sm {
  max-width: 420px;
}
.max-width-md {
  max-width: 560px;
}
.max-width-lg {
  max-width: 720px;
}
.max-width-xl {
  max-width: 960px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-4) var(--space-5);
  border-bottom: 1px solid var(--color-border);
}

.modal-title {
  margin: 0;
  font-family: var(--font-heading);
  font-size: var(--text-lg);
  font-weight: 700;
  color: var(--color-navy);
}

.modal-close {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  width: 20px;
  height: 20px;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s ease;
}
.modal-close:hover {
  color: var(--color-navy);
}
.modal-close svg {
  width: 20px;
  height: 20px;
}

.modal-body {
  padding: var(--space-5);
  overflow-y: auto;
  flex: 1 1 auto;
}

.modal-footer {
  padding: var(--space-3) var(--space-5);
  background-color: #fafbfe;
  border-top: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: var(--space-3);
}

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
