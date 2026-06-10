import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // ─── Public ───────────────────────────────────────────────────────────────
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/verifikasi',
    name: 'verify-email',
    component: () => import('@/views/auth/VerifyEmailView.vue'),
  },
  {
    path: '/set-password',
    name: 'set-password',
    component: () => import('@/views/auth/SetPasswordView.vue'),
  },

  // ─── Member ───────────────────────────────────────────────────────────────
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/member/DashboardView.vue'),
    meta: { requiresAuth: true, roles: ['member'] },
  },
  {
    path: '/lengkapi-profil',
    name: 'complete-profile',
    component: () => import('@/views/member/CompleteProfileView.vue'),
    meta: { requiresAuth: true, roles: ['member'] },
  },
  {
    path: '/review',
    name: 'review',
    component: () => import('@/views/member/ReviewView.vue'),
    meta: { requiresAuth: true, roles: ['member'] },
  },
  {
    path: '/bayar',
    name: 'payment',
    component: () => import('@/views/member/PaymentView.vue'),
    meta: { requiresAuth: true, roles: ['member'] },
  },

  // ─── Petugas ──────────────────────────────────────────────────────────────
  {
    path: '/survey',
    name: 'survey',
    component: () => import('@/views/survey/SurveyListView.vue'),
    meta: { requiresAuth: true, roles: ['dpc', 'dpd', 'dpp', 'super_admin'] },
  },

  // ─── Admin ────────────────────────────────────────────────────────────────
  {
    path: '/admin',
    component: () => import('@/views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, roles: ['dpc', 'dpd', 'dpp', 'super_admin'] },
    children: [
      { path: 'members', component: () => import('@/views/admin/MemberListView.vue') },
      { path: 'members/:id', component: () => import('@/views/admin/MemberDetailView.vue') },
      { path: 'refunds', component: () => import('@/views/admin/RefundListView.vue') },
      { path: 'sanctions', component: () => import('@/views/admin/SanctionListView.vue') },
      { path: 'starterkit', component: () => import('@/views/admin/StarterkitView.vue') },
      {
        path: 'settings',
        component: () => import('@/views/admin/SettingsView.vue'),
        meta: { roles: ['super_admin'] },
      },
      {
        path: 'users',
        component: () => import('@/views/admin/UserManagementView.vue'),
        meta: { roles: ['super_admin'] },
      },
    ],
  },

  // ─── Catch-all ────────────────────────────────────────────────────────────
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    return { top: 0 }
  },
})

let userFetched = false

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Ambil user sekali per session jika belum ada
  if (!userFetched && !auth.isAuthenticated) {
    userFetched = true
    await auth.fetchUser()
  }

  // Redirect jika halaman untuk guest tapi sudah login
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'home' }
  }

  // Butuh login
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  // Cek role (ambil dari route sendiri atau parent)
  const requiredRoles = to.meta.roles
  if (requiredRoles && auth.user && !requiredRoles.includes(auth.user.role)) {
    return { name: 'home' }
  }
})

export default router
