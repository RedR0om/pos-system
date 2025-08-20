# Use PHP 8.1 with Apache
FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache modules
RUN a2enmod rewrite

# Create entrypoint script
RUN echo '#!/bin/bash\n\
# Wait for database to be ready\n\
echo "Waiting for database connection..."\n\
until php artisan migrate:status > /dev/null 2>&1; do\n\
    echo "Database not ready. Waiting..."\n\
    sleep 5\n\
done\n\
\n\
# Run migrations\n\
echo "Running database migrations..."\n\
php artisan migrate --force\n\
\n\
# Run seeders if no users exist\n\
if ! php artisan tinker --execute="echo App\\Models\\User::count();" 2>/dev/null | grep -q "[1-9]"; then\n\
    echo "Running database seeders..."\n\
    php artisan db:seed --force\n\
fi\n\
\n\
# Generate application key if not exists\n\
if [ ! -f .env ] || ! grep -q "APP_KEY=base64:" .env; then\n\
    echo "Generating application key..."\n\
    php artisan key:generate --force\n\
fi\n\
\n\
# Clear and cache config\n\
echo "Optimizing application..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Start Apache\n\
echo "Starting Apache..."\n\
apache2-foreground' > /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80
EXPOSE 80

# Use entrypoint script
CMD ["/usr/local/bin/entrypoint.sh"]
