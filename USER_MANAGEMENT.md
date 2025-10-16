# User Management System

## Overview

The User Management page is a comprehensive interface for managing church members and their information. This page provides administrators and pastors with tools to view, create, edit, and manage user accounts and their associated data.

## Features

### 1. User Listing and Display

- **Table View**: Users are displayed in a clean, sortable table with the following columns:
  - User (Avatar, Name, Email)
  - Phone
  - Roles
  - Status (Active/Inactive)
  - Joined Date
  - Actions

- **Pagination**: Built-in pagination for handling large numbers of users
- **Sorting**: Click on column headers to sort data
- **Responsive Design**: Table adapts to different screen sizes

### 2. Search and Filtering

#### Search
- Real-time search by name or email
- Case-insensitive search functionality
- Clear search button for easy reset

#### Filters
- **Role Filter**: Filter users by their assigned roles
  - Administrator
  - Pastor
  - Finance Committee
  - Member
  
- **Status Filter**: Filter by user status
  - Active users
  - Inactive users

- **Refresh Button**: Manually refresh the user list

### 3. User Creation

Administrators can add new users with comprehensive information:

#### Required Fields
- Full Name
- Email (validated format)
- Password (minimum 6 characters)
- Password Confirmation (must match)

#### Optional Fields
- Phone
- Date of Birth
- Gender (Male, Female, Other)
- Address (Street, City, State, Zip Code)
- Marital Status (Single, Married, Divorced, Widowed)
- Occupation
- Emergency Contact

### 4. User Editing

Administrators can edit existing user information:

- Update all user profile fields
- Toggle user active/inactive status
- Cannot change password through this interface (use password reset instead)

### 5. User Details View

Click the "View Details" button to see comprehensive user information in a tabbed dialog:

#### Information Tab
- User avatar and basic info
- Contact details
- Personal information
- Address
- Occupation
- Emergency contact
- Assigned roles
- Member since date

#### Contributions Tab
- List of all user contributions
- Displays:
  - Amount
  - Type (Tithe, Offering, Donation, Pledge)
  - Payment method
  - Date
  - Notes
- **Total Contributions**: Shows aggregate sum

#### Ministries Tab
- List of all ministries the user is a member of
- Displays:
  - Ministry name and description
  - User's role in the ministry
  - Join date
  - Ministry status

### 6. Role Management

Administrators can manage user roles:

- View current roles assigned to a user
- Assign new roles to users
- Role options:
  - Administrator (full system access)
  - Pastor (ministry and member management)
  - Finance Committee (financial management)
  - Member (basic access)

**Note**: The role assignment feature requires backend implementation with proper role IDs. Currently shows an informational message.

## API Integration

### Endpoints Used

Based on the Postman collection:

1. **Get All Users**
   ```
   GET /api/users
   ```
   Returns: List of all users with their roles and information

2. **Get User by ID**
   ```
   GET /api/users/{id}
   ```
   Returns: Detailed information for a specific user

3. **Create User**
   ```
   POST /api/users
   ```
   Body: User registration data
   Returns: Created user object

4. **Update User**
   ```
   PUT /api/users/{id}
   ```
   Body: Updated user data
   Returns: Updated user object

5. **Get User Contributions**
   ```
   GET /api/users/{id}/contributions
   ```
   Returns: List of all contributions made by the user

6. **Get User Ministries**
   ```
   GET /api/users/{id}/ministries
   ```
   Returns: List of all ministries the user belongs to

7. **Assign Role** (Future Implementation)
   ```
   POST /api/users/{id}/roles
   ```
   Body: { role_id: number }
   Returns: Updated user with new role

## User Permissions

### Administrator Access
- View all users
- Create new users
- Edit user information
- Manage user roles
- View user contributions and ministries
- Toggle user active/inactive status

### Pastor Access
- View all users
- View user details
- View user contributions and ministries
- Limited editing capabilities

### Other Roles
- No access to user management page
- Can only view and edit their own profile through the Profile page

## Usage Examples

### Adding a New User

1. Click the **"Add User"** button in the top right
2. Fill in the required fields:
   - Full Name: "John Smith"
   - Email: "john.smith@church.com"
   - Password: "securepass123"
   - Confirm Password: "securepass123"
3. Optionally fill in additional information:
   - Phone: "+1234567890"
   - Date of Birth: "1990-05-15"
   - Gender: "Male"
   - Address details
   - Marital status, occupation, etc.
4. Click **"Add User"**
5. Success notification appears
6. User list refreshes with new member

### Searching for a User

1. Type in the search box: "john"
2. Results filter in real-time to show all users with "john" in their name or email
3. Click the "X" to clear the search

### Filtering by Role

1. Click the "Filter by Role" dropdown
2. Select "Pastor"
3. Table shows only users with the Pastor role
4. Clear the filter to show all users again

### Viewing User Details

1. Find the user in the table
2. Click the **eye icon** (View Details)
3. Dialog opens with three tabs:
   - **Information**: View all user details
   - **Contributions**: See all financial contributions with total
   - **Ministries**: View all ministry memberships
4. Navigate between tabs to see different information
5. Click the X or outside the dialog to close

### Editing a User

1. Find the user in the table
2. Click the **pencil icon** (Edit)
3. Update the desired fields
4. Toggle "Active User" if needed
5. Click **"Update"**
6. Success notification appears
7. Changes are reflected in the table

## Color Coding

