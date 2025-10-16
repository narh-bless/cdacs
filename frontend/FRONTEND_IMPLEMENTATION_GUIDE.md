# Church Management System - Frontend Implementation Guide

This guide provides a complete implementation roadmap for the Church Management System frontend built with Quasar Framework (Vue 3).

## ✅ Already Completed

1. **Quasar Project Setup** - Created with Vite, Pinia, Axios, and ESLint
2. **API Service** (`src/services/api.js`) - Complete with all endpoints and JWT interceptors
3. **Auth Store** (`src/stores/auth.js`) - Pinia store with authentication logic

## 📋 Implementation Steps

### Step 1: Update Router Configuration

**File: `src/router/routes.js`**

```javascript
const routes = [
  // Public routes
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    component: () => import('pages/auth/LoginPage.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    component: () => import('pages/auth/RegisterPage.vue'),
    meta: { requiresGuest: true }
  },
  
  // Protected routes
  {
    path: '/app',
    component: () => import('layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      // Dashboard routes
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('pages/dashboards/DashboardPage.vue')
      },
      
      // Announcements
      {
        path: 'announcements',
        name: 'announcements',
        component: () => import('pages/announcements/AnnouncementsList.vue')
      },
      {
        path: 'announcements/create',
        name: 'announcement-create',
        component: () => import('pages/announcements/AnnouncementForm.vue'),
        meta: { roles: ['pastor', 'administrator'] }
      },
      {
        path: 'announcements/:id',
        name: 'announcement-view',
        component: () => import('pages/announcements/AnnouncementView.vue')
      },
      
      // Events
      {
        path: 'events',
        name: 'events',
        component: () => import('pages/events/EventsList.vue')
      },
      {
        path: 'events/create',
        name: 'event-create',
        component: () => import('pages/events/EventForm.vue'),
        meta: { roles: ['pastor', 'administrator'] }
      },
      {
        path: 'events/:id',
        name: 'event-view',
        component: () => import('pages/events/EventView.vue')
      },
      
      // Messages
      {
        path: 'messages',
        name: 'messages',
        component: () => import('pages/messages/MessagesList.vue')
      },
      {
        path: 'messages/compose',
        name: 'message-compose',
        component: () => import('pages/messages/MessageCompose.vue')
      },
      {
        path: 'messages/:id',
        name: 'message-view',
        component: () => import('pages/messages/MessageView.vue')
      },
      
      // Contributions
      {
        path: 'contributions',
        name: 'contributions',
        component: () => import('pages/contributions/ContributionsList.vue')
      },
      {
        path: 'contributions/create',
        name: 'contribution-create',
        component: () => import('pages/contributions/ContributionForm.vue'),
        meta: { roles: ['finance_committee', 'administrator'] }
      },
      {
        path: 'contributions/reports',
        name: 'contribution-reports',
        component: () => import('pages/contributions/ContributionReports.vue'),
        meta: { roles: ['finance_committee', 'administrator'] }
      },
      
      // Ministries
      {
        path: 'ministries',
        name: 'ministries',
        component: () => import('pages/ministries/MinistriesList.vue')
      },
      {
        path: 'ministries/create',
        name: 'ministry-create',
        component: () => import('pages/ministries/MinistryForm.vue'),
        meta: { roles: ['administrator'] }
      },
      {
        path: 'ministries/:id',
        name: 'ministry-view',
        component: () => import('pages/ministries/MinistryView.vue')
      },
      
      // Users (Admin only)
      {
        path: 'users',
        name: 'users',
        component: () => import('pages/users/UsersList.vue'),
        meta: { roles: ['administrator'] }
      },
      {
        path: 'users/:id',
        name: 'user-view',
        component: () => import('pages/users/UserView.vue')
      },
      
      // Profile
      {
        path: 'profile',
        name: 'profile',
        component: () => import('pages/ProfilePage.vue')
      }
    ]
  },
  
  // 404 page
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
];

export default routes;
```

### Step 2: Add Router Guards

**File: `src/router/index.js`** (Update existing file)

