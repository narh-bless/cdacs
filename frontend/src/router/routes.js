const routes = [
  // Public routes (Auth)
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    component: () => import('pages/auth/LoginPage.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    component: () => import('pages/auth/RegisterPage.vue'),
    meta: { requiresGuest: true },
  },

  // Protected routes (Authenticated users)
  {
    path: '/app',
    component: () => import('layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/app/dashboard',
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('pages/dashboards/DashboardPage.vue'),
      },
      {
        path: 'dashboard/user',
        name: 'user-dashboard',
        component: () => import('pages/dashboards/UserDashboard.vue'),
        meta: { roles: ['user'] },
      },
      {
        path: 'dashboard/pastor',
        name: 'pastor-dashboard',
        component: () => import('pages/dashboards/PastorDashboard.vue'),
        meta: { roles: ['pastor'] },
      },
      {
        path: 'dashboard/finance',
        name: 'finance-dashboard',
        component: () => import('pages/dashboards/FinanceDashboard.vue'),
        meta: { roles: ['finance_committee'] },
      },
      {
        path: 'dashboard/admin',
        name: 'admin-dashboard',
        component: () => import('pages/dashboards/AdminDashboard.vue'),
        meta: { roles: ['administrator'] },
      },
      // Feature pages (to be implemented)
      {
        path: 'announcements',
        name: 'announcements',
        component: () => import('pages/announcements/AnnouncementsList.vue'),
      },
      {
        path: 'announcements/create',
        name: 'announcements-create',
        component: () => import('pages/announcements/AnnouncementForm.vue'),
      },
      {
        path: 'events',
        name: 'events',
        component: () => import('pages/events/EventsList.vue'),
      },
      {
        path: 'messages',
        name: 'messages',
        component: () => import('pages/messages/MessagesList.vue'),
      },
      {
        path: 'contributions',
        name: 'contributions',
        component: () => import('pages/contributions/ContributionsList.vue'),
      },
      {
        path: 'ministries',
        name: 'ministries',
        component: () => import('pages/ministries/MinistriesList.vue'),
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('pages/users/UserList.vue'),
        meta: { roles: ['administrator', 'pastor'] },
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('pages/ProfilePage.vue'),
      },
    ],
  },

  // Always leave this as last one
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
]

export default routes
