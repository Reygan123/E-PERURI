<script setup lang="ts">
import AppAlert from '@/Components/AppAlert.vue';
import AppHeader from '@/Components/AppHeader.vue';
import AppSidebar from '@/Components/AppSidebar.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Props {
    title?: string;
    breadcrumbs?: Array<{ label: string; href?: string }>;
}

withDefaults(defineProps<Props>(), {
    title: '',
    breadcrumbs: () => [],
});

const page = usePage();
const flash = computed(() => (page.props as any).flash || {});

const isMobileMenuOpen = ref(false);
</script>

<template>
    <div class="flex h-screen w-full overflow-hidden bg-ptkp-mist">
        <Head
            :title="
                title
                    ? `${title} - PT Kertas Padalarang`
                    : 'PT Kertas Padalarang'
            "
        />

        <!-- Sidebar Deep Navy with responsive drawer on mobile -->
        <AppSidebar
            :mobile-open="isMobileMenuOpen"
            @close="isMobileMenuOpen = false"
        />

        <!-- Main Container -->
        <div
            class="flex h-full min-w-0 flex-1 flex-col overflow-y-auto overflow-x-hidden"
        >
            <!-- Header with mobile menu toggle -->
            <AppHeader
                :title="title"
                :breadcrumbs="breadcrumbs"
                @toggle-menu="isMobileMenuOpen = !isMobileMenuOpen"
            />

            <!-- Content Area -->
            <main class="min-h-0 flex-1 bg-ptkp-mist p-4 sm:p-6 lg:p-8">
                <div
                    class="mx-auto flex max-w-[1280px] flex-col gap-5 sm:gap-6"
                >
                    <!-- Flash Notifications -->
                    <AppAlert
                        v-if="flash.success"
                        variant="success"
                        dismissible
                    >
                        {{ flash.success }}
                    </AppAlert>
                    <AppAlert v-if="flash.error" variant="error" dismissible>
                        {{ flash.error }}
                    </AppAlert>
                    <AppAlert v-if="flash.info" variant="info" dismissible>
                        {{ flash.info }}
                    </AppAlert>

                    <!-- Page Content Slot -->
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
