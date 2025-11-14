# Deployment Guide - Catch Me App

## Prerequisites

- Laravel Vapor CLI installed
- AWS Account configured
- Domain name registered
- Firebase project created
- MySQL database provisioned

## Step 1: Configure Environment

### 1.1 Create `.env.production`

```env
APP_NAME="Catch Me"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://catchme.app

# Database (RDS)
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.amazonaws.com
DB_PORT=3306
DB_DATABASE=catchme_production
DB_USERNAME=admin
DB_PASSWORD=your-secure-password

# AWS Vapor
VAPOR_ARTIFACT_NAME=catchme-app
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=catchme-production-assets

# Cache & Queue (Redis/SQS)
CACHE_DRIVER=redis
QUEUE_CONNECTION=sqs
SESSION_DRIVER=database
REDIS_HOST=your-redis-endpoint.amazonaws.com

# Firebase
FCM_SERVER_KEY=your-fcm-server-key
FCM_SENDER_ID=your-sender-id
FIREBASE_CREDENTIALS=s3://catchme-production-assets/firebase-credentials.json

# Google Maps
GOOGLE_MAPS_API_KEY=your-google-maps-api-key

# App Settings
DEFAULT_MAX_DISTANCE=50
GHOST_MODE_ENABLED=true
```

## Step 2: Vapor Configuration

### 2.1 Create `vapor.yml`

```yaml
id: 12345
name: catchme
environments:
  production:
    memory: 1024
    cli-memory: 512
    runtime: 'php-8.3:al2'
    build:
      - 'composer install --no-dev --optimize-autoloader'
      - 'php artisan event:cache'
      - 'npm ci && npm run build'
    deploy:
      - 'php artisan migrate --force'
      - 'php artisan storage:link'
      - 'php artisan config:cache'
      - 'php artisan route:cache'
      - 'php artisan view:cache'
    database: catchme-production
    cache: catchme-cache
    scheduler: true
    queue: true
    domain: api.catchme.app
    cors: true

  staging:
    memory: 512
    runtime: 'php-8.3:al2'
    database: catchme-staging
    domain: staging.catchme.app
```

## Step 3: AWS Services Setup

### 3.1 RDS (MySQL Database)

```bash
# Create RDS MySQL instance
aws rds create-db-instance \
  --db-instance-identifier catchme-production \
  --db-instance-class db.t3.micro \
  --engine mysql \
  --engine-version 8.0 \
  --master-username admin \
  --master-user-password your-password \
  --allocated-storage 20
```

### 3.2 S3 Bucket (File Storage)

```bash
# Create S3 bucket for assets
aws s3 mb s3://catchme-production-assets --region eu-west-1

# Enable CORS
aws s3api put-bucket-cors --bucket catchme-production-assets --cors-configuration file://cors.json
```

**cors.json:**
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

### 3.3 ElastiCache (Redis)

```bash
# Create Redis cluster
aws elasticache create-cache-cluster \
  --cache-cluster-id catchme-cache \
  --cache-node-type cache.t3.micro \
  --engine redis \
  --num-cache-nodes 1
```

### 3.4 SQS (Queue)

Vapor automatically creates SQS queues.

## Step 4: Firebase Setup

### 4.1 Configure Firebase

