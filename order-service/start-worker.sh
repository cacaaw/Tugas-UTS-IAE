#!/bin/sh
set -e

DB_HOST="${DB_HOST:-order-db}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-order_db}"
DB_USERNAME="${DB_USERNAME:-iae}"
DB_PASSWORD="${DB_PASSWORD:-iae}"
RABBITMQ_HOST="${RABBITMQ_HOST:-rabbitmq}"
RABBITMQ_PORT="${RABBITMQ_PORT:-5672}"
RABBITMQ_QUEUE="${RABBITMQ_QUEUE:-iae_orders}"

echo "Waiting for order database at ${DB_HOST}:${DB_PORT}..."
attempt=1
while [ "$attempt" -le 60 ]; do
  if PGPASSWORD="$DB_PASSWORD" pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" >/dev/null 2>&1; then
    echo "Order database is ready."
    break
  fi

  echo "Waiting for order database... Attempt ${attempt}/60"
  attempt=$((attempt + 1))
  sleep 2
done

if [ "$attempt" -gt 60 ]; then
  echo "Order database is not ready after 60 attempts."
  exit 1
fi

echo "Waiting for RabbitMQ at ${RABBITMQ_HOST}:${RABBITMQ_PORT}..."
attempt=1
while [ "$attempt" -le 60 ]; do
  if php -r '$h=getenv("RABBITMQ_HOST") ?: "rabbitmq"; $p=(int)(getenv("RABBITMQ_PORT") ?: 5672); $s=@fsockopen($h, $p, $errno, $errstr, 2); if ($s) { fclose($s); exit(0); } exit(1);'; then
    echo "RabbitMQ is ready."
    break
  fi

  echo "Waiting for RabbitMQ... Attempt ${attempt}/60"
  attempt=$((attempt + 1))
  sleep 2
done

if [ "$attempt" -gt 60 ]; then
  echo "RabbitMQ is not ready after 60 attempts."
  exit 1
fi

if [ ! -f vendor/autoload.php ]; then
  composer install --no-dev --no-interaction --prefer-dist --no-progress || composer update --no-dev --no-interaction --prefer-dist --no-progress
fi

php artisan config:clear || true

echo "Starting RabbitMQ queue worker for queue ${RABBITMQ_QUEUE}..."
php artisan queue:work rabbitmq --queue="${RABBITMQ_QUEUE}" --sleep=1 --tries=3 --timeout=90
