<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';

interface Props {
  title?: string;
  breadcrumbs?: Array<{ label: string; href?: string }>;
}

withDefaults(defineProps<Props>(), {
  title: '',
  breadcrumbs: () => [],
});

const page = usePage();
const user = computed(() => page.props.auth?.user as Record<string, any> | undefined);

const dropdownOpen = ref(false);

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const closeDropdown = () => {
  dropdownOpen.value = false;
};

const logout = () => {
  router.post('/logout');
};
</script>

<template>
  <header class="h-16 bg-white border-b border-ptkp-border flex items-center justify-between px-8 sticky top-0 z-30 shadow-sm">
    <div class="flex flex-col justify-center">
      <!-- Breadcrumbs -->
      <nav v-if="breadcrumbs.length > 0" class="mb-0.5" aria-label="Breadcrumb">
        <ol class="list-none m-0 p-0 flex items-center gap-1.5 font-body text-xs">
          <li v-for="(crumb, idx) in breadcrumbs" :key="idx" class="flex items-center gap-1.5">
            <Link v-if="crumb.href" :href="crumb.href" class="text-slate-500 hover:text-ptkp-navy hover:underline">
              {{ crumb.label }}
            </Link>
            <span v-else class="text-ptkp-navy font-semibold">{{ crumb.label }}</span>
            <span v-if="idx < breadcrumbs.length - 1" class="text-slate-300">/</span>
          </li>
        </ol>
      </nav>
      <h1 v-if="title" class="m-0 font-heading text-base font-bold text-ptkp-navy">
        {{ title }}
      </h1>
    </div>

    <div class="flex items-center gap-4">
      <!-- User Menu -->
      <div class="relative">
        <button
          type="button"
          class="flex items-center gap-3 bg-transparent border border-ptkp-border rounded-sm px-3 py-1.5 cursor-pointer hover:bg-ptkp-mist hover:border-ptkp-navy transition-colors"
          :aria-expanded="dropdownOpen"
          @click="toggleDropdown"
        >
          <div class="w-8 h-8 rounded-sm bg-ptkp-navy text-white font-heading font-bold text-sm flex items-center justify-center">
            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div class="flex flex-col text-left">
            <span class="font-body text-xs font-semibold text-ptkp-body">{{ user?.name || user?.username || 'Pegawai PTKP' }}</span>
            <span class="font-body text-[11px] text-slate-500">{{ user?.username ? `@${user.username}` : 'Platform User' }}</span>
          </div>
          <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="dropdownOpen"
          class="absolute right-0 top-full mt-2 w-56 bg-white border border-ptkp-border rounded-sm shadow-raised py-2 z-40"
          @click.outside="closeDropdown"
        >
          <div class="px-4 py-2">
            <p class="m-0 font-body text-sm font-semibold text-ptkp-body">{{ user?.name || 'Pegawai' }}</p>
            <p class="mt-0.5 mb-0 font-body text-xs text-slate-500">{{ user?.email || user?.username }}</p>
          </div>
          <div class="h-px bg-ptkp-border my-1"></div>

          <Link
            href="/profile"
            class="flex items-center gap-3 px-4 py-2 font-body text-xs text-ptkp-body hover:bg-ptkp-mist hover:text-ptkp-navy transition-colors w-full"
            @click="closeDropdown"
          >
            <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Profil Akun
          </Link>

          <Link
            href="/mandatory-password-change"
            class="flex items-center gap-3 px-4 py-2 font-body text-xs text-ptkp-body hover:bg-ptkp-mist hover:text-ptkp-navy transition-colors w-full"
            @click="closeDropdown"
          >
            <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Ganti Password
          </Link>

          <div class="h-px bg-ptkp-border my-1"></div>

          <button
            type="button"
            class="flex items-center gap-3 px-4 py-2 font-body text-xs text-ptkp-error hover:bg-ptkp-error-bg transition-colors w-full text-left"
            @click="logout"
          >
            <svg class="w-4 h-4 text-ptkp-error" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
            </svg>
            Keluar Sistem
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
