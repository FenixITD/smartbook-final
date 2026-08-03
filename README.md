# SmartBook — Online Bookstore

Laravel 12 application for selling books with full-text search, real-time notifications, and analytics.

## Tech Stack

| Component | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 12, Livewire 4, Laravel Fortify |
| Frontend | Tailwind CSS 4, Vite 7, Flux UI |
| Database | PostgreSQL 17 |
| Search | Elasticsearch 8.12 + Laravel Scout |
| Analytics | ClickHouse 24.3 |
| Queues | RabbitMQ 3 |
| WebSockets | Laravel Reverb |
| File Storage | MinIO (S3-compatible) |
| Cache & Sessions | Redis |
| Web Server | Nginx + PHP-FPM |
| Mail (dev) | Mailpit |

---

## Requirements

Before you begin, make sure the following are installed on your machine:

- [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/) (v2+)
- [Git](https://git-scm.com/)

> PHP, Node.js, and Composer do **not** need to be installed — everything runs inside Docker containers.

---

## How to Deploy the Project

1. Clone the project into a folder - `git clone https://github.com/FenixITD/smartbook-final <folder name>`
2. Navigate to the project folder - `cd <folder name>`
3. Run the command `./setup.sh` in your terminal

## Login Credentials

> The demo accounts below are created **only when `APP_ENV=local`** (via `setup.sh`). In other environments they are never seeded.

### Admin
- email -> admin@smartbook.com
- password -> admin123

### User
- email -> user@smartbook.com
- password -> user123

## What You Can Open in the Browser

| Service | URL | Access |
|---|---|---|
| Application | http://localhost:8000 | — |
| Swagger API Docs | http://localhost:8000/api/documentation | — |
| pgAdmin | http://localhost:5050 | `admin@smartbook.ru` / `admin123` |
| MinIO Console | http://localhost:9001 | `smartbook` / `smartbook123` |
| RabbitMQ Management | http://localhost:15672 | `guest` / `guest` |
| Mailpit (mail) | http://localhost:8025 | — |
