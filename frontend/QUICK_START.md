# Quick Start Guide

## 🚀 Get Started in 3 Steps

### Step 1: Start Backend Server
```bash
cd church-system
php artisan serve
```
Backend should be running at: `http://localhost:8000`

### Step 2: Start Frontend Server
```bash
cd frontend
quasar dev
```
Frontend should be running at: `http://localhost:9000`

### Step 3: Login & Explore!

---

## 🔐 Test Accounts

Use these test accounts to explore different roles:

### 👤 Regular Member
```
Email: user@church.test
Password: password
```
**What you'll see:**
- Basic dashboard with announcements, events, ministries
- Can view content
- Can send messages
- Can see own contributions

### ⛪ Pastor
```
Email: pastor@church.test
Password: password
```
**What you'll see:**
- Extended dashboard with management tools
- Can create announcements
- Can create events
- Can broadcast messages to all members
- Can manage ministries

### 💰 Finance Committee
```
Email: finance@church.test
Password: password
```
**What you'll see:**
- Financial dashboard with stats
- Can record contributions
- Can view all financial data
- Can generate reports
- Access to contributions page

### 👑 Administrator
```
Email: admin@church.test
Password: password
```
**What you'll see:**
- Complete system dashboard
- Can manage all users
- Can assign roles
- Can access all features
- System settings and reports

---

## 📍 What to Explore

### 1. Login Flow
1. Open http://localhost:9000
2. Should auto-redirect to `/login`
3. Enter credentials (use test accounts above)
4. Click "Sign In"
5. ✅ Should redirect to your role-based dashboard!

### 2. Navigation
After logging in:
- **Click sidebar menu items** → Navigate between pages
- **Click Dashboard** → Returns to your dashboard
- **Click user icon (top right)** → See profile menu
- **Click Logout** → Returns to login page

### 3. Role-Based Features

#### As a Member:
- View announcements
- See upcoming events
- Check ministry memberships
- View contribution history

#### As a Pastor:
- Create new announcements
- Schedule events
- Send broadcast messages
- Manage ministries

#### As Finance Committee:
- Record new contributions
- View financial stats
- See recent contributions
- Generate reports (placeholder)

#### As Administrator:
- Add new users
- View system stats
- See recent activity
- Access admin section in sidebar

---

## 🎨 What You Should See

### Login Page
- Church icon
- "Church Management System" title
- Email and password fields
- "Sign In" button
- "Register here" link

### After Login (Dashboard)
- **Header** (top):
  - Menu button (hamburger icon)
  - "Church Management System" title
  - User icon with dropdown menu

- **Sidebar** (left):
  - Your name and role
  - Navigation menu:
    - Dashboard
    - Announcements
    - Events
    - Messages
    - Ministries
    - Contributions (Finance/Admin only)
    - Administration section (Admin only)

- **Main Content** (center):
  - Role-specific dashboard with stats, cards, lists

---

## 🧪 Testing the Auth Flow

### Test 1: Protected Routes
1. Logout (if logged in)
2. Try to visit: http://localhost:9000/app/dashboard
3. ✅ Should redirect to `/login`

### Test 2: Guest Routes
1. Login with any account
2. Try to visit: http://localhost:9000/login
3. ✅ Should redirect to `/app/dashboard`

### Test 3: Role-Based Dashboards
1. Login as **user@church.test** → See User Dashboard
2. Logout
3. Login as **pastor@church.test** → See Pastor Dashboard
4. Logout
5. Login as **finance@church.test** → See Finance Dashboard
6. Logout
7. Login as **admin@church.test** → See Admin Dashboard

### Test 4: Sidebar Navigation
1. Login as admin
2. Look at sidebar:
   - ✅ Should see "Contributions" menu item
   - ✅ Should see "ADMINISTRATION" section
3. Logout
4. Login as regular member
5. Look at sidebar:
   - ✅ Should NOT see "Contributions"
   - ✅ Should NOT see "ADMINISTRATION" section

---

## ✅ What's Working

- ✅ Login/Register with validation
- ✅ JWT token storage and auto-attachment
- ✅ Auto-redirect based on auth status
- ✅ Role-based dashboard loading
- ✅ Sidebar with navigation
- ✅ User menu with logout
- ✅ Role-based menu visibility
- ✅ All 4 dashboards (User, Pastor, Finance, Admin)
- ✅ Real data from backend API
- ✅ Loading states and error handling
- ✅ Responsive design (try resizing window!)

---

## 📱 Try This!

### On Desktop:
- Sidebar stays open on the left
- Full stats cards in a row
- Tables show all columns

### On Mobile:
- Sidebar hidden by default
- Click hamburger menu to open
- Stats cards stack vertically
- Tables scroll horizontally

---

## 🔧 Troubleshooting

### Problem: Login page keeps redirecting to itself
**Solution:** Clear localStorage and try again:
```javascript
// In browser console (F12)
localStorage.clear()
location.reload()
```

### Problem: "Network Error" on login
**Solution:** Make sure backend is running:
```bash
cd church-system
php artisan serve
```

### Problem: "401 Unauthorized" errors
**Solution:** Token might be expired. Logout and login again.

### Problem: Sidebar not showing
**Solution:** Click the hamburger menu icon (≡) in top left

### Problem: Changes to .env not taking effect
**Solution:** Restart the Quasar dev server

---

## 📚 File Structure Reference

```
frontend/
├── src/
│   ├── pages/
│   │   ├── auth/
│   │   │   ├── LoginPage.vue        ← You are here after logout
│   │   │   └── RegisterPage.vue     ← New user registration
│   │   └── dashboards/
│   │       ├── DashboardPage.vue    ← Routes to correct dashboard
│   │       ├── UserDashboard.vue    ← Member view
│   │       ├── PastorDashboard.vue  ← Pastor view
│   │       ├── FinanceDashboard.vue ← Finance view
│   │       └── AdminDashboard.vue   ← Admin view
│   ├── layouts/
│   │   └── MainLayout.vue           ← Header + Sidebar
│   ├── stores/
│   │   └── auth.js                  ← Authentication state
│   └── services/
│       └── api.js                   ← API calls
```

---

## 🎯 Next: Build Feature Pages

Now that the foundation is complete, we can build the actual feature pages:

1. **Announcements** - Full CRUD with rich text editor
2. **Events** - Calendar view with registration
3. **Messages** - Inbox/compose messaging system
4. **Contributions** - Financial tracking and reports
5. **Ministries** - Ministry management with members
6. **Profile** - User profile editing

Let me know which feature you'd like to implement next! 🚀

