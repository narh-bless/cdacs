# User Management Testing Guide

## Quick Start Testing

This guide will help you test all features of the User Management page.

## Prerequisites

1. Backend server running on `http://127.0.0.1:8000`
2. Frontend development server running
3. Logged in as an Administrator or Pastor
4. At least one user exists in the database

## Test Scenarios

### 1. Access Control Testing

#### Test 1.1: Administrator Access
**Steps:**
1. Log in as Administrator
2. Click on "User Management" in the sidebar under ADMINISTRATION section
3. Page should load successfully

**Expected Result:**
- ✅ Page loads without errors
- ✅ "Add User" button is visible
- ✅ All action buttons (View, Edit, Manage Roles) are visible
- ✅ User table displays all users

#### Test 1.2: Pastor Access
**Steps:**
1. Log in as Pastor
2. Navigate to `/app/users`

**Expected Result:**
- ✅ Page loads successfully
- ✅ "Add User" button is NOT visible
- ✅ View button is visible
- ✅ Edit and Manage Roles buttons are NOT visible

#### Test 1.3: Regular User Access
**Steps:**
1. Log in as regular user (Member)
2. Try to access `/app/users`

**Expected Result:**
- ✅ Access denied or redirected
- ✅ "User Management" link NOT visible in sidebar

### 2. User List Display

#### Test 2.1: Initial Load
**Steps:**
1. Navigate to User Management page
2. Observe the initial load

**Expected Result:**
- ✅ Loading spinner appears briefly
- ✅ User table populates with data
- ✅ All columns display correctly:
  - User (with avatar and email)
  - Phone
  - Roles (colored badges)
  - Status (Active/Inactive badge)
  - Joined date
  - Actions

#### Test 2.2: Pagination
**Steps:**
1. If you have more than 10 users, observe pagination
2. Click next/previous page buttons
3. Change rows per page

**Expected Result:**
- ✅ Pagination controls appear
- ✅ Can navigate between pages
- ✅ Can change rows per page (5, 10, 25, 50)

#### Test 2.3: Sorting
**Steps:**
1. Click on "User" column header
2. Click again to reverse sort
3. Try sorting other columns

**Expected Result:**
- ✅ Data sorts alphabetically by name
- ✅ Second click reverses sort direction
- ✅ Sort indicator shows current sort state

### 3. Search and Filter

#### Test 3.1: Search by Name
**Steps:**
1. Type a user's first name in search box
2. Observe results update in real-time

**Expected Result:**
- ✅ Results filter immediately
- ✅ Only matching users are shown
- ✅ Case-insensitive matching works

#### Test 3.2: Search by Email
**Steps:**
1. Type an email address or part of one
2. Observe results

**Expected Result:**
- ✅ Users with matching email are shown
- ✅ Partial matches work

#### Test 3.3: Clear Search
**Steps:**
1. Enter a search query
2. Click the X button in search box

**Expected Result:**
- ✅ Search clears
- ✅ All users are shown again

#### Test 3.4: Role Filter
**Steps:**
1. Select "Administrator" from Role filter
2. Observe results
3. Try other roles
4. Clear the filter

**Expected Result:**
- ✅ Only users with Administrator role are shown
- ✅ Can filter by any role
- ✅ Clear button resets filter

#### Test 3.5: Status Filter
**Steps:**
1. Select "Active" from Status filter
2. Observe results
3. Try "Inactive"
4. Clear filter

**Expected Result:**
- ✅ Only active users are shown
- ✅ Can filter by inactive
- ✅ Clear button resets filter

#### Test 3.6: Combined Filters
**Steps:**
1. Enter search text
2. Apply role filter
3. Apply status filter
4. All filters should work together

**Expected Result:**
- ✅ Results match all filter criteria
- ✅ Filters work in combination

### 4. Create User

#### Test 4.1: Valid User Creation
**Steps:**
1. Click "Add User" button
2. Fill in required fields:
   - Name: "Test User"
   - Email: "test.user@church.com"
   - Password: "password123"
   - Confirm Password: "password123"
3. Fill in optional fields:
   - Phone: "+1234567890"
   - Date of Birth: "1990-01-01"
   - Gender: "Male"
   - Address: "123 Test St"
   - City: "Test City"
   - State: "TS"
   - Zip: "12345"
   - Marital Status: "Single"
   - Occupation: "Tester"
   - Emergency Contact: "Emergency Person - +0987654321"
4. Click "Add User"

**Expected Result:**
- ✅ Success notification appears
- ✅ Dialog closes
- ✅ User list refreshes
- ✅ New user appears in the table

#### Test 4.2: Validation - Missing Required Fields
**Steps:**
1. Click "Add User"
2. Leave name blank
3. Try to submit

**Expected Result:**
- ✅ Validation error for name field
- ✅ Form does not submit

