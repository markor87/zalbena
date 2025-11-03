<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center justify-between px-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <span class="text-xl font-bold text-gray-800">Zalbena</span>
        </div>
        <button @click="toggleSidebar" class="lg:hidden text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <router-link
          to="/"
          class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
          :class="{ 'bg-purple-50 text-purple-700': $route.path === '/' }"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span class="font-medium">Početna</span>
        </router-link>

        <!-- Podnosioci Zalbe -->
        <router-link
          to="/podnosioci-zalbe"
          class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
          :class="{ 'bg-purple-50 text-purple-700': $route.path === '/podnosioci-zalbe' }"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span class="font-medium">Podnosioci žalbe</span>
        </router-link>

        <!-- Zalbe -->
        <router-link
          to="/zalbe"
          class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
          :class="{ 'bg-purple-50 text-purple-700': $route.path === '/zalbe' }"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span class="font-medium">Žalbe</span>
        </router-link>

        <!-- Korisnici - Dropdown -->
        <div v-if="isAdmin">
          <button
            @click="toggleKorisnici"
            class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
            :class="{ 'bg-purple-50 text-purple-700': korisniciOpen || $route.path.startsWith('/korisnici') }"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <span class="font-medium">Korisnici</span>
            </div>
            <svg
              :class="['w-4 h-4 transition-transform duration-200', korisniciOpen ? 'rotate-180' : '']"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown Items -->
          <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
          >
            <div v-show="korisniciOpen" class="ml-4 mt-2 space-y-1 border-l-2 border-purple-200 pl-4">
              <router-link
                to="/korisnici"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/korisnici' }"
              >
                Svi korisnici
              </router-link>
            </div>
          </transition>
        </div>

        <!-- Izvestaji - Dropdown -->
        <div>
          <button
            @click="toggleIzvestaji"
            class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
            :class="{ 'bg-purple-50 text-purple-700': izvestajOpen || $route.path.startsWith('/izvestaji') }"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span class="font-medium">Izveštaji</span>
            </div>
            <svg
              :class="['w-4 h-4 transition-transform duration-200', izvestajOpen ? 'rotate-180' : '']"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown Items -->
          <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
          >
            <div v-show="izvestajOpen" class="ml-4 mt-2 space-y-1 border-l-2 border-purple-200 pl-4">
              <router-link
                to="/izvestaji/neresene-zalbe"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/izvestaji/neresene-zalbe' }"
              >
                Списак нерешених жалби
              </router-link>
              <router-link
                to="/izvestaji/po-datumu-prijema"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/izvestaji/po-datumu-prijema' }"
              >
                Евиденција жалби по датуму пријема
              </router-link>
              <router-link
                to="/izvestaji/tuzbe-od-us"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/izvestaji/tuzbe-od-us' }"
              >
                Тужбе примљене од Управног суда Србије
              </router-link>
              <router-link
                to="/izvestaji/datum-ekspedicije"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/izvestaji/datum-ekspedicije' }"
              >
                Датум експедиције решених жалби
              </router-link>
              <router-link
                to="/izvestaji/ekspedovane-tuzbe"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/izvestaji/ekspedovane-tuzbe' }"
              >
                Експедоване тужбе
              </router-link>
              <router-link
                to="/izvestaji/odluke-suda"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/izvestaji/odluke-suda' }"
              >
                Одлуке суда
              </router-link>
              <router-link
                to="/izvestaji/upravni-sporovi-u-toku"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/izvestaji/upravni-sporovi-u-toku' }"
              >
                Управни спорови у току
              </router-link>
              <router-link
                to="/izvestaji/upravni-sporovi-po-godinama"
                class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200"
                :class="{ 'bg-purple-50 text-purple-700': $route.path === '/izvestaji/upravni-sporovi-po-godinama' }"
              >
                Управни спорови у току по годинама
              </router-link>
            </div>
          </transition>
        </div>

      </nav>

    </aside>

    <!-- Overlay for mobile -->
    <div
      v-if="sidebarOpen"
      @click="toggleSidebar"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    ></div>

    <!-- Main Content -->
    <div class="lg:pl-64">
      <!-- Top Bar -->
      <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6">
        <button
          @click="toggleSidebar"
          class="lg:hidden text-gray-500 hover:text-gray-700"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <h1 class="text-xl font-semibold text-gray-800">{{ pageTitle }}</h1>

        <div class="flex items-center space-x-3">
          <!-- User Name and Avatar -->
          <div class="flex items-center space-x-2">
            <div class="w-9 h-9 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-sm">
              {{ userInitials }}
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">{{ userName }}</p>
              <p class="text-xs text-gray-500">{{ userRole }}</p>
            </div>
          </div>

          <!-- Logout Button -->
          <button
            @click="handleLogout"
            class="flex items-center space-x-1 px-3 py-2 text-sm text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
            title="Odjavi se"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="font-medium">Odjavi se</span>
          </button>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const sidebarOpen = ref(false);
const izvestajOpen = ref(false);
const korisniciOpen = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const toggleIzvestaji = () => {
  izvestajOpen.value = !izvestajOpen.value;
};

const toggleKorisnici = () => {
  korisniciOpen.value = !korisniciOpen.value;
};

const isAdmin = computed(() => {
  return authStore.user?.role === 'admin';
});

const userName = computed(() => {
  return authStore.user?.name || 'Korisnik';
});

const userRole = computed(() => {
  return authStore.user?.role === 'admin' ? 'Administrator' : 'Korisnik';
});

const userInitials = computed(() => {
  const name = userName.value;
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
});

const pageTitle = computed(() => {
  const titles = {
    '/': 'Početna',
    '/podnosioci-zalbe': 'Podnosioci žalbe',
    '/zalbe': 'Žalbe',
    '/korisnici': 'Korisnici',
    '/izvestaji': 'Izveštaji',
    '/izvestaji/neresene-zalbe': 'Izveštaj: Nerešene i upućene na dopunu',
    '/izvestaji/po-datumu-prijema': 'Izveštaj: Evidencija žalbi po datumu prijema',
    '/izvestaji/tuzbe-od-us': 'Izveštaj: Tužbe primljene od Upravnog suda Srbije',
    '/izvestaji/datum-ekspedicije': 'Izveštaj: Datum ekspedicije rešenih žalbi',
    '/izvestaji/ekspedovane-tuzbe': 'Izveštaj: Ekspedovane tužbe',
    '/izvestaji/odluke-suda': 'Izveštaj: Odluke suda',
    '/izvestaji/upravni-sporovi-u-toku': 'Izveštaj: Upravni sporovi u toku',
    '/izvestaji/upravni-sporovi-po-godinama': 'Izveštaj: Upravni sporovi u toku po godinama'
  };
  return titles[route.path] || 'Zalbena';
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
