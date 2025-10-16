# Dashboard Testing Guide

## Prerequisites

1. **Backend Running**: Ensure Laravel backend is running on `http://127.0.0.1:8000`
2. **Frontend Running**: Ensure Vue/Quasar frontend is running (usually `http://localhost:9000`)
3. **Database Seeded**: Have sample data in the database
4. **JWT Token**: Have valid authentication tokens for different user roles

## Quick Start

### 1. Start Backend
```bash
cd church-system
php artisan serve
```

### 2. Start Frontend
```bash
cd frontend
quasar dev
# or npm run dev
```

## Testing Each Dashboard

### Admin Dashboard

#### Access
1. Login as an administrator user
2. Navigate to `/app/dashboard`
3. Should see "Administrator Dashboard 👑"

#### What to Test
- [ ] Total Users card shows correct count
- [ ] Active Users card shows correct count
- [ ] Total Events card shows correct count
- [ ] Ministries card shows correct count
- [ ] Recent Members list shows last 5 users with active status
- [ ] User Roles distribution shows percentages
- [ ] Monthly Overview shows statistics
- [ ] Quick Actions buttons work (Add User dialog, navigation to routes)

#### Sample API Request
```bash
curl -X GET http://127.0.0.1:8000/api/dashboard/admin \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

#### Expected Response Format
```json
{
  "data": {
    "total_users": 15,
    "active_users": 12,
    "users_this_month": 3,
    "total_events": 8,
    "upcoming_events": 5,
    "events_this_month": 2,
    "total_ministries": 4,
    "active_ministries": 4,
    "total_announcements": 10,
    "published_announcements": 8,
    "announcements_this_month": 3,
    "total_messages": 25,
    "messages_this_month": 10,
    "user_roles": {
      "user": 10,
      "pastor": 2,
      "finance_committee": 2,
      "administrator": 1
    },
    "recent_users": [...],
    "monthly_overview": {...}
  }
}
```

---

### Pastor Dashboard

#### Access
1. Login as a pastor user
2. Navigate to `/app/dashboard`
3. Should see "Pastor Dashboard 🙏"

#### What to Test
- [ ] Total Members card shows correct count
- [ ] Announcements card shows this month's count
- [ ] Upcoming Events card shows correct count
- [ ] Ministries card shows correct count
- [ ] Recent Announcements list shows last 5 with publish status
- [ ] Upcoming Events list shows next 5 events with details
- [ ] Ministry Overview shows active ministries with member counts
- [ ] Quick Actions work (New Announcement, Create Event, Broadcast Message, Manage Ministries)
- [ ] Broadcast Message dialog opens and sends successfully

#### Sample API Request
```bash
curl -X GET http://127.0.0.1:8000/api/dashboard/pastor \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

#### Expected Response Format
```json
{
  "data": {
    "total_members": 15,
    "active_members": 12,
    "total_announcements": 10,
    "published_announcements": 8,
    "announcements_this_month": 3,
    "upcoming_events": 5,
    "total_events": 8,
    "events_this_month": 2,
    "total_ministries": 4,
    "active_ministries": 4,
    "recent_announcements": [...],
    "upcoming_events_list": [...],
    "active_ministries_list": [...]
  }
}
```

---

### Finance Dashboard

#### Access
1. Login as a finance_committee user
2. Navigate to `/app/dashboard`
3. Should see "Finance Committee Dashboard 💰"

#### What to Test
- [ ] Total Contributions card shows this month's total
- [ ] Tithes card shows this month's tithes
- [ ] Offerings card shows this month's offerings
- [ ] Donations card shows this month's donations
- [ ] Recent Contributions table shows last 5 with contributor names
- [ ] Contributions by Type shows breakdown with percentages
- [ ] Monthly Summary shows total income, contributors, and average
- [ ] Quick Actions work (Record Contribution, View Reports, Export Data, Generate Report)
- [ ] Record Contribution dialog opens and saves successfully

#### Sample API Request
```bash
curl -X GET http://127.0.0.1:8000/api/dashboard/finance \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

#### Expected Response Format
```json
{
  "data": {
    "total_contributions": "15000.00",
    "tithes": "8000.00",
    "offerings": "5000.00",
    "donations": "2000.00",
    "special": "0.00",
    "total_contributors": 12,
    "total_contribution_count": 45,
    "average_contribution": "333.33",
    "recent_contributions": [...],
    "contributions_by_type": [...]
  }
}
```

---

### User Dashboard

#### Access
1. Login as a regular user
2. Navigate to `/app/dashboard`
3. Should see "Welcome, [User Name]! 🙏"

#### What to Test
- [ ] My Contributions card shows user's total contribution count
- [ ] My Ministries card shows count of active memberships
- [ ] Unread Messages card shows count of unread messages
- [ ] Upcoming Events card shows count of upcoming events
- [ ] Recent Announcements list shows published announcements
- [ ] Upcoming Events list shows next 5 events
- [ ] My Ministries list shows ministries user is member of
- [ ] Quick Actions work (Send Message, My Profile, My Contributions, View Events)
- [ ] Member since date displays correctly

#### Sample API Request
```bash
curl -X GET http://127.0.0.1:8000/api/dashboard/user \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

