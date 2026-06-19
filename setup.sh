#!/bin/bash

set -e

echo "Starting deployment of the SmartBook project..."

if [ ! -f .env ]; then
    echo "Creating .env file from the example..."
    cp .env.example .env
fi

echo "Starting databases and PHP container..."
docker compose up -d app

echo "Installing PHP packages (Composer)..."
docker compose exec app composer install

echo "Building frontend and starting remaining services..."
docker compose up -d

echo "Generating APP_KEY..."
docker compose exec app php artisan key:generate

echo "Running analytics migrations (ClickHouse)..."
docker compose exec app php artisan clickhouse:migrate

echo "Running main DB migrations (PostgreSQL) and seeding test data..."
docker compose exec app php artisan migrate --seed

echo "Setting permissions for storage and cache folders..."
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache

echo "================================================="
echo "DONE! The project has been successfully deployed and started."
echo "================================================="
echo "🌍 Website:       http://localhost:8000"
echo "📚 API Docs:      http://localhost:8000/api/documentation"
echo "🐘 Database:      http://localhost:5050 (admin@smartbook.ru / admin123)"
echo "🐰 Queues:        http://localhost:15672 (guest / guest)"
echo "🪣 Storage:       http://localhost:9001 (smartbook / smartbook123)"
echo "📧 Mail:          http://localhost:8025"
echo "================================================="
echo ""
echo "Don't forget to change NOTIFICATION_RECIPIENT in .env later!"
