FROM php:8.2-cli

WORKDIR /var/www/html

ENV COMPOSER_PROCESS_TIMEOUT=0
ENV COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpq-dev \
        postgresql-client \
    && docker-php-ext-install pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

EXPOSE 8000

CMD ["sh", "./start.sh"]