1. Go to [Firebase Console](https://console.firebase.google.com)
2. Create a new project: "Catch Me"
3. Enable Cloud Messaging
4. Download service account credentials
5. Upload to S3:

```bash
aws s3 cp firebase-credentials.json s3://catchme-production-assets/
```

### 4.2 Get FCM Server Key

1. Project Settings → Cloud Messaging
2. Copy Server Key
3. Add to `.env.production`

## Step 5: Domain Configuration

### 5.1 Configure DNS

Add these records to your domain:

```
Type    Name        Value
A       @           (Vapor will provide)
A       api         (Vapor will provide)
A       admin       (Vapor will provide)
CNAME   www         catchme.app
```

### 5.2 SSL Certificate

Vapor automatically provisions SSL certificates via AWS ACM.

## Step 6: Deploy to Vapor

### 6.1 Initialize Vapor

```bash
# Install Vapor CLI
composer global require laravel/vapor-cli

# Login to Vapor
vapor login

# Initialize project
vapor init
```

### 6.2 Deploy

```bash
# Deploy to production
vapor deploy production

# Monitor deployment
vapor logs production
```

### 6.3 First-time Setup

```bash
# SSH into Vapor environment
vapor shell production

# Run seeders (only first time)
php artisan db:seed --class=AdminSeeder
```

## Step 7: Post-Deployment

### 7.1 Verify Deployment

```bash
# Check application status
curl https://api.catchme.app/api/auth/login

# Test admin login
# Visit: https://admin.catchme.app
```

### 7.2 Configure Monitoring

```bash
# View logs
vapor logs production

# Monitor metrics
vapor metrics production
```

### 7.3 Setup Backups

```bash
# Configure automated RDS snapshots
aws rds modify-db-instance \
  --db-instance-identifier catchme-production \
  --backup-retention-period 7 \
  --preferred-backup-window "03:00-04:00"
```

## Step 8: CI/CD Setup (Optional)

### 8.1 GitHub Actions

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Vapor

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'

      - name: Install Dependencies
        run: composer install --no-dev --optimize-autoloader

      - name: Deploy to Vapor
        run: vapor deploy production
        env:
          VAPOR_API_TOKEN: ${{ secrets.VAPOR_API_TOKEN }}
```

## Monitoring & Maintenance

### Application Logs

```bash
# Real-time logs
vapor logs production --follow

# Filter by type
vapor logs production --filter="error"
```

### Database Maintenance

```bash
# Connect to RDS
vapor database:shell production

# Run migrations
vapor command production "php artisan migrate"
```

### Queue Management

```bash
# Restart queue workers
vapor queue:restart production

# Monitor queue
vapor queue:work production
```

## Scaling

### Horizontal Scaling

Vapor automatically scales based on traffic.

Configure in `vapor.yml`:
```yaml
environments:
  production:
    auto-scaling:
      min: 1
      max: 10
```

### Database Scaling

```bash
# Upgrade RDS instance
aws rds modify-db-instance \
  --db-instance-identifier catchme-production \
  --db-instance-class db.t3.medium \
  --apply-immediately
```

## Security Checklist

- [ ] Environment variables secured
- [ ] Database credentials rotated
- [ ] S3 bucket permissions configured
- [ ] Firebase credentials protected
- [ ] SSL certificates active
- [ ] Admin passwords changed from defaults
- [ ] Rate limiting configured
- [ ] CORS properly configured
- [ ] WAF rules enabled (optional)
- [ ] CloudWatch alarms set up

## Rollback Procedure

```bash
# List deployments
vapor deployments production

# Rollback to previous version
vapor rollback production
```

## Troubleshooting

### Deployment Fails

```bash
# Clear caches
vapor command production "php artisan cache:clear"
vapor command production "php artisan config:clear"

# Check logs
vapor logs production
```

### Database Connection Issues

```bash
# Verify RDS security groups
# Ensure Lambda functions can access RDS
# Check database credentials
```

### Queue Not Processing

```bash
# Restart queue workers
vapor queue:restart production

# Check SQS permissions
```

## Cost Estimation

**Monthly AWS Costs (Estimated):**

- Lambda (Vapor): ~$20-50
- RDS (db.t3.micro): ~$15
- ElastiCache (cache.t3.micro): ~$12
- S3 Storage: ~$5
- Data Transfer: ~$10
- **Total: ~$62-92/month**

## Support

- **Vapor Docs**: https://docs.vapor.build
- **AWS Support**: AWS Console
- **Team Support**: devops@catchme.app

## Emergency Contacts

- **DevOps Lead**: devops@catchme.app
- **On-Call**: +33 6 XX XX XX XX
- **AWS Account**: account-admin@catchme.app
