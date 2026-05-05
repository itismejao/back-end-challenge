# ============================================
# Stage 1: Base image (shared by dev & prod)
# ============================================
FROM php:8.4-cli-alpine AS base

LABEL maintainer="NeoEsTech <jneto@neoestech.com>"
LABEL description="Laravel 12 + Octane (Swoole)"

# Build dependencies (removed after extension install)
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        curl-dev \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        linux-headers \
        oniguruma-dev \
        openssl-dev \
    && apk add --no-cache \
        curl \
        freetype \
        git \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libwebp \
        libzip \
        oniguruma \
        supervisor \
        unzip \
    # PHP extensions
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    # Swoole + Redis via PECL
    && pecl install swoole redis \
    && docker-php-ext-enable swoole redis \
    # Cleanup
    && apk del .build-deps \
    && rm -rf /tmp/pear /var/cache/apk/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP configuration
COPY docker/app/php.ini     $PHP_INI_DIR/conf.d/99-app.ini
COPY docker/app/opcache.ini $PHP_INI_DIR/conf.d/10-opcache.ini

# Create non-root user
RUN addgroup -g 1000 -S appuser \
    && adduser -u 1000 -S appuser -G appuser

WORKDIR /var/www

# Entrypoint (handles first-run bootstrap)
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000", "--workers=auto", "--task-workers=auto"]

# ============================================
# Stage 2: Production (baked application)
# ============================================
FROM base AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --no-scripts \
    --prefer-dist
COPY . .
RUN composer dump-autoload \
    --optimize \
    --classmap-authoritative \
    --no-dev

# ============================================
# Stage 3: Frontend assets
# ============================================
FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then \
        npm ci --no-audit --no-fund; \
    fi
COPY . .
RUN if [ -f vite.config.js ] || [ -f vite.config.ts ]; then \
        npm run build; \
    else \
        mkdir -p public/build; \
    fi

# ============================================
# Stage 4: Production final image
# ============================================
FROM base AS production

# Copy application with dependencies baked in
COPY --chown=appuser:appuser . .
COPY --chown=appuser:appuser --from=vendor /app/vendor ./vendor
COPY --chown=appuser:appuser --from=assets /app/public/build ./public/build

# Pre-cache framework files
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Remove unnecessary files from final image
RUN rm -rf \
    .git .github docker tests node_modules \
    storage/logs/*.log .env.example \
    phpunit.xml vite.config.* package*.json

# Fix storage permissions
RUN chown -R appuser:appuser storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER appuser

HEALTHCHECK --interval=15s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -f http://localhost:8000/up || exit 1
