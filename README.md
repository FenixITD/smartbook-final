# SmartBook — Интернет-магазин книг

Laravel 12 приложение для продажи книг с полнотекстовым поиском, real-time уведомлениями и аналитикой.

## Стек технологий

| Компонент | Технология |
|---|---|
| Backend | PHP 8.2+, Laravel 12, Livewire 4, Laravel Fortify |
| Frontend | Tailwind CSS 4, Vite 7, Flux UI |
| База данных | PostgreSQL 17 |
| Поиск | Elasticsearch 8.12 + Laravel Scout |
| Аналитика | ClickHouse 24.3 |
| Очереди | RabbitMQ 3 |
| WebSockets | Laravel Reverb |
| Хранилище файлов | MinIO (S3-совместимое) |
| Кэш и сессии | Redis |
| Веб-сервер | Nginx + PHP-FPM |
| Почта (dev) | Mailpit |

---

## Требования

Перед началом убедись, что на машине установлены:

- [Docker](https://docs.docker.com/get-docker/) и [Docker Compose](https://docs.docker.com/compose/install/) (v2+)
- [Git](https://git-scm.com/)

> PHP, Node.js, Composer устанавливать **не нужно** — всё работает внутри Docker-контейнеров.

---

## Быстрый старт

### 1. Клонировать репозиторий

```bash
git clone <url-репозитория> <название-папки>
cd <название-папки>
```

### 2. Создать файл окружения

```bash
cp .env.example .env
```

Все значения по умолчанию уже настроены под Docker и работают без изменений. 
Единственное, что нужно поменять — NOTIFICATION_RECIPIENT (email для уведомлений). 
Надо зайти в файл .env и в NOTIFICATION_RECIPIENT поменять значение с your@email.com на свой email.
### 3. Собрать и запустить контейнеры

```bash
docker compose up -d --build
```

> Первый запуск займёт несколько минут — Docker скачает образы и соберёт PHP-контейнер.
> Elasticsearch, ClickHouse и RabbitMQ стартуют с healthcheck, поэтому `app` поднимется только после их готовности.

### 4. Установить зависимости и подготовить приложение

```bash
# Заходим в контейнер:
docker compose exec app sh
# либо последующие команды в этом шаге вводим с приставкой 'docker compose exec app'

# Внутри контейнера (или с приставкой):
composer install
php artisan key:generate
php artisan migrate --seed
php artisan clickhouse:migrate
exit

#Пробуем открыть http://localhost:8000/. Если не открывается вводим эти команды по очереди:

docker compose exec app chown -R www-data:www-data storage bootstrap/cache

docker compose exec app chmod -R 775 storage bootstrap/cache
```

### 5. Что можно открыть в браузере

| Сервис | URL | Доступ |
|---|---|---|
| Приложение | http://localhost:8000 | — |
| Swagger API Docs | http://localhost:8000/api/documentation | — |
| pgAdmin | http://localhost:5050 | `admin@smartbook.ru` / `admin123` |
| MinIO Console | http://localhost:9001 | `smartbook` / `smartbook123` |
| RabbitMQ Management | http://localhost:15672 | `guest` / `guest` |
| Mailpit (почта) | http://localhost:8025 | — |
