#!/bin/sh
set -e

DB_HOST="${DB_HOST:-order-db}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-order_db}"
DB_USERNAME="${DB_USERNAME:-iae}"
DB_PASSWORD="${DB_PASSWORD:-iae}"

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

set_env_value() {
  key="$1"
  value="$2"

  if [ -f .env ]; then
    if grep -q "^${key}=" .env; then
      sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
      printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
  fi
}

set_env_value DB_CONNECTION pgsql
set_env_value DB_HOST "$DB_HOST"
set_env_value DB_PORT "$DB_PORT"
set_env_value DB_DATABASE "$DB_DATABASE"
set_env_value DB_USERNAME "$DB_USERNAME"
set_env_value DB_PASSWORD "$DB_PASSWORD"

echo "Waiting for database at ${DB_HOST}:${DB_PORT}..."
attempt=1
while [ "$attempt" -le 60 ]; do
  if PGPASSWORD="$DB_PASSWORD" pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" >/dev/null 2>&1; then
    echo "Database is ready."
    break
  fi

  echo "Waiting for database at ${DB_HOST}:${DB_PORT}... Attempt ${attempt}/60"
  attempt=$((attempt + 1))
  sleep 2
done

if [ "$attempt" -gt 60 ]; then
  echo "Database is not ready after 60 attempts."
  exit 1
fi

if [ ! -f vendor/autoload.php ]; then
  composer install --no-dev --no-interaction --prefer-dist --no-progress || composer update --no-dev --no-interaction --prefer-dist --no-progress
fi

php artisan config:clear || true

if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force || true
fi

migration_status=0
timeout 60s php artisan migrate --force || migration_status=$?
if [ "$migration_status" -ne 0 ] && [ "$migration_status" -ne 124 ]; then
  exit "$migration_status"
fi
if [ "$migration_status" -eq 124 ]; then
  echo "Migration command timed out after applying available migrations; continuing startup."
fi

php artisan serve --host=0.0.0.0 --port=8000
