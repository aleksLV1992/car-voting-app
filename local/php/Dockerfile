FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libz-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install Redis extension from GitHub
RUN curl -fsSL https://github.com/phpredis/phpredis/archive/6.1.0.tar.gz | tar xz \
    && cd phpredis-6.1.0 \
    && phpize \
    && ./configure \
    && make && make install \
    && cd .. && rm -rf phpredis-6.1.0 \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN useradd -G www-data,root -u 1000 -d /home/appuser appuser
RUN mkdir -p /home/appuser/.composer && \
    chown -R appuser:appuser /home/appuser

# Set working directory
WORKDIR /var/www/html

# Copy custom PHP config
COPY local.ini /usr/local/etc/php/conf.d/local.ini

# Set permissions
RUN chown -R appuser:www-data /var/www/html

USER appuser

EXPOSE 9000
CMD ["php-fpm"]
