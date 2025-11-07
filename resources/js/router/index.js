import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/',
    component: () => import('@/layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('@/views/Dashboard.vue')
      },
      {
        path: 'podnosioci-zalbe',
        name: 'PodnosioziZalbe',
        component: () => import('@/views/PodnosioziZalbe.vue')
      },
      {
        path: 'zalbe',
        name: 'Zalbe',
        component: () => import('@/views/Zalbe.vue')
      },
      {
        path: 'izvestaji/neresene-zalbe',
        name: 'IzvestajNeresenZalbi',
        component: () => import('@/views/IzvestajNeresenZalbi.vue')
      },
      {
        path: 'izvestaji/po-datumu-prijema',
        name: 'IzvestajPoDatamuPrijema',
        component: () => import('@/views/IzvestajPoDatamuPrijema.vue')
      },
      {
        path: 'izvestaji/tuzbe-od-us',
        name: 'IzvestajTuzbeOdUS',
        component: () => import('@/views/IzvestajTuzbeOdUS.vue')
      },
      {
        path: 'izvestaji/datum-ekspedicije',
        name: 'IzvestajDatumEkspedicije',
        component: () => import('@/views/IzvestajDatumEkspedicije.vue')
      },
      {
        path: 'izvestaji/ekspedovane-tuzbe',
        name: 'IzvestajEkspedovaneTuzbe',
        component: () => import('@/views/IzvestajEkspedovaneTuzbe.vue')
      },
      {
        path: 'izvestaji/odluke-suda',
        name: 'IzvestajOdlukeSuda',
        component: () => import('@/views/IzvestajOdlukeSuda.vue')
      },
      {
        path: 'izvestaji/upravni-sporovi-u-toku',
        name: 'IzvestajUpravniSporoviUToku',
        component: () => import('@/views/IzvestajUpravniSporoviUToku.vue')
      },
      {
        path: 'izvestaji/upravni-sporovi-po-godinama',
        name: 'IzvestajUpravniSporoviPoGodinama',
        component: () => import('@/views/IzvestajUpravniSporoviPoGodinama.vue')
      },
      {
        path: 'korisnici',
        name: 'Korisnici',
        component: () => import('@/views/Korisnici.vue')
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory('/zalbena'),
  routes
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Wait for auth initialization to complete
  if (!authStore.isInitialized) {
    await authStore.initAuth();
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/');
  } else {
    next();
  }
});

export default router;
