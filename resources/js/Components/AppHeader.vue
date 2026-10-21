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

const emit = defineEmits<{
  (e: 'toggle-menu'): void;
}>();

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
  <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
      <!-- Mobile hamburger toggle -->
      <button
        type="button"
        class="lg:hidden p-2 -ml-1 text-slate-600 hover:text-ptkp-navy hover:bg-slate-100 rounded-lg focus:outline-none transition-colors"
        aria-label="Buka Menu"
        @click="emit('toggle-menu')"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <div class="flex flex-col justify-center min-w-0">
        <!-- Breadcrumbs (hidden on small mobile) -->
        <nav v-if="breadcrumbs.length > 0" class="hidden sm:block mb-0.5" aria-label="Breadcrumb">
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
        <h1 v-if="title" class="m-0 font-heading text-sm sm:text-base font-bold text-ptkp-navy truncate">
          {{ title }}
        </h1>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <!-- User Menu -->
      <div class="relative">
        <button
          type="button"
          class="flex items-center gap-2 sm:gap-3 bg-transparent hover:bg-slate-100 rounded-lg px-2 sm:px-3 py-1.5 cursor-pointer transition-colors"
          :aria-expanded="dropdownOpen"
          @click="toggleDropdown"
        >
          <div class="w-8 h-8 rounded-lg bg-ptkp-navy text-white font-heading font-bold text-xs sm:text-sm flex items-center justify-center shadow-xs">
            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div class="hidden sm:flex flex-col text-left">
            <span class="font-body text-xs font-semibold text-ptkp-body max-w-[140px] truncate">{{ user?.name || user?.username || 'Pegawai PTKP' }}</span>
            <span class="font-body text-[11px] text-slate-500">{{ user?.username ? `@${user.username}` : 'Platform User' }}</span>
          </div>
          <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="dropdownOpen"
          class="absolute right-0 top-full mt-2 w-56 bg-white border border-slate-100 rounded-xl shadow-raised p-1.5 z-40"
          @click.outside="closeDropdown"
        >
          <div class="px-3 py-2">
            <p class="m-0 font-body text-sm font-semibold text-ptkp-body">{{ user?.name || 'Pegawai' }}</p>
            <p class="mt-0.5 mb-0 font-body text-xs text-slate-500 truncate">{{ user?.email || user?.username }}</p>
          </div>
          <div class="h-px bg-slate-100 my-1"></div>

          <Link
            href="/profile"
            class="flex items-center gap-3 px-3 py-2 rounded-lg font-body text-xs text-ptkp-body hover:bg-ptkp-mist hover:text-ptkp-navy transition-colors w-full"
            @click="closeDropdown"
          >
            <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Profil Akun
          </Link>

          <Link
            href="/mandatory-password-change"
            class="flex items-center gap-3 px-3 py-2 rounded-lg font-body text-xs text-ptkp-body hover:bg-ptkp-mist hover:text-ptkp-navy transition-colors w-full"
            @click="closeDropdown"
          >
            <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Ganti Password
          </Link>

          <div class="h-px bg-slate-100 my-1"></div>

          <button
            type="button"
            class="flex items-center gap-3 px-3 py-2 rounded-lg font-body text-xs text-ptkp-error hover:bg-ptkp-error-bg transition-colors w-full text-left cursor-pointer"
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