### Role Badges
- **Administrator**: Orange
- **Pastor**: Purple
- **Finance Committee**: Green
- **Member**: Blue

### Status Badges
- **Active**: Green (positive)
- **Inactive**: Grey

### Contribution Types
- **Tithe**: Blue (primary)
- **Offering**: Purple (secondary)
- **Donation**: Green (positive)
- **Pledge**: Light Blue (info)

## Technical Details

### Component Structure

```
UserList.vue
├── Header Section
│   ├── Title and subtitle
│   └── Add User button (admin only)
├── Filters Section
│   ├── Search input
│   ├── Role filter
│   ├── Status filter
│   └── Refresh button
├── User Table
│   ├── Column headers (sortable)
│   ├── User rows with custom rendering
│   └── Action buttons
└── Dialogs
    ├── Add User Dialog
    ├── Edit User Dialog
    ├── View Details Dialog (with tabs)
    └── Manage Roles Dialog
```

### State Management

The component uses Vue 3 Composition API with the following reactive state:

- `users`: Array of all users
- `loading`: Loading state for API calls
- `saving`: Saving state for create/update operations
- `searchQuery`: Search filter text
- `filterRole`: Selected role filter
- `filterStatus`: Selected status filter
- `selectedUser`: Currently selected user for details/edit
- `pagination`: Table pagination settings
- Form state objects for add/edit operations

### Computed Properties

- `filteredUsers`: Applies search and filter criteria to the user list

### API Service Integration

Uses the `usersAPI` service from `src/services/api.js`:

```javascript
import { usersAPI } from 'src/services/api';

// Examples:
await usersAPI.getAll();
await usersAPI.create(userData);
await usersAPI.update(id, userData);
await usersAPI.getContributions(id);
await usersAPI.getMinistries(id);
```

### Authentication & Authorization

Uses the `useAuthStore` for role-based access control:

```javascript
import { useAuthStore } from 'src/stores/auth';

const authStore = useAuthStore();

// Check permissions
if (authStore.isAdministrator) {
  // Show admin features
}
```

## Responsive Design

The page is fully responsive and adapts to different screen sizes:

- **Desktop**: Full table view with all columns
- **Tablet**: Stacked columns with adjusted spacing
- **Mobile**: Optimized mobile view with essential information

## Form Validation

All forms include built-in validation:

- **Email**: Must be valid email format
- **Password**: Minimum 6 characters
- **Password Confirmation**: Must match password
- **Required Fields**: Marked with asterisk (*)

## Error Handling

The component includes comprehensive error handling:

- **API Errors**: Displays error notifications from backend
- **Network Errors**: Shows appropriate error messages
- **Validation Errors**: Inline form validation
- **Loading States**: Visual feedback during operations

## Future Enhancements

1. **Role Assignment**: Complete backend integration for role management
2. **Bulk Operations**: Select multiple users for batch operations
3. **Export Functionality**: Export user list to CSV/Excel
4. **Advanced Filtering**: Date range filters, custom queries
5. **User Import**: Bulk import from CSV/Excel
6. **Email Notifications**: Send welcome emails to new users
7. **Password Reset**: Built-in password reset functionality
8. **Activity Log**: Track user activity and changes
9. **Photo Upload**: Allow user profile photos
10. **Advanced Reporting**: Generate user statistics and reports

## Troubleshooting

### Users Not Loading
- Check backend API is running on http://127.0.0.1:8000
- Verify JWT token is valid (check localStorage)
- Check browser console for API errors
- Verify user has proper permissions

### Cannot Create User
- Ensure all required fields are filled
- Check password meets minimum length
- Verify email format is valid
- Check backend validation rules

### Role Assignment Not Working
- This feature requires backend implementation
- Check backend has role management endpoints
- Verify role IDs are available from backend

### Contributions/Ministries Not Showing
- Verify user has contributions/ministries in database
- Check API endpoints are working
- Review backend relationships are properly loaded

## Testing Checklist

- [ ] User list loads successfully
- [ ] Search functionality works
- [ ] Role filter works correctly
- [ ] Status filter works correctly
- [ ] Can create new user
- [ ] Can edit existing user
- [ ] Can view user details
- [ ] Contributions tab loads data
- [ ] Ministries tab loads data
- [ ] Role badges display correctly
- [ ] Status badges display correctly
- [ ] Pagination works
- [ ] Sorting works
- [ ] Responsive on mobile
- [ ] Responsive on tablet
- [ ] Admin sees all features
- [ ] Pastor sees limited features
- [ ] Regular users cannot access page
- [ ] Error messages display properly
- [ ] Success notifications work

## Related Files

- **Component**: `frontend/src/pages/users/UserList.vue`
- **Routes**: `frontend/src/router/routes.js`
- **API Service**: `frontend/src/services/api.js`
- **Auth Store**: `frontend/src/stores/auth.js`
- **Layout**: `frontend/src/layouts/MainLayout.vue`

## Backend Requirements

For the user management page to work properly, ensure the backend implements:

1. User CRUD endpoints (GET, POST, PUT, DELETE)
2. User contributions endpoint
3. User ministries endpoint
4. Role management endpoints
5. Proper JWT authentication
6. Role-based authorization
7. Input validation
8. Proper error responses

## Support

For issues or questions:
1. Check the browser console for errors
2. Verify backend API is running
3. Check network tab for failed requests
4. Review API endpoint documentation
5. Verify user permissions and roles

