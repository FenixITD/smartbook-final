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

## Как развернуть проект

Просто запустите команду <span style="color: #ff9900;">./setup.sh</span> в терминале =)

## 5. Что можно открыть в браузере

| Сервис | URL | Доступ |
|---|---|---|
| Приложение | http://localhost:8000 | — |
| Swagger API Docs | http://localhost:8000/api/documentation | — |
| pgAdmin | http://localhost:5050 | `admin@smartbook.ru` / `admin123` |
| MinIO Console | http://localhost:9001 | `smartbook` / `smartbook123` |
| RabbitMQ Management | http://localhost:15672 | `guest` / `guest` |
| Mailpit (почта) | http://localhost:8025 | — |
