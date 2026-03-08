FROM php:8.3-apache

# Habilitar mod_rewrite para que las rutas de Laravel funcionen
RUN a2enmod rewrite

# Instalar dependencias del sistema y extensiones PHP requeridas por Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar Composer desde su imagen oficial sin instalarlo manualmente
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Apuntar Apache al directorio público de Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Reemplazar el DocumentRoot por defecto de Apache con el de Laravel
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Definir el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# Copiar solo los archivos de dependencias primero para aprovechar el caché de Docker
COPY composer.json composer.lock ./

# Instalar dependencias PHP sin paquetes de desarrollo
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copiar el resto del código fuente
COPY . .

# Crear directorios necesarios y asignar permisos de escritura a Laravel
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar y dar permisos al script de entrada
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]