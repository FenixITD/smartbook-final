# Берется официальный образ PHP версии 8.5 с установленным FPM (FastCGI Process Manager).
# Приставка alpine означает, что внутри используется минималистичный дистрибутив Linux (Alpine Linux).
# Это делает итоговый контейнер очень легким (весит в разы меньше, чем на базе Ubuntu/Debian) и быстрым.
FROM php:8.5-fpm-alpine

# Автоматический установщик расширений
# Вместо того чтобы вручную искать и ставить системные библиотеки через apk add (что часто превращается в ад с зависимостями),
# здесь скачивается популярный скрипт от mlocati. Он сам знает, какие системные пакеты нужны для каждого расширения PHP,
# устанавливает их, а после установки удаляет мусор, экономя место.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
# Команда делает файл исполняемым.
RUN chmod +x /usr/local/bin/install-php-extensions

# pdo_pgsql, pgsql: Для работы с базой данных PostgreSQL.
# opcache: Сильно ускоряет PHP, кэшируя скомпилированный код.
# redis: Для кэша и сессий.
# gd: Для обработки картинок (например, если пользователи загружают аватарки).
# pcntl, sockets, amqp: Это база для RabbitMQ! Именно они позволяют твоим фоновым воркерам (тем, что отправляют письма)
# держать постоянное TCP-соединение с брокером сообщений и управлять процессами.
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    opcache \
    intl \
    zip \
    bcmath \
    redis \
    gd \
    pcntl \
    sockets \
    amqp

# Composer
# Это "multi-stage build". Вместо того чтобы скачивать установщик и ставить Composer вручную, Docker просто берет готовый
# бинарник Composer'а из другого официального образа и копирует его к тебе. Быстро и чисто.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Это трюк для скорости сборки. Сначала копируются только файлы composer.json и composer.lock, и сразу запускается
# установка зависимостей. То есть, если поменялся только код в контроллере, но пакеты не трогались, Docker возьмет этот
# шаг из своего кэша. Сборка займет 2 секунды вместо минуты. Флаг --no-dev говорит не ставить пакеты для тестов
# и локальной разработки, а --optimize-autoloader ускоряет загрузку классов в Laravel.
WORKDIR /var/www/html

COPY ../../composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Только теперь копируется весь остальной код. Затем настраиваются права:
# Владельцем всех файлов назначается пользователь www-data (от имени этого пользователя работает PHP-FPM).
# Папкам storage (где лежат логи, кэш, загруженные файлы) и bootstrap/cache даются права на запись (775),
# иначе Laravel выдаст ошибку "Permission denied" при попытке записать лог или кэш шаблона.
COPY ../.. .

RUN mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# EXPOSE 9000 — это просто документация для разработчиков, говорящая: "Этот контейнер слушает порт 9000". \
# Nginx будет стучаться именно на этот порт.
#
# CMD ["php-fpm"] — команда, которая запускает сервер PHP-FPM при старте контейнера.
# Он остается работать на переднем плане и ждет запросов от Nginx.
EXPOSE 9000
CMD ["php-fpm"]
