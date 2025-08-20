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

# Copy application files first
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

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
set -e\n\
\n\
echo "Starting POS System..."\n\
\n\
# Create .env file from environment variables if it doesn\'t exist\n\
if [ ! -f .env ]; then\n\
    echo "Creating .env file from environment variables..."\n\
    cat > .env << EOF\n\
APP_NAME="POS System"\n\
APP_ENV=production\n\
APP_DEBUG=false\n\
APP_URL=\${APP_URL:-http://localhost}\n\
\n\
LOG_CHANNEL=stack\n\
LOG_DEPRECATIONS_CHANNEL=null\n\
LOG_LEVEL=debug\n\
\n\
DB_CONNECTION=mysql\n\
DB_HOST=\${DB_HOST:-gondola.proxy.rlwy.net}\n\
DB_PORT=\${DB_PORT:-14668}\n\
DB_DATABASE=\${DB_DATABASE:-railway}\n\
DB_USERNAME=\${DB_USERNAME:-root}\n\
DB_PASSWORD=\${DB_PASSWORD:-BYwycoigujQGKRloFfbWIotCZfaIKWrZ}\n\
\n\
CACHE_DRIVER=file\n\
SESSION_DRIVER=file\n\
SESSION_LIFETIME=120\n\
QUEUE_CONNECTION=sync\n\
\n\
CLOUDINARY_URL=\${CLOUDINARY_URL:-}\n\
CLOUDINARY_UPLOAD_PRESET=\${CLOUDINARY_UPLOAD_PRESET:-}\n\
EOF\n\
fi\n\
\n\
# Generate application key if not exists\n\
if ! grep -q "APP_KEY=base64:" .env; then\n\
    echo "Generating application key..."\n\
    php artisan key:generate --force\n\
fi\n\
\n\
# Test database connection with timeout\n\
echo "Testing database connection..."\n\
timeout=60\n\
counter=0\n\
while [ $counter -lt $timeout ]; do\n\
    if php artisan migrate:status > /dev/null 2>&1; then\n\
        echo "✓ Database connection successful!"\n\
        break\n\
    fi\n\
    echo "Database not ready. Waiting... ($counter/$timeout)"\n\
    sleep 5\n\
    counter=$((counter + 5))\n\
done\n\
\n\
if [ $counter -ge $timeout ]; then\n\
    echo "✗ Database connection timeout. Starting anyway..."\n\
fi\n\
\n\
# Run migrations\n\
echo "Running database migrations..."\n\
php artisan migrate --force || echo "Migration failed, continuing..."\n\
\n\
# Clear and cache config\n\
echo "Optimizing application..."\n\
php artisan config:cache || echo "Config cache failed, continuing..."\n\
php artisan route:cache || echo "Route cache failed, continuing..."\n\
php artisan view:cache || echo "View cache failed, continuing..."\n\
\n\
# Start Apache\n\
echo "Starting Apache..."\n\
apache2-foreground' > /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80
EXPOSE 80

# Use entrypoint script
CMD ["/usr/local/bin/entrypoint.sh"]
