#!/bin/sh
set -eu

if [ ! -f vendor/autoload.php ]; then
    echo "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist
fi

exec "$@"
