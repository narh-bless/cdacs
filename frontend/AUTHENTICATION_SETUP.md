# Authentication & Navigation Setup

## ✅ What's Been Implemented

### 1. **Complete Routing System**
- **Public Routes** (`/login`, `/register`) - Accessible only when NOT logged in
- **Protected Routes** (`/app/*`) - Accessible only when logged in
- **Role-based Dashboards** - Automatic routing based on user role
- **Navigation Guards** - Automatic redirects based on auth status

### 2. **MainLayout with Sidebar**
- **Header** with user menu (logout, profile)
- **Sidebar** with navigation links
- **Role-based menu items** (e.g., Contributions only for Finance & Admin)
- **Active route highlighting**
- **User info display** showing name and role

### 3. **Dashboard Pages**
All 4 role-based dashboards are complete:
- ✅ **User Dashboard** - Member view with announcements, events, ministries
- ✅ **Pastor Dashboard** - Pastor tools, broadcast messaging, ministry management
- ✅ **Finance Dashboard** - Financial stats, contributions, reports
- ✅ **Admin Dashboard** - User management, system stats, activity timeline

### 4. **Placeholder Pages**
Created placeholder pages for all features (to be implemented next):
- Announcements List & Form
- Events List
- Messages List
- Contributions List
- Ministries List
- Profile Page

---

## 🚀 How It Works

### Authentication Flow

```
1. User visits app (/) → Redirects to /login
2. User logs in → Auth store saves JWT token & user data
3. Login redirects to → /app/dashboard
4. DashboardPage component → Detects user role
5. Automatically loads → Correct dashboard component
```

### Role-Based Dashboard Loading

The `DashboardPage.vue` component dynamically loads the appropriate dashboard:

```javascript
if (user is Administrator) → AdminDashboard
else if (user is Pastor) → PastorDashboard
else if (user is Finance Committee) → FinanceDashboard
else → UserDashboard
```

### Navigation Guards

**Routes with `requiresAuth: true`**
- If NOT authenticated → Redirect to `/login`
- If authenticated → Allow access

**Routes with `requiresGuest: true`** (login/register)
- If authenticated → Redirect to `/app/dashboard`
- If NOT authenticated → Allow access

---

## 📂 File Structure

```
frontend/
├── src/
│   ├── layouts/
│   │   └── MainLayout.vue          ← Sidebar + Header
│   ├── pages/
│   │   ├── auth/
│   │   │   ├── LoginPage.vue       ← Login form
│   │   │   └── RegisterPage.vue    ← Registration form
│   │   ├── dashboards/
│   │   │   ├── DashboardPage.vue   ← Router component
│   │   │   ├── UserDashboard.vue   ← Member dashboard
│   │   │   ├── PastorDashboard.vue ← Pastor dashboard
│   │   │   ├── FinanceDashboard.vue← Finance dashboard
│   │   │   └── AdminDashboard.vue  ← Admin dashboard
│   │   ├── announcements/
│   │   ├── events/
│   │   ├── messages/
│   │   ├── contributions/
│   │   ├── ministries/
│   │   └── ProfilePage.vue
│   ├── router/
│   │   ├── index.js                ← Router with navigation guards
│   │   └── routes.js               ← Route definitions
│   ├── stores/
│   │   └── auth.js                 ← Pinia auth store
│   └── services/
│       └── api.js                  ← Axios instance + API methods
```

---

## 🧪 Testing the Flow

### 1. **Start the Backend**
```bash
cd church-system
php artisan serve
```

### 2. **Start the Frontend**
```bash
cd frontend
quasar dev
```

### 3. **Test Authentication**

#### **Register a New User:**
1. Visit http://localhost:9000 (should redirect to `/login`)
2. Click "Register here"
3. Fill in the registration form
4. Submit → Should login and redirect to User Dashboard

#### **Login with Existing User:**
1. Visit http://localhost:9000/login
2. Enter credentials
3. Submit → Should redirect to appropriate dashboard based on role

#### **Test Navigation:**
1. After login, click sidebar items
2. Click Dashboard → Should show your role's dashboard
3. Click Announcements, Events, etc. → Should navigate

#### **Test Logout:**
1. Click user icon in header (top right)
2. Click "Logout"
3. Should redirect to `/login`
4. JWT token cleared from localStorage

---

## 🔑 Role-Based Features

### **User/Member** (`user`)
Can access:
- Dashboard
- View Announcements
- View Events
- Messages
- Ministries

### **Pastor** (`pastor`)
Can access:
- All User features +
- Create Announcements
- Create Events
- Broadcast Messages
- Manage Ministries

### **Finance Committee** (`finance_committee`)
Can access:
- All User features +
- View/Record Contributions
- Financial Reports

### **Administrator** (`administrator`)
Can access:
- ALL features
- User Management
- System Settings
- Reports
- Full administrative control

---

## 🔄 How Login Redirects Work

### **After Login Success:**

```javascript
// In LoginPage.vue
const handleLogin = async () => {
  const success = await authStore.login(form.value);
  if (success) {
    router.push('/app/dashboard'); // Goes to DashboardPage
  }
};
```

### **DashboardPage Auto-Routes:**

```javascript
// In DashboardPage.vue
const dashboardComponent = computed(() => {
  if (authStore.isAdministrator) return AdminDashboard;
  else if (authStore.isPastor) return PastorDashboard;
  else if (authStore.isFinanceCommittee) return FinanceDashboard;
  else return UserDashboard;
});
```

So the flow is:
1. Login → `/app/dashboard`
2. `DashboardPage` → Dynamically loads correct dashboard component
3. User sees their role-specific dashboard

---

## 🎨 Sidebar Navigation

The sidebar shows different menu items based on role:

```javascript
// MainLayout.vue
<q-item v-if="authStore.isFinanceCommittee || authStore.isAdministrator">
  <!-- Contributions menu only for Finance & Admin -->
</q-item>

<template v-if="authStore.isAdministrator">
  <!-- Admin section only for Administrators -->
</template>
```

---

## 📝 Next Steps

The foundation is complete! Next, implement:

1. **Announcements List & Form** - Full CRUD for announcements
2. **Events List & Form** - Event management
3. **Messages** - Inbox/sent messages
4. **Contributions** - Full contribution tracking
5. **Ministries** - Ministry management
6. **Profile Page** - User profile editing

All the routing, authentication, and layout is ready to go! 🎉

