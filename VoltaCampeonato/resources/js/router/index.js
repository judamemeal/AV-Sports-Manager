import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';

// Layouts
import PublicLayout from '../layouts/PublicLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';

const routes = [
  // ── Public routes ──
  {
    path: '/',
    component: PublicLayout,
    children: [
      { path: '', name: 'home', component: () => import('../pages/public/HomePage.vue') },
      { path: 'campeonatos', name: 'championships', component: () => import('../pages/public/ChampionshipsPage.vue') },
      { path: 'campeonatos/:id', name: 'championship-detail', component: () => import('../pages/public/ChampionshipDetailPage.vue') },
      { path: 'equipos', name: 'teams', component: () => import('../pages/public/TeamsPage.vue') },
      { path: 'equipos/:id', name: 'team-detail', component: () => import('../pages/public/TeamDetailPage.vue') },
      { path: 'jugadores', name: 'players', component: () => import('../pages/public/PlayersPage.vue') },
      { path: 'jugadores/:id', name: 'player-detail', component: () => import('../pages/public/PlayerDetailPage.vue') },
      { path: 'partidos', name: 'matches', component: () => import('../pages/public/MatchesPage.vue') },
      { path: 'partidos/:id', name: 'match-detail', component: () => import('../pages/public/MatchDetailPage.vue') },
      { path: 'posiciones/:id?', name: 'standings', component: () => import('../pages/public/StandingsPage.vue') },
      { path: 'estadisticas/:id?', name: 'stats', component: () => import('../pages/public/StatsPage.vue') },
      { path: 'calendario', name: 'calendar', component: () => import('../pages/public/CalendarPage.vue') },
      { path: 'cruces/:id?', name: 'brackets', component: () => import('../pages/public/BracketPage.vue') },
      { path: 'login', name: 'login', component: () => import('../pages/public/LoginPage.vue') },
    ],
  },

  // ── Admin routes ──
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: '', name: 'admin-dashboard', component: () => import('../pages/admin/DashboardPage.vue') },
      { path: 'campeonatos', name: 'admin-championships', component: () => import('../pages/admin/ChampionshipsManage.vue') },
      { path: 'campeonatos/crear', name: 'admin-championship-create', component: () => import('../pages/admin/ChampionshipForm.vue') },
      { path: 'campeonatos/:id/editar', name: 'admin-championship-edit', component: () => import('../pages/admin/ChampionshipForm.vue') },
      { path: 'equipos', name: 'admin-teams', component: () => import('../pages/admin/TeamsManage.vue') },
      { path: 'equipos/crear', name: 'admin-team-create', component: () => import('../pages/admin/TeamForm.vue') },
      { path: 'equipos/:id/editar', name: 'admin-team-edit', component: () => import('../pages/admin/TeamForm.vue') },
      { path: 'jugadores', name: 'admin-players', component: () => import('../pages/admin/PlayersManage.vue') },
      { path: 'jugadores/crear', name: 'admin-player-create', component: () => import('../pages/admin/PlayerForm.vue') },
      { path: 'jugadores/:id/editar', name: 'admin-player-edit', component: () => import('../pages/admin/PlayerForm.vue') },
      { path: 'partidos', name: 'admin-matches', component: () => import('../pages/admin/MatchesManage.vue') },
      { path: 'partidos/crear', name: 'admin-match-create', component: () => import('../pages/admin/MatchForm.vue') },
      { path: 'partidos/:id/editar', name: 'admin-match-edit', component: () => import('../pages/admin/MatchForm.vue') },
      { path: 'partidos/:id/jugar', name: 'admin-match-play', component: () => import('../pages/admin/MatchPlay.vue') },
      { path: 'programacion', name: 'admin-schedule', component: () => import('../pages/admin/ScheduleManage.vue') },
      { path: 'formatos/:championshipId', name: 'admin-format-builder', component: () => import('../pages/admin/FormatBuilder.vue') },
      { path: 'posiciones', name: 'admin-standings', component: () => import('../pages/admin/StandingsManage.vue') },
      { path: 'usuarios', name: 'admin-users', component: () => import('../pages/admin/UsersManage.vue') },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

// Navigation guard
router.beforeEach(async (to) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    await authStore.fetchUser();
    if (!authStore.isAuthenticated) {
      return { name: 'login', query: { redirect: to.fullPath } };
    }
  }

  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return { name: 'home' };
  }
});

export default router;
