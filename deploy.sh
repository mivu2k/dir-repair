#!/bin/bash

echo "🚀 Starting Deployment Process..."

# 1. Update Code
git pull origin main

# 2. PHP Dependencies
composer install --no-dev --optimize-autoloader

# 3. Database Migrations
php artisan migrate --force

# 4. Frontend Assets
npm install
npm run build

# 5. Optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data .

echo "✅ Deployment Successful!"
