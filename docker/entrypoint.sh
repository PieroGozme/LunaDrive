#!/bin/sh
set -e

mkdir -p \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/cache \
  storage/framework/tmp \
  storage/logs \
  bootstrap/cache

chmod -R ug+rwx storage bootstrap/cache || true

if [ -f artisan ]; then
  php artisan config:clear || true
fi

exec "$@"