```javascript
import { route } from 'quasar/wrappers';
import { createRouter, createMemoryHistory, createWebHistory, createWebHashHistory } from 'vue-router';
import routes from './routes';
import { useAuthStore } from 'src/stores/auth';

export default route(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : process.env.VUE_ROUTER_MODE === 'history'
      ? createWebHistory
      : createWebHashHistory;

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,
    history: createHistory(process.env.VUE_ROUTER_BASE),
  });

  // Navigation guards
  Router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresGuest = to.matched.some(record => record.meta.requiresGuest);
    const requiredRoles = to.meta.roles;

    // Check if route requires authentication
    if (requiresAuth && !authStore.isAuthenticated) {
      next('/login');
      return;
    }

    // Check if route is for guests only (login/register)
    if (requiresGuest && authStore.isAuthenticated) {
      next('/app/dashboard');
      return;
    }

    // Check role-based access
    if (requiredRoles && authStore.isAuthenticated) {
      const hasRequiredRole = requiredRoles.some(role => authStore.hasRole(role));
      if (!hasRequiredRole) {
        next('/app/dashboard'); // Redirect to dashboard if no permission
        return;
      }
    }

    next();
  });

  return Router;
});
```

### Step 3: Create Login Page

**File: `src/pages/auth/LoginPage.vue`**

```vue
<template>
  <q-layout view="lHh lpr lFf" class="bg-grey-1">
    <q-page-container>
      <q-page class="flex flex-center">
        <q-card class="q-pa-md shadow-2" style="width: 400px">
          <q-card-section class="text-center">
            <div class="text-h4 text-primary q-mb-sm">
              <q-icon name="church" size="48px" />
            </div>
            <div class="text-h5 text-weight-bold">Church Management</div>
            <div class="text-subtitle2 text-grey-7">Sign in to continue</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="handleLogin" class="q-gutter-md">
              <q-input
                v-model="form.email"
                type="email"
                label="Email"
                outlined
                :rules="[val => !!val || 'Email is required']"
                prepend-inner-icon="email"
              />

              <q-input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                label="Password"
                outlined
                :rules="[val => !!val || 'Password is required']"
                prepend-inner-icon="lock"
              >
                <template v-slot:append>
                  <q-icon
                    :name="showPassword ? 'visibility_off' : 'visibility'"
                    class="cursor-pointer"
                    @click="showPassword = !showPassword"
                  />
                </template>
              </q-input>

              <q-btn
                type="submit"
                label="Sign In"
                color="primary"
                class="full-width"
                size="lg"
                :loading="authStore.loading"
              />

              <div class="text-center q-mt-md">
                <router-link to="/register" class="text-primary">
                  Don't have an account? Register here
                </router-link>
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: ''
});

const showPassword = ref(false);

const handleLogin = async () => {
  const success = await authStore.login(form.value);
  if (success) {
    router.push('/app/dashboard');
  }
};
</script>
```

### Step 4: Create Register Page

**File: `src/pages/auth/RegisterPage.vue`**

```vue
<template>
  <q-layout view="lHh lpr lFf" class="bg-grey-1">
    <q-page-container>
      <q-page class="flex flex-center">
        <q-card class="q-pa-md shadow-2" style="width: 500px">
          <q-card-section class="text-center">
            <div class="text-h4 text-primary q-mb-sm">
              <q-icon name="church" size="48px" />
            </div>
            <div class="text-h5 text-weight-bold">Join Our Church</div>
            <div class="text-subtitle2 text-grey-7">Create your account</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="handleRegister" class="q-gutter-md">
              <q-input
                v-model="form.name"
                label="Full Name *"
                outlined
                :rules="[val => !!val || 'Name is required']"
              />

              <q-input
                v-model="form.email"
                type="email"
                label="Email *"
                outlined
                :rules="[val => !!val || 'Email is required']"
              />

              <q-input
                v-model="form.phone"
                label="Phone Number"
                outlined
                mask="(###) ###-####"
              />

              <q-input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                label="Password *"
                outlined
                :rules="[
                  val => !!val || 'Password is required',
                  val => val.length >= 6 || 'Password must be at least 6 characters'
                ]"
              >
                <template v-slot:append>
                  <q-icon
                    :name="showPassword ? 'visibility_off' : 'visibility'"
                    class="cursor-pointer"
                    @click="showPassword = !showPassword"
                  />
                </template>
              </q-input>

              <q-input
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                label="Confirm Password *"
                outlined
                :rules="[
                  val => !!val || 'Please confirm password',
                  val => val === form.password || 'Passwords do not match'
                ]"
              />

              <q-btn
                type="submit"
                label="Register"
                color="primary"
                class="full-width"
                size="lg"
                :loading="authStore.loading"
              />

              <div class="text-center q-mt-md">
                <router-link to="/login" class="text-primary">
                  Already have an account? Sign in
                </router-link>
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: ''
});

const showPassword = ref(false);

const handleRegister = async () => {
  const success = await authStore.register(form.value);
  if (success) {
    router.push('/app/dashboard');
  }
};
</script>
```

