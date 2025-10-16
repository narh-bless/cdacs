# Church Management System - Frontend

A modern, responsive church management frontend built with Quasar Framework (Vue 3), featuring role-based dashboards and comprehensive church administration tools.

## 🚀 Quick Start

### Prerequisites
- Node.js (v16 or higher)
- npm or yarn
- Backend API running on `http://127.0.0.1:8000`

### Installation

```bash
# Install dependencies
npm install

# Start development server
quasar dev
# or
npm run dev
```

The application will be available at `http://localhost:9000`

## ✅ What's Already Set Up

1. ✅ **Quasar Framework** with Vue 3 Composition API
2. ✅ **Pinia State Management**
3. ✅ **Axios HTTP Client** with JWT interceptors
4. ✅ **Authentication Store** (`src/stores/auth.js`)
5. ✅ **API Service** (`src/services/api.js`) with all endpoints
6. ✅ **Login Page** (`src/pages/auth/LoginPage.vue`)
7. ✅ **Project Structure** with organized folders

## 📂 Project Structure

```
frontend/
├── src/
│   ├── assets/              # Static assets
│   ├── boot/                # Boot files (axios config)
│   ├── components/          # Reusable Vue components
│   ├── composables/         # Vue composables
│   ├── css/                 # Global styles
│   ├── layouts/             # Layout components
│   │   └── MainLayout.vue   # Main app layout (UPDATE THIS)
│   ├── pages/               # Page components
│   │   ├── auth/            # Authentication pages
│   │   │   ├── LoginPage.vue        ✅ Created
│   │   │   └── RegisterPage.vue     📝 To create
│   │   ├── dashboards/      # Role-based dashboards
│   │   ├── announcements/   # Announcement pages
│   │   ├── events/          # Event pages
│   │   ├── messages/        # Messaging pages
│   │   ├── contributions/   # Contribution pages
│   │   ├── ministries/      # Ministry pages
│   │   └── users/           # User management pages
│   ├── router/              # Vue Router configuration
│   │   ├── index.js         # Router setup (UPDATE THIS)
│   │   └── routes.js        # Route definitions (UPDATE THIS)
│   ├── services/            # API services
│   │   └── api.js           ✅ Complete with all endpoints
│   └── stores/              # Pinia stores
│       ├── auth.js          ✅ Authentication store
│       └── index.js         # Store configuration
├── public/                  # Public static files
├── quasar.config.js         # Quasar configuration
└── package.json
```

## 🔐 Authentication Flow

1. User enters credentials on Login Page
2. Auth Store sends request to Laravel API
3. API returns JWT token and user data
4. Token stored in localStorage
5. Axios interceptor adds token to all requests
6. Router guards protect authenticated routes

## 🎨 Creating Pages - Quick Guide

### Example: Create Register Page

**File:** `src/pages/auth/RegisterPage.vue`

```vue
<template>
  <q-layout view="lHh lpr lFf" class="bg-grey-1">
    <q-page-container>
      <q-page class="flex flex-center">
        <q-card class="q-pa-md" style="width: 500px">
          <q-card-section class="text-center">
            <div class="text-h5">Register</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="handleRegister">
              <q-input v-model="form.name" label="Name" outlined />
              <q-input v-model="form.email" type="email" label="Email" outlined />
              <q-input v-model="form.password" type="password" label="Password" outlined />
              <q-input v-model="form.password_confirmation" type="password" label="Confirm Password" outlined />
              
              <q-btn type="submit" label="Register" color="primary" class="full-width" />
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
  password: '',
  password_confirmation: ''
});

const handleRegister = async () => {
  const success = await authStore.register(form.value);
  if (success) router.push('/app/dashboard');
};
</script>
```

## 🛣️ Update Router

### Step 1: Update `src/router/routes.js`

```javascript
const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    component: () => import('pages/auth/LoginPage.vue')
  },
  {
    path: '/register',
    component: () => import('pages/auth/RegisterPage.vue')
  },
  {
    path: '/app',
    component: () => import('layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        component: () => import('pages/dashboards/DashboardPage.vue')
      },
      {
        path: 'announcements',
        component: () => import('pages/announcements/AnnouncementsList.vue')
      },
      // Add more routes...
    ]
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
];

export default routes;
```

### Step 2: Add Auth Guards in `src/router/index.js`

```javascript
import { useAuthStore } from 'src/stores/auth';

// In the router export function, add:
Router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
  } else if ((to.path === '/login' || to.path === '/register') && authStore.isAuthenticated) {
    next('/app/dashboard');
  } else {
    next();
  }
});
```

## 📋 Using the API Service

### In Any Component:

```vue
<script setup>
import { ref, onMounted } from 'vue';
import { announcementsAPI } from 'src/services/api';

const announcements = ref([]);
const loading = ref(false);

const fetchAnnouncements = async () => {
  loading.value = true;
  try {
    const response = await announcementsAPI.getAll();
    announcements.value = response.data;
  } catch (error) {
    console.error('Error fetching announcements:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchAnnouncements();
});
</script>
```

## 🎯 Role-Based Access in Components

```vue
<template>
  <!-- Show only to Pastors and Admins -->
  <q-btn
    v-if="authStore.isPastor || authStore.isAdministrator"
    label="Create Announcement"
    @click="createAnnouncement"
  />
  
  <!-- Show only to Finance Committee and Admins -->
  <q-btn
    v-if="authStore.isFinanceCommittee || authStore.isAdministrator"
    label="Record Contribution"
  />
</template>

<script setup>
import { useAuthStore } from 'src/stores/auth';

const authStore = useAuthStore();
</script>
```

## 🎨 Quasar Components Examples

### Data Table
```vue
<q-table
  :rows="announcements"
  :columns="columns"
  row-key="id"
  :loading="loading"
/>
```

### Dialog
```vue
<q-dialog v-model="showDialog">
  <q-card>
    <q-card-section>
      <div class="text-h6">Announcement Details</div>
    </q-card-section>
    <q-card-actions align="right">
      <q-btn flat label="Close" v-close-popup />
    </q-card-actions>
  </q-card>
</q-dialog>
```

### Form with Validation
```vue
<q-form @submit="onSubmit" class="q-gutter-md">
  <q-input
    v-model="title"
    label="Title *"
    :rules="[val => !!val || 'Title is required']"
  />
  <q-btn type="submit" label="Submit" color="primary" />
</q-form>
```

## 🔧 Available Scripts

```bash
# Start development server
quasar dev

# Build for production
quasar build

# Lint code
npm run lint

# Format code
npm run format
```

## 📚 Next Steps

1. ✅ Login page is ready
2. 📝 Create Register page (copy from FRONTEND_IMPLEMENTATION_GUIDE.md)
3. 📝 Update router configuration
4. 📝 Update MainLayout.vue with navigation
5. 📝 Create Dashboard pages for each role
6. 📝 Implement feature modules (announcements, events, etc.)

## 🆘 Common Issues

### CORS Error
Make sure Laravel backend has CORS configured properly in `config/cors.php`

### 401 Unauthorized
Check if JWT token is valid and not expired. Token is automatically refreshed by the auth store.

### API Not Found
Verify backend is running on `http://127.0.0.1:8000` and routes are correct.

## 📖 Documentation Links

- [Quasar Framework](https://quasar.dev)
- [Vue 3 Documentation](https://vuejs.org)
- [Pinia Store](https://pinia.vuejs.org)
- [Vue Router](https://router.vuejs.org)

## 🎉 You're Ready!

The foundation is set! Follow the FRONTEND_IMPLEMENTATION_GUIDE.md for detailed component examples and continue building the remaining pages.

Happy coding! 🚀⛪

