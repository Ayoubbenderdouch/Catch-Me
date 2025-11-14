#!/bin/bash

# Catch Me - Quick Setup Script
# This script automates the initial setup of the Laravel application

echo "🚀 Starting Catch Me Setup..."
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ NPM is not installed. Please install Node.js and NPM first."
    exit 1
fi

echo "✅ Prerequisites check passed"
echo ""

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install
echo "✅ PHP dependencies installed"
echo ""

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm install
echo "✅ Node dependencies installed"
echo ""

# Copy environment file
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "⚠️  .env file already exists, skipping..."
fi
echo ""

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate
echo "✅ Application key generated"
echo ""

# Prompt for database configuration
echo "🗄️  Database Configuration"
read -p "Database host [127.0.0.1]: " db_host
db_host=${db_host:-127.0.0.1}

read -p "Database name [catchme_app]: " db_name
db_name=${db_name:-catchme_app}

read -p "Database username [root]: " db_user
db_user=${db_user:-root}

read -sp "Database password: " db_pass
echo ""

# Update .env file with database credentials
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    sed -i '' "s/DB_HOST=.*/DB_HOST=$db_host/" .env
    sed -i '' "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
    sed -i '' "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
    sed -i '' "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env
else
    # Linux
    sed -i "s/DB_HOST=.*/DB_HOST=$db_host/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env
fi

echo "✅ Database configuration updated"
echo ""

# Ask if user wants to run migrations
read -p "Run database migrations and seeders? (y/n): " run_migrations
if [ "$run_migrations" = "y" ] || [ "$run_migrations" = "Y" ]; then
    echo "🗄️  Running migrations..."
    php artisan migrate
    echo "✅ Migrations completed"
    echo ""

    echo "🌱 Running seeders..."
    php artisan db:seed
    echo "✅ Seeders completed"
    echo ""
fi

# Build frontend assets
echo "🎨 Building frontend assets..."
npm run build
echo "✅ Frontend assets built"
echo ""

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link
echo "✅ Storage link created"
echo ""

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✅ Caches cleared"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 Setup completed successfully!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📌 Next Steps:"
echo ""
echo "1. Configure Firebase:"
echo "   - Add FCM_SERVER_KEY to .env"
echo "   - Place firebase-credentials.json in storage/"
echo ""
echo "2. Configure Google Maps:"
echo "   - Add GOOGLE_MAPS_API_KEY to .env"
echo ""
echo "3. Configure AWS S3:"
echo "   - Add AWS credentials to .env"
echo "   - Update AWS_BUCKET name"
echo ""
echo "4. Start the development server:"
echo "   php artisan serve"
echo ""
echo "5. Access Admin Dashboard:"
echo "   http://localhost:8000/admin/login"
echo "   Email: admin@catchme.app"
echo "   Password: password"
echo ""
echo "6. View API Documentation:"
echo "   php artisan l5-swagger:generate"
echo "   http://localhost:8000/api/documentation"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📚 Documentation:"
echo "   - README.md - Main documentation"
echo "   - API_DOCUMENTATION.md - API reference"
echo "   - DEPLOYMENT.md - Deployment guide"
echo "   - PROJECT_STRUCTURE.md - Project overview"
echo ""
echo "🆘 Need help? Contact: tech@catchme.app"
echo ""
