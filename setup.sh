#!/bin/bash

set -e

echo "Starting deployment of the SmartBook project..."

if [ ! -f .env ]; then
    echo "Creating .env file from the example..."
    cp .env.example .env
fi

if ! grep -q "^REVERB_APP_ID=.\+" .env; then
    echo "Generating Reverb app credentials..."
    sed -i "s/^REVERB_APP_ID=.*/REVERB_APP_ID=$(openssl rand -hex 4)/" .env
    sed -i "s/^REVERB_APP_KEY=.*/REVERB_APP_KEY=$(openssl rand -hex 10)/" .env
    sed -i "s/^REVERB_APP_SECRET=.*/REVERB_APP_SECRET=$(openssl rand -hex 10)/" .env
fi

echo "Starting databases and PHP container..."
docker compose up -d app

echo "Installing PHP packages (Composer)..."
docker compose exec app composer install

echo "Clearing any stale config cache..."
docker compose exec app php artisan config:clear

echo "Building frontend and starting remaining services..."
docker compose up -d

echo "Generating APP_KEY..."
docker compose exec app php artisan key:generate

echo "Running analytics migrations (ClickHouse)..."
docker compose exec app php artisan clickhouse:migrate
echo "Running main DB migrations (PostgreSQL)..."
docker compose exec app php artisan migrate

APP_ENV_VALUE=$(grep -E "^APP_ENV=" .env | cut -d '=' -f2 | tr -d '[:space:]')
if [ "$APP_ENV_VALUE" = "local" ]; then
    echo "APP_ENV=local detected — seeding demo/test data..."
    docker compose exec app php artisan db:seed
else
    echo "APP_ENV is \"$APP_ENV_VALUE\" (not local) — skipping db:seed."
    echo "No demo accounts are created; production users must be created manually."
fi

echo "Setting permissions for storage and cache folders..."
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache

echo "================================================="
echo "DONE! The project has been successfully deployed and started."
echo "================================================="
echo "🌍 Website:       http://localhost:8000"
echo "📚 API Docs:      http://localhost:8000/api/documentation"
if [ "$APP_ENV_VALUE" = "local" ]; then
    echo "👤 Demo admin:    admin@smartbook.com / admin123 (local only)"
    echo "👤 Demo user:     user@smartbook.com / user123 (local only)"
else
    echo "👤 Demo accounts: not seeded (APP_ENV is not \"local\")"
fi
echo "🐘 pgAdmin:       http://localhost:5050 ($(grep -E '^PGADMIN_DEFAULT_EMAIL=' .env | cut -d '=' -f2 | tr -d '[:space:]'))"
echo "🐰 Queues:        http://localhost:15672 (guest / guest)"
echo "🪣 Storage:       http://localhost:9001 ($(grep -E '^MINIO_ROOT_USER=' .env | cut -d '=' -f2 | tr -d '[:space:]') / $(grep -E '^MINIO_ROOT_PASSWORD=' .env | cut -d '=' -f2 | tr -d '[:space:]'))"
echo "📧 Mail:          http://localhost:8025"
echo "================================================="
echo ""
echo "Don't forget to change NOTIFICATION_RECIPIENT in .env later!"
