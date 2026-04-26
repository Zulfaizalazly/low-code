#!/bin/bash
set -e

# ============================================
# Arrahnumation V3 - Server Setup (Ubuntu 22.04/24.04)
# Run once on fresh droplet: bash setup.sh
# ============================================

echo "🔧 Setting up server..."

# Update system
sudo apt update && sudo apt upgrade -y

# PHP 8.3 + extensions
echo "📦 Installing PHP 8.3..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-sqlite3 php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath \
    php8.3-intl php8.3-readline php8.3-tokenizer

# Nginx
echo "🌐 Installing Nginx..."
sudo apt install -y nginx

# Composer
echo "📦 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20 LTS + PM2
echo "📦 Installing Node.js 20 + PM2..."
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
sudo npm install -g pm2

# Git
sudo apt install -y git unzip

# Create app directory
echo "📁 Setting up app directory..."
sudo mkdir -p /var/www/arrahnumation
sudo chown -R $USER:www-data /var/www/arrahnumation

# Clone repo (update URL)
echo "📥 Clone your repo:"
echo "  cd /var/www/arrahnumation"
echo "  git clone YOUR_REPO_URL ."

echo ""
echo "============================================"
echo "✅ Server setup complete!"
echo ""
echo "Next steps:"
echo "  1. Clone repo ke /var/www/arrahnumation"
echo "  2. Copy .env.production jadi .env"
echo "  3. Setup Nginx config:"
echo "     sudo cp server/nginx.conf /etc/nginx/sites-available/arrahnumation"
echo "     sudo ln -s /etc/nginx/sites-available/arrahnumation /etc/nginx/sites-enabled/"
echo "     sudo rm /etc/nginx/sites-enabled/default"
echo "     sudo nginx -t && sudo systemctl reload nginx"
echo "  4. Run: bash deploy.sh"
echo "  5. PM2 startup: pm2 startup && pm2 save"
echo "============================================"
