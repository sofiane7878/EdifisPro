FROM php:8.2-apache

# Installer les dependances systeme
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer OPcache pour les performances en production
RUN docker-php-ext-install opcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/php.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/php.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/php.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/php.ini

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir le repertoire de travail
WORKDIR /var/www/html

# Copier composer.json et composer.lock d'abord
COPY composer.json composer.lock ./

# Installer les dependances (sans scripts automatiques)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Installer Symfony Runtime (sans scripts)
RUN composer require symfony/runtime --no-interaction --no-scripts

# Copier le reste des fichiers
COPY . .

# Copier .env.example en .env pour le conteneur
COPY .env.example .env

# Configurer Apache
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Configurer les permissions et creer les repertoires necessaires
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/logs
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 777 /var/www/html/var
RUN chmod -R 777 /var/www/html/public/uploads

# Creer le repertoire pour les uploads
RUN mkdir -p /var/www/html/public/uploads/images && \
    chown -R www-data:www-data /var/www/html/public/uploads && \
    chmod -R 777 /var/www/html/public/uploads

# Script de demarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

# Health check pour Railway
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

CMD ["/bin/bash", "/start.sh"]