#### Expected Response Format
```json
{
  "data": {
    "total_contributions": 12,
    "total_contribution_amount": "5000.00",
    "contributions_this_year": "3500.00",
    "total_ministries": 2,
    "ministries": [...],
    "unread_messages": 3,
    "total_messages": 15,
    "upcoming_events": 5,
    "upcoming_events_list": [...],
    "recent_announcements": [...]
  }
}
```

---

## Common Issues & Solutions

### Issue: Dashboard shows "Failed to load dashboard data"
**Solution**: 
- Check if backend is running
- Verify JWT token is valid
- Check browser console for detailed error
- Verify user has correct role

### Issue: Statistics show zero or empty data
**Solution**:
- Seed database with sample data
- Check database migrations are run
- Verify relationships in models

### Issue: 401 Unauthorized error
**Solution**:
- Login again to get fresh JWT token
- Check token is being sent in Authorization header
- Verify JWT_SECRET in .env matches

### Issue: 500 Server Error
**Solution**:
- Check Laravel logs in `storage/logs/laravel.log`
- Verify all migrations are run
- Check database connection

### Issue: Recent items not showing
**Solution**:
- Add more data to database
- Check date filters (some queries filter by current month)
- Verify eager loading relationships

---

## Sample Data Creation

### Create Test Users
```bash
php artisan tinker
```

```php
// First, ensure roles exist (run the RoleSeeder if not)
// php artisan db:seed --class=RoleSeeder

// Get role IDs
$adminRole = App\Models\Role::where('name', 'administrator')->first();
$pastorRole = App\Models\Role::where('name', 'pastor')->first();
$financeRole = App\Models\Role::where('name', 'finance_committee')->first();
$userRole = App\Models\Role::where('name', 'user')->first();

// Create admin user
$admin = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@church.com',
    'password' => bcrypt('password'),
    'is_active' => true,
]);
$admin->roles()->attach($adminRole->id);

// Create pastor user
$pastor = App\Models\User::create([
    'name' => 'Pastor John',
    'email' => 'pastor@church.com',
    'password' => bcrypt('password'),
    'is_active' => true,
]);
$pastor->roles()->attach($pastorRole->id);

// Create finance user
$finance = App\Models\User::create([
    'name' => 'Finance Manager',
    'email' => 'finance@church.com',
    'password' => bcrypt('password'),
    'is_active' => true,
]);
$finance->roles()->attach($financeRole->id);

// Create regular user
$user = App\Models\User::create([
    'name' => 'John Doe',
    'email' => 'user@church.com',
    'password' => bcrypt('password'),
    'is_active' => true,
]);
$user->roles()->attach($userRole->id);
```

### Create Test Events
```php
Event::create([
    'title' => 'Sunday Service',
    'description' => 'Weekly Sunday worship service',
    'type' => 'service',
    'start_date' => now()->addDays(7),
    'end_date' => now()->addDays(7)->addHours(2),
    'location' => 'Main Sanctuary',
    'organizer_id' => 1,
    'is_published' => true,
]);
```

### Create Test Contributions
```php
Contribution::create([
    'user_id' => 1,
    'type' => 'tithe',
    'amount' => 500,
    'payment_method' => 'cash',
    'contribution_date' => now(),
    'recorded_by' => 1,
]);
```

### Create Test Ministries
```php
$ministry = Ministry::create([
    'name' => 'Youth Ministry',
    'description' => 'Ministry for young people',
    'leader_id' => 2,
    'is_active' => true,
]);

// Add members
$ministry->members()->attach(1, [
    'role' => 'member',
    'joined_date' => now(),
    'is_active' => true,
]);
```

---

## Performance Testing

### Load Time Test
1. Open browser DevTools (F12)
2. Go to Network tab
3. Navigate to dashboard
4. Check:
   - Dashboard API call time (should be < 500ms)
   - Total page load time (should be < 2s)

### Data Accuracy Test
1. Note statistics shown on dashboard
2. Verify against direct database queries:
   ```sql
   SELECT COUNT(*) FROM users;
   SELECT COUNT(*) FROM events WHERE start_date >= NOW();
   SELECT SUM(amount) FROM contributions WHERE MONTH(contribution_date) = MONTH(NOW());
   ```

---

## Browser Testing

Test dashboards on:
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile browsers (responsive design)

---

## Security Testing

1. **Test unauthorized access**:
   - Try accessing admin dashboard as regular user
   - Verify redirects or shows appropriate dashboard

2. **Test data isolation**:
   - User dashboard should only show that user's data
   - Finance dashboard should only show current month by default

3. **Test token expiration**:
   - Wait for token to expire
   - Verify automatic redirect to login

---

**Happy Testing! 🎉**

