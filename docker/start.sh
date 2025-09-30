#!/bin/bash

if [ ! -d "/var/lib/mysql/${MYSQL_DATABASE}" ]; then
    echo "First MySQL initialization..."
    /usr/local/bin/mysql-init.sh
else
    echo "MySQL already initialized"
fi


echo "Running composer scripts..."
composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

echo "Creating storage link..."
php artisan storage:link

if [ "$SKIP_MIGRATIONS" != "true" ]; then
    echo "Running migrations with seeders..."
    php artisan migrate:fresh --seed --force
else
    echo "Skipping migrations (SKIP_MIGRATIONS=true)"
    echo "Run migrations manually: docker exec <container> php artisan migrate"
fi

echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
