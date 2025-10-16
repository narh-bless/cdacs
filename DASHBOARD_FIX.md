# Dashboard Role Column Fix

## Problem
The dashboard was trying to query a `role` column directly on the `users` table, but the system uses a **many-to-many role relationship** through pivot tables.

## Error
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'role' in 'field list'
```

## Root Cause
The database schema uses:
- `roles` table - stores role definitions (user, pastor, finance_committee, administrator)
- `user_roles` table - pivot table linking users to their roles
- `users` table - **does NOT have a `role` column**

## Solution Applied

### 1. Fixed User Roles Distribution Query
**Before:**
```php
'user_roles' => User::select('role', DB::raw('count(*) as count'))
    ->groupBy('role')
    ->get()
```

**After:**
```php
'user_roles' => DB::table('user_roles')
    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
    ->select('roles.name', DB::raw('count(*) as count'))
    ->groupBy('roles.name', 'roles.id')
    ->get()
    ->mapWithKeys(function ($item) {
        return [$item->name => $item->count];
    }),
```

### 2. Fixed Recent Users Query
**Before:**
```php
'recent_users' => User::orderBy('created_at', 'desc')
    ->limit(5)
    ->select('id', 'name', 'email', 'is_active', 'role', 'created_at')
    ->get(),
```

**After:**
```php
'recent_users' => User::with('roles:id,name')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->select('id', 'name', 'email', 'is_active', 'created_at')
    ->get()
    ->map(function ($user) {
        $user->role = $user->roles->first()->name ?? 'user';
        unset($user->roles);
        return $user;
    }),
```

## Files Modified
1. ✅ `church-system/app/Http/Controllers/Api/DashboardController.php`
2. ✅ `church-system/routes/api.php` (changed `/dashboard/admin` to `/dashboard/administrator`)
3. ✅ `DASHBOARD_IMPLEMENTATION.md` (updated documentation)
4. ✅ `DASHBOARD_TESTING_GUIDE.md` (updated user creation examples)

## How to Apply the Fix

### 1. Restart Your Laravel Server
If you have the Laravel development server running:
```bash
# Stop it with Ctrl+C, then restart
cd C:\Users\Aquacy\Documents\cdacs\church-system
php artisan serve
```

### 2. Clear Laravel Caches (if needed)
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 3. Test the Dashboard
1. Login to your frontend application
2. Navigate to the dashboard
3. The admin dashboard should now load without errors

## Expected Behavior Now

### Admin Dashboard
- ✅ Shows user roles distribution correctly
- ✅ Displays recent users with their assigned role
- ✅ All statistics load from database

### Role Assignment Structure
Users can have roles assigned via the `user_roles` pivot table:

```
users (id, name, email, ...)
  ↓
user_roles (user_id, role_id)
  ↓
roles (id, name, display_name)
```

## Verifying Users Have Roles

Run this query to check if users have roles assigned:
```sql
SELECT u.name, r.name as role 
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id;
```

If users don't have roles, assign them using the examples in `DASHBOARD_TESTING_GUIDE.md`.

## Creating Test Data with Roles

### Option 1: Using Tinker
```bash
php artisan tinker
```

```php
// Get roles
$adminRole = App\Models\Role::where('name', 'administrator')->first();
$userRole = App\Models\Role::where('name', 'user')->first();

// Create user and assign role
$user = App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'is_active' => true,
]);

// Attach role
$user->roles()->attach($userRole->id);
```

### Option 2: Seed Roles First
```bash
php artisan db:seed --class=RoleSeeder
```

This will create the 4 default roles:
- user
- pastor
- finance_committee
- administrator

## Common Issues After Fix

### Issue: "No roles found for user"
**Solution:** Assign roles to users using the `user_roles` pivot table

### Issue: "Dashboard still shows errors"
**Solution:** 
1. Clear browser cache (Ctrl+Shift+R)
2. Restart Laravel server
3. Check Laravel logs: `storage/logs/laravel.log`

### Issue: "User roles distribution is empty"
**Solution:** Ensure users have roles assigned in the `user_roles` table

## Testing Checklist

- [ ] Dashboard loads without SQL errors
- [ ] User roles distribution shows correct counts
- [ ] Recent users display with their role names
- [ ] All 4 dashboards work (admin, pastor, finance, user)
- [ ] Statistics are accurate

---

**Status:** ✅ Fixed and Ready to Test
**Date:** October 16, 2025

