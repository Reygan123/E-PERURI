<script setup lang="ts">
interface Props {
    rows?: number;
    card?: boolean;
}

withDefaults(defineProps<Props>(), {
    rows: 3,
    card: true,
});
</script>

<template>
    <div
        :class="[
            'flex flex-col gap-4',
            card ? 'rounded-xl bg-white p-6 shadow-card' : '',
        ]"
    >
        <div class="flex flex-col gap-2">
            <div class="h-5 w-1/3 animate-pulse rounded-md bg-slate-200"></div>
            <div
                class="h-3.5 w-3/5 animate-pulse rounded-md bg-slate-100"
            ></div>
        </div>
        <div class="flex flex-col gap-3">
            <div v-for="n in rows" :key="n" class="w-full">
                <div
                    class="h-10 w-full animate-pulse rounded-md bg-slate-100"
                ></div>
            </div>
        </div>
    </div>
</template>
