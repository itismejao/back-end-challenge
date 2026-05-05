#!/bin/sh
set -e

# ─── First-run: bootstrap Laravel project if not present ───
if [ ! -f /var/www/artisan ]; then
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  First run detected — creating Laravel project"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    # Create project in temp dir
    composer create-project laravel/laravel /tmp/laravel \
        --prefer-dist --no-interaction

    # Copy Laravel files without overwriting existing ones (docker/, .env, etc)
    cp -rn /tmp/laravel/. /var/www/ 2>/dev/null || true
    # Force-copy essential Laravel files that must exist
    cp -f /tmp/laravel/artisan /var/www/artisan
    cp -f /tmp/laravel/composer.json /var/www/composer.json
    cp -f /tmp/laravel/composer.lock /var/www/composer.lock
    cp -rf /tmp/laravel/vendor /var/www/vendor
    cp -rf /tmp/laravel/bootstrap /var/www/bootstrap
    cp -rf /tmp/laravel/config /var/www/config
    cp -rf /tmp/laravel/routes /var/www/routes
    cp -rf /tmp/laravel/app /var/www/app
    rm -rf /tmp/laravel

    cd /var/www

    # Install Octane
    composer require laravel/octane --no-interaction

    # Generate app key if not set
    php artisan key:generate --no-interaction

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Laravel project created successfully!"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
fi

# ─── Ensure storage structure exists ───
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/testing
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

# ─── Run migrations if DB is available ───
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations..."
    php /var/www/artisan migrate --force --no-interaction 2>/dev/null || true
    echo "Running seeders..."
    php /var/www/artisan db:seed --class=CountrySeeder --force --no-interaction 2>/dev/null || true
    php /var/www/artisan db:seed --class=ProviderSeeder --force --no-interaction 2>/dev/null || true
fi

# ─── Execute the main command ───
exec "$@"
