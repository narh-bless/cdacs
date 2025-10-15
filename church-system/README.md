# Church Database and Communication System

A comprehensive Laravel 11 backend API for church management with JWT authentication and role-based access control.

## Features

- **User Management**: Member registration, profile management, role assignment
- **Authentication**: JWT-based authentication with role-based access control
- **Announcements**: Create, manage, and publish church announcements
- **Events**: Event management with recurring event support
- **Messaging**: Personal, ministry, and broadcast messaging system
- **Contributions**: Track tithes, offerings, donations, and special contributions
- **Ministry Management**: Organize church ministries and member assignments
- **Reports**: Financial reports and contribution analytics

## User Roles

- **User (Member)**: Can view announcements, send messages, update profile, view contribution history
- **Pastor**: Can post announcements, manage events, communicate with members, manage ministries
- **Finance Committee**: Can manage contributions/donations, send announcements
- **Administrator**: Full system access including user management, role assignment, and data oversight

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login with email/password
- `POST /api/auth/logout` - Logout (requires JWT token)
- `POST /api/auth/refresh` - Refresh JWT token
- `GET /api/auth/me` - Get current user profile

### Users
- `GET /api/users` - List all users (Admin only)
- `GET /api/users/{id}` - Get user details
- `PUT /api/users/{id}` - Update user (Admin only)
- `DELETE /api/users/{id}` - Delete user (Admin only)
- `GET /api/users/{id}/contributions` - Get user's contributions
- `GET /api/users/{id}/ministries` - Get user's ministries

### Announcements
- `GET /api/announcements` - List announcements
- `GET /api/announcements/published` - List published announcements
- `POST /api/announcements` - Create announcement (Pastor/Admin)
- `GET /api/announcements/{id}` - Get announcement details
- `PUT /api/announcements/{id}` - Update announcement (Pastor/Admin)
- `DELETE /api/announcements/{id}` - Delete announcement (Pastor/Admin)
- `POST /api/announcements/{id}/publish` - Publish announcement

### Events
- `GET /api/events` - List events
- `GET /api/events/upcoming` - List upcoming events
- `GET /api/events/published` - List published events
- `POST /api/events` - Create event (Pastor/Admin)
- `GET /api/events/{id}` - Get event details
- `PUT /api/events/{id}` - Update event (Pastor/Admin)
- `DELETE /api/events/{id}` - Delete event (Pastor/Admin)
- `POST /api/events/{id}/publish` - Publish event

### Messages
- `GET /api/messages` - List messages
- `GET /api/messages/inbox` - Get inbox messages
- `GET /api/messages/sent` - Get sent messages
- `POST /api/messages` - Send message
- `GET /api/messages/{id}` - Get message details
- `POST /api/messages/{id}/read` - Mark message as read
- `POST /api/messages/broadcast` - Send broadcast message

### Contributions
- `GET /api/contributions` - List contributions
- `POST /api/contributions` - Record contribution (Finance/Admin)
- `GET /api/contributions/{id}` - Get contribution details
- `PUT /api/contributions/{id}` - Update contribution (Finance/Admin)
- `DELETE /api/contributions/{id}` - Delete contribution (Finance/Admin)
- `GET /api/contributions/reports/summary` - Get contribution summary
- `GET /api/contributions/reports/by-type` - Get contributions by type
- `GET /api/contributions/reports/by-ministry` - Get contributions by ministry
- `GET /api/contributions/reports/by-date-range` - Get contributions by date range

### Ministries
- `GET /api/ministries` - List ministries
- `POST /api/ministries` - Create ministry (Admin)
- `GET /api/ministries/{id}` - Get ministry details
- `PUT /api/ministries/{id}` - Update ministry (Admin)
- `DELETE /api/ministries/{id}` - Delete ministry (Admin)
- `GET /api/ministries/{id}/members` - Get ministry members
- `POST /api/ministries/{id}/members` - Add member to ministry
- `DELETE /api/ministries/{id}/members/{user}` - Remove member from ministry
- `GET /api/ministries/{id}/contributions` - Get ministry contributions

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy environment file: `cp .env.example .env`
4. Generate application key: `php artisan key:generate`
5. Generate JWT secret: `php artisan jwt:secret`
6. Configure database in `.env` file
7. Run migrations: `php artisan migrate`
8. Seed roles: `php artisan db:seed --class=RoleSeeder`
9. Start development server: `php artisan serve`

## Authentication

The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your-jwt-token}
```

## Database Schema

### Core Tables
- `users` - User accounts with church-specific fields
- `roles` - User roles (user, pastor, finance_committee, administrator)
- `user_roles` - Many-to-many relationship between users and roles
- `announcements` - Church announcements
- `events` - Church events and services
- `messages` - Internal messaging system
- `contributions` - Financial contributions and donations
- `ministries` - Church ministries and groups
- `user_ministries` - Many-to-many relationship between users and ministries

## Technology Stack

- **Framework**: Laravel 11
- **Authentication**: JWT (tymon/jwt-auth)
- **Database**: SQLite (development) / MySQL (production)
- **ORM**: Eloquent
- **Validation**: Laravel Form Requests
- **API**: RESTful API with JSON responses

## Development

The system is built with Laravel 11 and follows RESTful API conventions. All responses are in JSON format with consistent structure:

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {...}
}
```

## Security Features

- JWT token-based authentication
- Role-based access control
- Input validation and sanitization
- SQL injection protection via Eloquent ORM
- CSRF protection for web routes

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).