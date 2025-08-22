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
    unzip

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir le repertoire de travail
WORKDIR /var/www/html

# Copier composer.json et composer.lock d'abord
COPY composer.json composer.lock ./

# Installer les dependances (sans les scripts automatiques)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Installer Symfony Runtime (sans scripts)
RUN composer require symfony/runtime --no-interaction --no-scripts

# Copier le reste des fichiers
COPY . .

# Configurer Apache
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Configurer les permissions et creer les repertoires necessaires
RUN chown -R www-data:www-data /var/www/html
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/logs
RUN chmod -R 755 /var/www/html/var

# Creer le repertoire pour les uploads
RUN mkdir -p /var/www/html/public/uploads/images && \
    chown -R www-data:www-data /var/www/html/public/uploads

# Script de demarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]

