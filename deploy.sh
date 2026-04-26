#!/bin/bash
set -e

# ============================================
# Arrahnumation V3 - Deploy Script
# Usage: bash deploy.sh
# ============================================

APP_DIR="/var/www/arrahnumation"
BRANCH="main"

echo "🚀 Starting deployment..."

# Pull latest code
echo "📥 Pulling latest from ${BRANCH}..."
cd "$APP_DIR"
git fetch origin
git reset --hard "origin/${BRANCH}"

# Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies & build assets
echo "🔨 Building frontend assets..."
npm ci --ignore-scripts
npm run build

# Laravel optimizations
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache 2>/dev/null || true

# Run migrations
echo "🗃️  Running migrations..."
php artisan migrate --force

# Storage link
php artisan storage:link 2>/dev/null || true

# Restart PM2 processes
echo "🔄 Restarting PM2 processes..."
pm2 restart ecosystem.config.cjs --update-env 2>/dev/null || pm2 start ecosystem.config.cjs

# Reload Nginx
echo "🌐 Reloading Nginx..."
sudo nginx -t && sudo systemctl reload nginx

echo "✅ Deployment complete!"
