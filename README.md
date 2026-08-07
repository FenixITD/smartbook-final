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

> **Linux / WSL2 only:** Elasticsearch requires the host's `vm.max_map_count` to be
> at least `262144` (the default is `65530`). If the check inside `setup.sh` fails,
> apply one of the following and then run `./setup.sh` again:
>
> - Temporarily (current session only): `sudo sysctl -w vm.max_map_count=262144`
> - Permanently (recommended): `echo 'vm.max_map_count=262144' | sudo tee /etc/sysctl.d/99-elasticsearch.conf`

## How to Deploy the Project

1. Clone the project into a folder - `git clone https://github.com/FenixITD/smartbook-final <folder name>`
2. Navigate to the project folder - `cd <folder name>`
3. Run the command `./setup.sh` in your terminal

> **Note:** the following ports must be free: `5432`, `6379`, `8000`, `8080`, `8123`, `9000`, `9001`, `9200`, `5050`, `8025`, `5672`, `15672`, `1025`.

> **Note:** right after `setup.sh` finishes, the frontend may still be building
> (the `node` container runs `npm install && npm run build`). Wait for it to exit
> with code `0` (`docker compose ps`) before opening the site — otherwise you may see
> a 500 error (missing `public/build/manifest.json`).
>
> **Note:** `setup.sh` is meant to be run **once** on a fresh clone. Re-running it
> regenerates the app key and re-seeds the database.

## Demo Accounts

The `php artisan migrate --seed` command generates random passwords for the demo
accounts (`admin@smartbook.com` / `user@smartbook.com`) and prints them to the console.

## What You Can Open in the Browser

| Service | URL | Access |
|---|---|---|
| Application | http://localhost:8000 | — |
| Swagger API Docs | http://localhost:8000/api/documentation | — |
| pgAdmin | http://localhost:5050 | see `PGADMIN_DEFAULT_*` in `.env` |
| MinIO Console | http://localhost:9001 | see `MINIO_ROOT_*` in `.env` |
| RabbitMQ Management | http://localhost:15672 | `guest` / `guest` |
| Mailpit (mail) | http://localhost:8025 | — |
