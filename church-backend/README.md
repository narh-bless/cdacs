# Church Database and Communication System Backend

A comprehensive Laravel 11 backend API for managing church operations, member communications, events, finances, and ministry activities.

## 🚀 Features

### Core Functionality
- **JWT Authentication** - Secure token-based authentication
- **Role-Based Access Control** - Four distinct user roles with appropriate permissions
- **Member Management** - Complete user profile and membership management
- **Announcements** - Church-wide communication system
- **Event Management** - Event creation, registration, and attendance tracking
- **Messaging System** - Personal and group messaging capabilities
- **Financial Management** - Contributions and donations tracking
- **Ministry Management** - Ministry organization and member assignment
- **Sermon & Teaching Notes** - Content management for spiritual teachings

### User Roles

1. **Member** - Basic access to view announcements, events, send messages, and manage profile
2. **Pastor** - Can post announcements, manage events, communicate with members, manage ministries, sermons and teaching notes
3. **Finance Committee** - Handle donations, contributions, financial communications and announcements
4. **Administrator** - Full system access including user management, role assignment, and system oversight

## 🛠️ Technology Stack

- **Framework**: Laravel 11
- **Authentication**: JWT (tymon/jwt-auth)
- **Database**: MySQL
- **ORM**: Eloquent
- **Validation**: Laravel Form Requests
- **API**: RESTful API with proper HTTP status codes

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 5.7 or higher
- Laravel CLI (optional)

## 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd church-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp env.example .env
   ```
   
   Update the `.env` file with your database credentials and JWT secret:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=church_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   JWT_SECRET=your_jwt_secret_key
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Generate JWT secret**
   ```bash
   php artisan jwt:secret
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed the database**
   ```bash
   php artisan db:seed
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

## 📚 API Documentation

### Authentication Endpoints

| Method | Endpoint | Description | Access |
|--------|----------|-------------|---------|
| POST | `/api/register` | Register new user | Public |
| POST | `/api/login` | User login | Public |
| POST | `/api/logout` | User logout | Authenticated |
| POST | `/api/refresh` | Refresh JWT token | Authenticated |
| GET | `/api/me` | Get current user info | Authenticated |

### User Management

| Method | Endpoint | Description | Access |
|--------|----------|-------------|---------|
| GET | `/api/users` | List all users | Admin |
| POST | `/api/users` | Create new user | Admin |
| GET | `/api/users/{id}` | Get user details | Admin |
| PUT | `/api/users/{id}` | Update user | Admin |
| DELETE | `/api/users/{id}` | Delete user | Admin |
| GET | `/api/profile` | Get own profile | Authenticated |
| PUT | `/api/profile` | Update own profile | Authenticated |

### Announcements

| Method | Endpoint | Description | Access |
|--------|----------|-------------|---------|
| GET | `/api/announcements` | List announcements | All |
| POST | `/api/announcements` | Create announcement | Pastor/Finance/Admin |
| GET | `/api/announcements/{id}` | Get announcement | All |
| PUT | `/api/announcements/{id}` | Update announcement | Author/Admin |
| DELETE | `/api/announcements/{id}` | Delete announcement | Author/Admin |
| POST | `/api/announcements/{id}/publish` | Publish announcement | Pastor/Finance/Admin |
| POST | `/api/announcements/{id}/unpublish` | Unpublish announcement | Pastor/Finance/Admin |

### Events

| Method | Endpoint | Description | Access |
|--------|----------|-------------|---------|
| GET | `/api/events` | List events | All |
| POST | `/api/events` | Create event | Pastor/Admin |
| GET | `/api/events/{id}` | Get event details | All |
| PUT | `/api/events/{id}` | Update event | Organizer/Admin |
| DELETE | `/api/events/{id}` | Delete event | Organizer/Admin |
| POST | `/api/events/{id}/register` | Register for event | Authenticated |
| DELETE | `/api/events/{id}/unregister` | Unregister from event | Authenticated |

### Messages

| Method | Endpoint | Description | Access |
|--------|----------|-------------|---------|
| GET | `/api/messages` | List user messages | Authenticated |
| POST | `/api/messages` | Send message | Authenticated |
| GET | `/api/messages/{id}` | Get message details | Sender/Recipient |
| PUT | `/api/messages/{id}` | Update message | Sender |
| DELETE | `/api/messages/{id}` | Delete message | Sender |
| POST | `/api/messages/{id}/reply` | Reply to message | Authenticated |

### Finance

| Method | Endpoint | Description | Access |
|--------|----------|-------------|---------|
| GET | `/api/contributions` | List contributions | Finance/Admin |
| POST | `/api/contributions` | Record contribution | Finance/Admin |
| GET | `/api/donations` | List donations | Finance/Admin |
| POST | `/api/donations` | Record donation | Finance/Admin |
| GET | `/api/financial-summary` | Financial summary | Finance/Admin |
| GET | `/api/my-financial-history` | Personal financial history | Authenticated |

## 🔐 Authentication

The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:

```
Authorization: Bearer <your_jwt_token>
```

## 📊 Sample Data

The seeder creates sample data including:
- Default roles and permissions
- Admin user (admin@church.com / password)
- Pastor user (pastor@church.com / password)
- Finance committee member (finance@church.com / password)
- Sample members with various roles
- Sample announcements, events, contributions, and donations

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

## 📝 Environment Variables

Key environment variables to configure:

```env
APP_NAME="Church Backend"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=church_database
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=your_jwt_secret_key
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions, please contact the development team or create an issue in the repository.
