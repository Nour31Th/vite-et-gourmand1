FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql intl mbstring xml \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-scripts

ENV APP_ENV=prod
ENV APP_DEBUG=0

EXPOSE 8080

CMD php bin/console cache:warmup --env=prod && \
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod && \
    php -S 0.0.0.0:$PORT -t public/
    