#### Test 4.3: Validation - Invalid Email
**Steps:**
1. Enter "invalid-email" in email field
2. Try to submit

**Expected Result:**
- ✅ Email validation error appears
- ✅ Form does not submit

#### Test 4.4: Validation - Password Mismatch
**Steps:**
1. Enter "password123" in password
2. Enter "password456" in confirm password
3. Try to submit

**Expected Result:**
- ✅ Password mismatch error appears
- ✅ Form does not submit

#### Test 4.5: Validation - Short Password
**Steps:**
1. Enter "pass" (less than 6 characters)
2. Try to submit

**Expected Result:**
- ✅ Password length error appears
- ✅ Form does not submit

#### Test 4.6: Cancel Creation
**Steps:**
1. Click "Add User"
2. Fill in some fields
3. Click "Cancel"

**Expected Result:**
- ✅ Dialog closes
- ✅ No user is created
- ✅ Form resets

### 5. Edit User

#### Test 5.1: Valid User Update
**Steps:**
1. Find a user in the table
2. Click the edit (pencil) icon
3. Update some fields:
   - Change name
   - Update phone
   - Change occupation
4. Click "Update"

**Expected Result:**
- ✅ Success notification appears
- ✅ Dialog closes
- ✅ User list refreshes
- ✅ Changes are visible in the table

#### Test 5.2: Toggle User Status
**Steps:**
1. Edit a user
2. Toggle "Active User" checkbox
3. Click "Update"

**Expected Result:**
- ✅ Update succeeds
- ✅ Status badge changes in table

#### Test 5.3: Cancel Edit
**Steps:**
1. Click edit on a user
2. Make some changes
3. Click "Cancel"

**Expected Result:**
- ✅ Dialog closes
- ✅ Changes are not saved
- ✅ User data unchanged in table

### 6. View User Details

#### Test 6.1: Information Tab
**Steps:**
1. Click eye icon on a user
2. Verify Information tab

**Expected Result:**
- ✅ Dialog opens on Information tab
- ✅ User avatar displays
- ✅ Name and email shown
- ✅ Status badge visible
- ✅ All user details display correctly:
  - Phone
  - Date of birth
  - Gender
  - Address
  - Marital status
  - Occupation
  - Emergency contact
  - Roles (colored badges)
  - Member since date

#### Test 6.2: Contributions Tab
**Steps:**
1. Open user details
2. Click "Contributions" tab
3. Wait for data to load

**Expected Result:**
- ✅ Loading spinner appears
- ✅ Contributions list displays
- ✅ Each contribution shows:
  - Amount
  - Type
  - Payment method
  - Date
  - Notes (if any)
- ✅ Total contributions calculated and displayed
- ✅ If no contributions: Shows "No contributions found"

#### Test 6.3: Ministries Tab
**Steps:**
1. Open user details
2. Click "Ministries" tab
3. Wait for data to load

**Expected Result:**
- ✅ Loading spinner appears
- ✅ Ministries list displays
- ✅ Each ministry shows:
  - Ministry name
  - Description
  - User's role
  - Join date
  - Status badge
- ✅ If no ministries: Shows "Not a member of any ministry"

#### Test 6.4: Navigate Between Tabs
**Steps:**
1. Open user details
2. Click each tab in sequence
3. Go back to previously viewed tabs

**Expected Result:**
- ✅ Can switch between tabs smoothly
- ✅ Data persists when returning to tabs
- ✅ No re-loading when switching back

#### Test 6.5: Close Details Dialog
**Steps:**
1. Open user details
2. Click X button or click outside dialog

**Expected Result:**
- ✅ Dialog closes
- ✅ Returns to user list

### 7. Role Management

#### Test 7.1: Open Role Management
**Steps:**
1. Click the admin panel icon (Manage Roles) on a user
2. Observe the dialog

**Expected Result:**
- ✅ Role management dialog opens
- ✅ Shows user's current roles
- ✅ Shows role selection dropdown
- ✅ Available roles listed

#### Test 7.2: Assign Role (Note: Requires Backend)
**Steps:**
1. Open role management for a user
2. Select a role from dropdown
3. Click "Assign Role"

**Expected Result:**
- ✅ Currently shows info message about backend requirement
- ✅ In production: Would assign role and refresh user list

### 8. Refresh Functionality

#### Test 8.1: Manual Refresh
**Steps:**
1. Click "Refresh" button
2. Observe loading state

**Expected Result:**
- ✅ Button shows loading state
- ✅ User list reloads from API
- ✅ Data updates if changes occurred

### 9. Responsive Design

#### Test 9.1: Desktop View (> 1024px)
**Steps:**
1. View page on desktop
2. Observe layout

**Expected Result:**
- ✅ Full table visible
- ✅ All columns displayed
- ✅ Proper spacing and alignment

#### Test 9.2: Tablet View (768px - 1024px)
**Steps:**
1. Resize browser to tablet size
2. Observe layout changes

