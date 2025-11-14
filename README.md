# Catch Me - Social Proximity App

A Laravel-based social proximity application that connects nearby people who like each other within 50 meters.

## Features

### Admin Dashboard
- **Users Management**: View, edit, ban/unban users, manage profiles
- **Likes & Matches**: Monitor all like requests and matches
- **Chats**: View and manage conversations between matched users
- **Reports**: Review and handle user reports
- **Security**: Track banned users and admin activity logs
- **Live Map**: Real-time map view of online users with their GPS locations
- **Statistics**: Comprehensive charts and analytics
- **Push Notifications**: Send FCM notifications to users
- **App Settings**: Configure max distance, ghost mode, terms & privacy
- **Admin Users**: Multi-role admin management system

### RESTful API
- User authentication (Phone, Google, Apple)
- Location updates with Haversine formula
- Find nearby users within configurable radius
- Like/Accept/Reject matching system
- Real-time messaging between matches
- User reporting system
- Profile management
- Ghost mode for privacy

## Tech Stack

- **Framework**: Laravel 12.x (PHP 8.3)
- **Database**: MySQL
- **Authentication**: Laravel Breeze (Admin) + Sanctum (API)
- **Frontend**: Blade Templates + TailwindCSS
- **Charts**: ApexCharts
- **Maps**: Google Maps API / LeafletJS
- **Push Notifications**: Firebase Cloud Messaging (FCM)
- **API Documentation**: L5-Swagger (OpenAPI)
- **Hosting**: Laravel Vapor (AWS)
- **Languages**: Bilingual (French + Arabic)

## Installation

### Prerequisites
- PHP 8.3+
- Composer
- MySQL 8.0+
- Node.js & NPM

### Setup

1. **Clone the repository**
```bash
cd "Catch Me Dashboard"
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure your `.env` file**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=catchme_app
DB_USERNAME=root
DB_PASSWORD=your_password

FCM_SERVER_KEY=your_fcm_server_key
GOOGLE_MAPS_API_KEY=your_google_maps_api_key

AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_BUCKET=your_s3_bucket
```

6. **Run migrations and seeders**
```bash
php artisan migrate --seed
```

7. **Build assets**
```bash
npm run build
```

8. **Start the development server**
```bash
php artisan serve
```

9. **Generate API documentation**
```bash
php artisan l5-swagger:generate
```

## Default Admin Credentials

- **Email**: admin@catchme.app
- **Password**: password

⚠️ **Important**: Change these credentials in production!

## API Documentation

Once the application is running, access the API documentation at:
```
http://localhost:8000/api/documentation
```

## Key API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login with phone/password
- `POST /api/auth/social` - Social login (Google/Apple)
- `POST /api/auth/logout` - Logout user

### User Management
- `PUT /api/user/profile` - Update profile
- `POST /api/user/profile-image` - Upload profile image
- `POST /api/user/location` - Update GPS location
- `GET /api/user/nearby` - Get nearby users

### Likes & Matches
- `POST /api/likes` - Send a like
- `PUT /api/likes/{id}/accept` - Accept a like
- `PUT /api/likes/{id}/reject` - Reject a like
- `GET /api/matches` - Get all matches

### Messages
- `POST /api/messages` - Send message
- `GET /api/messages/{userId}` - Get conversation
- `GET /api/conversations` - List all conversations

### Reports
- `POST /api/reports` - Report a user

## Database Structure

### Users
- id, name, email, phone, password
- gender, latitude, longitude
- is_visible (ghost mode), is_banned
- profile_image, bio, language
- fcm_token, google_id, apple_id

### Likes
- id, from_user_id, to_user_id
- status (pending/accepted/rejected)

### Messages
- id, sender_id, receiver_id
- message, is_read, read_at

### Reports
- id, reporter_id, reported_user_id
- reason, status, admin_notes

### Admins
- id, name, email, password
- role (super_admin, report_moderator, chat_moderator, user_moderator)

## Admin Dashboard Routes

- `/admin/dashboard` - Main dashboard with statistics
- `/admin/users` - User management
- `/admin/likes` - Likes and matches
- `/admin/messages` - Chat management
- `/admin/reports` - User reports
- `/admin/security` - Security and logs
- `/admin/map` - Live user map
- `/admin/notifications` - Push notifications
- `/admin/settings` - App settings
- `/admin/admins` - Admin user management

## Features Explained

### Ghost Mode
Users can enable ghost mode (`is_visible = false`) to:
- Hide from the live map
- Prevent location updates
- Become invisible to nearby users search

### Proximity Matching
- Uses Haversine formula for accurate distance calculation
- Default radius: 50 meters (configurable)
- Only visible, non-banned users appear in searches

### Matching Logic
- User A likes User B → Status: pending
- User B accepts → Both get match notification
- If User B already liked User A → Instant match!

### Security Features
- Admin activity logging
- User ban/unban functionality
- Report system with review workflow
- Session management
- CSRF protection
- API rate limiting

## Localization

The app supports French and Arabic:

**French (fr)**: Default language
**Arabic (ar)**: Full RTL support

Language files located in:
- `resources/lang/fr/`
- `resources/lang/ar/`

## Deployment (Laravel Vapor)

1. Install Vapor CLI
```bash
composer require laravel/vapor-cli
```

2. Configure `vapor.yml`
```yaml
id: your-project-id
name: catchme
environments:
  production:
    memory: 1024
    database: catchme-production
    cache: catchme-cache
```

3. Deploy
```bash
vapor deploy production
```

## Firebase Setup

1. Create a Firebase project
2. Enable Cloud Messaging
3. Download credentials JSON to `storage/firebase-credentials.json`
4. Add FCM_SERVER_KEY to `.env`

## Contributing

This is a private project. For issues or feature requests, contact the development team.

## License

Proprietary - All rights reserved

## Support

For technical support, contact: tech@catchme.app
