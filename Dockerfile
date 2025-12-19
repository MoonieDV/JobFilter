FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    default-libmysqlclient-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install \
    && npm run build

# Create necessary directories
RUN mkdir -p storage/logs storage/app/public \
    && chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Set environment variables for production
ENV APP_ENV=production
ENV APP_DEBUG=false

# Run migrations and start server (guard .env.example, use $PORT and tolerate migrate failures)
CMD sh -c "if [ ! -f .env ]; then if [ -f .env.example ]; then cp .env.example .env; fi; fi && \
    if [ -z \"$${APP_KEY}\" ]; then php artisan key:generate --force; fi && \
    php artisan migrate --force || true && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"
