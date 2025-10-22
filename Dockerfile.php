FROM php:8.2-fpm-alpine

ARG PIMP_ENV=development

# Install system dependencies including MySQL development libraries
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    sqlite \
    sqlite-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    openssl-dev \
    mysql-client \
    mysql-dev \
    postgresql-dev \
    mongodb-tools \
    redis \
    autoconf \
    g++ \
    make

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        mysqli \
        bcmath \
        ctype \
        dom \
        fileinfo \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        tokenizer \
        xml \
        zip \
        sockets

# Install MongoDB extension
RUN pecl install mongodb && \
    docker-php-ext-enable mongodb

# Install Redis extension
RUN pecl install redis && \
    docker-php-ext-enable redis

# Clean up build dependencies
RUN apk del autoconf g++ make

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create user and set permissions
RUN addgroup -g 1000 -S pimp && \
    adduser -u 1000 -S pimp -G pimp

# Create necessary directories
RUN mkdir -p /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
             /var/www/html/config/database && \
    chown -R pimp:pimp /var/www/html && \
    chmod -R 755 /var/www/html/storage

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY --chown=pimp:pimp composer.json composer.lock* ./

# Switch to pimp user
USER pimp

# Install PHP dependencies
RUN if [ "$PIMP_ENV" = "production" ]; then \
        composer install --no-dev --no-scripts --optimize-autoloader; \
    else \
        composer install --no-scripts --optimize-autoloader; \
    fi

# Copy application files
USER root
COPY --chown=pimp:pimp . .
USER pimp

# Generate autoload files
RUN composer dump-autoload --optimize

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php -r "echo 'OK';" || exit 1

EXPOSE 9000

CMD ["php-fpm"]