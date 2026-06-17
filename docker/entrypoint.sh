#!/usr/bin/env bash
set -e

mkdir -p \
    storage/app/documents \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ "${DB_CONNECTION:-}" = "mysql" ] && [ -n "${DB_HOST:-}" ]; then
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    until mysqladmin ping \
        -h"${DB_HOST}" \
        -P"${DB_PORT:-3306}" \
        -u"${DB_USERNAME:-root}" \
        "--password=${DB_PASSWORD:-}" \
        --ssl=0 \
        --silent; do
        sleep 2
    done
fi

php artisan config:clear --no-interaction || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${RUN_OPTIMIZE:-true}" = "true" ] && [ "${APP_ENV:-}" = "production" ]; then
    php artisan config:cache --no-interaction
    php artisan view:cache --no-interaction
fi

exec "$@"
