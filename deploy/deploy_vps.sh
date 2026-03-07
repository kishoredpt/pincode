#!/usr/bin/env bash
set -euo pipefail
sudo apt update
sudo apt install -y nginx php8.3-fpm php8.3-mysql git
sudo mkdir -p /var/www/pincode
sudo rsync -av --delete ./ /var/www/pincode/
sudo cp deploy/nginx.conf /etc/nginx/sites-available/pincode.conf
sudo ln -sf /etc/nginx/sites-available/pincode.conf /etc/nginx/sites-enabled/pincode.conf
cd /var/www/pincode
php scripts/migrate.php || true
sudo systemctl restart php8.3-fpm
sudo nginx -t && sudo systemctl reload nginx
