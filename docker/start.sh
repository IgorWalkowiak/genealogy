#!/bin/bash

if [ -f "/tmp/env-config/.env" ]; then
    echo "Copying environment configuration from ConfigMap..."
    cp /tmp/env-config/.env /var/www/html/.env
    
    for var in APP_KEY DB_USERNAME DB_PASSWORD; do
        if [ ! -z "${!var}" ]; then
            sed -i "s|\${${var}}|${!var}|g" /var/www/html/.env
        fi
    done
else
    echo "No ConfigMap, using .env.example..."
    cp .env.example .env
fi

echo "Running composer scripts..."
composer install --no-dev --optimize-autoloader --no-scripts

php artisan key:generate --force

echo "Creating storage link..."
php artisan storage:link

echo "Running migrations with seeders..."
php artisan migrate:fresh --seed

echo "Installing npm dependencies..."
npm install --include=dev

echo "Building assets..."
npm run build

echo "Running development server..."
php artisan serve -vvv