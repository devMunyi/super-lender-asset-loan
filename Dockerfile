# Use the official PHP 7.4 FPM image as the base
FROM php:7.4-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    git \
    curl \
    vim \
    unzip \
    pkg-config \
    libmagickwand-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg=/usr/include/ && \
    docker-php-ext-install gd pdo pdo_mysql mbstring zip exif pcntl bcmath xml && \
    pecl install imagick memcached && \
    docker-php-ext-enable imagick memcached

# Set the working directory
WORKDIR /var/www/html

# Copy application files (if needed)
COPY ./ /var/www/html

# Configure PHP to use Memcached for sessions
RUN echo "session.save_handler = memcached" >> /usr/local/etc/php/conf.d/memcached.ini && \
    echo "session.save_path = \"127.0.0.1:11211\"" >> /usr/local/etc/php/conf.d/memcached.ini && \
    echo "session.lazy_write = Off" >> /usr/local/etc/php/conf.d/memcached.ini && \
    echo "memcached.sess_locking = Off" >> /usr/local/etc/php/conf.d/memcached.ini

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
