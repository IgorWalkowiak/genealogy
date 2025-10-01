#!/bin/bash
echo "Running composer scripts..."
composer install

php artisan key:generate --force

echo "Creating storage link..."
php artisan storage:link

echo "Running migrations with seeders..."
php artisan migrate:fresh --seed

echo "Installing npm dependencies..."
npm install

echo "Building assets..."
npm run build

echo "Running development server..."
php artisan serve -vvv