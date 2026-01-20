FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Cache config and routes
RUN php artisan config:cache && php artisan route:cache

# Expose port
EXPOSE 8080

# Start server
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
