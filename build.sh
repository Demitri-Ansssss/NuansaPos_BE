#!/usr/bin/env bashFi
# Exit on error
set -o errexit

echo "Running Composer..."
composer install --no-dev --optimize-autoloader

echo "Running Migrations..."
# --force is required for production
php artisan migrate --force

echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Build finished successfully!"
