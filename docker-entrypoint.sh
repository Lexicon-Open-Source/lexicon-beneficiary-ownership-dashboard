#!/bin/sh

set -e

echo "Starting Laravel application initialization..."

# Function to wait for database
wait_for_db() {
    echo "Waiting for database connection..."
    max_tries=30
    count=0

    until php artisan db:show >/dev/null 2>&1 || [ $count -eq $max_tries ]; do
        count=$((count + 1))
        echo "Database is unavailable - sleeping (attempt $count/$max_tries)"
        sleep 2
    done

    if [ $count -eq $max_tries ]; then
        echo "Error: Database connection timeout"
        exit 1
    fi

    echo "Database is ready!"
}

# Wait for database to be ready
if [ "${DB_CONNECTION:-}" = "pgsql" ] || [ "${DB_CONNECTION:-}" = "mysql" ]; then
    wait_for_db
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Clear and cache config for better performance
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if it doesn't exist
if [ ! -L /app/public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link
fi

echo "Application initialization complete!"

# Change to www-data user and execute the CMD
echo "Starting FrankenPHP..."
exec gosu www-data "$@"
