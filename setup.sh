#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

if [ ! -f .env ]; then
  cp .env.example .env
fi

echo "Starting Postgres..."
docker compose up -d postgres

echo "Waiting for database..."
until docker compose exec -T postgres pg_isready -U "${DB_USERNAME:-lunadrive}" -d "${DB_DATABASE:-lunadrive}" >/dev/null 2>&1; do
  sleep 1
done

echo "Building app image..."
docker compose build app

echo "Installing PHP dependencies..."
docker compose run --rm app composer install --no-interaction

if ! grep -q '^APP_KEY=base64:' .env; then
  docker compose run --rm app php artisan key:generate --force
fi

echo "Running migrations..."
docker compose run --rm -e DB_HOST=postgres app php artisan migrate --force

echo "Installing JS dependencies with pnpm..."
pnpm install
pnpm run build

echo "Starting full stack..."
docker compose up -d

echo
echo "LunaDrive is ready at http://localhost:${APP_PORT:-8080}"
