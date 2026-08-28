#!/bin/bash

set -e

echo "Starting deployment of the SmartBook project..."

if [ -f /proc/sys/vm/max_map_count ]; then
    CURRENT_MAP_COUNT=$(cat /proc/sys/vm/max_map_count)
    if [ "$CURRENT_MAP_COUNT" -lt 262144 ]; then
        echo ""
        echo "ERROR: Elasticsearch requires vm.max_map_count >= 262144, but the current value is ${CURRENT_MAP_COUNT}."
        echo ""
        echo "Fix it temporarily (current session only):"
        echo "  sudo sysctl -w vm.max_map_count=262144"
        echo ""
        echo "Or permanently (recommended):"
        echo "  echo 'vm.max_map_count=262144' | sudo tee /etc/sysctl.d/99-elasticsearch.conf"
        echo ""
        echo "Then run ./setup.sh again."
        exit 1
    fi
fi

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

echo "Generating APP_KEY..."
docker compose exec app php artisan key:generate

echo "Building frontend and starting remaining services..."
docker compose up -d

echo "Running analytics migrations (ClickHouse)..."
docker compose exec app php artisan clickhouse:migrate

echo "Running main DB migrations (PostgreSQL) and seeding test data..."
docker compose exec app php artisan migrate --seed

echo "Setting permissions for storage and cache folders..."
docker compose exec app chmod -R 777 storage bootstrap/cache

echo "================================================="
echo "DONE! The project has been successfully deployed and started."
echo "================================================="
echo "🌍 Website:       http://localhost:8000"
echo "📚 API Docs:      http://localhost:8000/api/documentation"
echo "🐘 pgAdmin:       http://localhost:5050 ($(grep -E '^PGADMIN_DEFAULT_EMAIL=' .env | cut -d '=' -f2 | tr -d '[:space:]'))"
echo "🐰 Queues:        http://localhost:15672 (guest / guest)"
echo "🪣 Storage:       http://localhost:9001 ($(grep -E '^MINIO_ROOT_USER=' .env | cut -d '=' -f2 | tr -d '[:space:]') / $(grep -E '^MINIO_ROOT_PASSWORD=' .env | cut -d '=' -f2 | tr -d '[:space:]'))"
echo "📧 Mail:          http://localhost:8025"
echo "================================================="
echo ""
echo "Don't forget to change NOTIFICATION_RECIPIENT in .env later!"
