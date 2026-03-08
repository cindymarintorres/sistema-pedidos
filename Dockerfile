FROM php:8.3-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Traer Composer desde su imagen oficial
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Configurar DocumentRoot de Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copiar solo composer.json primero (aprovecha el cache de Docker)
COPY composer.json composer.lock ./

# Instalar dependencias PHP dentro del contenedor
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Copiar el resto del código
COPY . .

# Permisos para Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

EXPOSE 80