# Catch Me - Complete Project Structure

## 📁 Project Overview

This is a complete Laravel 12.x social proximity application with a full-featured Admin Dashboard and RESTful API.

## 🗂️ Directory Structure

```
Catch Me Dashboard/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Admin dashboard controllers
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── LikeController.php
│   │   │   │   ├── MessageController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── SecurityController.php
│   │   │   │   ├── MapController.php
│   │   │   │   ├── NotificationController.php
│   │   │   │   ├── SettingController.php
│   │   │   │   └── AdminManagementController.php
│   │   │   ├── Api/              # API controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── LikeController.php
│   │   │   │   ├── MessageController.php
│   │   │   │   └── ReportController.php
│   │   │   └── Auth/
│   │   │       └── AdminAuthController.php
│   │   └── Middleware/
│   ├── Models/                   # Eloquent models
│   │   ├── User.php
│   │   ├── Admin.php
│   │   ├── Like.php
│   │   ├── Message.php
│   │   ├── Report.php
│   │   ├── AdminActivityLog.php
│   │   ├── AppSetting.php
│   │   └── Notification.php
│   └── Services/                 # Business logic services
│       ├── LocationService.php   # Haversine formula & proximity
│       └── FirebaseService.php   # Push notifications
│
├── database/
│   ├── migrations/               # Database schema
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_admins_table.php
│   │   ├── 2024_01_01_000003_create_likes_table.php
│   │   ├── 2024_01_01_000004_create_messages_table.php
│   │   ├── 2024_01_01_000005_create_reports_table.php
│   │   ├── 2024_01_01_000006_create_admin_activity_logs_table.php
│   │   ├── 2024_01_01_000007_create_app_settings_table.php
│   │   ├── 2024_01_01_000008_create_notifications_table.php
│   │   └── 2024_01_01_000009_create_password_reset_tokens_table.php
│   └── seeders/                  # Test data
│       ├── DatabaseSeeder.php
│       ├── AdminSeeder.php
│       ├── UserSeeder.php
│       ├── LikeSeeder.php
│       ├── MessageSeeder.php
│       └── ReportSeeder.php
│
├── resources/
│   ├── views/                    # Blade templates
│   │   ├── layouts/
│   │   │   └── admin.blade.php   # Main admin layout
│   │   ├── auth/
│   │   │   └── admin-login.blade.php
│   │   └── admin/
│   │       ├── dashboard.blade.php
│   │       ├── users/
│   │       │   └── index.blade.php
│   │       ├── map/
│   │       │   └── index.blade.php
│   │       └── settings/
│   │           └── index.blade.php
│   ├── lang/                     # Localization files
│   │   ├── fr/                   # French
│   │   │   ├── auth.php
│   │   │   ├── messages.php
│   │   │   └── notifications.php
│   │   └── ar/                   # Arabic
│   │       ├── auth.php
│   │       ├── messages.php
│   │       └── notifications.php
│   ├── css/
│   │   └── app.css               # TailwindCSS
│   └── js/
│       ├── app.js
│       └── bootstrap.js
│
├── routes/
│   ├── web.php                   # Admin routes
│   ├── api.php                   # API routes
│   └── auth.php                  # Authentication routes
│
├── config/
│   ├── auth.php                  # Multi-guard authentication
│   ├── sanctum.php               # API authentication
│   ├── services.php              # Firebase & Google Maps
│   └── l5-swagger.php            # API documentation
│
├── .env.example                  # Environment template
├── composer.json                 # PHP dependencies
├── package.json                  # Node dependencies
├── tailwind.config.js            # TailwindCSS config
├── vite.config.js                # Vite bundler config
├── README.md                     # Main documentation
├── API_DOCUMENTATION.md          # API reference
├── DEPLOYMENT.md                 # Deployment guide
└── PROJECT_STRUCTURE.md          # This file
```

## 🔑 Key Components

### 1. Database Tables

| Table | Purpose |
|-------|---------|
| `users` | App users with location data |
| `admins` | Admin users with roles |
| `likes` | Like requests and matches |
| `messages` | Chat messages between matched users |
| `reports` | User reports for moderation |
| `notifications` | Push notification history |
| `app_settings` | Configurable app settings |
| `admin_activity_logs` | Admin action tracking |

### 2. API Endpoints

#### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/social` - Social authentication
- `POST /api/auth/logout` - User logout

#### User Management
- `PUT /api/user/profile` - Update profile
- `POST /api/user/profile-image` - Upload image
- `POST /api/user/location` - Update GPS location
- `GET /api/user/nearby` - Find nearby users

#### Likes & Matches
- `POST /api/likes` - Send like
- `PUT /api/likes/{id}/accept` - Accept like
- `PUT /api/likes/{id}/reject` - Reject like
- `GET /api/matches` - Get matches

#### Messages
- `POST /api/messages` - Send message
- `GET /api/messages/{userId}` - Get conversation
- `GET /api/conversations` - List conversations

#### Reports
- `POST /api/reports` - Report user

### 3. Admin Dashboard Routes

- `/admin/dashboard` - Statistics & charts
- `/admin/users` - User management
- `/admin/likes` - Likes & matches
- `/admin/messages` - Chat moderation
- `/admin/reports` - User reports
- `/admin/security` - Security & logs
- `/admin/map` - Live user map
- `/admin/notifications` - Push notifications
- `/admin/settings` - App settings
- `/admin/admins` - Admin users

### 4. Services

