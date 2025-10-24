# syntax=docker/dockerfile:1.7

#########################
# Base Composer Image #
#########################
FROM composer:2.7 AS composer-base

#########################
# PHP Dependencies Stage #
#########################
FROM php:8.3-cli AS composer-prod

# Install build dependencies and PHP extensions in one layer
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git unzip libicu-dev libzip-dev libpq-dev \
        && rm -rf /var/lib/apt/lists/*; \
    \
    docker-php-ext-install \
        intl \
        zip \
        pdo_pgsql \
        pgsql; \
    \
    docker-php-source delete

# Bring in Composer binary from the official image
COPY --from=composer-base /usr/bin/composer /usr/bin/composer

WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy only composer files first for better cache utilization
COPY composer.json composer.lock ./

# Install dependencies with optimization
RUN --mount=type=cache,target=/tmp/composer,sharing=locked \
    composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-progress \
        --no-scripts \
        --apcu-autoloader

#########################
# Frontend Build Stage #
#########################
FROM node:20-slim AS frontend-builder

# Enable pnpm
RUN corepack enable

WORKDIR /app

# Copy package files first
COPY package.json pnpm-lock.yaml ./

# Install dependencies with cache
RUN --mount=type=cache,target=/root/.local/share/pnpm/store,sharing=locked \
    pnpm install --frozen-lockfile --prod=false

# Copy necessary files for build
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY --from=composer-prod /app/vendor ./vendor

# Build frontend assets
RUN pnpm run build

#########################
# Final Runtime Image #
#########################
FROM dunglas/frankenphp:1.1-php8.3-bookworm

# Install required PHP extensions
RUN install-php-extensions \
        pdo_pgsql \
        pgsql \
        redis \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache

WORKDIR /app

# Copy application files (optimized order)
COPY --from=composer-prod /app/vendor ./vendor
COPY --chown=www-data:www-data app ./app
COPY --chown=www-data:www-data bootstrap ./bootstrap
COPY --chown=www-data:www-data config ./config
COPY --chown=www-data:www-data database ./database
COPY --chown=www-data:www-data routes ./routes
COPY --chown=www-data:www-data storage ./storage
COPY --chown=www-data:www-data public ./public
COPY --chown=www-data:www-data artisan ./
COPY --chown=www-data:www-data composer.json composer.lock ./

# Copy built assets
COPY --from=frontend-builder /app/public/build ./public/build

# Create necessary directories and set permissions in one layer
RUN mkdir -p storage/framework/{cache,views,sessions,testing} \
         storage/logs \
         bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Production environment variables
ENV APP_ENV=production \
    APP_DEBUG=0 \
    LOG_LEVEL=warning \
    OPCACHE_ENABLE=1 \
    OPCACHE_VALIDATE_TIMESTAMPS=0 \
    OPCACHE_MAX_ACCELERATED_FILES=20000 \
    OPCACHE_MEMORY_CONSUMPTION=256 \
    OPCACHE_JIT_BUFFER_SIZE=64

# Optimized OPcache configuration for production
RUN echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.enable_file_override=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Clean up unnecessary files from vendor
RUN find vendor -type d \( \
        -name tests \
        -name test \
        -name .github \
        -name examples \
        -name docs \
        -name doc \
    \) -prune -exec rm -rf {} + 2>/dev/null || true; \
    find vendor -type f \( \
        -name "*.md" \
        -name "*.rst" \
        -name "*.txt" \
        -name "CHANGELOG*" \
        -name "README*" \
        -name "CONTRIBUTING*" \
        -name "LICENSE*" \
        -name "Makefile" \
        -name "*.yml" \
        -name "*.yaml" \
    \) -delete 2>/dev/null || true

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Create entrypoint
RUN echo '#!/bin/sh' > /usr/local/bin/docker-entrypoint.sh \
    && echo 'set -e' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'if [ "$1" = "php" ]; then' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '    exec php "$@"' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'fi' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'exec "$@"' >> /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://127.0.0.1:8000/api/health || exit 1

USER www-data

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000", "--admin-port=2020", "--max-requests=1000"]