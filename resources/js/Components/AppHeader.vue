<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import AppBadge from './AppBadge.vue';

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
  <header class="app-header">
    <div class="header-left">
      <!-- Breadcrumbs -->
      <nav v-if="breadcrumbs.length > 0" class="header-breadcrumbs" aria-label="Breadcrumb">
        <ol>
          <li v-for="(crumb, idx) in breadcrumbs" :key="idx">
            <Link v-if="crumb.href" :href="crumb.href" class="crumb-link">
              {{ crumb.label }}
            </Link>
            <span v-else class="crumb-current">{{ crumb.label }}</span>
            <span v-if="idx < breadcrumbs.length - 1" class="crumb-separator">/</span>
          </li>
        </ol>
      </nav>
      <h1 v-if="title" class="header-title">{{ title }}</h1>
    </div>

    <div class="header-right">
      <!-- User Menu -->
      <div class="user-menu-container">
        <button
          type="button"
          class="user-menu-trigger"
          :aria-expanded="dropdownOpen"
          @click="toggleDropdown"
        >
          <div class="user-avatar" aria-hidden="true">
            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div class="user-meta">
            <span class="user-name">{{ user?.name || user?.username || 'Pegawai PTKP' }}</span>
            <span class="user-role">{{ user?.username ? `@${user.username}` : 'Platform User' }}</span>
          </div>
          <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div v-if="dropdownOpen" class="user-dropdown" @click.outside="closeDropdown">
          <div class="dropdown-header">
            <p class="dropdown-header-name">{{ user?.name || 'Pegawai' }}</p>
            <p class="dropdown-header-email">{{ user?.email || user?.username }}</p>
          </div>
          <div class="dropdown-divider"></div>

          <Link href="/profile" class="dropdown-item" @click="closeDropdown">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Profil Akun
          </Link>

          <Link href="/mandatory-password-change" class="dropdown-item" @click="closeDropdown">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Ganti Password
          </Link>

          <div class="dropdown-divider"></div>

          <button type="button" class="dropdown-item logout-item" @click="logout">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
            </svg>
            Keluar Sistem
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.app-header {
  height: 64px;
  background-color: var(--color-white);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--space-6);
  position: sticky;
  top: 0;
  z-index: 30;
}

.header-left {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.header-breadcrumbs ol {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-family: var(--font-body);
  font-size: var(--text-xs);
}

.crumb-link {
  color: #64748b;
  text-decoration: none;
}
.crumb-link:hover {
  color: var(--color-navy);
  text-decoration: underline;
}

.crumb-current {
  color: var(--color-navy);
  font-weight: 600;
}

.crumb-separator {
  color: #cbd5e1;
}

.header-title {
  margin: 0;
  font-family: var(--font-heading);
  font-size: var(--text-base);
  font-weight: 700;
  color: var(--color-navy);
}

.header-right {
  display: flex;
  align-items: center;
  gap: var(--space-4);
}

.user-menu-container {
  position: relative;
}

.user-menu-trigger {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  background: transparent;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: var(--space-1) var(--space-3);
  cursor: pointer;
  transition: all 0.15s ease;
}

.user-menu-trigger:hover {
  background-color: var(--color-mist);
  border-color: var(--color-navy);
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-sm);
  background-color: var(--color-navy);
  color: var(--color-white);
  font-family: var(--font-heading);
  font-weight: 700;
  font-size: var(--text-sm);
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-meta {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.user-name {
  font-family: var(--font-body);
  font-size: var(--text-xs);
  font-weight: 600;
  color: var(--color-text-body);
}

.user-role {
  font-family: var(--font-body);
  font-size: 11px;
  color: #64748b;
}

.chevron-icon {
  width: 16px;
  height: 16px;
  color: #64748b;
}

.user-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + var(--space-2));
  width: 220px;
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  box-shadow: var(--shadow-raised);
  padding: var(--space-2) 0;
  z-index: 40;
}

.dropdown-header {
  padding: var(--space-2) var(--space-4);
}

.dropdown-header-name {
  margin: 0;
  font-family: var(--font-body);
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--color-text-body);
}

.dropdown-header-email {
  margin: 2px 0 0 0;
  font-family: var(--font-body);
  font-size: var(--text-xs);
  color: #64748b;
}

.dropdown-divider {
  height: 1px;
  background-color: var(--color-border);
  margin: var(--space-1) 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2) var(--space-4);
  font-family: var(--font-body);
  font-size: var(--text-xs);
  color: var(--color-text-body);
  text-decoration: none;
  background: transparent;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  box-sizing: border-box;
}

.dropdown-item svg {
  width: 16px;
  height: 16px;
  color: #64748b;
}

.dropdown-item:hover {
  background-color: var(--color-mist);
  color: var(--color-navy);
}

.dropdown-item:hover svg {
  color: var(--color-navy);
}

.logout-item {
  color: var(--color-error);
}
.logout-item svg {
  color: var(--color-error);
}
.logout-item:hover {
  background-color: var(--color-error-bg);
  color: var(--color-error);
}
</style>
