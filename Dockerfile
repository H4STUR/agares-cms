# Use PHP 8.3 with Apache
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    unzip \
    git \
 && rm -rf /var/lib/apt/lists/*

# Install Node.js 20.x (with npm bundled)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

# Set the document root to /var/www/public
WORKDIR /var/www
RUN sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf

# Copy package files first (better build cache for npm)
COPY package*.json ./

# Install Node.js dependencies
RUN npm install

# Copy rest of the app
COPY --chown=www-data:www-data . /var/www

# Switch to www-data for runtime
USER www-data

# Expose Apache port
EXPOSE 80

# Run npm dev + Apache together (dev mode)
CMD ["bash", "-c", "npm run dev & apache2-foreground"]
