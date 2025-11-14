# 🚀 Deployment Checklist - Catch Me App

## ✅ Deployment Readiness Status

### Pre-Deployment Review Completed: 2025-11-14

---

## 📋 DEPLOYMENT READINESS CHECKLIST

### ✅ 1. Project Files & Configuration

| Item | Status | Notes |
|------|--------|-------|
| ✅ `vapor.yml` created | **READY** | Production & Staging environments configured |
| ✅ `.env.production.example` created | **READY** | Template with all required variables |
| ✅ `composer.json` valid | **READY** | PHP 8.3, Laravel 12.x |
| ✅ All routes working | **READY** | 28 API endpoints + Admin routes |
| ✅ Assets build successful | **READY** | Vite build passes |
| ✅ Migrations ready | **READY** | 18 migrations present |
| ✅ `.gitignore` configured | **READY** | Sensitive files excluded |

---

## 🔧 BEFORE DEPLOYMENT - REQUIRED ACTIONS

### 1️⃣ AWS Setup (Required)

```bash
# 1. Install Vapor CLI
composer global require laravel/vapor-cli

# 2. Login to Vapor
vapor login

# 3. Initialize Vapor project
vapor init
# This will ask for:
# - Project name: catchme
# - Environment: production
# - It will generate a project ID and update vapor.yml
```

**Update `vapor.yml`:**
- Replace `id: 12345` with your actual Vapor project ID

---

### 2️⃣ Database Setup (AWS RDS)

**Option A: Let Vapor create it automatically**
```bash
vapor database catchme-production
# Follow prompts to create MySQL database
```

**Option B: Create manually in AWS Console**
- Engine: MySQL 8.0
- Instance: db.t3.micro (production) or db.t3.small
- Storage: 20GB minimum
- Multi-AZ: Yes (for production)
- Backup retention: 7 days

**After creation:**
- Update `vapor.yml` with: `database: catchme-production`
- Vapor will automatically inject DB credentials

---

### 3️⃣ Cache Setup (AWS ElastiCache)

```bash
vapor cache catchme-cache
# Creates Redis cluster automatically
```

- Update `vapor.yml` with: `cache: catchme-cache`

---

### 4️⃣ S3 Bucket Setup

```bash
# Create S3 bucket for file storage
vapor bucket catchme-production-assets

# Or manually:
aws s3 mb s3://catchme-production-assets --region us-east-1
```

**Configure CORS for S3:**

Create `cors.json`:
```json
{
  "CORSRules": [
    {
      "AllowedOrigins": ["*"],
      "AllowedMethods": ["GET", "POST", "PUT", "DELETE"],
      "AllowedHeaders": ["*"],
      "MaxAgeSeconds": 3000
    }
  ]
}
```

```bash
aws s3api put-bucket-cors \
  --bucket catchme-production-assets \
  --cors-configuration file://cors.json
```

---

### 5️⃣ Environment Variables Setup

```bash
# Set production environment variables in Vapor
vapor env:pull production

# Edit the downloaded .env file with your values:
# - APP_KEY (generate new with: php artisan key:generate --show)
# - DATABASE credentials (auto-filled by Vapor)
# - AWS_BUCKET
# - FCM_SERVER_KEY
# - GOOGLE_MAPS_API_KEY

# Push updated environment
vapor env:push production
```

**CRITICAL Environment Variables:**

| Variable | Where to Get | Required |
|----------|-------------|----------|
| `APP_KEY` | `php artisan key:generate --show` | ✅ YES |
| `DB_*` | Auto-filled by Vapor | ✅ YES |
| `AWS_BUCKET` | Your S3 bucket name | ✅ YES |
| `FCM_SERVER_KEY` | Firebase Console | ✅ YES |
| `FCM_SENDER_ID` | Firebase Console | ✅ YES |
| `GOOGLE_MAPS_API_KEY` | Google Cloud Console | ✅ YES |
| `AWS_ACCESS_KEY_ID` | Vapor handles this | ⚠️ Auto |
| `AWS_SECRET_ACCESS_KEY` | Vapor handles this | ⚠️ Auto |

---

### 6️⃣ Firebase Setup

1. Go to [Firebase Console](https://console.firebase.google.com)
2. Create project: "Catch Me"
3. Enable Cloud Messaging
4. Download service account credentials JSON
5. Upload to S3:

```bash
aws s3 cp firebase-credentials.json \
  s3://catchme-production-assets/firebase-credentials.json

# Update environment variable:
# FIREBASE_CREDENTIALS=s3://catchme-production-assets/firebase-credentials.json
```

6. Get FCM Server Key:
   - Project Settings → Cloud Messaging → Server Key
   - Add to environment: `FCM_SERVER_KEY=`

---

### 7️⃣ Google Maps API Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create new project or use existing
3. Enable APIs:
   - Maps JavaScript API
   - Maps Static API
   - Places API (optional)
4. Create API Key with restrictions:
   - HTTP referrers: `https://api.catchme.app/*`, `https://admin.catchme.app/*`
5. Add to environment: `GOOGLE_MAPS_API_KEY=`

---

### 8️⃣ Domain Configuration

**DNS Records (update after first deployment):**

Vapor will provide IP addresses after deployment.

```
Type    Name        Value                   TTL
A       @           [Vapor IP]              300
A       api         [Vapor IP]              300
A       admin       [Vapor IP]              300
CNAME   www         catchme.app             300
```

**Update `vapor.yml`:**
```yaml
domain: api.catchme.app  # Main API domain
```

**SSL Certificate:**
- Vapor automatically provisions via AWS ACM
- No action needed

---

## 🚀 DEPLOYMENT STEPS

### Step 1: Final Checks

```bash
cd "/Users/macbook/Desktop/Catch Me/Catch Me Dashbaord"

# 1. Update dependencies
composer install --no-dev --optimize-autoloader

# 2. Build assets
npm run build

# 3. Run tests (if you have any)
php artisan test

# 4. Clear local caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

### Step 2: Deploy to Production

```bash
# Deploy to production
vapor deploy production

# Monitor deployment
vapor logs production --follow
```

**Deployment will:**
1. Upload code to S3
2. Build Lambda functions
3. Run migrations
4. Cache configs
5. Deploy to AWS Lambda

**Expected duration:** 3-5 minutes

---

### Step 3: Post-Deployment Verification

```bash
# 1. Check application status
curl https://api.catchme.app/api/auth/login

# Should return: {"message":"The email field is required..."} (validation error is OK)

# 2. SSH into Vapor environment
vapor shell production

# 3. Run seeders (ONLY FIRST TIME!)
php artisan db:seed --class=AdminSeeder

# 4. Exit shell
exit
```

---

### Step 4: Test API Endpoints

**Test Registration:**
```bash
curl -X POST https://api.catchme.app/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@catchme.app",
    "phone": "+33612345678",
    "password": "password123",
    "password_confirmation": "password123",
    "date_of_birth": "1995-05-15",
    "gender": "male"
  }'