#### LocationService
- Calculate distance using Haversine formula
- Find nearby users within radius
- Update user locations
- Get online users for map

#### FirebaseService
- Send push notifications to users
- Send notification to all users
- Specialized notifications (like, match, message)
- Track notification history

### 5. Authentication Guards

| Guard | Provider | Use Case |
|-------|----------|----------|
| `web` | users | Web sessions (not used) |
| `admin` | admins | Admin dashboard |
| `api` | users | Mobile API (Sanctum) |

### 6. Admin Roles

- `super_admin` - Full access
- `report_moderator` - Manage reports
- `chat_moderator` - Moderate chats
- `user_moderator` - Manage users

## 🚀 Getting Started

### Quick Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Setup database
php artisan migrate --seed

# 4. Build assets
npm run build

# 5. Start server
php artisan serve
```

### Default Admin Login

- Email: `admin@catchme.app`
- Password: `password`

### Test Users

Check `database/seeders/UserSeeder.php` for test user credentials.

## 📱 Mobile App Integration

### Authentication Flow

1. User registers/logs in via API
2. Receives Bearer token
3. Includes token in all subsequent requests
4. Provides FCM token for push notifications

### Location Updates

```javascript
// Example: Update location every 30 seconds
setInterval(async () => {
  const position = await getCurrentPosition();

  await fetch('https://api.catchme.app/api/user/location', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      latitude: position.coords.latitude,
      longitude: position.coords.longitude
    })
  });
}, 30000);
```

### Real-time Features

For real-time messaging, integrate with:
- **Laravel Echo** + **Pusher** (recommended)
- **Laravel WebSockets**
- **Firebase Realtime Database**

## 🔧 Configuration

### Important Settings

#### `.env`
```env
# Max proximity distance (meters)
DEFAULT_MAX_DISTANCE=50

# Firebase for push notifications
FCM_SERVER_KEY=your_key
FIREBASE_CREDENTIALS=storage/firebase-credentials.json

# Google Maps for admin dashboard
GOOGLE_MAPS_API_KEY=your_key

# AWS S3 for file storage
AWS_BUCKET=your_bucket
```

#### Database Settings (`app_settings` table)
- `max_distance` - Proximity radius (default: 50m)
- `ghost_mode_enabled` - Allow users to hide
- `terms_content` - Terms & conditions text
- `privacy_content` - Privacy policy text

## 🔒 Security Features

- Multi-guard authentication (Admin + API)
- Sanctum token-based API auth
- CSRF protection for admin
- Password hashing (bcrypt)
- User ban system
- Admin activity logging
- Rate limiting on API
- Input validation on all endpoints
- XSS protection via Laravel escaping

## 🌍 Localization

Supported languages:
- **French (fr)** - Default
- **Arabic (ar)** - Full RTL support

Translation files in `resources/lang/{locale}/`

## 📊 Features Summary

### User Features
- ✅ Phone/Email/Social authentication
- ✅ GPS-based proximity matching
- ✅ Like/Match system
- ✅ Real-time messaging
- ✅ Ghost mode (hide from others)
- ✅ Profile customization
- ✅ Report abusive users
- ✅ Push notifications
- ✅ Bilingual (FR/AR)

### Admin Features
- ✅ Comprehensive dashboard with charts
- ✅ User management (view, edit, ban)
- ✅ Live map of online users
- ✅ Chat moderation
- ✅ Report handling
- ✅ Activity logging
- ✅ Push notification broadcaster
- ✅ App settings configuration
- ✅ Multi-role admin system

## 📈 Performance Considerations

### Optimization Tips

1. **Database Indexes** - Already added to migrations
2. **Query Optimization** - Use eager loading (`with()`)
3. **Caching** - Redis for settings and sessions
4. **CDN** - Use CloudFront for S3 assets
5. **Queue Jobs** - Process notifications asynchronously
6. **API Rate Limiting** - Prevent abuse

### Scalability

- Horizontal scaling via Laravel Vapor
- Database read replicas
- Redis cluster for cache
- SQS for queue distribution
- CloudFront for asset delivery

## 🐛 Debugging

### Common Issues

**Can't login to admin:**
- Run `php artisan migrate:fresh --seed`
- Check `admins` table has records

**API returns 401:**
- Verify Bearer token is included
- Check token hasn't expired
- User might be banned

**Location not updating:**
- Check if user has `is_visible = false` (ghost mode)
- Verify latitude/longitude values are valid

**Push notifications not working:**
- Verify FCM_SERVER_KEY in `.env`
- Check user has `fcm_token` in database
- Review Firebase Console logs

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Sanctum Docs](https://laravel.com/docs/12.x/sanctum)
- [TailwindCSS Docs](https://tailwindcss.com)
- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)
- [Vapor Documentation](https://docs.vapor.build)

## 💡 Next Steps

1. **Setup Firebase project** and add credentials
2. **Configure Google Maps API** key
3. **Setup AWS S3** for file storage
4. **Test all API endpoints** with Postman
5. **Build mobile app** (Flutter/React Native)
6. **Deploy to staging** environment
7. **Load testing** before production
8. **Setup monitoring** (Laravel Telescope, Sentry)

## 🤝 Support

For technical support:
- **Email**: tech@catchme.app
- **Documentation**: README.md
- **API Docs**: API_DOCUMENTATION.md
- **Deployment**: DEPLOYMENT.md

---

**Built with ❤️ using Laravel 12.x**
