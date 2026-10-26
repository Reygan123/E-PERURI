<script setup lang="ts">
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, watch } from 'vue';

interface Props {
    modelValue?: string | null;
    id?: string;
    label?: string;
    placeholder?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    id: () => `richtext-${Math.random().toString(36).substring(2, 9)}`,
    label: '',
    placeholder: 'Tuliskan isi surat di sini...',
    error: '',
    hint: '',
    required: false,
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editor = useEditor({
    content: props.modelValue || '',
    editable: !props.disabled,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [2, 3, 4],
            },
        }),
    ],
    onUpdate: () => {
        if (editor.value) {
            const html = editor.value.getHTML();
            emit('update:modelValue', html === '<p></p>' ? '' : html);
        }
    },
});

watch(
    () => props.modelValue,
    (newVal) => {
        if (editor.value && editor.value.getHTML() !== newVal) {
            editor.value.commands.setContent(newVal || '', {
                emitUpdate: false,
            });
        }
    },
);

watch(
    () => props.disabled,
    (val) => {
        if (editor.value) {
            editor.value.setEditable(!val);
        }
    },
);

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy();
    }
});
</script>

<template>
    <div class="flex w-full flex-col gap-1">
        <label
            v-if="label"
            :for="id"
            class="font-body text-xs font-semibold text-slate-700"
        >
            {{ label }}
            <span
                v-if="required"
                class="ml-0.5 text-rose-500"
                aria-hidden="true"
                >*</span
            >
        </label>

        <div
            class="overflow-hidden rounded-lg border bg-white transition-all duration-150"
            :class="[
                error
                    ? 'border-rose-500 focus-within:ring-2 focus-within:ring-rose-500/20'
                    : 'border-slate-200 focus-within:border-[#20288E] focus-within:ring-2 focus-within:ring-[#20288E]/15',
                disabled ? 'cursor-not-allowed bg-slate-50 opacity-80' : '',
            ]"
        >
            <!-- Toolbar -->
            <div
                v-if="editor && !disabled"
                class="flex flex-wrap items-center gap-1 border-b border-slate-100 bg-slate-50/70 px-3 py-1.5 text-xs font-medium text-slate-700"
            >
                <button
                    type="button"
                    class="rounded px-2 py-1 transition hover:bg-slate-200"
                    :class="{
                        'bg-slate-300 font-bold text-[#20288E]':
                            editor.isActive('bold'),
                    }"
                    title="Tebal (Ctrl+B)"
                    @click="editor.chain().focus().toggleBold().run()"
                >
                    <strong>B</strong>
                </button>

                <button
                    type="button"
                    class="rounded px-2 py-1 italic transition hover:bg-slate-200"
                    :class="{
                        'bg-slate-300 font-bold text-[#20288E]':
                            editor.isActive('italic'),
                    }"
                    title="Miring (Ctrl+I)"
                    @click="editor.chain().focus().toggleItalic().run()"
                >
                    <em>I</em>
                </button>

                <button
                    type="button"
                    class="rounded px-2 py-1 line-through transition hover:bg-slate-200"
                    :class="{
                        'bg-slate-300 font-bold text-[#20288E]':
                            editor.isActive('strike'),
                    }"
                    title="Coret"
                    @click="editor.chain().focus().toggleStrike().run()"
                >
                    S
                </button>

                <span class="mx-1 h-4 w-px bg-slate-300"></span>

                <button
                    type="button"
                    class="rounded px-2 py-1 transition hover:bg-slate-200"
                    :class="{
                        'bg-slate-300 font-bold text-[#20288E]':
                            editor.isActive('heading', { level: 2 }),
                    }"
                    title="Heading 2"
                    @click="
                        editor.chain().focus().toggleHeading({ level: 2 }).run()
                    "
                >
                    H2
                </button>

                <button
                    type="button"
                    class="rounded px-2 py-1 transition hover:bg-slate-200"
                    :class="{
                        'bg-slate-300 font-bold text-[#20288E]':
                            editor.isActive('heading', { level: 3 }),
                    }"
                    title="Heading 3"
                    @click="
                        editor.chain().focus().toggleHeading({ level: 3 }).run()
                    "
                >
                    H3
                </button>

                <button
                    type="button"
                    class="rounded px-2 py-1 transition hover:bg-slate-200"
                    :class="{
                        'bg-slate-300 font-bold text-[#20288E]':
                            editor.isActive('paragraph'),
                    }"
                    title="Paragraf Biasa"
                    @click="editor.chain().focus().setParagraph().run()"
                >
                    P
                </button>

                <span class="mx-1 h-4 w-px bg-slate-300"></span>

                <button
                    type="button"
                    class="rounded px-2 py-1 transition hover:bg-slate-200"
                    :class="{
                        'bg-slate-300 font-bold text-[#20288E]':
                            editor.isActive('bulletList'),
                    }"
                    title="Daftar Poin"
                    @click="editor.chain().focus().toggleBulletList().run()"
                >
                    • List
                </button>

                <button
                    type="button"
                    class="rounded px-2 py-1 transition hover:bg-slate-200"
                    :class="{
                        'bg-slate-300 font-bold text-[#20288E]':
                            editor.isActive('orderedList'),
                    }"
                    title="Daftar Nomor"
                    @click="editor.chain().focus().toggleOrderedList().run()"
                >
                    1. List
                </button>

                <span class="mx-1 h-4 w-px bg-slate-300"></span>

                <button
                    type="button"
                    class="rounded px-2 py-1 transition hover:bg-slate-200"
                    title="Urungkan (Undo)"
                    :disabled="!editor.can().undo()"
                    @click="editor.chain().focus().undo().run()"
                >
                    &larr;
                </button>

                <button
                    type="button"
                    class="rounded px-2 py-1 transition hover:bg-slate-200"
                    title="Ulangi (Redo)"
                    :disabled="!editor.can().redo()"
                    @click="editor.chain().focus().redo().run()"
                >
                    &rarr;
                </button>
            </div>

            <!-- Editor Content Area -->
            <editor-content
                :editor="editor"
                class="prose prose-sm min-h-[160px] max-w-none p-4 text-slate-800 focus:outline-none"
            />
        </div>

        <p
            v-if="error"
            class="m-0 font-body text-xs text-rose-500"
            role="alert"
        >
            {{ error }}
        </p>
        <p v-else-if="hint" class="m-0 font-body text-xs text-slate-500">
            {{ hint }}
        </p>
    </div>
</template>

<style>
/* Tiptap Editor Base Styles */
.tiptap {
    outline: none;
    min-height: 140px;
}
.tiptap p {
    margin-top: 0;
    margin-bottom: 0.75rem;
    line-height: 1.6;
}
.tiptap ul,
.tiptap ol {
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}
.tiptap ul {
    list-style-type: disc;
}
.tiptap ol {
    list-style-type: decimal;
}
.tiptap h2 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    color: #20288e;
}
.tiptap h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 0.75rem;
    margin-bottom: 0.25rem;
    color: #20288e;
}
</style>