**Expected Result:**
- ✅ Table adjusts to available space
- ✅ Columns may stack or adjust
- ✅ All features still accessible

#### Test 9.3: Mobile View (< 768px)
**Steps:**
1. Resize browser to mobile size
2. Observe layout changes

**Expected Result:**
- ✅ Mobile-optimized view
- ✅ Essential information visible
- ✅ Actions accessible
- ✅ Dialogs adapt to screen size

### 10. Error Handling

#### Test 10.1: Backend Offline
**Steps:**
1. Stop backend server
2. Try to load user list

**Expected Result:**
- ✅ Error notification appears
- ✅ "Server error" message shown
- ✅ Page doesn't crash

#### Test 10.2: Invalid Data
**Steps:**
1. Try to create user with existing email
2. Try to update with invalid data

**Expected Result:**
- ✅ Appropriate error message from backend
- ✅ User-friendly error notification
- ✅ Form remains open for correction

#### Test 10.3: Network Timeout
**Steps:**
1. Simulate slow network
2. Try various operations

**Expected Result:**
- ✅ Loading states show appropriately
- ✅ Timeout errors handled gracefully

## Sample Test Data

### Sample User 1
```json
{
  "name": "John Doe",
  "email": "john.doe@church.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "date_of_birth": "1990-05-15",
  "gender": "male",
  "address": "123 Church Street",
  "city": "New York",
  "state": "NY",
  "zip_code": "10001",
  "country": "USA",
  "marital_status": "married",
  "occupation": "Software Engineer",
  "emergency_contact": "Jane Doe - +1234567891"
}
```

### Sample User 2
```json
{
  "name": "Mary Smith",
  "email": "mary.smith@church.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1987654321",
  "date_of_birth": "1985-08-20",
  "gender": "female",
  "address": "456 Faith Avenue",
  "city": "Los Angeles",
  "state": "CA",
  "zip_code": "90001",
  "country": "USA",
  "marital_status": "single",
  "occupation": "Teacher",
  "emergency_contact": "Bob Smith - +1987654322"
}
```

### Sample User 3 (Minimal)
```json
{
  "name": "Test User",
  "email": "test@church.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

## Using Postman for Backend Testing

Before testing the frontend, verify backend endpoints work:

1. Import the `church-system/postman_collection.json`
2. Set variables:
   - `base_url`: `http://127.0.0.1:8000/api`
   - `jwt_token`: (obtained from login)

3. Test endpoints in order:
   - Login
   - Get All Users
   - Create User
   - Get User by ID
   - Update User
   - Get User Contributions
   - Get User Ministries

## Common Issues and Solutions

### Issue: Users not loading
**Solution:**
- Check backend is running
- Verify API endpoint in `src/services/api.js`
- Check browser console for errors
- Verify JWT token is valid

### Issue: Cannot create user
**Solution:**
- Check all required fields are filled
- Verify email format
- Check password meets requirements
- Review backend validation rules

### Issue: Roles not displaying correctly
**Solution:**
- Verify backend returns roles in correct format
- Check role badge color mapping in component
- Ensure roles relationship is loaded in backend

### Issue: Contributions/Ministries not loading
**Solution:**
- Verify user has data in those tables
- Check API endpoints return correct data
- Review backend relationships

### Issue: Permission denied
**Solution:**
- Verify logged in user has correct role
- Check route meta permissions
- Review backend authorization middleware

## Performance Testing

### Load Testing
1. Create 100+ users in database
2. Load user management page
3. Observe load time and responsiveness

**Expected:**
- ✅ Page loads within 2 seconds
- ✅ Pagination handles large dataset
- ✅ Search remains responsive

### Stress Testing
1. Rapidly switch between filters
2. Quickly open/close multiple dialogs
3. Spam search input

**Expected:**
- ✅ No crashes
- ✅ Debounced search prevents overload
- ✅ UI remains responsive

## Browser Compatibility

Test on:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

All features should work consistently across browsers.

## Checklist Summary

- [ ] Access control works for all roles
- [ ] User list displays correctly
- [ ] Search functionality works
- [ ] Filters work (role and status)
- [ ] Can create new user with validation
- [ ] Can edit existing user
- [ ] Can view user details (all tabs)
- [ ] Role management dialog works
- [ ] Refresh button works
- [ ] Responsive on all screen sizes
- [ ] Error handling works properly
- [ ] Loading states display correctly
- [ ] Success notifications appear
- [ ] Color coding is correct
- [ ] Pagination works
- [ ] Sorting works
- [ ] All buttons and icons functional
- [ ] Forms validate properly
- [ ] Dialogs open and close correctly

## Reporting Issues

When reporting an issue, include:
1. Steps to reproduce
2. Expected behavior
3. Actual behavior
4. Browser and version
5. Console errors (if any)
6. Network tab screenshot
7. User role performing the action

