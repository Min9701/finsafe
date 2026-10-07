FROM node:22-bookworm AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.* ./
COPY postcss.config.* ./
COPY tailwind.config.* ./

RUN npm run build

FROM php:8.4-fpm

RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Laravel application
COPY . .

# Copy frontend build from Node stage
COPY --from=frontend /app/public/build ./public/build

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Laravel writable directories
RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache

CMD ["php-fpm"]