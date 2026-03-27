# -------------------------------------------------------------------
# 1. Base Image
# -------------------------------------------------------------------
# We use the official PHP 8.2 FPM image (FastCGI Process Manager)
FROM php:8.2-fpm

# -------------------------------------------------------------------
# 2. Set Working Directory
# -------------------------------------------------------------------
WORKDIR /var/www

# -------------------------------------------------------------------
# 3. Install System Dependencies
# -------------------------------------------------------------------
# These are required by Laravel and various PHP extensions
RUN apt-get update && apt-get install -y \
    build-essential \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nano

# Clear out the local repository of retrieved package files to reduce image size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# -------------------------------------------------------------------
# 4. Install PHP Extensions
# -------------------------------------------------------------------
# Configure GD library for image manipulation (e.g., Course Thumbnails)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Redis Extension via PECL (Crucial for Caching and Job Queues)
RUN pecl install redis && docker-php-ext-enable redis

# -------------------------------------------------------------------
# 5. Install Composer
# -------------------------------------------------------------------
# Copy the Composer executable directly from the official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -------------------------------------------------------------------
# 6. Copy Application Code
# -------------------------------------------------------------------
# Copy all of your Laravel files from your local machine into the container
COPY . /var/www

# -------------------------------------------------------------------
# 7. Install PHP Dependencies (Composer)
# -------------------------------------------------------------------
# We run composer install inside the container. 
# NOTE: If deploying to actual production, add --no-dev --optimize-autoloader
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# -------------------------------------------------------------------
# 8. Set Directory Permissions
# -------------------------------------------------------------------
# Ensure the web server (www-data) owns the files and has permission 
# to write to the storage and cache directories.
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# -------------------------------------------------------------------
# 9. Expose Port & Start Server
# -------------------------------------------------------------------
# Expose port 9000 and start the PHP-FPM server
EXPOSE 9000
CMD ["php-fpm"]