### Step 5: Update Main Layout

**File: `src/layouts/MainLayout.vue`**

```vue
<template>
  <q-layout view="hHh lpR fFf">
    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn dense flat round icon="menu" @click="toggleLeftDrawer" />

        <q-toolbar-title>
          <q-icon name="church" size="28px" class="q-mr-sm" />
          Church Management
        </q-toolbar-title>

        <q-btn flat round dense icon="notifications">
          <q-badge color="red" floating>3</q-badge>
        </q-btn>

        <q-btn flat round dense icon="account_circle">
          <q-menu>
            <q-list style="min-width: 200px">
              <q-item>
                <q-item-section>
                  <q-item-label class="text-weight-bold">
                    {{ authStore.user?.name }}
                  </q-item-label>
                  <q-item-label caption>
                    {{ authStore.user?.email }}
                  </q-item-label>
                </q-item-section>
              </q-item>
              
              <q-separator />
              
              <q-item clickable v-ripple @click="$router.push('/app/profile')">
                <q-item-section avatar>
                  <q-icon name="person" />
                </q-item-section>
                <q-item-section>Profile</q-item-section>
              </q-item>
              
              <q-item clickable v-ripple @click="handleLogout">
                <q-item-section avatar>
                  <q-icon name="logout" />
                </q-item-section>
                <q-item-section>Logout</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered>
      <q-list>
        <q-item-label header>Navigation</q-item-label>

        <q-item clickable v-ripple to="/app/dashboard">
          <q-item-section avatar>
            <q-icon name="dashboard" />
          </q-item-section>
          <q-item-section>Dashboard</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/app/announcements">
          <q-item-section avatar>
            <q-icon name="campaign" />
          </q-item-section>
          <q-item-section>Announcements</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/app/events">
          <q-item-section avatar>
            <q-icon name="event" />
          </q-item-section>
          <q-item-section>Events</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/app/messages">
          <q-item-section avatar>
            <q-icon name="message" />
          </q-item-section>
          <q-item-section>Messages</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/app/ministries">
          <q-item-section avatar>
            <q-icon name="groups" />
          </q-item-section>
          <q-item-section>Ministries</q-item-section>
        </q-item>

        <!-- Finance Committee & Admin -->
        <q-item
          v-if="authStore.isFinanceCommittee || authStore.isAdministrator"
          clickable
          v-ripple
          to="/app/contributions"
        >
          <q-item-section avatar>
            <q-icon name="attach_money" />
          </q-item-section>
          <q-item-section>Contributions</q-item-section>
        </q-item>

        <!-- Admin Only -->
        <q-item
          v-if="authStore.isAdministrator"
          clickable
          v-ripple
          to="/app/users"
        >
          <q-item-section avatar>
            <q-icon name="people" />
          </q-item-section>
          <q-item-section>Users</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const leftDrawerOpen = ref(false);

const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value;
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
```

## 🎨 Dashboard Pages

Each role should have a customized dashboard view. Create these files:

1. **`src/pages/dashboards/DashboardPage.vue`** - Main dashboard router
2. **`src/pages/dashboards/UserDashboard.vue`** - Member view
3. **`src/pages/dashboards/PastorDashboard.vue`** - Pastor view with quick actions
4. **`src/pages/dashboards/FinanceDashboard.vue`** - Finance committee view with reports
5. **`src/pages/dashboards/AdminDashboard.vue`** - Administrator view with statistics

## 🚀 Running the Application

1. **Start Backend** (in `church-system` directory):
   ```bash
   php artisan serve
   ```

2. **Start Frontend** (in `frontend` directory):
   ```bash
   quasar dev
   ```

3. **Access Application**:
   - Frontend: http://localhost:9000
   - Backend API: http://127.0.0.1:8000/api

## 📦 Additional Dependencies (if needed)

```bash
npm install date-fns chart.js vue-chartjs
```

## 🎯 Next Steps

1. Implement all page components mentioned in routes
2. Add form validation and error handling
3. Create reusable components for tables, cards, forms
4. Implement real-time notifications
5. Add loading states and skeletons
6. Implement search and filtering
7. Add data export functionality
8. Implement print views for reports

## 📚 Resources

- Quasar Documentation: https://quasar.dev
- Vue 3 Documentation: https://vuejs.org
- Pinia Documentation: https://pinia.vuejs.org

This setup provides a solid foundation for the Church Management System frontend!

