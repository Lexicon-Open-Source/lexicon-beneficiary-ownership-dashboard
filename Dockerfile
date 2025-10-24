# syntax=docker/dockerfile:1

# Stage 1: Build frontend assets with pnpm
FROM node:20-alpine AS frontend-builder

# Install pnpm
RUN corepack enable && corepack prepare pnpm@latest --activate

WORKDIR /app

# Copy package files
COPY package*.json pnpm-lock.yaml* ./

# Install dependencies (use frozen-lockfile if lock exists, otherwise generate it)
RUN if [ -f pnpm-lock.yaml ]; then \
        pnpm install --frozen-lockfile; \
    else \
        pnpm install --no-frozen-lockfile; \
    fi

# Copy frontend source files
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources

# Build assets
RUN pnpm run build

# Stage 2: Build PHP application with FrankenPHP
FROM dunglas/frankenphp:latest-php8.2 AS base

# Install system dependencies
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    redis \
    zip \
    exif \
    pcntl \
    bcmath \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (skip dev dependencies for production)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Copy application files
COPY . .

# Copy built frontend assets from frontend-builder
COPY --from=frontend-builder /app/public/build ./public/build

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Create entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions for Laravel
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Create Caddyfile for FrankenPHP
RUN echo '{\n\
    frankenphp\n\
    order php_server before file_server\n\
}\n\
\n\
:80 {\n\
    root * /app/public\n\
    php_server\n\
    encode gzip\n\
    file_server\n\
}\n' > /etc/caddy/Caddyfile

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Use entrypoint script
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Start FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
