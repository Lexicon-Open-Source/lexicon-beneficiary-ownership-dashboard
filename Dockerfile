# syntax=docker/dockerfile:1

# Stage 1: Install composer dependencies
FROM composer:latest AS composer-dependencies

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Copy application structure needed for composer autoload
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY routes ./routes
COPY database ./database

# Install all dependencies (including dev) for the build process
# Ignore platform requirements since we're just preparing vendor for copying
RUN composer install --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# Stage 2: Build frontend assets with pnpm
FROM node:20-alpine AS frontend-builder

# Install pnpm
RUN corepack enable && corepack prepare pnpm@latest --activate

WORKDIR /app

# Copy vendor directory from composer stage (needed for Filament preset)
COPY --from=composer-dependencies /app/vendor ./vendor

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

# Stage 3: Build PHP application with FrankenPHP + Octane
FROM dunglas/frankenphp:latest-php8.3-alpine AS builder

# Install system dependencies and PHP extensions, then clean up
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
    opcache \
    # Clean up APK cache and unnecessary files
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/* \
    && rm -rf /usr/share/man/* \
    && rm -rf /usr/share/doc/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Copy built frontend assets from frontend-builder
COPY --from=frontend-builder /app/public/build ./public/build

# Install PHP dependencies, clean up vendor, and optimize
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader \
    && composer clear-cache \
    # Remove unnecessary files from vendor
    && find vendor -type d -name "test" -o -name "tests" -o -name "Tests" -o -name "testing" | xargs rm -rf \
    && find vendor -type d -name "doc" -o -name "docs" -o -name "example" -o -name "examples" | xargs rm -rf \
    && find vendor -type d -name ".git" | xargs rm -rf \
    && find vendor -name "*.md" -o -name "*.txt" -o -name "*.rst" -o -name "composer.json" | xargs rm -f \
    && find vendor -name "phpunit.xml*" -o -name "phpcs.xml*" -o -name ".travis.yml" -o -name ".gitignore" -o -name ".gitattributes" | xargs rm -f \
    && find vendor -name "LICENSE*" -o -name "CHANGELOG*" -o -name "CONTRIBUTING*" -o -name "UPGRADE*" | xargs rm -f \
    # Remove composer and cache
    && rm -rf /root/.composer \
    && rm -f /usr/bin/composer \
    # Remove development files
    && rm -rf tests \
    && rm -rf .git .github \
    && find . -maxdepth 1 -name "*.md" ! -name "README.md" -delete

# Copy Caddyfile for FrankenPHP
COPY Caddyfile /etc/caddy/Caddyfile

# Create entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/

# Set permissions, clean storage, and finalize in single layer
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    # Clean storage directories but keep .gitkeep files
    && find storage/logs -type f ! -name ".gitkeep" -delete 2>/dev/null || true \
    && find storage/framework/cache -type f ! -name ".gitkeep" -delete 2>/dev/null || true \
    && find storage/framework/sessions -type f ! -name ".gitkeep" -delete 2>/dev/null || true \
    && find storage/framework/views -type f ! -name ".gitkeep" -delete 2>/dev/null || true \
    && rm -rf storage/debugbar 2>/dev/null || true \
    # Set proper permissions
    && chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Stage 4: Final production image
FROM dunglas/frankenphp:latest-php8.3-alpine

# Install only runtime PHP extensions (no build tools)
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
    opcache \
    # Clean up immediately
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/* \
    && rm -rf /usr/share/man/* \
    && rm -rf /usr/share/doc/*

WORKDIR /app

# Copy only necessary files from builder
COPY --from=builder --chown=www-data:www-data /app /app
COPY --from=builder /usr/local/bin/docker-entrypoint.sh /usr/local/bin/
COPY --from=builder /etc/caddy/Caddyfile /etc/caddy/Caddyfile

# Expose port 8000 (Octane default)
EXPOSE 8000

# Health check (updated for Octane port)
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8000/api/health || exit 1

# Use entrypoint script
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Start Laravel Octane with FrankenPHP
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000", "--admin-port=2019", "--max-requests=500"]
