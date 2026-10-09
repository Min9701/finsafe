#!/bin/sh
set -eu

mkdir -p \
    storage/framework/cache \
    storage/framework/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ ! -f vendor/autoload.php ]; then
    echo "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist
fi

exec "$@"
