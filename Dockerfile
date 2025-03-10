# Use the official PHP CLI image instead of FPM since we're using artisan serve
FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (production only)
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# Copy the entire application
COPY . .

# Generate optimized autoload files and run post-install hooks
RUN composer dump-autoload --optimize

# Set permissions for Laravel
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Expose port 8000 for Laravel's built-in server
EXPOSE 8000

# Environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false

# Generate application key if not present
RUN if [ ! -f ".env" ]; then cp .env.example .env && php artisan key:generate; fi

# Start the application
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]