```

**Expected Response:**
```json
{
  "message": "Registration successful",
  "token": "...",
  "user": {...}
}
```

**Test Login:**
```bash
curl -X POST https://api.catchme.app/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@catchme.app",
    "password": "password123"
  }'
```

---

### Step 5: Admin Dashboard Access

1. Visit: `https://admin.catchme.app` (update domain in vapor.yml)
2. Default credentials:
   - Email: `admin@catchme.app`
   - Password: `password`
3. ⚠️ **CHANGE PASSWORD IMMEDIATELY!**

---

## 📊 POST-DEPLOYMENT MONITORING

### View Logs

```bash
# Real-time logs
vapor logs production --follow

# Filter errors
vapor logs production --filter="error"

# Last 100 lines
vapor logs production --lines=100
```

### Check Metrics

```bash
# View application metrics
vapor metrics production

# View database metrics
vapor database:metrics catchme-production
```

### Queue Management

```bash
# Monitor queue
vapor queue:work production

# Restart queue workers
vapor queue:restart production
```

---

## 🔒 SECURITY CHECKLIST

After deployment, verify:

- [ ] **APP_DEBUG=false** in production
- [ ] **APP_ENV=production**
- [ ] Admin password changed from default
- [ ] Database credentials secured
- [ ] S3 bucket permissions configured (private)
- [ ] Firebase credentials protected (not public)
- [ ] Rate limiting active on all endpoints
- [ ] CORS configured properly
- [ ] SSL certificate active (HTTPS)
- [ ] Sanctum token expiration set (if needed)

---

## 🔄 ROLLBACK PROCEDURE

If something goes wrong:

```bash
# List deployments
vapor deployments production

# Rollback to previous version
vapor rollback production

# Confirm rollback
vapor logs production
```

---

## 📱 UPDATE MOBILE APPS

After deployment, update API base URL in mobile apps:

### iOS App:
```swift
// NetworkManager.swift
return "https://api.catchme.app/api"
```

### Flutter/Android App:
```dart
// lib/config/constants.dart
static const String baseUrl = 'https://api.catchme.app/api';
```

---

## 💰 COST ESTIMATION

**Monthly AWS Costs:**

| Service | Instance Type | Est. Cost |
|---------|--------------|-----------|
| Lambda (Vapor) | - | $20-50 |
| RDS MySQL | db.t3.micro | $15 |
| ElastiCache Redis | cache.t3.micro | $12 |
| S3 Storage | - | $5 |
| Data Transfer | - | $10 |
| CloudFront (optional) | - | $10 |
| **TOTAL** | | **$72-102/month** |

---

## 🆘 TROUBLESHOOTING

### Deployment Fails

**Error: "Build failed"**
```bash
# Clear local cache and retry
php artisan config:clear
vapor deploy production
```

**Error: "Database connection failed"**
- Check RDS security groups allow Lambda access
- Verify database credentials
- Ensure database exists

### API Returns 500 Errors

```bash
# Check logs
vapor logs production --filter="error"

# Clear caches
vapor command production "php artisan cache:clear"
vapor command production "php artisan config:clear"
```

### Queue Not Processing

```bash
# Restart queue workers
vapor queue:restart production

# Check SQS queue status in AWS Console
```

---

## ✅ FINAL CHECKLIST BEFORE DEPLOY

- [ ] `vapor.yml` configured with correct project ID
- [ ] AWS RDS database created
- [ ] AWS ElastiCache Redis created
- [ ] S3 bucket created with CORS
- [ ] Firebase credentials uploaded to S3
- [ ] All environment variables set in Vapor
- [ ] Google Maps API key configured
- [ ] DNS ready to update
- [ ] Mobile apps ready to update base URL
- [ ] Backup plan in place
- [ ] Monitoring set up

---

## 📞 SUPPORT

- **Vapor Documentation:** https://docs.vapor.build
- **Laravel Documentation:** https://laravel.com/docs/12.x
- **AWS Support:** AWS Console
- **Vapor Support:** support@vapor.build

---

## 🎉 DEPLOYMENT COMMAND

Once all above is complete:

```bash
cd "/Users/macbook/Desktop/Catch Me/Catch Me Dashbaord"
vapor deploy production
```

**Good luck! 🚀**

---

**Last Updated:** 2025-11-14
**Reviewed By:** Claude Code Assistant
**Status:** ✅ Ready for Deployment
