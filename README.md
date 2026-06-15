# 📚 SmartBook — Интернет-магазин книг

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
git clone <url-репозитория>
cd <название-папки>
```

### 2. Создать файл окружения

```bash
cp .env.example .env
```

Все значения по умолчанию уже настроены под Docker и работают без изменений. Единственное, что нужно поменять — `NOTIFICATION_RECIPIENT` (email для уведомлений).

### 3. Собрать и запустить контейнеры

```bash
docker compose up -d --build
```

> Первый запуск займёт несколько минут — Docker скачает образы и соберёт PHP-контейнер.
> Elasticsearch, ClickHouse и RabbitMQ стартуют с healthcheck, поэтому `app` поднимется только после их готовности.

### 4. Установить зависимости и подготовить приложение

```bash
docker compose exec app bash

# Внутри контейнера:
composer install
php artisan key:generate
php artisan migrate --seed
exit
```

### 5. Создать бакет в MinIO

Открой MinIO Console по адресу **http://localhost:9001**

- Логин: `smartbook`
- Пароль: `smartbook123`

Создай бакет с именем **`smartbook-covers`** и установи его политику доступа на **Public**.

### 6. Открыть в браузере

| Сервис | URL | Доступ |
|---|---|---|
| Приложение | http://localhost:8000 | — |
| Swagger API Docs | http://localhost:8000/api/documentation | — |
| pgAdmin | http://localhost:5050 | `admin@smartbook.ru` / `admin123` |
| MinIO Console | http://localhost:9001 | `smartbook` / `smartbook123` |
| RabbitMQ Management | http://localhost:15672 | `guest` / `guest` |
| Mailpit (почта) | http://localhost:8025 | — |

---

## Переменные окружения

Все переменные описаны в `.env.example`. Ниже — только те, которые **нужно поменять** перед первым запуском:

| Переменная | Что это | Дефолт |
|---|---|---|
| `APP_KEY` | Генерируется командой `php artisan key:generate` | пусто |
| `NOTIFICATION_RECIPIENT` | Email для системных уведомлений | `your@email.com` |
| `REVERB_APP_ID` | ID приложения Reverb (можно оставить как есть) | `851880` |
| `REVERB_APP_KEY` | Ключ Reverb | случайная строка |
| `REVERB_APP_SECRET` | Секрет Reverb | случайная строка |

Все остальные значения работают с Docker из коробки и менять их не нужно.

<details>
<summary>Полный список переменных по сервисам</summary>

**PostgreSQL**
```dotenv
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=smartbook
DB_USERNAME=smartbook_user
DB_PASSWORD=StrongPassword_123!
```

**Redis**
```dotenv
REDIS_HOST=redis
REDIS_PORT=6379
SESSION_DRIVER=redis
CACHE_STORE=redis
```

**RabbitMQ**
```dotenv
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_QUEUE=notifications
```

**Elasticsearch**
```dotenv
SCOUT_DRIVER=elastic
ELASTICSEARCH_HOST=http://smartbook_elasticsearch:9200
ELASTICSEARCH_BOOKS_INDEX=books
```

**ClickHouse**
```dotenv
CLICKHOUSE_HOST=clickhouse
CLICKHOUSE_PORT=8123
CLICKHOUSE_DATABASE=smartbook
CLICKHOUSE_USERNAME=default
CLICKHOUSE_PASSWORD=clickhouse
```

**MinIO (S3)**
```dotenv
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=smartbook
AWS_SECRET_ACCESS_KEY=smartbook123
AWS_BUCKET=smartbook-covers
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

**Laravel Reverb (WebSockets)**
```dotenv
BROADCAST_CONNECTION=reverb
REVERB_HOST=reverb
REVERB_PORT=8080
```

**Mailpit (почта)**
```dotenv
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```
</details>

---

## Разработка

### Запуск dev-сервера (Vite + hot-reload)

Поскольку Node.js вынесен в отдельный контейнер, для запуска Vite в режиме разработки используйте команду `run`:

```bash
# Запуск Vite (фронтенд)
docker compose run --rm -p 5173:5173 node npm run dev

# В другом окне терминала запуск слушателя очередей:
docker compose exec app php artisan queue:listen --tries=1
```

### Полезные команды

```bash
# Посмотреть логи всех сервисов
docker compose logs -f

# Зайти в контейнер приложения
docker compose exec app bash

# Запустить миграции
docker compose exec app php artisan migrate

# Очистить кэш
docker compose exec app php artisan cache:clear

# Запустить tinker
docker compose exec app php artisan tinker

# Остановить все контейнеры
docker compose down

# Остановить и удалить все данные (volumes)
docker compose down -v
```

---

## Тестирование

Проект использует **PHPUnit** и включает статический анализ **PHPStan** (строгий режим + правила устаревания).

```bash
# Запустить всё: линтинг + PHPStan + тесты
docker compose exec app composer test

# Только PHPUnit
docker compose exec app vendor/bin/phpunit

# Только PHPStan
docker compose exec app composer stan

# Линтинг (Laravel Pint)
docker compose exec app composer lint

# Проверка стиля без исправлений
docker compose exec app composer test:lint
```

---

## API документация

Swagger / OpenAPI доступен после запуска по адресу:
**http://localhost:8000/api/documentation**

Документация генерируется автоматически при каждом запросе (`L5_SWAGGER_GENERATE_ALWAYS=true`).
Для ручной перегенерации:

```bash
docker compose exec app php artisan l5-swagger:generate
```

---

## Структура сервисов Docker

```
┌─────────────────────────────────────────────────────┐
│                   docker-compose.yml                │
│                                                     │
│  nginx:8000 ──► app (PHP-FPM)                       │
│                     │                               │
│           ┌─────────┼──────────┐                    │
│           ▼         ▼          ▼                    │
│         db:5432  redis:6379  rabbitmq:5672          │
│           │                   │                     │
│      elasticsearch:9200   queue-worker              │
│      clickhouse:8123      reverb:8080               │
│      minio:9000/9001      mailpit:8025/1025         │
└─────────────────────────────────────────────────────┘
```

| Контейнер | Роль |
|---|---|
| `app` | PHP-FPM, обработка HTTP-запросов |
| `nginx` | Веб-сервер, проксирует запросы в `app` |
| `queue-worker` | Обработчик очереди RabbitMQ |
| `reverb` | WebSocket-сервер |
| `db` | PostgreSQL — основная БД |
| `redis` | Кэш и сессии |
| `rabbitmq` | Очередь сообщений |
| `elasticsearch` | Полнотекстовый поиск |
| `clickhouse` | Аналитика |
| `minio` | S3-совместимое хранилище файлов |
| `pgadmin` | GUI для PostgreSQL |
| `mailpit` | Перехват email в разработке |

---

## Возможные проблемы

**Elasticsearch не стартует**

На Linux нужно увеличить лимит виртуальной памяти:
```bash
sudo sysctl -w vm.max_map_count=262144
```
Чтобы изменение сохранялось после перезагрузки, добавь `vm.max_map_count=262144` в `/etc/sysctl.conf`.

**Порт уже занят**

Проверь, что порты `8000`, `5432`, `9200`, `5672`, `6379` свободны:
```bash
lsof -i :8000
```

**Права на файлы (Linux)**
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

**Картинки не загружаются / MinIO недоступен**

Убедись, что бакет `smartbook-covers` создан и имеет публичный доступ (см. шаг 5 быстрого старта).

---

## Лицензия

MIT
